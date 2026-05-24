<?php
// admin/reset_password.php
require_once '../config/db.php';

// এখানে আপনার নতুন পাসওয়ার্ড দিন (আপাতত 123456 দেওয়া হলো)
$new_password = '123456';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

try {
    // 'admin' ইউজারের পাসওয়ার্ড আপডেট করা হচ্ছে
    $stmt = $pdo->prepare("UPDATE admins SET password = :password WHERE username = 'admin'");
    $stmt->execute([':password' => $hashed_password]);

    echo "পাসওয়ার্ড সফলভাবে রিসেট হয়েছে!<br>";
    echo "আপনার ইউজারনেম: admin<br>";
    echo "আপনার নতুন পাসওয়ার্ড: 123456<br><br>";
    echo "<a href='login.php' style='color:blue; text-decoration:underline;'>লগইন পেজে ফিরে যান</a>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
