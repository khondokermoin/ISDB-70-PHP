<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['invoice_id']) || !isset($_SESSION['bkash_token'])) {
    header("Location: user_dashboard.php");
    exit;
}

require_once '../config/database.php';
require_once '../config/bkash_config.php';

$db = (new Database())->getConnection();
$invoice_id = (int)$_GET['invoice_id'];
$status = $_GET['status'] ?? '';
$paymentID = $_GET['paymentID'] ?? '';

if ($status !== 'success' || empty($paymentID)) {
    header("Location: user_dashboard.php?msg=payment_cancelled_or_failed");
    exit;
}

// ৩. Execute Payment API Call
$post_execute = json_encode(['paymentID' => $paymentID]);

$url = BKASH_BASE_URL . '/tokenized/checkout/execute';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: ' . $_SESSION['bkash_token'],
    'X-APP-Key: ' . BKASH_APP_KEY
]);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_execute);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
$execute_response = curl_exec($ch);
curl_close($ch);

$execute_data = json_decode($execute_response, true);

if (isset($execute_data['transactionStatus']) && $execute_data['transactionStatus'] === 'Completed') {
    $trx_id = $execute_data['trxID'];

    try {
        $db->beginTransaction();

        // ইনভয়েস, সাবস্ক্রিপশন এবং বর্তমান প্যাকেজের ডাটা আনা
        $inv = $db->prepare("
            SELECT i.user_id, i.amount, i.subscription_id, i.invoice_number, s.end_date, p.duration_days, p.package_id
            FROM invoices i 
            JOIN subscriptions s ON i.subscription_id = s.subscription_id 
            JOIN packages p ON s.package_id = p.package_id 
            WHERE i.invoice_id = ?
        ");
        $inv->execute([$invoice_id]);
        $invData = $inv->fetch();

        if ($invData) {
            // ১. ইনভয়েস স্ট্যাটাস 'paid' করা
            $db->prepare("UPDATE invoices SET status = 'paid' WHERE invoice_id = ?")->execute([$invoice_id]);

            // ২. পেমেন্ট রেকর্ড ইনসার্ট করা
            $db->prepare("
                INSERT INTO payments (invoice_id, user_id, amount, method, transaction_ref) 
                VALUES (?, ?, ?, 'bKash', ?)
            ")->execute([$invoice_id, $invData['user_id'], $invData['amount'], $trx_id]);

            // ডিফল্ট ভ্যালু
            $target_package_id = $invData['package_id'];
            $duration_days = $invData['duration_days'];
            $is_upgrade = (strpos($invData['invoice_number'], 'UPG-') === 0);

            // =========================================================
            // 🔥 PROFESSIONAL ISP UPGRADE & RENEWAL LOGIC
            // =========================================================
            if ($is_upgrade) {
                // ৩ (ক). আপগ্রেড লজিক: প্যাকেজ পরিবর্তন হবে এবং মেয়াদ "আজ থেকে" নতুন করে শুরু হবে
                $invoice_parts = explode('-', $invData['invoice_number']);
                $new_pkg_id = (isset($invoice_parts[1])) ? (int)$invoice_parts[1] : $target_package_id;

                // নতুন প্যাকেজের দিন (যেমন: 30) আনা
                $pkgStmt = $db->prepare("SELECT duration_days FROM packages WHERE package_id = ?");
                $pkgStmt->execute([$new_pkg_id]);
                $newPkg = $pkgStmt->fetch();
                $duration_days = $newPkg ? $newPkg['duration_days'] : 30;

                // সাবস্ক্রিপশন আপডেট (পুরো সাইকেল রিসেট করে আজ থেকে শুরু করা হলো)
                $db->prepare("
                    UPDATE subscriptions 
                    SET package_id = ?, status = 'active', start_date = CURDATE(), end_date = DATE_ADD(CURDATE(), INTERVAL ? DAY) 
                    WHERE subscription_id = ?
                ")->execute([$new_pkg_id, $duration_days, $invData['subscription_id']]);

                // ইনভয়েসের period_start এবং period_end আপডেট করা (যাতে NULL না থাকে)
                $db->prepare("UPDATE invoices SET period_start = CURDATE(), period_end = DATE_ADD(CURDATE(), INTERVAL ? DAY) WHERE invoice_id = ?")
                    ->execute([$duration_days, $invoice_id]);
            } else {
                // ৩ (খ). সাধারণ রিনিউ লজিক: বর্তমান মেয়াদের শেষ দিন থেকে পরবর্তী ৩০ দিন যোগ হবে
                $current_expiry = $invData['end_date'];
                $base_date = ($current_expiry && strtotime($current_expiry) > time()) ? $current_expiry : date('Y-m-d');

                $db->prepare("
                    UPDATE subscriptions 
                    SET status = 'active', end_date = DATE_ADD(?, INTERVAL ? DAY) 
                    WHERE subscription_id = ?
                ")->execute([$base_date, $duration_days, $invData['subscription_id']]);

                // ইনভয়েসের period আপডেট
                $db->prepare("UPDATE invoices SET period_start = ?, period_end = DATE_ADD(?, INTERVAL ? DAY) WHERE invoice_id = ?")
                    ->execute([$base_date, $base_date, $duration_days, $invoice_id]);
            }
            // =========================================================

            // ৪. ইউজার একাউন্ট অ্যাক্টিভ করা (নতুন লজিক: সব বকেয়া ক্লিয়ার হলেই কেবল একটিভ হবে)
            $checkDues = $db->prepare("
                SELECT COUNT(*) FROM invoices 
                WHERE user_id = ? AND status = 'unpaid' AND invoice_id != ?
            ");
            $checkDues->execute([$invData['user_id'], $invoice_id]);
            $pending_dues = $checkDues->fetchColumn();

            if ($pending_dues == 0) {
                // ইউজারের আর কোনো ডিউ নেই, তাই তার একাউন্ট এবং সাবস্ক্রিপশন পুনরায় চালু করা হচ্ছে
                $db->prepare("UPDATE users SET status = 'active' WHERE user_id = ?")->execute([$invData['user_id']]);

                // সাবস্ক্রিপশন যদি সাসপেন্ড থাকে, সেটাও একটিভ করে দিন
                $db->prepare("UPDATE subscriptions SET status = 'active' WHERE subscription_id = ?")->execute([$invData['subscription_id']]);
            }

            // ৫. আপগ্রেড হলে সাপোর্ট টিকিট অটোমেটিক ক্লোজ (Resolved) করে দেওয়া
            if ($is_upgrade) {
                $db->prepare("UPDATE tickets SET status = 'resolved' WHERE user_id = ? AND category = 'Package Upgrade' AND status != 'resolved'")
                    ->execute([$invData['user_id']]);
            }

            // ৬. অ্যাডমিন নোটিফিকেশন
            $adminQuery = $db->query("SELECT user_id FROM users WHERE role = 'admin' LIMIT 1")->fetch();
            if ($adminQuery) {
                $notif_msg = $is_upgrade
                    ? "🚀 Package Upgraded Auto: Invoice #$invoice_id paid via bKash. User package updated and ticket resolved."
                    : "✅ bKash Payment Received: Invoice #$invoice_id auto-verified. Validity extended.";

                $db->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")->execute([$adminQuery['user_id'], $notif_msg]);
            }
        }

        $db->commit();
        unset($_SESSION['bkash_token']);
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
