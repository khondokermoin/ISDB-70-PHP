<?php

require_once '../config/database.php';
$db = (new Database())->getConnection();

echo "Running ISP Cron Job...<br>\n";

try {

    $db->beginTransaction();

    /* ==========================================
       1. Expiry Warning (3 Days Remaining)
    ========================================== */

    // ফিক্স ১: PHP এর বদলে সরাসরি MySQL এর DATEDIFF() কুয়েরিতে নিয়ে আসা হলো
    $warningQuery = $db->query("
        SELECT
            u.user_id,
            u.full_name,
            u.email,
            s.end_date,
            DATEDIFF(s.end_date, CURDATE()) as days_left
        FROM users u
        INNER JOIN subscriptions s ON u.user_id = s.user_id
        WHERE
            u.status = 'active'
            AND s.status = 'active'
            AND DATEDIFF(s.end_date, CURDATE()) BETWEEN 0 AND 3
    ");

    $usersToNotify = $warningQuery->fetchAll(PDO::FETCH_ASSOC);

    foreach ($usersToNotify as $user) {

        // সরাসরি ডাটাবেস থেকে দিন নেওয়া হলো
        $daysLeft = (int)$user['days_left'];

        $message = "Your internet package will expire in {$daysLeft} day(s) on "
            . date('d M Y', strtotime($user['end_date']))
            . ". Please pay your bill to avoid interruption.";

        $check = $db->prepare("
            SELECT notification_id
            FROM notifications
            WHERE user_id = ?
            AND type = 'expiry_warning'
            AND DATE(sent_at) = CURDATE()
        ");

        $check->execute([$user['user_id']]);

        if ($check->rowCount() == 0) {

            $insert = $db->prepare("
                INSERT INTO notifications
                (user_id, type, message)
                VALUES (?, 'expiry_warning', ?)
            ");

            $insert->execute([$user['user_id'], $message]);

            if (!empty($user['email'])) {
                $subject = "Package Expiry Notice";
                $headers = "From: billing@yourisp.com\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                $body = "
                <h3>Internet Package Expiry Warning</h3>
                <p>{$message}</p>
                <p>Please make payment before expiry.</p>
                ";
                @mail($user['email'], $subject, $body, $headers);
            }
        }
    }


    /* ==========================================
       2. Invoice Warning & Auto Suspend
    ========================================== */

    $invoiceUsers = $db->query("
        SELECT
            user_id,
            COUNT(*) AS unpaid_count
        FROM invoices
        WHERE status = 'unpaid'
        GROUP BY user_id
    ");

    foreach ($invoiceUsers as $row) {

        $uid = $row['user_id'];
        $unpaidCount = (int)$row['unpaid_count'];

        // ইউজারের বর্তমান স্ট্যাটাস চেক করা (যাতে আগে থেকে সাসপেন্ড থাকলে লুপে না পড়ে)
        $userStatusQuery = $db->prepare("SELECT status FROM users WHERE user_id = ?");
        $userStatusQuery->execute([$uid]);
        $currentStatus = $userStatusQuery->fetchColumn();

        if ($unpaidCount == 1 || $unpaidCount == 2) {

            $type = ($unpaidCount == 1) ? 'billing_warning_1' : 'billing_warning_2';
            $msg = ($unpaidCount == 1)
                ? "Warning: You have 1 unpaid invoice."
                : "Final Warning: You have 2 unpaid invoices. Please pay immediately.";

            // ফিক্স ২: একই দিনে বারবার যেন মেসেজ না যায় তার চেক বসানো হলো
            $checkWarning = $db->prepare("SELECT notification_id FROM notifications WHERE user_id = ? AND type = ? AND DATE(sent_at) = CURDATE()");
            $checkWarning->execute([$uid, $type]);

            if ($checkWarning->rowCount() == 0) {
                $db->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, ?, ?)")->execute([$uid, $type, $msg]);
            }
        } elseif ($unpaidCount >= 3) {

            // ফিক্স ৩: ইউজার যদি আগে থেকেই 'suspended' না থাকে, তবেই শুধু তাকে সাসপেন্ড করা হবে
            if ($currentStatus !== 'suspended') {
                $db->prepare("UPDATE users SET status = 'suspended' WHERE user_id = ?")->execute([$uid]);
                $db->prepare("UPDATE subscriptions SET status = 'suspended' WHERE user_id = ? AND status = 'active'")->execute([$uid]);

                $msg = "Your connection has been suspended due to 3 unpaid invoices.";
                $db->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'suspension', ?)")->execute([$uid, $msg]);
            }
        }
    }

    $db->commit();
    echo "Cron executed successfully.";
} catch (Exception $e) {

    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "Cron Failed: " . $e->getMessage();
}
