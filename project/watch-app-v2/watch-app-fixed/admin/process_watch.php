<?php
session_start();
require_once '../config/db.php';

// ১. সিকিউরিটি চেক (Admin Login Check)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit;
}

// ২. CSRF Token যাচাই
if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'Security token mismatch. Please try again.'
    ];
    header("Location: dashboard.php");
    exit;
}

$action = $_POST['action'] ?? 'add';  // 'add' অথবা 'edit'
$id     = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// ৩. ইনপুট স্যানিটাইজ ও রিসিভ
$name          = trim($_POST['name']          ?? '');
$model         = trim($_POST['model']         ?? '');
$brand         = trim($_POST['brand']         ?? '');
$buying_price  = (float)($_POST['buying_price']  ?? 0);
$selling_price = (float)($_POST['selling_price'] ?? 0);
$description   = trim($_POST['description']   ?? '');
$quantity      = max(0, (int)($_POST['quantity'] ?? 0));

// ৪. বেসিক ভ্যালিডেশন
if (empty($name) || empty($model) || $buying_price <= 0 || $selling_price <= 0) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'নাম, মডেল এবং দাম অবশ্যই পূরণ করতে হবে।'
    ];
    header("Location: " . ($action === 'edit' ? "edit_watch.php?id=$id" : "add_watch.php"));
    exit;
}

try {
    // ট্রানজেকশন শুরু (যাতে ঘড়ি সেভ হলে তবেই ছবি সেভ হয়)
    $pdo->beginTransaction();

    if ($action === 'add') {
        $stmt = $pdo->prepare("
            INSERT INTO watches (name, model, brand, buying_price, selling_price, description, quantity, created_at)
            VALUES (:name, :model, :brand, :buying, :selling, :desc, :qty, NOW())
        ");
        $stmt->execute([
            ':name'   => $name,
            ':model'  => $model,
            ':brand'  => $brand,
            ':buying' => $buying_price,
            ':selling' => $selling_price,
            ':desc'   => $description,
            ':qty'    => $quantity,
        ]);
        $watch_id = (int)$pdo->lastInsertId();
    } else {
        // Edit লজিক (নিরাপত্তার জন্য ID যাচাই)
        $check = $pdo->prepare("SELECT id FROM watches WHERE id = :id");
        $check->execute([':id' => $id]);

        if (!$check->fetch()) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'ঘড়িটি পাওয়া যায়নি।'];
            header("Location: dashboard.php");
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE watches SET
                name = :name, model = :model, brand = :brand,
                buying_price = :buying, selling_price = :selling,
                description = :desc, quantity = :qty
            WHERE id = :id
        ");
        $stmt->execute([
            ':name'   => $name,
            ':model'  => $model,
            ':brand'  => $brand,
            ':buying' => $buying_price,
            ':selling' => $selling_price,
            ':desc'   => $description,
            ':qty'    => $quantity,
            ':id'     => $id,
        ]);
        $watch_id = $id;
    }

    // ৫. ছবি আপলোড লজিক (যেকোনো বাইরের ফাংশন ছাড়া)
    $filesKey = ($action === 'add') ? 'images' : 'new_images';

    if (isset($_FILES[$filesKey]) && !empty($_FILES[$filesKey]['name'][0])) {
        $uploadDir = '../assets/uploads/';

        // ফোল্ডার না থাকলে তৈরি করে নেওয়া
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $imageCount = count($_FILES[$filesKey]['name']);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        for ($i = 0; $i < $imageCount; $i++) {
            $tmpName = $_FILES[$filesKey]['tmp_name'][$i];
            $originalName = $_FILES[$filesKey]['name'][$i];

            if ($tmpName != "") {
                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                // শুধুমাত্র অনুমোদিত ফাইল টাইপ চেক করা
                if (in_array($ext, $allowedExtensions)) {
                    $newName = uniqid('watch_') . '_' . time() . '.' . $ext;
                    $uploadPath = $uploadDir . $newName;
                    $dbPath = 'assets/uploads/' . $newName;

                    if (move_uploaded_file($tmpName, $uploadPath)) {
                        $imgStmt = $pdo->prepare("INSERT INTO watch_images (watch_id, image_url, sort_order) VALUES (:watch_id, :image_url, :sort_order)");
                        $imgStmt->execute([
                            ':watch_id' => $watch_id,
                            ':image_url' => $dbPath,
                            ':sort_order' => $i + 1
                        ]);
                    }
                }
            }
        }
    }

    // সব কাজ সফল হলে ট্রানজেকশন সেভ করা
    $pdo->commit();

    // সাকসেস মেসেজ সেট করা
    $msg = ($action === 'add') ? 'নতুন ঘড়ি সফলভাবে যোগ হয়েছে!' : 'ঘড়ির তথ্য আপডেট হয়েছে!';
    $_SESSION['flash'] = [
        'type' => 'success',
        'message' => $msg
    ];
    header("Location: dashboard.php");
    exit;
} catch (Exception $e) {
    // কোনো এরর হলে আগের অবস্থায় ফিরে যাওয়া (Rollback)
    $pdo->rollBack();
    error_log("process_watch error: " . $e->getMessage());

    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'একটি ডাটাবেজ সমস্যা হয়েছে: ' . $e->getMessage()
    ];
    header("Location: " . ($action === 'edit' ? "edit_watch.php?id=$id" : "add_watch.php"));
    exit;
}
