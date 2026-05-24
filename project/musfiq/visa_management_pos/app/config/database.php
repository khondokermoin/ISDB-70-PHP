<?php
// database.php
$host = "localhost";
$dbname = "visamanagementdb"; // আপনার ডাটাবেসের নাম
$username = "root"; // XAMPP হলে ডিফল্ট root থাকে
$password = ""; // XAMPP হলে পাসওয়ার্ড ফাঁকা থাকে

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
