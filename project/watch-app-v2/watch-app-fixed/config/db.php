<?php
// ডাটাবেজ ক্রেডেনশিয়াল — .env ফাইল থেকে লোড করুন (প্রোডাকশনে)
// লোকালহোস্টে সরাসরি এখানে পরিবর্তন করা যাবে
$host     = getenv('DB_HOST')     ?: 'localhost';
$dbname   = getenv('DB_NAME')     ?: 'watch_inventory';
$username = getenv('DB_USER')     ?: 'root';
$password = getenv('DB_PASS')     ?: '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

} catch (PDOException $e) {
    // শুধু লগে লেখা হবে — ভিজিটরকে ডিটেইলস দেখানো হবে না
    error_log("DB Connection Error: " . $e->getMessage());
    die("একটি সার্ভার সমস্যা হয়েছে। পরে আবার চেষ্টা করুন।");
}
