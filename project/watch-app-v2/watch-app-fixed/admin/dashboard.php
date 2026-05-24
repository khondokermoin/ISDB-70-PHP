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

// সব ঘড়ির তথ্য — একটি কোয়েরিতে ছবিও
$stmt = $pdo->query("
    SELECT w.*,
           (SELECT image_url FROM watch_images WHERE watch_id = w.id ORDER BY sort_order ASC LIMIT 1) AS thumb
    FROM watches w
    ORDER BY w.id DESC
");
$watches = $stmt->fetchAll(PDO::FETCH_ASSOC);

// সারসংক্ষেপ তথ্য
$summary = $pdo->query("
    SELECT
        COUNT(*) AS total_watches,
        SUM(buying_price * COALESCE(quantity,0))  AS total_buying_value,
        SUM(selling_price * COALESCE(quantity,0)) AS total_selling_value,
        SUM((selling_price - buying_price) * COALESCE(quantity,0)) AS total_profit,
        SUM(quantity) AS total_stock
    FROM watches
")->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Watch Inventory</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 min-h-screen">

    <nav class="bg-white border-b border-gray-200 py-4 px-6 flex justify-between items-center sticky top-0 z-20">
        <div class="flex items-center gap-3">
            <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-bold text-lg text-gray-900 tracking-tight">Watch Inventory</span>
        </div>
        <div class="flex items-center gap-3">
            <a href="../index.php" class="text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-md transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                </svg>
                View Site
            </a>
            <a href="add_watch.php" class="text-sm font-medium text-white bg-gray-800 hover:bg-gray-900 px-4 py-2 rounded-md transition flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Watch
            </a>
            <a href="logout.php" class="text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 px-4 py-2 rounded-md transition ml-2 border border-red-100">
                Logout
            </a>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 py-8">

        <?php if (!empty($flash)): ?>
            <?php $bg = $flash['type'] === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'; ?>
            <div class="<?= $bg ?> border px-4 py-3 rounded-md mb-6 flex items-center gap-3 shadow-sm">
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200 flex flex-col justify-between h-28">
                <p class="text-sm font-medium text-gray-500">Total Watches</p>
                <p class="text-3xl font-bold text-gray-900"><?= (int)$summary['total_watches'] ?></p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200 flex flex-col justify-between h-28">
                <p class="text-sm font-medium text-gray-500">Total Stock</p>
                <p class="text-3xl font-bold text-blue-600"><?= (int)$summary['total_stock'] ?></p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200 flex flex-col justify-between h-28">
                <p class="text-sm font-medium text-gray-500">Total Investment</p>
                <p class="text-3xl font-bold text-gray-900">৳<?= number_format((float)$summary['total_buying_value']) ?></p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200 flex flex-col justify-between h-28">
                <p class="text-sm font-medium text-gray-500">Est. Profit</p>
                <p class="text-3xl font-bold text-emerald-600">৳<?= number_format((float)$summary['total_profit']) ?></p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

            <div class="px-6 py-5 border-b border-gray-200 flex flex-col md:flex-row justify-between items-center gap-4 bg-white">
                <h2 class="text-lg font-semibold text-gray-800">Inventory List <span class="text-sm font-normal text-gray-500 ml-2">(<?= count($watches) ?> items)</span></h2>
                <div class="relative w-full md:w-64">
                    <svg class="w-4 h-4 absolute left-3 top-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input type="text" id="tableSearch" oninput="filterTable()" placeholder="Search brand or model..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-gray-800 focus:border-gray-800 transition">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap" id="watchTable">
                    <thead class="bg-gray-50 text-gray-500 font-medium border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4">Image</th>
                            <th class="px-6 py-4">Brand</th>
                            <th class="px-6 py-4">Model</th>
                            <th class="px-6 py-4">Details</th>
                            <th class="px-6 py-4">Buying</th>
                            <th class="px-6 py-4">Selling</th>
                            <th class="px-6 py-4">Profit</th>
                            <th class="px-6 py-4">Stock</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <?php foreach ($watches as $w):
                            $profit = $w['selling_price'] - $w['buying_price'];
                            $qty    = (int)($w['quantity'] ?? 0);

                            if ($qty <= 0) {
                                $stockClass = 'bg-red-50 text-red-700 border border-red-100';
                                $stockLabel = 'Out of Stock';
                            } elseif ($qty <= 3) {
                                $stockClass = 'bg-amber-50 text-amber-700 border border-amber-100';
                                $stockLabel = 'Low (' . $qty . ')';
                            } else {
                                $stockClass = 'bg-emerald-50 text-emerald-700 border border-emerald-100';
                                $stockLabel = 'In Stock (' . $qty . ')';
                            }
                        ?>
                            <tr class="hover:bg-gray-50 transition duration-150 watch-row">
                                <td class="px-6 py-3">
                                    <?php if ($w['thumb']): ?>
                                        <img src="../<?= htmlspecialchars($w['thumb'], ENT_QUOTES, 'UTF-8') ?>"
                                            class="w-12 h-12 object-cover rounded border border-gray-200" alt="">
                                    <?php else: ?>
                                        <div class="w-12 h-12 bg-gray-100 rounded border border-gray-200 flex items-center justify-center text-gray-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-3 text-gray-900 font-semibold row-brand"><?= htmlspecialchars($w['brand'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-6 py-3 text-gray-600 row-model"><?= htmlspecialchars($w['model'], ENT_QUOTES, 'UTF-8') ?></td>

                                <td class="px-6 py-3 text-gray-500 text-xs whitespace-normal max-w-xs">
                                    <?php
                                    $desc = htmlspecialchars($w['description'] ?? '', ENT_QUOTES, 'UTF-8');
                                    echo mb_strlen($desc) > 60 ? mb_substr($desc, 0, 60) . '...' : $desc;
                                    ?>
                                </td>

                                <td class="px-6 py-3 text-gray-600">৳<?= number_format((float)$w['buying_price']) ?></td>
                                <td class="px-6 py-3 font-medium text-gray-900">৳<?= number_format((float)$w['selling_price']) ?></td>
                                <td class="px-6 py-3 font-medium text-emerald-600">৳<?= number_format($profit) ?></td>
                                <td class="px-6 py-3">
                                    <span class="<?= $stockClass ?> text-xs px-2.5 py-1 rounded-md font-medium whitespace-nowrap"><?= $stockLabel ?></span>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <div class="flex justify-center gap-3">
                                        <a href="edit_watch.php?id=<?= (int)$w['id'] ?>" class="text-blue-600 hover:text-blue-800 transition font-medium text-sm flex items-center gap-1">
                                            Edit
                                        </a>
                                        <span class="text-gray-300">|</span>
                                        <form action="delete_watch.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this watch? This action cannot be undone.')">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                            <input type="hidden" name="id" value="<?= (int)$w['id'] ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-800 transition font-medium text-sm flex items-center gap-1">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($watches)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <p class="text-gray-500 font-medium">No watches found in inventory.</p>
                                    <p class="text-gray-400 text-sm mt-1">Click "Add Watch" to create your first entry.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function filterTable() {
            const q = document.getElementById('tableSearch').value.toLowerCase();
            document.querySelectorAll('.watch-row').forEach(row => {
                const model = row.querySelector('.row-model')?.innerText.toLowerCase() || '';
                const brand = row.querySelector('.row-brand')?.innerText.toLowerCase() || '';
                row.style.display = (model + brand).includes(q) ? '' : 'none';
            });
        }
    </script>
</body>

</html>