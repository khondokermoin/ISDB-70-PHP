<?php
require_once '../config/database.php';
require_once '../config/ssl_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../vendor/autoload.php';

$db = (new Database())->getConnection();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$status = $_GET['status'] ?? '';

if ($status == 'fail' || $status == 'cancel') {
    header("Location: user_dashboard.php?msg=payment_failed");
    exit;
}

if ($status == 'success' && isset($_POST['val_id'])) {
    $val_id = $_POST['val_id'];
    $invoice_id = (int)$_GET['inv_id'];

    // ==========================================
    // ⚡ PHASE 1: SSLCommerz Validation
    // ==========================================
    $val_url = SSL_BASE_URL . "/validator/api/validationserverAPI.php?val_id=" . $val_id . "&store_id=" . SSL_STORE_ID . "&store_passwd=" . SSL_STORE_PASSWORD . "&v=1&format=json";

    $handle = curl_init();
    curl_setopt($handle, CURLOPT_URL, $val_url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($handle);
    curl_close($handle);

    $validationResponse = json_decode($result, true);

    if (isset($validationResponse['status']) && in_array($validationResponse['status'], ['VALID', 'VALIDATED'])) {
        $trx_id = $validationResponse['tran_id'];
        $payment_success = false;

        // ==========================================
        // ⚡ PHASE 2: FAST & SECURE DB TRANSACTION
        // ==========================================
        try {
            $db->beginTransaction();

            // FOR UPDATE দিয়ে Row Lock করা হলো যাতে ডাবল পেমেন্ট না হয়
            $inv = $db->prepare("
                SELECT i.user_id, i.amount, i.subscription_id, i.invoice_number, 
                       s.end_date, s.status AS sub_status, p.duration_days, p.package_id, p.name AS package_name, 
                       u.full_name, u.email
                FROM invoices i 
                LEFT JOIN subscriptions s ON i.subscription_id = s.subscription_id 
                LEFT JOIN packages p ON s.package_id = p.package_id 
                JOIN users u ON i.user_id = u.user_id
                WHERE i.invoice_id = ? FOR UPDATE
            ");
            $inv->execute([$invoice_id]);
            $invData = $inv->fetch();

            if (!$invData) throw new Exception("invoice_not_found");
            if ((float)$validationResponse['amount'] < (float)$invData['amount']) throw new Exception("amount_mismatch");

            // ১. ইনভয়েস ও পেমেন্ট আপডেট
            $db->prepare("UPDATE invoices SET status = 'paid' WHERE invoice_id = ?")->execute([$invoice_id]);
            $db->prepare("INSERT INTO payments (invoice_id, user_id, amount, method, transaction_ref) VALUES (?, ?, ?, 'SSLCommerz', ?)")
                ->execute([$invoice_id, $invData['user_id'], $invData['amount'], $trx_id]);

            $is_upgrade = (strpos($invData['invoice_number'], 'UPG-') === 0);
            $final_package_name = $invData['package_name'] ?? 'Internet Subscription';
            $notif_msg = "";
            $duration = $invData['duration_days'] ?? 30;

            // ২. প্যাকেজ/রিনিউয়াল লজিক
            if ($is_upgrade) {
                $parts = explode('-', $invData['invoice_number']);
                $new_pkg_id = $parts[1] ?? $invData['package_id'];

                $final_package_name = $db->query("SELECT name FROM packages WHERE package_id = " . (int)$new_pkg_id)->fetchColumn() ?: "Unknown Package";

                $db->prepare("UPDATE subscriptions SET package_id = ?, status = 'active', end_date = DATE_ADD(CURDATE(), INTERVAL ? DAY) WHERE subscription_id = ?")
                    ->execute([$new_pkg_id, $duration, $invData['subscription_id']]);

                $db->prepare("UPDATE users SET status = 'active' WHERE user_id = ?")->execute([$invData['user_id']]);

                $notif_msg = "Upgrade Payment: {$invData['full_name']} paid #{$invData['invoice_number']} via SSLCommerz. Action: Update Mikrotik Speed (Pkg: {$final_package_name}).";
            } else {
                $sub_status = $invData['sub_status'] ?? 'pending';
                if (empty($invData['end_date']) || in_array($sub_status, ['pending', 'inactive'])) {
                    $db->prepare("UPDATE subscriptions SET status = 'pending' WHERE subscription_id = ?")->execute([$invData['subscription_id']]);
                    $notif_msg = "New Connection Payment: {$invData['full_name']} paid #{$invData['invoice_number']} via SSLCommerz. Action: Assign Field Engineer.";
                } else {
                    $base_date = (strtotime($invData['end_date']) > time()) ? $invData['end_date'] : date('Y-m-d');
                    $db->prepare("UPDATE subscriptions SET status = 'active', end_date = DATE_ADD(?, INTERVAL ? DAY) WHERE subscription_id = ?")
                        ->execute([$base_date, $duration, $invData['subscription_id']]);

                    $db->prepare("UPDATE users SET status = 'active' WHERE user_id = ?")->execute([$invData['user_id']]);
                    $notif_msg = "Renewal Payment: {$invData['full_name']} paid #{$invData['invoice_number']} via SSLCommerz. Auto-extended.";
                }
            }

            // ৩. অ্যাডমিন নোটিফিকেশন
            $admin_id = $db->query("SELECT user_id FROM users WHERE role = 'admin' LIMIT 1")->fetchColumn();
            if ($admin_id && $notif_msg) {
                $db->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")->execute([$admin_id, $notif_msg]);
            }

            // 🔥 ক্রিটিকাল ফিক্স: মেইল পাঠানোর আগেই ডাটাবেস সেভ করে লক রিলিজ করা হলো
            $db->commit();
            $payment_success = true;
        } catch (Exception $e) {
            $db->rollBack();
            header("Location: user_dashboard.php?msg=" . $e->getMessage());
            exit;
        }

        // ==========================================
        // 📧 PHASE 3: SLOW NETWORK CALL (PHPMailer)
        // ==========================================
        if ($payment_success) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'mouin2000ab@gmail.com'; // TODO: Update 
                $mail->Password   = 'jsbv camb ogyv yxjh';  // TODO: Update
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                $mail->setFrom('mouin2000ab@gmail.com', 'AMAR IT Billing');
                $mail->addAddress($invData['email'], $invData['full_name']);

                $mail->isHTML(true);
                $mail->Subject = "Payment Receipt - Invoice #{$invData['invoice_number']}";

                $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #ddd; padding: 20px; border-top: 4px solid #dc2626; border-radius: 8px;'>
                    <h2 style='color: #dc2626; text-align: center;'>AMAR IT</h2>
                    <h3 style='text-align: center; color: #333;'>Payment Receipt</h3>
                    <p>Dear <strong>{$invData['full_name']}</strong>,</p>
                    <p>Thank you for your payment. Your transaction was successful. Here are your payment details:</p>
                    
                    <table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>
                        <tr>
                            <td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>Invoice No:</strong></td>
                            <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>{$invData['invoice_number']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>Package:</strong></td>
                            <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>{$final_package_name}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>Paid Amount:</strong></td>
                            <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right; color: #16a34a; font-weight: bold;'>৳" . number_format($invData['amount'], 2) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>Date:</strong></td>
                            <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>" . date('d M Y, h:i A') . "</td>
                        </tr>
                    </table>
                    
                    <p style='margin-top: 30px; font-size: 14px; color: #555;'>If you have any questions, feel free to contact our support team.</p>
                    <p style='font-size: 14px; color: #555;'>Regards,<br><strong>AMAR IT Billing Team</strong></p>
                </div>";

                $mail->send();
            } catch (Exception $e) {
                error_log("Invoice Mail failed: {$mail->ErrorInfo}");
            }

            // মেইল পাঠানো শেষ হলে (বা ফেইল করলেও) কাস্টমারকে সাকসেস পেজে পাঠিয়ে দেওয়া হবে
            header("Location: user_dashboard.php?msg=payment_success&trx=" . $trx_id);
            exit;
        }
    } else {
        header("Location: user_dashboard.php?msg=payment_verification_failed");
        exit;
    }
} else {
    header("Location: user_dashboard.php?msg=invalid_request");
    exit;
}
