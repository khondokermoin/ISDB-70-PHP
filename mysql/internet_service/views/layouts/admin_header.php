<?php
// Detect the current page to highlight the active menu item
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// 🔥 অ্যাডমিনের সব নোটিফিকেশন মডালের জন্য আনা হচ্ছে
$allNotifs = [];
if (isset($db) && isset($_SESSION['user_id'])) {
    $notifStmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY sent_at DESC");
    $notifStmt->execute([$_SESSION['user_id']]);
    $allNotifs = $notifStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Admin Dashboard - Amar IT</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 font-sans flex">

    <div class="w-64 bg-gray-900 text-white min-h-screen flex flex-col shadow-xl">
        <div class="p-6 border-b border-gray-800 text-center">
            <h2 class="text-2xl font-bold text-red-500">AMAR <span class="text-white">IT</span></h2>
            <p class="text-xs text-gray-400 mt-1">Admin Panel</p>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="admin.php?page=dashboard" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage == 'dashboard') ? 'bg-gray-800 text-white border-l-4 border-red-500' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fa fa-tachometer-alt w-6"></i> Dashboard
            </a>

            <a href="admin.php?page=expenses" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage == 'expenses') ? 'bg-gray-800 text-white border-l-4 border-red-500' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fa fa-wallet w-6"></i> Expenses & Profit
            </a>

            <a href="admin.php?page=packages" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage == 'packages' || $currentPage == 'create_package' || $currentPage == 'edit_package') ? 'bg-gray-800 text-white border-l-4 border-red-500' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fa fa-box w-6"></i> Packages
            </a>

            <a href="admin.php?page=users" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage == 'users') ? 'bg-gray-800 text-white border-l-4 border-red-500' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fa fa-users w-6"></i> Manage Customers
            </a>

            <a href="admin.php?page=staff" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage == 'staff') ? 'bg-gray-800 text-white border-l-4 border-red-500' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fa fa-user-tie w-6"></i> Manage Staff
            </a>

            <a href="admin.php?page=billings" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage == 'billings') ? 'bg-gray-800 text-white border-l-4 border-red-500' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fa fa-file-invoice-dollar w-6"></i> Billings
            </a>

            <a href="admin.php?page=tickets" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage == 'tickets') ? 'bg-gray-800 text-white border-l-4 border-red-500' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fa fa-headset w-6"></i> Support Tickets
            </a>
        </nav>

        <div class="p-4 border-t border-gray-800">
            <a href="logout.php" class="flex items-center px-4 py-2 text-red-400 hover:text-red-500 transition">
                <i class="fa fa-sign-out-alt w-6"></i> Logout
            </a>
        </div>
    </div>

    <div class="flex-1 flex flex-col">
        <header class="bg-white shadow px-6 py-4 flex justify-between items-center relative z-20">
            <h1 class="text-xl font-semibold text-gray-800">Control Panel</h1>
            <div class="flex items-center space-x-6">
                <button onclick="toggleNotifModal()" class="relative text-gray-500 hover:text-red-500 transition">
                    <i class="fa fa-bell text-2xl"></i>
                    <?php if (count($allNotifs) > 0): ?>
                        <span class="absolute -top-1 -right-2 bg-red-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white"><?php echo count($allNotifs); ?></span>
                    <?php endif; ?>
                </button>
                <span class="text-gray-600 border-l pl-6"><i class="fa fa-user-circle mr-2 text-gray-400 text-xl"></i> Admin</span>
            </div>
        </header>

        <div id="notifModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex justify-center items-center">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg max-h-[80vh] flex flex-col overflow-hidden transform transition-all scale-100">
                <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-800"><i class="fa fa-bell text-red-500 mr-2"></i> All Notifications</h3>
                    <button onclick="toggleNotifModal()" class="text-gray-400 hover:text-red-500 transition"><i class="fa fa-times text-2xl"></i></button>
                </div>
                <div class="p-4 overflow-y-auto flex-1 bg-gray-50">
                    <?php if (count($allNotifs) > 0): ?>
                        <ul class="space-y-3">
                            <?php foreach ($allNotifs as $n): ?>
                                <li class="bg-white p-4 rounded-lg border border-gray-200 flex items-start shadow-sm hover:shadow transition">
                                    <div class="bg-red-100 text-red-600 rounded-full w-8 h-8 flex items-center justify-center mr-3 flex-shrink-0 mt-1">
                                        <i class="fa fa-bolt text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-800 font-medium"><?php echo htmlspecialchars($n['message']); ?></p>
                                        <span class="text-xs text-gray-400 mt-1 inline-block"><i class="fa fa-clock mr-1"></i><?php echo isset($n['sent_at']) ? date("d M Y, h:i A", strtotime($n['sent_at'])) : 'Just now'; ?></span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center py-10">
                            <i class="fa fa-inbox text-5xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500 font-semibold">No notifications found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script>
            function toggleNotifModal() {
                const modal = document.getElementById('notifModal');
                modal.classList.toggle('hidden');
            }
        </script>

        <main class="p-6 flex-1 overflow-y-auto">