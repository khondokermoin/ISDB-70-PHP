<?php
// ডাটাবেজ কানেকশন
require_once 'config/db.php';

// ডাটাবেজ থেকে সব ঘড়ির তথ্য নিয়ে আসা
try {
    $stmt = $pdo->query("SELECT * FROM watches ORDER BY id DESC");
    $watches = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching watches: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch Inventory - 1 Click Copy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="bg-gray-100 text-gray-800 font-sans">

    <!-- নেভিগেশন বার -->
    <nav class="bg-white shadow-sm py-4 mb-6">
        <div class="max-w-7xl mx-auto px-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-700"> Watch Inventory</h1>

            <!-- ডানদিকের অংশ (স্টাফ ব্যাজ এবং এডমিন লগইন বাটন) -->
            <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-blue-600 bg-blue-50 px-3 py-1 rounded-full hidden sm:block">Staff Panel</span>

                <!-- এডমিন লগইন পেজে যাওয়ার লিংক -->
                <a href="admin/login.php" class="bg-gray-800 hover:bg-gray-900 text-white text-sm px-4 py-1.5 rounded-md font-medium transition flex items-center gap-1">
                    Admin Login
                </a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 pb-10">

        <!-- লাইভ সার্চ বার -->
        <div class="mb-8 flex justify-center">
            <div class="relative w-full md:w-1/2">
                <input type="text" id="searchInput" onkeyup="filterWatches()" placeholder=" ঘড়ির নাম বা মডেল দিয়ে খুঁজুন..."
                    class="w-full px-5 py-3 pl-12 border border-gray-300 rounded-full shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                <span class="absolute left-4 top-3 text-gray-400 text-lg">🔎</span>
            </div>
        </div>

        <!-- ঘড়ির কার্ড গ্রিড -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="watchesContainer">

            <?php foreach ($watches as $watch): ?>
                <!-- সার্চ করার জন্য watch-card ক্লাসটি জরুরি -->
                <div class="watch-card bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 transition-all duration-300 hover:shadow-lg">

                    <!-- ছবি দেখানোর সেকশন -->
                    <div class="flex overflow-x-auto scrollbar-hide snap-x h-64 bg-gray-50 relative group">
                        <?php
                        $imgStmt = $pdo->prepare("SELECT image_url FROM watch_images WHERE watch_id = :watch_id");
                        $imgStmt->execute([':watch_id' => $watch['id']]);
                        $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

                        if (count($images) > 0):
                            foreach ($images as $index => $img):
                        ?>
                                <div class="min-w-full snap-center relative">
                                    <img src="<?= htmlspecialchars($img['image_url']) ?>" alt="Watch Image" class="w-full h-full object-cover">
                                </div>
                            <?php
                            endforeach;
                        else:
                            ?>
                            <div class="flex items-center justify-center w-full h-full text-gray-400">কোনো ছবি নেই</div>
                        <?php endif; ?>
                    </div>

                    <!-- ঘড়ির তথ্য এবং কপি বাটন সেকশন -->
                    <div class="p-5">
                        <div class="flex justify-between items-start mb-2">
                            <!-- ঘড়ির নাম (সার্চের জন্য টার্গেট করা হবে) -->
                            <h2 class="watch-name text-xl font-bold text-gray-800"><?= htmlspecialchars($watch['name']) ?></h2>
                            <span class="bg-gray-100 text-gray-500 text-xs px-2 py-1 rounded">কেনা: ৳<?= $watch['buying_price'] ?></span>
                        </div>

                        <!-- ঘড়ির মডেল (সার্চের জন্য টার্গেট করা হবে) -->
                        <p class="text-gray-600 text-sm mb-4">মডেল: <span class="watch-model font-semibold"><?= htmlspecialchars($watch['model']) ?></span></p>

                        <!-- Hidden Texts for Copy -->
                        <textarea id="short_info_<?= $watch['id'] ?>" class="hidden">
⌚ Product: <?= htmlspecialchars($watch['name']) ?> 
🔖 Model: <?= htmlspecialchars($watch['model']) ?> 
💰 Price: <?= $watch['selling_price'] ?> Tk
                        </textarea>

                        <textarea id="full_info_<?= $watch['id'] ?>" class="hidden">
⌚ Product: <?= htmlspecialchars($watch['name']) ?> 
🔖 Model: <?= htmlspecialchars($watch['model']) ?> 
💰 Price: <?= $watch['selling_price'] ?> Tk

📋 Details:
<?= htmlspecialchars($watch['description']) ?>
                        </textarea>

                        <!-- 1-Click Buttons -->
                        <div class="space-y-3">

                            <?php
                            $imgUrls = [];
                            foreach ($images as $img) {
                                $imgUrls[] = $img['image_url'];
                            }
                            $urlsString = implode(',', $imgUrls);
                            ?>

                            <?php if (count($imgUrls) > 0): ?>
                                <button onclick="downloadAllImages('<?= $urlsString ?>', '<?= htmlspecialchars($watch['name']) ?>')" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm px-4 py-2.5 rounded-lg transition flex justify-center items-center gap-2">
                                    📥 সব ছবি ডাউনলোড করুন (<?= count($imgUrls) ?> টি)
                                </button>
                            <?php endif; ?>

                            <div class="flex items-center justify-between bg-blue-50 p-3 rounded-lg border border-blue-100">
                                <div>
                                    <span class="block text-xs text-blue-500 font-semibold uppercase tracking-wider">বিক্রির দাম</span>
                                    <span class="text-lg font-bold text-blue-700">৳<?= $watch['selling_price'] ?></span>
                                </div>
                                <button onclick="copyText('short_info_<?= $watch['id'] ?>', 'শর্ট ইনফো')" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-md transition">
                                    Copy Price
                                </button>
                            </div>

                            <button onclick="copyText('full_info_<?= $watch['id'] ?>', 'সম্পূর্ণ তথ্য')" class="w-full bg-gray-800 hover:bg-gray-900 text-white font-medium text-sm px-4 py-2.5 rounded-lg transition">
                                📝 Copy Full Details
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (count($watches) === 0): ?>
                <div class="col-span-full text-center py-10 bg-white rounded-lg shadow-sm border border-gray-200">
                    <p class="text-gray-500 text-lg">এখনো কোনো ঘড়ি যোগ করা হয়নি।</p>
                </div>
            <?php endif; ?>

            <!-- নো রেজাল্ট মেসেজ (সার্চে কিছু না পেলে দেখাবে) -->
            <div id="noResultMessage" class="hidden col-span-full text-center py-10 bg-white rounded-lg shadow-sm border border-gray-200">
                <p class="text-gray-500 text-lg">এই নাম বা মডেলের কোনো ঘড়ি পাওয়া যায়নি। ❌</p>
            </div>

        </div>
    </div>

    <!-- Custom JS ফাইল লিঙ্ক -->
    <script src="assets/js/script.js"></script>

    <!-- লাইভ সার্চের জন্য ছোট স্ক্রিপ্ট -->
    <script>
        function filterWatches() {
            // ইনপুট থেকে সার্চের লেখা নেওয়া
            let input = document.getElementById('searchInput').value.toLowerCase();
            let cards = document.getElementsByClassName('watch-card');
            let hasResult = false;

            // প্রতিটি কার্ড চেক করা
            for (let i = 0; i < cards.length; i++) {
                let name = cards[i].querySelector('.watch-name').innerText.toLowerCase();
                let model = cards[i].querySelector('.watch-model').innerText.toLowerCase();

                // যদি নাম বা মডেলের সাথে সার্চের লেখা মিলে যায়
                if (name.includes(input) || model.includes(input)) {
                    cards[i].style.display = ""; // কার্ড দেখাবে
                    hasResult = true;
                } else {
                    cards[i].style.display = "none"; // কার্ড লুকিয়ে ফেলবে
                }
            }

            // যদি কোনো ঘড়ি না পাওয়া যায়, তাহলে নো রেজাল্ট মেসেজ দেখাবে
            document.getElementById('noResultMessage').style.display = hasResult ? "none" : "block";
        }
    </script>
</body>

</html>