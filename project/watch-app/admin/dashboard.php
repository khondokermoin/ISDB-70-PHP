<?php
// সিকিউরিটি: লগইন করা না থাকলে পেজে ঢুকতে পারবে না
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once '../config/db.php';

// ডাটাবেজ থেকে সব ঘড়ির তথ্য আনা
try {
    $stmt = $pdo->query("SELECT * FROM watches ORDER BY id DESC");
    $watches = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-6 font-sans">

    <div class="max-w-7xl mx-auto">

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-700">Admin Dashboard - Watch List</h2>
            <div class="space-x-2">
                <a href="../index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded shadow-sm">View Site</a>
                <a href="add_watch.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow-sm">+ Add New Watch</a>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded shadow-sm">Logout</a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-800 text-white">
                        <th class="p-3 border">ID</th>
                        <th class="p-3 border">Name</th>
                        <th class="p-3 border">Model</th>
                        <th class="p-3 border">Buying Price</th>
                        <th class="p-3 border">Selling Price</th>
                        <th class="p-3 border">Details</th>
                        <th class="p-3 border text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($watches as $watch): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 border"><?= $watch['id'] ?></td>
                            <td class="p-3 border font-semibold"><?= htmlspecialchars($watch['name']) ?></td>
                            <td class="p-3 border"><?= htmlspecialchars($watch['model']) ?></td>
                            <td class="p-3 border text-red-600">৳<?= $watch['buying_price'] ?></td>
                            <td class="p-3 border text-green-600 font-bold">৳<?= $watch['selling_price'] ?></td>
                            <td class="p-3 border text-gray-600 text-sm">
                                <?php
                                $desc = htmlspecialchars($watch['description']);
                                // ডেসক্রিপশন বড় হলে প্রথম ৫০ অক্ষর দেখাবে
                                echo mb_strlen($desc) > 50 ? mb_substr($desc, 0, 50) . '...' : $desc;
                                ?>
                            </td>
                            <td class="p-3 border text-center space-x-2">
                                <a href="edit_watch.php?id=<?= $watch['id'] ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm">Edit</a>
                                <a href="delete_watch.php?id=<?= $watch['id'] ?>" onclick="return confirm('আপনি কি নিশ্চিত যে এটি ডিলিট করতে চান?')" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (count($watches) === 0): ?>
                        <tr>
                            <td colspan="7" class="p-5 text-center text-gray-500">কোনো ডাটা পাওয়া যায়নি।</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>