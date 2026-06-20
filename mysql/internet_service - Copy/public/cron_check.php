<?php
// cron_check.php
// 🚀 PRODUCTION GRADE PATTERN:
//   - Fast DB Transaction (No locks held during SMTP calls)
//   - Queue-based Mail Dispatching
//   - HTTPS enforce for login URL
//   - UTF-8 & Emoji support for Mail Subjects

require_once __DIR__ . '/config/database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php';

// ==========================================================
// 📧 PHPMailer SMTP + HTML Branding হেল্পার ফাংশন
// ==========================================================
function sendBrandedMail($toEmail, $toName, $subjectText, array $bodyLines, $loginUrl = "https://amarit.westernwatchbd.com/login.php")
{
    if (empty($toEmail)) return false;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        // TODO: Update your credentials
        $mail->Username   = 'mouin2000ab@gmail.com';
        $mail->Password   = 'jsbv camb ogyv yxjh';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // 🔥 UTF-8 এবং ইমোজি সাপোর্ট
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('mouin2000ab@gmail.com', 'AMAR IT Billing');
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        // 🔥 বাংলা এবং ইমোজির জন্য এনকোডিং
        $mail->Subject = mb_encode_mimeheader($subjectText, 'UTF-8');

        $htmlContent = "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #ddd; padding: 25px; border-top: 5px solid #dc2626; border-radius: 8px; background-color: #fafafa;'>";
        $htmlContent .= "<h2 style='color: #dc2626; text-align: center; margin-bottom: 5px;'>AMAR IT</h2>";
        $htmlContent .= "<h3 style='text-align: center; color: #333; margin-top: 0; padding-bottom: 15px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($subjectText) . "</h3>";
        $htmlContent .= "<div style='color: #444; font-size: 15px; line-height: 1.6;'>";

        foreach ($bodyLines as $line) {
            if (empty(trim($line))) continue;
            $formattedLine = preg_replace('/^(Dear .*?,|Warning:|Final Warning:)/', '<strong>$1</strong>', htmlspecialchars($line));
            $htmlContent .= "<p>" . $formattedLine . "</p>";
        }

        $htmlContent .= "</div>";
        $htmlContent .= "<div style='text-align: center; margin-top: 30px; margin-bottom: 20px;'>";
        $htmlContent .= "<a href='{$loginUrl}' style='background-color: #dc2626; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px; display: inline-block; box-shadow: 0 4px 6px rgba(220, 38, 38, 0.2);'>Login to Dashboard</a>";
        $htmlContent .= "</div>";
        $htmlContent .= "<p style='margin-top: 30px; font-size: 13px; color: #777; text-align: center; border-top: 1px solid #eee; padding-top: 15px;'>Thank you for staying with us,<br><strong>AMAR IT Billing Team</strong></p>";
        $htmlContent .= "</div>";

        $mail->Body = $htmlContent;
        $plainText = implode("\n", $bodyLines) . "\n\nLogin here: {$loginUrl}\n\nThank you,\nAMAR IT Billing Team";
        $mail->AltBody = strip_tags($plainText);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Cron Mail failed to {$toEmail}: {$mail->ErrorInfo}");
        return false;
    }
}

// ==========================================================
// 🚀 মেইন এক্সিকিউশন স্টার্ট
// ==========================================================
$mailQueue = []; // মেইল পাঠানোর কিউ (Queue)

