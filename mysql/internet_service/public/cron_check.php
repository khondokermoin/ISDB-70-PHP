<?php
require_once '../config/database.php';
$db = (new Database())->getConnection();

// ১. যাদের মেয়াদ শেষ, তাদের লাইন অফ (Suspend) করা
$db->query("UPDATE users u JOIN subscriptions s ON u.user_id = s.user_id SET u.status = 'suspended', s.status = 'expired' WHERE s.end_date < CURDATE() AND u.status = 'active'");

// ২. যাদের মেয়াদ ৩ দিন বা তার কম বাকি তাদের নোটিফিকেশন এবং ইমেইল পাঠানো
$warning_query = "SELECT u.user_id, u.full_name, u.email, s.end_date 
                  FROM users u JOIN subscriptions s ON u.user_id = s.user_id 
                  WHERE s.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY) AND u.status = 'active'";
$users_to_notify = $db->query($warning_query)->fetchAll(PDO::FETCH_ASSOC);

foreach ($users_to_notify as $user) {
    $msg = "Dear " . $user['full_name'] . ", your internet package will expire on " . date('d M Y', strtotime($user['end_date'])) . ". Please pay your bill to avoid interruption.";
    
    // চেক করা হচ্ছে আজকের দিনে মেসেজ অলরেডি দেওয়া হয়েছে কি না
    $check = $db->prepare("SELECT notification_id FROM notifications WHERE user_id = ? AND message LIKE ? AND DATE(sent_at) = CURDATE()");
    $check->execute([$user['user_id'], "%expire%"]);
    
    if ($check->rowCount() == 0) {
        // ডাটাবেসে নোটিফিকেশন সেভ করা (Customer Dashboard এর জন্য)
        $db->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'expiry_warning', ?)")->execute([$user['user_id'], $msg]);
        
        // 🔥 ইমেইল (Email) পাঠানোর কোড
        $to = $user['email'];
        $subject = "Amar IT - Package Expiry Notice";
        $headers = "From: billing@amarit.com\r\n";
        $headers .= "Reply-To: support@amarit.com\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        $email_body = "<h3>Package Expiry Warning</h3><p>{$msg}</p><p>Thank you,<br>Amar IT Support Team</p>";
        
        // মেইল ফাংশন (সার্ভারে SMTP কনফিগার থাকতে হবে)
        @mail($to, $subject, $email_body, $headers);
    }
}
echo "Checked successfully. " . count($users_to_notify) . " users notified.";
?>