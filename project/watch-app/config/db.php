<?php
// ডাটাবেজ ক্রেডেনশিয়াল
$host = 'localhost';
$dbname = 'watch_inventory'; // phpMyAdmin-এ ঠিক এই নামের ডাটাবেজ তৈরি থাকতে হবে
$username = 'root';
$password = ''; // লোকালহোস্টে (XAMPP/WAMP) ডিফল্ট পাসওয়ার্ড ফাঁকা থাকে

try {
    // PDO কানেকশন তৈরি
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    // এরর মোড সেট করা (যাতে কোনো সমস্যা হলে সহজেই বোঝা যায়)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // কানেকশন চেক করার জন্য নিচের লাইনটি আনকমেন্ট করে টেস্ট করতে পারেন
    // echo "Database Connected Successfully!";

} catch (PDOException $e) {
    // কানেকশন ফেইল হলে এরর মেসেজ দেখাবে এবং স্ক্রিপ্ট বন্ধ করে দিবে
    die("Database Connection failed: " . $e->getMessage());
}
