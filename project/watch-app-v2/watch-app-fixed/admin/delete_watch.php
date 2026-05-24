<?php
session_start();
require_once '../config/db.php';

// ১. সিকিউরিটি চেক (অ্যাডমিন লগইন ভেরিফিকেশন)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// শুধুমাত্র POST রিকোয়েস্ট রিসিভ করবে (লিংক কপি করে ডিলিট করা যাবে না)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit;
}

// ২. CSRF Token যাচাই (হ্যাকিং বা ক্রস-সাইট রিকোয়েস্ট ঠেকানোর জন্য)
if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Security token mismatch!'];
    header("Location: dashboard.php");
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if (!$id) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'অবৈধ অনুরোধ।'];
    header("Location: dashboard.php");
    exit;
}

try {
    // ৩. ঘড়ির ছবিগুলো ডাটাবেজ থেকে খুঁজে বের করে ফোল্ডার থেকে ফিজিক্যালি মুছে ফেলা
    $stmt = $pdo->prepare("SELECT image_url FROM watch_images WHERE watch_id = :id");
    $stmt->execute([':id' => $id]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($images as $img) {
        $path = __DIR__ . '/../' . $img['image_url'];
        if (file_exists($path)) {
            unlink($path); // সার্ভার থেকে ফাইল ডিলিট
        }
    }

    // ৪. ডাটাবেজ থেকে মুছে ফেলা
    // (সতর্কতার জন্য প্রথমে ইমেজের ডাটাগুলো এবং পরে মূল ঘড়ির ডাটা ডিলিট করা হলো)
    $delImages = $pdo->prepare("DELETE FROM watch_images WHERE watch_id = :id");
    $delImages->execute([':id' => $id]);

    $delWatch = $pdo->prepare("DELETE FROM watches WHERE id = :id");
    $delWatch->execute([':id' => $id]);

    // সফল মেসেজ সেট করা
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'ঘড়িটি সম্পূর্ণভাবে মুছে ফেলা হয়েছে!'];
} catch (PDOException $e) {
    error_log("delete_watch error: " . $e->getMessage());
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'ঘড়ি মুছতে সমস্যা হয়েছে। আবার চেষ্টা করুন।'];
}

// ৫. ড্যাশবোর্ডে ফেরত পাঠানো
header("Location: dashboard.php");
exit;
