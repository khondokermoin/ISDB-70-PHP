<?php
session_start();
require_once '../config/db.php';

// ১. সিকিউরিটি চেক
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// ২. CSRF Token জেনারেট
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// ৩. ফ্ল্যাশ মেসেজ সিস্টেম
$flash = null;
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}
?>
<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Watch — Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 min-h-screen p-6">

    <div class="max-w-4xl mx-auto bg-white p-8 rounded-xl shadow-sm border border-gray-200 mt-6">

        <div class="flex justify-between items-center mb-8 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Add New Watch</h2>
                <p class="text-sm text-gray-500 mt-1">তারকা (<span class="text-red-500">*</span>) চিহ্নিত ফিল্ডগুলো অবশ্যই পূরণ করতে হবে।</p>
            </div>
            <a href="dashboard.php" class="text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-md transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Dashboard
            </a>
        </div>

        <?php if (!empty($flash)): ?>
            <?php $bg = $flash['type'] === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'; ?>
            <div class="<?= $bg ?> border px-4 py-3 rounded-md mb-6 flex items-center gap-3 shadow-sm text-sm font-medium">
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form action="process_watch.php" method="POST" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="action" value="add">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">ঘড়ির নাম <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required maxlength="150"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-gray-800 focus:border-gray-800 transition"
                        placeholder="e.g. Casio G-Shock">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">ব্র্যান্ড</label>
                    <input type="text" name="brand" maxlength="80"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-gray-800 focus:border-gray-800 transition"
                        placeholder="e.g. Casio, Rolex">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">মডেল নম্বর <span class="text-red-500">*</span></label>
                    <input type="text" name="model" required maxlength="100"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-gray-800 focus:border-gray-800 transition"
                        placeholder="e.g. GA-110">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">স্টক পরিমাণ <span class="text-red-500">*</span></label>
                    <input type="number" name="quantity" min="0" value="1" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-gray-800 focus:border-gray-800 transition"
                        placeholder="e.g. 10">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-5 rounded-lg border border-gray-100">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">কেনার দাম (৳) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="buying_price" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-gray-800 focus:border-gray-800 transition"
                        placeholder="e.g. 5000">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">বিক্রির দাম (৳) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="selling_price" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-gray-800 focus:border-gray-800 transition"
                        placeholder="e.g. 6500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">বিবরণ (ডেসক্রিপশন)</label>
                <textarea name="description" rows="8"
                    class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-gray-800 focus:border-gray-800 transition"
                    placeholder="Product Type: Men's Sport Watch&#13;&#10;Case Material: Resin&#13;&#10;Dial Diameter: 50mm&#13;&#10;Water Resistant: 200m"></textarea>
                <p class="text-xs text-gray-500 mt-1.5">প্রতিটি লাইন আলাদা পয়েন্ট হিসেবে সেভ হবে এবং স্টাফ প্যানেল থেকে হুবহু কপি হবে।</p>
            </div>

            <div class="border-t border-gray-200 pt-6 mt-6">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">ঘড়ির ছবি আপলোড করুন</label>
                <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp,image/gif"
                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 transition cursor-pointer border border-gray-200 rounded-md">
                <p class="text-xs text-gray-500 mt-1.5">শুধু JPG, PNG, WEBP, GIF সাপোর্টেড। Ctrl (Windows) বা Cmd (Mac) চেপে একসাথে একাধিক ছবি সিলেক্ট করুন।</p>
            </div>

            <div class="pt-6 border-t border-gray-200">
                <button type="submit" class="w-full bg-gray-900 hover:bg-black text-white font-semibold py-3 px-4 rounded-md transition duration-200 flex justify-center items-center gap-2 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                    </svg>
                    সেভ করুন
                </button>
            </div>
        </form>
    </div>

</body>

</html>