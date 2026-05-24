<?php
// ডাটাবেজ কানেকশন ফাইল ইমপোর্ট করা (যেহেতু admin ফোল্ডারে আছি, তাই এক ফোল্ডার ব্যাকে গিয়ে config ফোল্ডার পাবো)
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ফর্ম থেকে ডাটা রিসিভ করা
    $name = $_POST['name'] ?? '';
    $model = $_POST['model'] ?? '';
    $buying_price = $_POST['buying_price'] ?? 0;
    $selling_price = $_POST['selling_price'] ?? 0;
    $description = $_POST['description'] ?? '';

    // ছবি সেভ করার ফোল্ডার পাথ
    $uploadDir = '../assets/uploads/';

    // ফোল্ডার না থাকলে তৈরি করে নেওয়া
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    try {
        // ট্রানজ্যাকশন শুরু
        $pdo->beginTransaction();

        // ১. ঘড়ির মূল ইনফরমেশন watches টেবিলে সেভ করা
        $stmt = $pdo->prepare("INSERT INTO watches (name, model, buying_price, selling_price, description) 
                               VALUES (:name, :model, :buying_price, :selling_price, :description)");

        $stmt->execute([
            ':name' => $name,
            ':model' => $model,
            ':buying_price' => $buying_price,
            ':selling_price' => $selling_price,
            ':description' => $description
        ]);

        // সদ্য সেভ হওয়া ঘড়ির ID বের করা (যাতে ছবির সাথে লিঙ্ক করা যায়)
        $watch_id = $pdo->lastInsertId();

        // ২. একাধিক ছবি প্রসেস এবং সেভ করা
        if (!empty($_FILES['images']['name'][0])) {
            $imageCount = count($_FILES['images']['name']);

            for ($i = 0; $i < $imageCount; $i++) {
                $tmpName = $_FILES['images']['tmp_name'][$i];
                $originalName = $_FILES['images']['name'][$i];

                if ($tmpName != "") {
                    // ফাইলের এক্সটেনশন বের করা (যেমন: jpg, png)
                    $ext = pathinfo($originalName, PATHINFO_EXTENSION);

                    // একই নামের ছবি রিপ্লেস হওয়া এড়াতে ইউনিক নাম জেনারেট করা
                    $newName = uniqid('watch_') . '_' . time() . '.' . $ext;
                    $uploadPath = $uploadDir . $newName;

                    // ডাটাবেজে সেভ করার জন্য রিলেটিভ পাথ (index.php থেকে যেভাবে লোড হবে)
                    $dbPath = 'assets/uploads/' . $newName;

                    // ছবি ফোল্ডারে মুভ করা
                    if (move_uploaded_file($tmpName, $uploadPath)) {
                        // watch_images টেবিলে ডাটা ইনসার্ট করা
                        $imgStmt = $pdo->prepare("INSERT INTO watch_images (watch_id, image_url) VALUES (:watch_id, :image_url)");
                        $imgStmt->execute([
                            ':watch_id' => $watch_id,
                            ':image_url' => $dbPath
                        ]);
                    }
                }
            }
        }

        // সবকিছু ঠিক থাকলে ট্রানজ্যাকশন সেভ (Commit) করা
        $pdo->commit();

        // সাকসেস মেসেজ দেখিয়ে আগের পেজে ফেরত পাঠানো
        echo "<script>
                alert('ঘড়ির তথ্য এবং ছবি সফলভাবে সেভ হয়েছে!');
                window.location.href = 'add_watch.php';
              </script>";
    } catch (Exception $e) {
        // কোনো লাইনে এরর হলে ডাটাবেজের সব পরিবর্তন বাতিল (Rollback) করা
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
} else {
    // সরাসরি এই পেজে এক্সেস করতে চাইলে রিডাইরেক্ট করে দেওয়া
    header("Location: add_watch.php");
    exit;
}
