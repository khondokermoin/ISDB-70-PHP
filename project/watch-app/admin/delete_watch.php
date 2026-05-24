<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // ১. ডাটাবেজ থেকে এই ঘড়ির ছবিগুলোর URL খুঁজে বের করা
        $stmt = $pdo->prepare("SELECT image_url FROM watch_images WHERE watch_id = :id");
        $stmt->execute([':id' => $id]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ২. ফোল্ডার থেকে ছবিগুলো মুছে ফেলা (Unlink)
        foreach ($images as $img) {
            $filePath = '../' . $img['image_url'];
            if (file_exists($filePath)) {
                unlink($filePath); // সার্ভার থেকে ফাইল ডিলিট
            }
        }

        // ৩. ডাটাবেজ থেকে ঘড়িটি মুছে ফেলা
        // (ডাটাবেজ বানানোর সময় আমরা ON DELETE CASCADE দিয়েছিলাম, তাই watch_images টেবিলের ডাটাও অটোমেটিক ডিলিট হয়ে যাবে)
        $deleteStmt = $pdo->prepare("DELETE FROM watches WHERE id = :id");
        $deleteStmt->execute([':id' => $id]);

        // ডিলিট সফল হলে আবার ড্যাশবোর্ডে পাঠিয়ে দেওয়া
        echo "<script>
                alert('ঘড়িটি সফলভাবে ডিলিট হয়েছে!');
                window.location.href = 'dashboard.php';
              </script>";
    } catch (PDOException $e) {
        die("Error deleting watch: " . $e->getMessage());
    }
} else {
    header("Location: dashboard.php");
    exit;
}
