<?php
// Detect the current page to highlight the active menu item
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// 🔥 অ্যাডমিনের সব নোটিফিকেশন মডালের জন্য আনা হচ্ছে
$allNotifs   = [];
$unreadCount = 0;

if (isset($db) && isset($_SESSION['user_id'])) {
    $notifStmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY sent_at DESC");
    $notifStmt->execute([$_SESSION['user_id']]);
    $allNotifs = $notifStmt->fetchAll(PDO::FETCH_ASSOC);

    // আনরিড নোটিফিকেশন গোনা
    foreach ($allNotifs as $n) {
        if (isset($n['is_read']) && $n['is_read'] == 0) {
            $unreadCount++;
        }
    }
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

    <!-- ===== SIDEBAR ===== -->
    <div class="w-64 bg-gray-900 text-white min-h-screen flex flex-col shadow-xl">
        <div class="p-6 border-b border-gray-800 text-center">
            <h2 class="text-2xl font-bold text-red-500">AMAR <span class="text-white">IT</span></h2>
            <p class="text-xs text-gray-400 mt-1">Admin Panel</p>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="admin.php?page=dashboard" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage == 'dashboard') ? 'bg-gray-800 text-white border-l-4 border-red-500' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fa fa-tachometer-alt w-6"></i>&nbsp; Dashboard
            </a>

            <a href="admin.php?page=expenses" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage == 'expenses') ? 'bg-gray-800 text-white border-l-4 border-red-500' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fa fa-wallet w-6"></i>&nbsp; Expenses & Profit
            </a>

            <a href="admin.php?page=packages" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage == 'packages' || $currentPage == 'create_package' || $currentPage == 'edit_package') ? 'bg-gray-800 text-white border-l-4 border-red-500' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fa fa-box w-6"></i>&nbsp; Packages
            </a>

            <a href="admin.php?page=users" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage == 'users') ? 'bg-gray-800 text-white border-l-4 border-red-500' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fa fa-users w-6"></i>&nbsp; Manage Customers
            </a>

            <a href="admin.php?page=staff" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage == 'staff') ? 'bg-gray-800 text-white border-l-4 border-red-500' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fa fa-user-tie w-6"></i>&nbsp; Manage Staff
            </a>

            <a href="admin.php?page=coverage_admin" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage == 'coverage_admin') ? 'bg-gray-800 text-white border-l-4 border-red-600' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fa fa-map-marked-alt w-6"></i>&nbsp; Coverage Areas
            </a>

            <a href="admin.php?page=billings" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage == 'billings') ? 'bg-gray-800 text-white border-l-4 border-red-500' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fa fa-file-invoice-dollar w-6"></i>&nbsp; Billings
            </a>

            <a href="admin.php?page=tickets" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage == 'tickets') ? 'bg-gray-800 text-white border-l-4 border-red-500' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fa fa-headset w-6"></i>&nbsp; Support Tickets
            </a>
        </nav>

        <div class="p-4 border-t border-gray-800">
            <a href="logout.php" class="flex items-center px-4 py-2 text-red-400 hover:text-red-500 transition">
                <i class="fa fa-sign-out-alt w-6"></i>&nbsp; Logout
            </a>
        </div>
    </div>

    <!-- ===== MAIN CONTENT AREA ===== -->
    <div class="flex-1 flex flex-col">

        <!-- Top Header Bar -->
        <header class="bg-white shadow px-6 py-4 flex justify-between items-center relative z-20">
            <h1 class="text-xl font-semibold text-gray-800">Control Panel</h1>
            <div class="flex items-center space-x-6">

                <!-- Bell Icon with Unread Badge -->
                <button onclick="toggleNotifModal()" class="relative text-gray-500 hover:text-red-500 transition">
                    <i class="fa fa-bell text-2xl"></i>
                    <?php if ($unreadCount > 0): ?>
                        <span class="absolute -top-1 -right-2 bg-red-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white">
                            <?php echo $unreadCount; ?>
                        </span>
                    <?php endif; ?>
                </button>

                <span class="text-gray-600 border-l pl-6">
                    <i class="fa fa-user-circle mr-2 text-gray-400 text-xl"></i> Admin
                </span>
            </div>
        </header>

        <!-- ===== NOTIFICATION MODAL ===== -->
        <div id="notifModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex justify-center items-center">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg max-h-[80vh] flex flex-col overflow-hidden">

                <!-- Modal Header -->
                <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-800">
                        <i class="fa fa-bell text-red-500 mr-2"></i> Notifications
                    </h3>
                    <button onclick="toggleNotifModal()" class="text-gray-400 hover:text-red-500 transition">
                        <i class="fa fa-times text-2xl"></i>
                    </button>
                </div>

                <!-- Notification List -->
                <div class="p-4 overflow-y-auto flex-1 bg-gray-50">
                    <?php if (count($allNotifs) > 0): ?>
                        <ul class="space-y-3">
                            <?php foreach ($allNotifs as $n):
                                $isUnread = isset($n['is_read']) && $n['is_read'] == 0;
                                $msgLower = strtolower($n['message']);

                                // ✅ FIX: Safely read notification ID — works with both 'id' and 'notification_id' column names
                                $notifId = isset($n['notification_id']) ? (int)$n['notification_id']
                                    : (isset($n['id'])            ? (int)$n['id'] : 0);
                            ?>
                                <li class="rounded-lg border transition-all duration-300 shadow-sm <?php echo $isUnread ? 'bg-white border-red-200 shadow-md' : 'bg-gray-50 border-gray-200 opacity-75'; ?>">

                                    <!-- ✅ FIX: admin_actions.php → admin.php (handler lives in admin.php) -->
                                    <a href="admin.php?action=read_and_redirect&notif_id=<?php echo $notifId; ?>" class="block p-4 group">
                                        <div class="flex items-start">

                                            <!-- Dynamic Icon based on message content -->
                                            <div class="<?php echo $isUnread ? 'bg-red-100 text-red-600' : 'bg-gray-200 text-gray-500'; ?> rounded-full w-9 h-9 flex items-center justify-center mr-3 flex-shrink-0 transition-colors">
                                                <?php if (strpos($msgLower, 'payment') !== false): ?>
                                                    <i class="fa fa-hand-holding-dollar text-sm"></i>
                                                <?php elseif (strpos($msgLower, 'ticket') !== false || strpos($msgLower, 'job') !== false): ?>
                                                    <i class="fa fa-headset text-sm"></i>
                                                <?php elseif (strpos($msgLower, 'upgrade') !== false): ?>
                                                    <i class="fa fa-arrow-up text-sm"></i>
                                                <?php elseif (strpos($msgLower, 'order') !== false || strpos($msgLower, 'connection') !== false): ?>
                                                    <i class="fa fa-plug text-sm"></i>
                                                <?php else: ?>
                                                    <i class="fa fa-bolt text-sm"></i>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Message + Timestamp -->
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium leading-tight <?php echo $isUnread ? 'text-gray-800' : 'text-gray-500'; ?>">
                                                    <?php echo htmlspecialchars($n['message']); ?>
                                                </p>
                                                <span class="text-[10px] text-gray-400 mt-1 flex items-center">
                                                    <i class="fa fa-clock mr-1"></i>
                                                    <?php echo isset($n['sent_at']) ? date("d M Y, h:i A", strtotime($n['sent_at'])) : 'Just now'; ?>
                                                </span>
                                            </div>

                                            <!-- Unread red dot indicator -->
                                            <?php if ($isUnread): ?>
                                                <span class="ml-2 mt-1.5 w-2 h-2 bg-red-500 rounded-full flex-shrink-0"></span>
                                            <?php endif; ?>

                                        </div>
                                    </a>
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

                <!-- Modal Footer: Mark All Read -->
                <?php if ($unreadCount > 0): ?>
                    <div class="p-3 bg-white text-center border-t border-gray-200">
                        <!-- ✅ FIX: admin_actions.php → admin.php -->
                        <a href="admin.php?action=mark_notifs_read" class="text-sm font-bold text-blue-600 hover:text-blue-800 transition">
                            <i class="fa fa-check-double mr-1"></i> Mark all as read
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        </div>

        <script>
            function toggleNotifModal() {
                const modal = document.getElementById('notifModal');
                modal.classList.toggle('hidden');
            }

            // ✅ Close modal when clicking the dark backdrop
            document.getElementById('notifModal').addEventListener('click', function(e) {
                if (e.target === this) toggleNotifModal();
            });
        </script>

        <main class="p-6 flex-1 overflow-y-auto">