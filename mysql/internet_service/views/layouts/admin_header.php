<?php
// ─── SESSION SECURITY ────────────────────────────────────────────────────────
// session cookie hardening — XSS/CSRF থেকে session চুরি আটকায়
// user_dashboard.php-এও একই pattern apply করা হয়েছে

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── PAGE WHITELIST ──────────────────────────────────────────────────────────
// [USER v2 FIX 1] $_GET['page'] whitelist validation
$allowedPages = ['dashboard', 'expenses', 'packages', 'create_package', 'edit_package', 'users', 'staff', 'coverage_admin', 'billings', 'tickets'];
$currentPage = (isset($_GET['page']) && in_array($_GET['page'], $allowedPages)) ? $_GET['page'] : 'dashboard';

// ─── CSRF TOKEN ──────────────────────────────────────────────────────────────
// [USER v2 FIX 2] markAllAsReadAjax() POST request verify করার জন্য
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];    // camelCase naming

// ─── DB QUERIES ──────────────────────────────────────────────────────────────
$allNotifs   = [];
$unreadCount = 0;
$adminInfo   = null;

if (isset($db) && isset($_SESSION['user_id'])) {

    // [USER v2] অ্যাডমিনের নাম ও ID আলাদা query-তে আনা — sidebar/dropdown-এ ব্যবহার হয়
    $adminStmt = $db->prepare("SELECT user_id, full_name FROM users WHERE user_id = ?");
    $adminStmt->execute([$_SESSION['user_id']]);
    $adminInfo = $adminStmt->fetch(PDO::FETCH_ASSOC);

    // [USER v2 FIX 3] SELECT * এর বদলে explicit columns
    // notification_id সবসময় present থাকায় $notifId lookup নির্ভরযোগ্য
    $notifStmt = $db->prepare("SELECT notification_id, message, is_read, sent_at FROM notifications WHERE user_id = ? ORDER BY sent_at DESC");
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

<body class="bg-gray-100 font-sans flex overflow-hidden h-screen">

    <!-- ===== MOBILE SIDEBAR OVERLAY ===== -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden transition-opacity" onclick="toggleSidebar()"></div>

    <!-- ===== SIDEBAR ===== -->
    <aside id="sidebar" class="w-64 bg-gray-900 text-white h-screen flex flex-col shadow-xl fixed md:relative z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300">

        <div class="p-6 border-b border-gray-800 flex justify-between items-center">
            <div class="text-center w-full">
                <h2 class="text-2xl font-bold text-red-500">AMAR <span class="text-white">IT</span></h2>
                <p class="text-xs text-gray-400 mt-1 tracking-widest uppercase">Admin Panel</p>
            </div>
            <button onclick="toggleSidebar()" class="md:hidden text-gray-400 hover:text-white transition">
                <i class="fa fa-times text-xl"></i>
            </button>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto custom-scrollbar">
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

            <!-- FIX 4: border-red-600 → border-red-500 for consistency -->
            <a href="admin.php?page=coverage_admin" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage == 'coverage_admin') ? 'bg-gray-800 text-white border-l-4 border-red-500' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
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
    </aside>

    <!-- ===== MAIN CONTENT AREA ===== -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden relative">

        <!-- Top Header Bar -->
        <header class="bg-white shadow-sm border-b border-gray-200 px-4 md:px-6 py-3 flex justify-between items-center relative z-20">

            <div class="flex items-center">
                <button onclick="toggleSidebar()" class="text-gray-600 hover:text-red-500 focus:outline-none mr-4 md:hidden transition">
                    <i class="fa fa-bars text-2xl"></i>
                </button>
                <h1 class="text-xl font-bold text-gray-800 hidden sm:block">Control Panel</h1>
            </div>

            <!-- [USER v2 FIX 5] Proper <form> instead of bare input+onkeydown
                 Semantic HTML — works without JS, accessible, correct submit behaviour -->
            <form action="admin.php" method="GET" id="globalSearch" class="hidden md:flex flex-1 max-w-md mx-6 relative group">
                <input type="hidden" name="page" value="users">
                <input type="text" name="search" placeholder="Search customers by name, phone or ID..."
                    class="w-full bg-gray-100 border border-transparent rounded-full px-5 py-2 pl-10 text-sm focus:bg-white focus:border-red-300 focus:ring-2 focus:ring-red-100 outline-none transition-all text-gray-700">
                <button type="submit" class="absolute left-4 top-2.5 text-gray-400 group-focus-within:text-red-500 transition-colors">
                    <i class="fa fa-search"></i>
                </button>
            </form>

            <div class="flex items-center space-x-4 md:space-x-6">
                <!-- [USER v2 FIX 6] Mobile search icon → toggleMobileSearch() -->
                <button onclick="toggleMobileSearch()" class="md:hidden text-gray-500 hover:text-red-500 transition">
                    <i class="fa fa-search text-xl"></i>
                </button>

                <!-- Notification Bell -->
                <button onclick="toggleNotifModal()" class="relative text-gray-500 hover:text-red-500 transition focus:outline-none">
                    <i class="fa fa-bell text-2xl"></i>
                    <?php if ($unreadCount > 0): ?>
                        <span id="notifBadge" class="absolute -top-1 -right-2 bg-red-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white transition-transform transform hover:scale-110">
                            <?php echo $unreadCount; ?>
                        </span>
                    <?php endif; ?>
                </button>

                <!-- Admin Profile Dropdown -->
                <div class="relative border-l pl-4 md:pl-6">
                    <button onclick="toggleProfileMenu()" class="flex items-center text-gray-600 hover:text-red-600 focus:outline-none transition group">
                        <i class="fa fa-user-circle text-gray-400 group-hover:text-red-500 text-2xl md:text-xl md:mr-2 transition-colors"></i>
                        <span class="hidden md:inline-block font-semibold">
                            <!-- [USER v2] $adminInfo থেকে real name দেখানো — hardcoded "Admin" নয় -->
                            <?php echo $adminInfo ? htmlspecialchars($adminInfo['full_name']) : 'Admin'; ?>
                        </span>
                        <i class="fa fa-chevron-down ml-2 text-[10px] text-gray-400 group-hover:text-red-500 hidden md:block transition-colors"></i>
                    </button>

                    <div id="profileMenu" class="absolute right-0 mt-3 w-48 bg-white rounded-xl shadow-lg border border-gray-100 hidden z-50 overflow-hidden transform origin-top-right transition-all">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 text-center">
                            <p class="text-sm font-bold text-gray-800 truncate"><?php echo $adminInfo ? htmlspecialchars($adminInfo['full_name']) : 'Administrator'; ?></p>
                            <!-- [USER v2] str_pad দিয়ে ID format #001, #023 etc. — hardcoded #001 নয় -->
                            <p class="text-xs text-gray-500 font-mono mt-0.5">ID: #<?php echo $adminInfo ? str_pad($adminInfo['user_id'], 3, '0', STR_PAD_LEFT) : '001'; ?></p>
                        </div>
                        <a href="#" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition"><i class="fa fa-cog mr-2 w-4"></i> System Settings</a>
                        <a href="logout.php" class="block px-4 py-2.5 text-sm text-red-600 font-semibold hover:bg-red-50 transition"><i class="fa fa-sign-out-alt mr-2 w-4"></i> Secure Logout</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- [USER v2 FIX 7] Mobile Search Bar — absolutely positioned, toggleMobileSearch() দিয়ে দেখানো/লুকানো -->
        <div id="mobileSearchBar" class="hidden absolute top-[60px] left-0 w-full bg-white border-b border-gray-200 p-3 z-10 md:hidden shadow-sm">
            <form action="admin.php" method="GET" class="relative">
                <input type="hidden" name="page" value="users">
                <input type="text" name="search" placeholder="Search customers..."
                    class="w-full bg-gray-100 border border-transparent rounded-full px-5 py-2 pl-10 text-sm focus:bg-white focus:border-red-300 focus:ring-2 focus:ring-red-100 outline-none text-gray-700">
                <button type="submit" class="absolute left-4 top-2.5 text-gray-400">
                    <i class="fa fa-search"></i>
                </button>
            </form>
        </div>

        <!-- ===== NOTIFICATION MODAL ===== -->
        <!-- [USER v2 FIX 8] flex class HTML-এ নেই — JS-এ style.display='flex' set করা হয়
             hidden class দিয়ে state track করা হয়, flex dynamically add হয় -->
        <div id="notifModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden justify-center items-center transition-opacity backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[85vh] flex flex-col overflow-hidden mx-4 transform scale-95 transition-all duration-300 ease-out" id="notifContent">

                <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-white">
                    <h3 class="text-lg font-bold text-gray-800">
                        <i class="fa fa-bell text-red-500 mr-2"></i> Notifications
                    </h3>
                    <button onclick="toggleNotifModal()" class="text-gray-400 hover:bg-gray-100 hover:text-red-500 w-8 h-8 rounded-full flex items-center justify-center transition">
                        <i class="fa fa-times text-lg"></i>
                    </button>
                </div>

                <div class="p-4 overflow-y-auto flex-1 bg-gray-50 custom-scrollbar">
                    <?php if (count($allNotifs) > 0): ?>
                        <ul class="space-y-3" id="notifList">
                            <?php foreach ($allNotifs as $n):
                                $notifId = isset($n['notification_id']) ? (int)$n['notification_id'] : 0;

                                // [USER v2 FIX 9] invalid ID হলে continue — notif_id=0 link render হবে না
                                if ($notifId <= 0) continue;

                                $isUnread = isset($n['is_read']) && $n['is_read'] == 0;
                                $msgLower = strtolower($n['message']);
                            ?>
                                <li class="rounded-xl border transition-all duration-300 shadow-sm <?php echo $isUnread ? 'bg-white border-red-200 shadow-md notif-unread' : 'bg-gray-50 border-gray-200 opacity-75'; ?>">
                                    <a href="admin.php?action=read_and_redirect&notif_id=<?php echo $notifId; ?>" class="block p-4 group">
                                        <div class="flex items-start">
                                            <div class="<?php echo $isUnread ? 'bg-red-100 text-red-600' : 'bg-gray-200 text-gray-500'; ?> rounded-full w-10 h-10 flex items-center justify-center mr-3 flex-shrink-0 transition-colors">
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

                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium leading-snug <?php echo $isUnread ? 'text-gray-800' : 'text-gray-500'; ?>">
                                                    <?php echo htmlspecialchars($n['message']); ?>
                                                </p>
                                                <span class="text-[10px] text-gray-400 mt-1.5 flex items-center font-mono">
                                                    <i class="fa fa-clock mr-1"></i>
                                                    <?php echo isset($n['sent_at']) ? date("d M Y, h:i A", strtotime($n['sent_at'])) : 'Just now'; ?>
                                                </span>
                                            </div>

                                            <?php if ($isUnread): ?>
                                                <span class="ml-2 mt-2 w-2.5 h-2.5 bg-red-500 rounded-full flex-shrink-0 unread-dot animate-pulse"></span>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fa fa-bell-slash text-3xl text-gray-300"></i>
                            </div>
                            <p class="text-gray-500 font-bold">You're all caught up!</p>
                            <p class="text-xs text-gray-400 mt-1">No new notifications to display.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($unreadCount > 0): ?>
                    <div class="p-3 bg-white text-center border-t border-gray-100" id="markReadContainer">
                        <button onclick="markAllAsReadAjax()" class="text-sm font-bold text-red-600 hover:text-red-800 transition flex items-center justify-center w-full py-2 rounded-lg hover:bg-red-50">
                            <i class="fa fa-check-double mr-2"></i> Mark all as read
                        </button>
                    </div>
                <?php endif; ?>

            </div>
        </div>

        <!-- [USER v2 FIX 10 + MY VERSION] <style> before <script> — FOUC (Flash of Unstyled Content) আটকায় -->
        <style>
            .custom-scrollbar::-webkit-scrollbar {
                width: 4px;
            }

            .custom-scrollbar::-webkit-scrollbar-track {
                background: transparent;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 4px;
            }

            .custom-scrollbar:hover::-webkit-scrollbar-thumb {
                background: #94a3b8;
            }
        </style>

        <script>
            // ── 1. Sidebar Toggle ─────────────────────────────────────────────
            function toggleSidebar() {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebarOverlay');
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            }

            // ── 2. Mobile Search Toggle ───────────────────────────────────────
            // [USER v2 FIX 6] আগে onclick ছিলই না — এখন dedicated function
            function toggleMobileSearch() {
                const mobileSearch = document.getElementById('mobileSearchBar');
                mobileSearch.classList.toggle('hidden');
            }

            // ── 3. Profile Dropdown ───────────────────────────────────────────
            function toggleProfileMenu() {
                const menu = document.getElementById('profileMenu');
                menu.classList.toggle('hidden');
            }

            // Close Profile Menu on outside click
            document.addEventListener('click', function(event) {
                const menu = document.getElementById('profileMenu');
                const btn = menu.previousElementSibling;
                if (!menu.contains(event.target) && !btn.contains(event.target)) {
                    menu.classList.add('hidden');
                }
            });

            // ── 4. Notification Modal ─────────────────────────────────────────
            // [USER v2 FIX 11] hidden class দিয়ে state track + style.display='flex' dynamically
            // classList.replace() এর বদলে remove/add — silent fail এড়াতে
            function toggleNotifModal() {
                const modal = document.getElementById('notifModal');
                const content = document.getElementById('notifContent');

                if (modal.classList.contains('hidden')) {

                    // Show modal
                    modal.classList.remove('hidden');
                    modal.style.display = 'flex';

                    // Initial state
                    modal.style.opacity = '0';
                    content.style.opacity = '0';
                    content.style.transform = 'translateY(20px) scale(0.95)';

                    requestAnimationFrame(() => {
                        modal.style.transition = 'opacity 0.25s ease';
                        content.style.transition = 'all 0.3s ease';

                        modal.style.opacity = '1';
                        content.style.opacity = '1';
                        content.style.transform = 'translateY(0) scale(1)';
                    });

                } else {

                    // Hide animation
                    modal.style.opacity = '0';
                    content.style.opacity = '0';
                    content.style.transform = 'translateY(20px) scale(0.95)';

                    setTimeout(() => {
                        modal.classList.add('hidden');
                        modal.style.display = '';
                    }, 300);
                }
            }

            // Close Modal on outside click
            document.getElementById('notifModal').addEventListener('click', function(e) {
                if (e.target === this) toggleNotifModal();
            });

            // ── 5. Mark All as Read (AJAX) ────────────────────────────────────
            // [USER v2 FIX 12 + MY VERSION]
            // CRITICAL: GET → POST + CSRF token (CSRF attack আটকায়)
            // MEDIUM: res.ok check — server error হলে UI update হবে না
            // MEDIUM: .catch() alert — user-কে error জানানো হয়
            // card update: classList.remove/add — replace() এর silent fail নেই
            function markAllAsReadAjax() {
                const formData = new FormData();
                formData.append('csrf_token', '<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, "UTF-8"); ?>');

                fetch('admin.php?action=mark_notifs_read', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Server response error: ' + res.status);

                        // Badge hide
                        const badge = document.getElementById('notifBadge');
                        if (badge) badge.style.display = 'none';

                        // Red dot hide
                        document.querySelectorAll('.unread-dot').forEach(dot => dot.style.display = 'none');

                        // Card style: unread → read (remove/add — no silent fails)
                        document.querySelectorAll('.notif-unread').forEach(card => {
                            card.classList.remove('bg-white', 'border-red-200', 'notif-unread');
                            card.classList.add('bg-gray-50', 'border-gray-200', 'opacity-75');

                            const iconBox = card.querySelector('.bg-red-100');
                            if (iconBox) {
                                iconBox.classList.remove('bg-red-100', 'text-red-600');
                                iconBox.classList.add('bg-gray-200', 'text-gray-500');
                            }

                            const textP = card.querySelector('p.text-gray-800');
                            if (textP) {
                                textP.classList.remove('text-gray-800');
                                textP.classList.add('text-gray-500');
                            }
                        });

                        const btnContainer = document.getElementById('markReadContainer');
                        if (btnContainer) btnContainer.style.display = 'none';
                    })
                    .catch(err => {
                        console.error("Error marking notifications as read:", err);
                        alert("Failed to mark notifications as read. Please try again.");
                    });
            }

            // ── 6. 🔥 AUTO-REFRESH NOTIFICATION BADGE (প্রতি ৩০ সেকেন্ড) ─────
            // নতুন notification এলে badge auto-update হবে, পেজ refresh করতে হবে না
            function refreshNotificationBadge() {
                fetch('admin.php?action=get_unread_count')
                    .then(res => {
                        if (!res.ok) throw new Error('Network response was not ok');
                        return res.json();
                    })
                    .then(data => {
                        const badge = document.getElementById('notifBadge');
                        const newCount = parseInt(data.unread) || 0;

                        if (newCount > 0) {
                            if (badge) {
                                // আগে কত ছিল সেটা দেখি
                                const oldCount = parseInt(badge.textContent) || 0;

                                // নতুন notification এলে pulse effect
                                if (newCount > oldCount) {
                                    badge.classList.add('animate-pulse');
                                    setTimeout(() => badge.classList.remove('animate-pulse'), 2000);
                                }

                                badge.textContent = newCount;
                                badge.style.display = 'inline-block';
                            } else {
                                // Badge না থাকলে নতুন করে তৈরি করি
                                const bell = document.querySelector('button[onclick="toggleNotifModal()"]');
                                if (bell) {
                                    const newBadge = document.createElement('span');
                                    newBadge.id = 'notifBadge';
                                    newBadge.className = 'absolute -top-1 -right-2 bg-red-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white animate-pulse';
                                    newBadge.textContent = newCount;
                                    bell.appendChild(newBadge);

                                    setTimeout(() => newBadge.classList.remove('animate-pulse'), 2000);
                                }
                            }
                        } else {
                            // সব পড়া হয়ে গেলে badge লুকিয়ে ফেলি
                            if (badge) badge.style.display = 'none';
                        }
                    })
                    .catch(err => {
                        // Silent fail — user-কে বিরক্ত করব না, শুধু console-এ লিখব
                        console.warn('Notification refresh failed:', err.message);
                    });
            }

            // 🔁 প্রতি ৩০ সেকেন্ড পর পর check করবে
            setInterval(refreshNotificationBadge, 30000);

            // ⚡ পেজ লোড হওয়ার ৫ সেকেন্ড পর প্রথমবার check (যদি এই সময়ে নতুন notification আসে)
            setTimeout(refreshNotificationBadge, 5000);
        </script>

        <main class="p-4 md:p-6 flex-1 overflow-y-auto custom-scrollbar bg-gray-100">