<?php
// session_start(); <-- এই লাইনটি মুছে ফেলা হয়েছে
require_once '../config/database.php';
require_once '../config/ssl_config.php';

$db = (new Database())->getConnection();
$status = $_GET['status'] ?? '';

if ($status == 'fail' || $status == 'cancel') {
    header("Location: user_dashboard.php?msg=payment_failed");
    exit;
}

if ($status == 'success' && isset($_POST['val_id'])) {
    $val_id = $_POST['val_id'];
    $invoice_id = (int)$_GET['inv_id'];

    // ১. SSLCommerz Validation API Call (পেমেন্ট আসলেই হয়েছে কি না চেক করা)
    $val_url = SSL_BASE_URL . "/validator/api/validationserverAPI.php?val_id=" . $val_id . "&store_id=" . SSL_STORE_ID . "&store_passwd=" . SSL_STORE_PASSWORD . "&v=1&format=json";

    $handle = curl_init();
    curl_setopt($handle, CURLOPT_URL, $val_url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($handle);
    curl_close($handle);

    $validationResponse = json_decode($result, true);

    if (isset($validationResponse['status']) && $validationResponse['status'] == 'VALID') {
        $trx_id = $validationResponse['tran_id']; // Transaction ID

        try {
            $db->beginTransaction();

            // ইনভয়েস, সাবস্ক্রিপশন এবং বর্তমান প্যাকেজের ডাটা আনা
            $inv = $db->prepare("
                SELECT i.user_id, i.amount, i.subscription_id, i.invoice_number, s.end_date, p.duration_days, p.package_id, u.full_name
                FROM invoices i 
                JOIN subscriptions s ON i.subscription_id = s.subscription_id 
                JOIN packages p ON s.package_id = p.package_id 
                JOIN users u ON i.user_id = u.user_id
                WHERE i.invoice_id = ?
            ");
            $inv->execute([$invoice_id]);
            $invData = $inv->fetch();

            if ($invData && $validationResponse['amount'] >= $invData['amount']) {

                // ১. ইনভয়েস পেইড করা
                $db->prepare("UPDATE invoices SET status = 'paid' WHERE invoice_id = ?")->execute([$invoice_id]);

                // ২. পেমেন্ট রেকর্ড ইনসার্ট করা
                $db->prepare("
                    INSERT INTO payments (invoice_id, user_id, amount, method, transaction_ref) 
                    VALUES (?, ?, ?, 'SSLCommerz', ?)
                ")->execute([$invoice_id, $invData['user_id'], $invData['amount'], $trx_id]);

                $target_package_id = $invData['package_id'];
                $duration_days = $invData['duration_days'];
                $is_upgrade = (strpos($invData['invoice_number'], 'UPG-') === 0);

                // =========================================================
                // 🔥 PROFESSIONAL UPGRADE & RENEWAL LOGIC
                // =========================================================
                if ($is_upgrade) {
                    $invoice_parts = explode('-', $invData['invoice_number']);
                    $new_pkg_id = (isset($invoice_parts[1])) ? (int)$invoice_parts[1] : $target_package_id;

                    $pkgStmt = $db->prepare("SELECT duration_days FROM packages WHERE package_id = ?");
                    $pkgStmt->execute([$new_pkg_id]);
                    $newPkg = $pkgStmt->fetch();
                    $duration_days = $newPkg ? $newPkg['duration_days'] : 30;

                    // সাবস্ক্রিপশন আপডেট (আজ থেকে শুরু)
                    $db->prepare("
                        UPDATE subscriptions 
                        SET package_id = ?, status = 'active', start_date = CURDATE(), end_date = DATE_ADD(CURDATE(), INTERVAL ? DAY) 
                        WHERE subscription_id = ?
                    ")->execute([$new_pkg_id, $duration_days, $invData['subscription_id']]);

                    // ইনভয়েসের period আপডেট
                    $db->prepare("UPDATE invoices SET period_start = CURDATE(), period_end = DATE_ADD(CURDATE(), INTERVAL ? DAY) WHERE invoice_id = ?")
                        ->execute([$duration_days, $invoice_id]);

                    // টিকিট ক্লোজ করা
                    $db->prepare("UPDATE tickets SET status = 'resolved' WHERE user_id = ? AND category = 'Package Upgrade' AND status != 'resolved'")
                        ->execute([$invData['user_id']]);
                } else {
                    // সাধারণ রিনিউ লজিক
                    $current_expiry = $invData['end_date'];
                    $base_date = ($current_expiry && strtotime($current_expiry) > time()) ? $current_expiry : date('Y-m-d');

                    $db->prepare("
                        UPDATE subscriptions 
                        SET status = 'active', end_date = DATE_ADD(?, INTERVAL ? DAY) 
                        WHERE subscription_id = ?
                    ")->execute([$base_date, $duration_days, $invData['subscription_id']]);

                    $db->prepare("UPDATE invoices SET period_start = ?, period_end = DATE_ADD(?, INTERVAL ? DAY) WHERE invoice_id = ?")
                        ->execute([$base_date, $base_date, $duration_days, $invoice_id]);
                }

                // ইউজার স্ট্যাটাস একটিভ করা
                $db->prepare("UPDATE users SET status = 'active' WHERE user_id = ?")->execute([$invData['user_id']]);

                // ৩. অ্যাডমিন নোটিফিকেশন
                $adminQuery = $db->query("SELECT user_id FROM users WHERE role = 'admin' LIMIT 1")->fetch();
                if ($adminQuery) {
                    $notif_msg = $is_upgrade
                        ? "🚀 Package Upgraded Auto: Invoice {$invData['invoice_number']} paid via SSLCommerz by {$invData['full_name']}. Package updated."
                        : "✅ SSLCommerz Payment: Invoice {$invData['invoice_number']} paid by {$invData['full_name']}. Validity extended.";

                    $db->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")->execute([$adminQuery['user_id'], $notif_msg]);
                }
            }

            $db->commit();
            header("Location: user_dashboard.php?msg=payment_success&trx=" . $trx_id);
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            header("Location: user_dashboard.php?msg=db_error");
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
