<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
// admin/delete_image.php ফাইলের কোড
require_once '../config/db.php';

$img_id = $_GET['img_id'] ?? null;
$watch_id = $_GET['watch_id'] ?? null;

if ($img_id && $watch_id) {
    try {
        // ডাটাবেজ থেকে ছবির URL আনা
        $stmt = $pdo->prepare("SELECT image_url FROM watch_images WHERE id = :id");
        $stmt->execute([':id' => $img_id]);
        $image = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($image) {
            // ফোল্ডার থেকে ফাইল ডিলিট করা
            $filePath = '../' . $image['image_url'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // ডাটাবেজ থেকে মুছে ফেলা
            $delStmt = $pdo->prepare("DELETE FROM watch_images WHERE id = :id");
            $delStmt->execute([':id' => $img_id]);
        }
    } catch (PDOException $e) {
        die("Error deleting image: " . $e->getMessage());
    }
}

// এডিট পেজেই আবার ফেরত পাঠানো
header("Location: edit_watch.php?id=" . $watch_id);
exit;
