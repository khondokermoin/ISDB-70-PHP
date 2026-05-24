<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';

// URL থেকে ঘড়ির ID নেওয়া
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: dashboard.php");
    exit;
}

// ফর্ম সাবমিট হলে ডাটা আপডেট করার লজিক
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $model = $_POST['model'];
    $buying_price = $_POST['buying_price'];
    $selling_price = $_POST['selling_price'];
    $description = $_POST['description'];

    try {
        // ১. watches টেবিল আপডেট করা
        $stmt = $pdo->prepare("UPDATE watches SET name = :name, model = :model, buying_price = :buying_price, selling_price = :selling_price, description = :description WHERE id = :id");
        $stmt->execute([
            ':name' => $name,
            ':model' => $model,
            ':buying_price' => $buying_price,
            ':selling_price' => $selling_price,
            ':description' => $description,
            ':id' => $id
        ]);

        // ২. নতুন কোনো ছবি আপলোড করলে সেগুলো সেভ করা
        if (!empty($_FILES['new_images']['name'][0])) {
            $uploadDir = '../assets/uploads/';
            $imageCount = count($_FILES['new_images']['name']);

            for ($i = 0; $i < $imageCount; $i++) {
                $tmpName = $_FILES['new_images']['tmp_name'][$i];
                $originalName = $_FILES['new_images']['name'][$i];

                if ($tmpName != "") {
                    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                    $newName = uniqid('watch_') . '_' . time() . '.' . $ext;
                    $uploadPath = $uploadDir . $newName;
                    $dbPath = 'assets/uploads/' . $newName;

                    if (move_uploaded_file($tmpName, $uploadPath)) {
                        $imgStmt = $pdo->prepare("INSERT INTO watch_images (watch_id, image_url) VALUES (:watch_id, :image_url)");
                        $imgStmt->execute([
                            ':watch_id' => $id,
                            ':image_url' => $dbPath
                        ]);
                    }
                }
            }
        }

        echo "<script>
                alert('ঘড়ির তথ্য সফলভাবে আপডেট হয়েছে!');
                window.location.href = 'dashboard.php';
              </script>";
        exit;
    } catch (PDOException $e) {
        $error = "Error updating: " . $e->getMessage();
    }
}

// ফর্মে দেখানোর জন্য আগের ডাটাগুলো ডাটাবেজ থেকে আনা
$stmt = $pdo->prepare("SELECT * FROM watches WHERE id = :id");
$stmt->execute([':id' => $id]);
$watch = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$watch) {
    die("ঘড়িটি খুঁজে পাওয়া যায়নি!");
}

// আগের ছবিগুলো ডাটাবেজ থেকে আনা
$imgStmt = $pdo->prepare("SELECT * FROM watch_images WHERE watch_id = :id");
$imgStmt->execute([':id' => $id]);
$images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Watch - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-800 font-sans p-6">

    <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow-md mt-10">
        <div class="flex justify-between items-center mb-6 border-b pb-3">
            <h2 class="text-2xl font-bold text-gray-700">ঘড়ির তথ্য আপডেট করুন</h2>
            <a href="dashboard.php" class="text-blue-600 hover:underline">← Back to Dashboard</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= $error ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="space-y-5">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ঘড়ির নাম</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($watch['name']) ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">মডেল নাম্বার</label>
                    <input type="text" name="model" value="<?= htmlspecialchars($watch['model']) ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">কেনার দাম (৳)</label>
                    <input type="number" step="0.01" name="buying_price" value="<?= $watch['buying_price'] ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">বিক্রির দাম (৳)</label>
                    <input type="number" step="0.01" name="selling_price" value="<?= $watch['selling_price'] ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ডেসক্রিপশন</label>
                <textarea name="description" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 outline-none"><?= htmlspecialchars($watch['description']) ?></textarea>
            </div>

            <!-- আগের ছবিগুলো দেখানোর সেকশন -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">বর্তমান ছবিসমূহ:</label>
                <div class="flex flex-wrap gap-3">
                    <?php foreach ($images as $img): ?>
                        <div class="relative group">
                            <img src="../<?= $img['image_url'] ?>" class="w-24 h-24 object-cover rounded border">
                            <!-- ছবি ডিলিট করার বাটন -->
                            <a href="delete_image.php?img_id=<?= $img['id'] ?>&watch_id=<?= $watch['id'] ?>" onclick="return confirm('ছবিটি মুছে ফেলতে চান?')" class="absolute top-1 right-1 bg-red-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition" title="Delete Image">X</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- নতুন ছবি যোগ করার অপশন -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">নতুন ছবি যোগ করুন (ঐচ্ছিক)</label>
                <input type="file" name="new_images[]" multiple accept="image/*" class="w-full text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            <div class="pt-4 flex gap-3">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-md transition duration-300">
                    আপডেট করুন
                </button>
            </div>
        </form>
    </div>

</body>

</html>