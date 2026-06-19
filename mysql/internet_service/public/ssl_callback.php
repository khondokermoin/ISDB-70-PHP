<?php
require_once '../config/database.php';
require_once '../config/ssl_config.php';

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

    // ১. SSLCommerz Validation
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

        try {
            $db->beginTransaction();

            // ইনভয়েস এবং সাবস্ক্রিপশনের ডাটা আনা
            $inv = $db->prepare("
                SELECT i.user_id, i.amount, i.subscription_id, i.invoice_number, 
                       s.end_date, s.status AS sub_status, p.duration_days, p.package_id, u.full_name
                FROM invoices i 
                LEFT JOIN subscriptions s ON i.subscription_id = s.subscription_id 
                LEFT JOIN packages p ON s.package_id = p.package_id 
                JOIN users u ON i.user_id = u.user_id
                WHERE i.invoice_id = ?
            ");
            $inv->execute([$invoice_id]);
            $invData = $inv->fetch();

            if (!$invData) {
                header("Location: user_dashboard.php?msg=invoice_not_found");
                exit;
            }

            // অ্যামাউন্ট চেক
            if ((float)$validationResponse['amount'] >= (float)$invData['amount']) {

                // ১. ইনভয়েস পেইড করা
                $db->prepare("UPDATE invoices SET status = 'paid' WHERE invoice_id = ?")->execute([$invoice_id]);

                // ২. পেমেন্ট রেকর্ড ইনসার্ট করা
                $db->prepare("
                    INSERT INTO payments (invoice_id, user_id, amount, method, transaction_ref) 
                    VALUES (?, ?, ?, 'SSLCommerz', ?)
                ")->execute([$invoice_id, $invData['user_id'], $invData['amount'], $trx_id]);

                $target_package_id = $invData['package_id'];
                $duration_days = $invData['duration_days'] ?? 30;
                $is_upgrade = (strpos($invData['invoice_number'], 'UPG-') === 0);

                $notif_msg = "";

                // =========================================================
                // 🔥 প্যাকেজ আপগ্রেড লজিক (Crash-proof)
                // =========================================================
                if ($is_upgrade) {
                    $invoice_parts = explode('-', $invData['invoice_number']);
                    $new_pkg_id = (isset($invoice_parts[1])) ? (int)$invoice_parts[1] : $target_package_id;

                    // 🔥 নতুন লজিক: ডাটাবেস থেকে প্যাকেজের আসল নামটি (Name) খুঁজে বের করা
                    $pkgStmt = $db->prepare("SELECT name FROM packages WHERE package_id = ?");
                    $pkgStmt->execute([$new_pkg_id]);
                    $pkgInfo = $pkgStmt->fetch(PDO::FETCH_ASSOC);
                    $new_pkg_name = $pkgInfo ? $pkgInfo['name'] : "Unknown Package";

                    $db->prepare("
                        UPDATE subscriptions 
                        SET package_id = ?, status = 'active', end_date = DATE_ADD(CURDATE(), INTERVAL ? DAY) 
                        WHERE subscription_id = ?
                    ")->execute([$new_pkg_id, $duration_days, $invData['subscription_id']]);

                    $db->prepare("UPDATE users SET status = 'active' WHERE user_id = ?")->execute([$invData['user_id']]);

                    // 🔥 আপডেট করা আপগ্রেড মেসেজ (এখন ID এর বদলে $new_pkg_name দেখাবে)
                    $notif_msg = "Upgrade Payment: {$invData['full_name']} paid #{$invData['invoice_number']} via SSLCommerz. Action: Update Mikrotik Speed (Pkg: {$new_pkg_name}).";
                } else {
                    // =========================================================
                    // 🔥 নতুন কানেকশন এবং মাসিক রিনিউয়াল লজিক
                    // =========================================================
                    $sub_status = $invData['sub_status'] ?? 'pending';
                    $current_expiry = $invData['end_date'];

                    // 🔥 FIX: end_date ফাঁকা থাকলে বা status pending/inactive থাকলে সেটাকে ১০০% নতুন কানেকশন ধরবে!
                    if (empty($current_expiry) || in_array($sub_status, ['pending', 'inactive'])) {

                        // সাবস্ক্রিপশন স্ট্যাটাস জোর করে 'pending' করে রাখা হচ্ছে, যাতে admin activate করার আগ পর্যন্ত লাইন চালু না হয়।
                        $db->prepare("UPDATE subscriptions SET status = 'pending' WHERE subscription_id = ?")->execute([$invData['subscription_id']]);

                        // আপডেট করা নতুন কানেকশন মেসেজ
                        $notif_msg = "New Connection Payment: {$invData['full_name']} paid #{$invData['invoice_number']} via SSLCommerz. Action: Assign Field Engineer.";
                    } else {
                        // Monthly Renewal Logic (আগের মতোই থাকবে)
                        $base_date = ($current_expiry && strtotime($current_expiry) > time()) ? $current_expiry : date('Y-m-d');

                        $db->prepare("
                            UPDATE subscriptions 
                            SET status = 'active', end_date = DATE_ADD(?, INTERVAL ? DAY) 
                            WHERE subscription_id = ?
                        ")->execute([$base_date, $duration_days, $invData['subscription_id']]);

                        $db->prepare("UPDATE users SET status = 'active' WHERE user_id = ?")->execute([$invData['user_id']]);

                        $notif_msg = "Renewal Payment: {$invData['full_name']} paid #{$invData['invoice_number']} via SSLCommerz. Auto-extended.";
                    }
                }

                // ৩. অ্যাডমিন নোটিফিকেশন পাঠানো
                $adminQuery = $db->query("SELECT user_id FROM users WHERE role = 'admin' LIMIT 1")->fetch();
                if ($adminQuery && !empty($notif_msg)) {
                    $db->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")->execute([$adminQuery['user_id'], $notif_msg]);
                }

                $db->commit();
                header("Location: user_dashboard.php?msg=payment_success&trx=" . $trx_id);
                exit;
            } else {
                header("Location: user_dashboard.php?msg=amount_mismatch");
                exit;
            }
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
