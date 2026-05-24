<?php
// সিকিউরিটি: লগইন করা না থাকলে পেজে ঢুকতে পারবে না
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Watch - Admin Panel</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-800 font-sans p-6">

    <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow-md mt-10">
        <div class="flex justify-between items-center mb-6 border-b pb-3">
            <h2 class="text-2xl font-bold text-gray-700">ঘড়ির নতুন তথ্য যোগ করুন</h2>
            <a href="dashboard.php" class="text-blue-600 hover:underline">← Back to Dashboard</a>
        </div>

        <!-- ফর্ম শুরু, enctype="multipart/form-data" ছবি আপলোডের জন্য বাধ্যতামূলক -->
        <form action="process_watch.php" method="POST" enctype="multipart/form-data" class="space-y-5">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ঘড়ির নাম</label>
                    <input type="text" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="e.g. Casio G-Shock">
                </div>

                <!-- Model -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">মডেল নাম্বার</label>
                    <input type="text" name="model" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="e.g. GA-110">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Buying Price -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">কেনার দাম (৳)</label>
                    <input type="number" step="0.01" name="buying_price" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="e.g. 5000">
                </div>

                <!-- Selling Price -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">বিক্রির দাম (৳)</label>
                    <input type="number" step="0.01" name="selling_price" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="e.g. 6500">
                </div>
            </div>

            <!-- Description (Updated for Format) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ডেসক্রিপশন (পয়েন্ট আকারে লিখুন)</label>
                <textarea name="description" rows="10" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Product Type: Men’s Sport / Fashion Watch&#10;Case Material: Resin Alloy&#10;Case Shape: Round&#10;Dial Diameter: 50mm..."></textarea>
                <p class="text-xs text-gray-500 mt-1">এখানে আপনি ঠিক যেভাবে লাইন ব্রেক (Enter) দিয়ে পয়েন্টগুলো লিখবেন, কপি করার পর হুবহু সেভাবেই মেসেঞ্জারে যাবে।</p>
            </div>

            <!-- Multiple Images Upload -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ঘড়ির ছবি (একাধিক ছবি সিলেক্ট করতে পারবেন)</label>
                <!-- name="images[]" দেওয়া হয়েছে যাতে পিএইচপি এটাকে এরে (Array) হিসেবে নিতে পারে -->
                <input type="file" name="images[]" multiple accept="image/*" required class="w-full text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="text-xs text-gray-500 mt-1">Ctrl (Windows) বা Cmd (Mac) চেপে একসাথে একাধিক ছবি সিলেক্ট করুন।</p>
            </div>

            <!-- Submit Button -->
            <div class="pt-4">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-md transition duration-300">
                    সেভ করুন
                </button>
            </div>
        </form>
    </div>

</body>

</html>