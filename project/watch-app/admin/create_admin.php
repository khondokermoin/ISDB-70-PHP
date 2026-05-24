<?php
// admin/create_admin.php
require_once '../config/db.php';

$username = 'admin';
// পাসওয়ার্ডটি অবশ্যই হ্যাশ করে নিতে হবে
$password = password_hash('123456', PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (:username, :password)");
    $stmt->execute([
        ':username' => $username,
        ':password' => $password
    ]);
    echo "সফলভাবে এডমিন তৈরি হয়েছে! <br> Username: admin <br> Password: 123456 <br> <a href='login.php'>লগইন পেজে যান</a>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