try {
    $db = (new Database())->getConnection();
    echo "Starting Daily Cron Check...\n";

    // ⚡ DB TRANSACTION START (FAST PHASE)
    $db->beginTransaction();

    $checkSent = $db->prepare("SELECT notification_id FROM notifications WHERE user_id = ? AND type = ? AND DATE(sent_at) = CURDATE()");
    $logNotif  = $db->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, ?, ?)");

    /* --- ১. Expiry Warning --- */
    $expiringSubs = $db->query("
        SELECT u.user_id, u.full_name, u.email, p.name AS package_name, p.price, DATEDIFF(s.end_date, CURDATE()) AS days_left
        FROM subscriptions s JOIN users u ON s.user_id = u.user_id JOIN packages p ON s.package_id = p.package_id
        WHERE u.status = 'active' AND s.status = 'active' AND DATEDIFF(s.end_date, CURDATE()) BETWEEN 0 AND 3
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($expiringSubs as $sub) {
        $checkSent->execute([$sub['user_id'], 'expiry_warning']);
        if ($checkSent->rowCount() > 0) continue;

        $days = (int)$sub['days_left'];
        $timeMsg = ($days == 0) ? "expires TODAY" : "will expire in {$days} day(s)";
        $subjectText = ($days == 0) ? "🚨 URGENT: Your Internet Expires TODAY!" : "⏳ Reminder: Your Internet Expires in {$days} Day(s)";
        $priceText = number_format($sub['price'], 2);

        $logMsg = "Your internet package ({$sub['package_name']}) {$timeMsg}. Renewal fee: ৳{$priceText}.";
        $logNotif->execute([$sub['user_id'], 'expiry_warning', $logMsg]);

        // কিউ-তে যুক্ত করা হলো (মেইল পাঠানো হচ্ছে না)
        $mailQueue[] = [
            'type' => 'expiry_warning',
            'email' => $sub['email'],
            'name' => $sub['full_name'],
            'subject' => $subjectText,
            'body' => [
                "Dear {$sub['full_name']},",
                "",
                "This is a gentle reminder that your internet package ({$sub['package_name']}) {$timeMsg}.",
                "Please log in to your dashboard and pay the renewal fee of ৳{$priceText} to avoid disconnection."
            ]
        ];
    }

    /* --- ২. Auto Suspend --- */
    $toExpire = $db->query("
        SELECT u.user_id, u.full_name, u.email
        FROM subscriptions s JOIN users u ON s.user_id = u.user_id
        WHERE s.status = 'active' AND DATEDIFF(s.end_date, CURDATE()) < 0
    ")->fetchAll(PDO::FETCH_ASSOC);

    $db->prepare("
        UPDATE subscriptions s JOIN users u ON s.user_id = u.user_id
        SET s.status = 'expired', u.status = 'suspended'
        WHERE s.status = 'active' AND DATEDIFF(s.end_date, CURDATE()) < 0
    ")->execute();

    foreach ($toExpire as $u) {
        $msg = "Your connection has been suspended because your subscription expired. Please renew to restore service.";
        $logNotif->execute([$u['user_id'], 'suspension', $msg]);

        $mailQueue[] = [
            'type' => 'suspension',
            'email' => $u['email'],
            'name' => $u['full_name'],
            'subject' => "⚠️ Your Internet Connection Has Been Suspended",
            'body' => [
                "Dear {$u['full_name']},",
                "",
                "Your internet package has expired and your connection has been suspended.",
                "Please log in to your dashboard and renew your package to restore service."
            ]
        ];
    }

    /* --- ৩. Unpaid Invoice Warning --- */
    $invoiceUsers = $db->query("
        SELECT i.user_id, u.full_name, u.email, u.status AS current_status, COUNT(*) AS unpaid_count
        FROM invoices i JOIN users u ON i.user_id = u.user_id
        WHERE i.status = 'unpaid' GROUP BY i.user_id, u.full_name, u.email, u.status
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($invoiceUsers as $row) {
        $uid = $row['user_id'];
        $unpaidCount = (int)$row['unpaid_count'];
        $currentStatus = $row['current_status'];

        if ($unpaidCount == 1 || $unpaidCount == 2) {
            $type = ($unpaidCount == 1) ? 'billing_warning_1' : 'billing_warning_2';
            $msg = ($unpaidCount == 1) ? "Warning: You have 1 unpaid invoice." : "Final Warning: You have 2 unpaid invoices. Please pay immediately.";

            $checkSent->execute([$uid, $type]);
            if ($checkSent->rowCount() == 0) {
                $logNotif->execute([$uid, $type, $msg]);
                $subjectText = ($unpaidCount == 1) ? "Unpaid Invoice Reminder" : "Final Notice: Unpaid Invoices";

                $mailQueue[] = [
                    'type' => 'billing_warning',
                    'email' => $row['email'],
                    'name' => $row['full_name'],
                    'subject' => $subjectText,
                    'body' => [
                        "Dear {$row['full_name']},",
                        "",
                        $msg,
                        "Please log in to your dashboard and clear your due balance to avoid service interruption."
                    ]
                ];
            }
        } elseif ($unpaidCount >= 3 && $currentStatus !== 'suspended') {
            $db->prepare("UPDATE users SET status = 'suspended' WHERE user_id = ?")->execute([$uid]);
            $db->prepare("UPDATE subscriptions SET status = 'suspended' WHERE user_id = ? AND status = 'active'")->execute([$uid]);

            $msg = "Your connection has been suspended due to 3 unpaid invoices.";
            $logNotif->execute([$uid, 'suspension', $msg]);

            $mailQueue[] = [
                'type' => 'billing_suspend',
                'email' => $row['email'],
                'name' => $row['full_name'],
                'subject' => "Connection Suspended",
                'body' => [
                    "Dear {$row['full_name']},",
                    "",
                    $msg,
                    "Please clear your outstanding invoices to restore service."
                ]
            ];
        }
    }

    // ⚡ DB TRANSACTION END - COMMIT FAST! 
    $db->commit();
    echo "Database locked operations finished. Transaction Committed.\n";
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo "Cron DB Phase Failed: " . $e->getMessage();
    exit; // DB ফেইল করলে মেইল পাঠানো হবে না
}

// ==========================================================
// 📧 SMTP MAIL PROCESSING (SLOW PHASE - DB LOCK FREE)
// ==========================================================
$counts = ['expiry_warning' => 0, 'suspension' => 0, 'billing_warning' => 0, 'billing_suspend' => 0];

echo "Processing Mail Queue (" . count($mailQueue) . " emails)...\n";

foreach ($mailQueue as $mailData) {
    $sent = sendBrandedMail(
        $mailData['email'],
        $mailData['name'],
        $mailData['subject'],
        $mailData['body']
    );

    if ($sent) {
        $counts[$mailData['type']]++;
    }
}

// 📊 ফাইনাল রিপোর্ট
echo "--- Delivery Report ---\n";
echo "[{$counts['expiry_warning']}] Expiry warning emails sent.\n";
echo "[{$counts['suspension']}] Accounts suspended (expiry) emails sent.\n";
echo "[{$counts['billing_warning']}] Billing warning emails sent.\n";
echo "[{$counts['billing_suspend']}] Accounts suspended (unpaid) emails sent.\n";
echo "Cron Check Completed Successfully.\n";
