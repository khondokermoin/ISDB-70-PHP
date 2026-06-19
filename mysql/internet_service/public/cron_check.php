<?php
// cron_check.php
require_once __DIR__ . '/config/database.php';

try {
    $db = (new Database())->getConnection();
    echo "Starting Daily Cron Check...\n";

    // =================================================================
    // কাজ ১: যাদের মেয়াদ শেষ (End date পার হয়ে গেছে), তাদের অ্যাকাউন্ট Suspended করা
    // =================================================================

    // UPDATE চালানোর আগে কাদের সাসপেন্ড হবে সেই তথ্য নিয়ে রাখা হলো
    // (UPDATE হয়ে গেলে status আর 'active' থাকবে না, তখন আর এই তথ্য পাওয়া যাবে না)
    $toSuspendStmt = $db->query("
        SELECT u.user_id, u.full_name, u.email
        FROM subscriptions s
        JOIN users u ON s.user_id = u.user_id
        WHERE s.status = 'active' AND DATEDIFF(s.end_date, CURDATE()) < 0
    ");
    $toSuspend = $toSuspendStmt->fetchAll(PDO::FETCH_ASSOC);

    $suspendQuery = "UPDATE subscriptions s
                     JOIN users u ON s.user_id = u.user_id
                     SET s.status = 'expired', u.status = 'suspended'
                     WHERE s.status = 'active' AND DATEDIFF(s.end_date, CURDATE()) < 0";

    $stmtSuspend = $db->prepare($suspendQuery);
    $stmtSuspend->execute();
    $suspendedCount = $stmtSuspend->rowCount();
    echo "[$suspendedCount] Accounts suspended.\n";

    // সাসপেন্ড হওয়া প্রতিটা ইউজারকে নোটিফিকেশন লগ করা এবং মেইল পাঠানো
    $logSuspension = $db->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'suspension', ?)");

    foreach ($toSuspend as $u) {
        $suspendMsg = "Your connection has been suspended because your subscription expired. Please renew to restore service.";
        $logSuspension->execute([$u['user_id'], $suspendMsg]);

        if (!empty($u['email'])) {
            $subject = mb_encode_mimeheader("⚠️ Your Internet Connection Has Been Suspended", 'UTF-8');

            $message = "Dear " . $u['full_name'] . ",\n\n";
            $message .= "Your internet package has expired and your connection has been suspended.\n";
            $message .= "Please log in to your dashboard and renew your package to restore service.\n\n";
            $message .= "Login here: https://yourwebsite.com/login.php\n\n";
            $message .= "Thank you,\nAMAR IT Billing Team";

            $headers = "From: billing@amarit.com\r\n";
            $headers .= "Reply-To: support@amarit.com\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            @mail($u['email'], $subject, $message, $headers);
        }
    }

    // =================================================================
    // কাজ ২: যাদের মেয়াদ ৩, ২, ১ দিন আছে বা আজকেই শেষ (০), তাদের মেইল পাঠানো
    // =================================================================
    $query = "SELECT s.subscription_id, u.user_id, s.end_date, u.full_name, u.email, p.name as package_name, p.price,
              DATEDIFF(s.end_date, CURDATE()) as days_left
              FROM subscriptions s
              JOIN users u ON s.user_id = u.user_id
              JOIN packages p ON s.package_id = p.package_id
              WHERE s.status = 'active' AND DATEDIFF(s.end_date, CURDATE()) IN (3, 2, 1, 0)";

    $stmt = $db->query($query);
    $expiring_subs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // একই দিনে একই ইউজারকে একই ধরনের মেইল দুইবার যাওয়া আটকানোর জন্য চেক
    $checkSent = $db->prepare("SELECT notification_id FROM notifications WHERE user_id = ? AND type = ? AND DATE(sent_at) = CURDATE()");
    $logSent = $db->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, ?, ?)");

    $emailCount = 0;
    foreach ($expiring_subs as $sub) {
        $to = $sub['email'];
        $days = (int)$sub['days_left'];
        $type = "expiry_warning_{$days}d";

        // আজকে এই ইউজারকে এই টাইপের মেইল আগে পাঠানো হয়ে থাকলে স্কিপ করা হলো
        $checkSent->execute([$sub['user_id'], $type]);
        if ($checkSent->rowCount() > 0) {
            continue;
        }

        if (empty($to)) {
            continue;
        }

        // মেইলের সাবজেক্ট এবং টাইম লজিক
        if ($days == 0) {
            $rawSubject = "🚨 URGENT: Your Internet Expires TODAY!";
            $time_msg = "expires TODAY";
        } else {
            $rawSubject = "⏳ Reminder: Your Internet Expires in $days Day(s)";
            $time_msg = "will expire in $days day(s)";
        }
        $subject = mb_encode_mimeheader($rawSubject, 'UTF-8');

        // মেইলের বডি
        $message = "Dear " . $sub['full_name'] . ",\n\n";
        $message .= "This is a gentle reminder that your internet package ({$sub['package_name']}) $time_msg.\n";
        $message .= "Please log in to your dashboard and pay the renewal fee of ৳" . number_format($sub['price'], 2) . " to avoid disconnection.\n\n";
        $message .= "Login here: https://yourwebsite.com/login.php\n\n";
        $message .= "Thank you,\nAMAR IT Billing Team";

        $headers = "From: billing@amarit.com\r\n";
        $headers .= "Reply-To: support@amarit.com\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        // মেইল সেন্ড করা (যদি আপনার আগের PHPMailer এর কোড থাকে, তবে mail() এর বদলে সেটা এখানে বসাতে পারেন)
        if (mail($to, $subject, $message, $headers)) {
            $emailCount++;
            $logSent->execute([$sub['user_id'], $type, $message]);
        }
    }
    echo "[$emailCount] Warning emails sent.\n";
    echo "Cron Check Completed Successfully.\n";

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "General Error: " . $e->getMessage();
}