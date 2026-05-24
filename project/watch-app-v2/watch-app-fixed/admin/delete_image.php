<?php
session_start();

// Auth check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    exit('Unauthorized');
}

require_once '../config/db.php';

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit;
}

// CSRF check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid request. Please try again.'];
    header("Location: dashboard.php");
    exit;
}

$img_id  = (int)($_POST['img_id']  ?? 0);
$watch_id = (int)($_POST['watch_id'] ?? 0);

if (!$img_id || !$watch_id) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid image or watch ID.'];
    header("Location: edit_watch.php?id=$watch_id");
    exit;
}

try {
    // DB থেকে image path বের করা
    $stmt = $pdo->prepare("SELECT image_url FROM watch_images WHERE id = :id AND watch_id = :watch_id");
    $stmt->execute([':id' => $img_id, ':watch_id' => $watch_id]);
    $img = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$img) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Image not found.'];
        header("Location: edit_watch.php?id=$watch_id");
        exit;
    }

    // Physical file মুছে ফেলা
    $filePath = '../' . $img['image_url'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // DB থেকে record মুছে ফেলা
    $del = $pdo->prepare("DELETE FROM watch_images WHERE id = :id AND watch_id = :watch_id");
    $del->execute([':id' => $img_id, ':watch_id' => $watch_id]);

    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Image deleted successfully.'];
} catch (PDOException $e) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
}

header("Location: edit_watch.php?id=$watch_id");
exit;
