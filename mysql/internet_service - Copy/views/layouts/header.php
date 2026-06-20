<?php
// 🔥 Intelephense Warning Fix: ভেরিয়েবলটি ফাইলের শুরুতেই ডিক্লেয়ার করা হলো
$nav_dashboard_url = 'user_dashboard.php'; // Default
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        $nav_dashboard_url = 'admin.php';
    } elseif ($_SESSION['role'] === 'staff') {
        $nav_dashboard_url = 'staff_dashboard.php';
    }
}

// Get the current page name to add the active class
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Amar IT</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        amberRed: '#dc2626', // Custom red matching Amber IT
                        amberDark: '#111827', // Dark color for footer/topbar
                    }
                }
            }
        }
    </script>

    <style>
        .nav-item {
            position: relative;
            padding-bottom: 4px;
            /* আন্ডারলাইনের জন্য একটু স্পেস দেওয়া হলো */
        }

        .nav-item::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: 0;
            width: 0;
            height: 2px;
            background: #dc2626;
            /* থিমের সাথে মিল রেখে লাল (amberRed) করা হলো */
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-item:hover::after,
        .nav-item.active::after {
            width: 100%;
            /* হোভার করলে এবং একটিভ থাকলে আন্ডারলাইন ১০০% হবে */
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 font-sans antialiased">

    <div class="bg-amberDark text-gray-300 text-xs md:text-sm hidden md:block py-2">
        <div class="container mx-auto px-4 flex justify-between items-center max-w-6xl">
            <div class="flex space-x-6 items-center">
                <span class="font-semibold text-white">Welcome to Amar IT</span>
                <span><i class="fa fa-phone text-amberRed mr-1"></i> 09611123123</span>
                <span><i class="fa fa-envelope text-amberRed mr-1"></i> support@amarit.com.bd</span>
            </div>
            <div class="flex space-x-4 items-center">
                <a href="#" class="hover:text-white transition">BTRC Approved Tariff</a>
                <a href="#" class="hover:text-white transition">Blog</a>
                <div class="flex space-x-3 text-lg">
                    <a href="#" class="hover:text-blue-500 transition"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#" class="hover:text-red-500 transition"><i class="fa-brands fa-youtube"></i></a>
                </div>
            </div>
        </div>
    </div>

    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center max-w-6xl">
            <a href="index.php" class="text-3xl font-extrabold text-amberRed tracking-tight">
                AMAR <span class="text-gray-800">IT</span>
            </a>

            <nav class="hidden md:flex space-x-6 font-semibold text-gray-600 items-center">
                <a href="home_internet.php" class="nav-item hover:text-amberRed transition <?php echo ($current_page == 'home_internet.php') ? 'active text-amberRed' : ''; ?>">Home Internet</a>
                <a href="corporate.php" class="nav-item hover:text-amberRed transition <?php echo ($current_page == 'corporate.php') ? 'active text-amberRed' : ''; ?>">Corporate</a>
                <a href="coverage.php" class="nav-item hover:text-amberRed transition <?php echo ($current_page == 'coverage.php') ? 'active text-amberRed' : ''; ?>">Coverage</a>
                <a href="iptsp.php" class="nav-item hover:text-amberRed transition <?php echo ($current_page == 'iptsp.php') ? 'active text-amberRed' : ''; ?>">IPTSP</a>
                <a href="hosting.php" class="nav-item hover:text-amberRed transition <?php echo ($current_page == 'hosting.php') ? 'active text-amberRed' : ''; ?>">Hosting</a>
                <a href="support.php" class="nav-item hover:text-amberRed transition <?php echo ($current_page == 'support.php') ? 'active text-amberRed' : ''; ?>">Support</a>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo $nav_dashboard_url; ?>" class="bg-amberRed text-white px-5 py-2 rounded-full hover:bg-red-700 transition shadow-md">Dashboard</a>
                <?php else: ?>
                    <a href="login.php" class="bg-gray-800 text-white px-5 py-2 rounded-full hover:bg-amberRed transition shadow-md"><i class="fa fa-user mr-2"></i> Login</a>
                <?php endif; ?>
            </nav>

            <button id="mobile-menu-btn" class="md:hidden text-gray-600 hover:text-amberRed focus:outline-none transition">
                <i id="mobile-icon" class="fa fa-bars text-2xl"></i>
            </button>
        </div>

        <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-100 shadow-lg absolute w-full left-0 transition-all duration-300 ease-in-out">
            <nav class="flex flex-col font-semibold text-gray-600 p-4 space-y-4">
                <a href="home_internet.php" class="hover:text-amberRed transition block <?php echo ($current_page == 'home_internet.php') ? 'text-amberRed' : ''; ?>"><i class="fa fa-wifi w-6 text-center mr-2"></i>Home Internet</a>
                <a href="corporate.php" class="hover:text-amberRed transition block <?php echo ($current_page == 'corporate.php') ? 'text-amberRed' : ''; ?>"><i class="fa fa-building w-6 text-center mr-2"></i>Corporate</a>
                <a href="coverage.php" class="hover:text-amberRed transition block <?php echo ($current_page == 'coverage.php') ? 'text-amberRed' : ''; ?>"><i class="fa fa-map-marked-alt w-6 text-center mr-2"></i>Coverage</a>
                <a href="iptsp.php" class="hover:text-amberRed transition block <?php echo ($current_page == 'iptsp.php') ? 'text-amberRed' : ''; ?>"><i class="fa fa-globe w-6 text-center mr-2"></i>IPTSP</a>
                <a href="hosting.php" class="hover:text-amberRed transition block <?php echo ($current_page == 'hosting.php') ? 'text-amberRed' : ''; ?>"><i class="fa fa-server w-6 text-center mr-2"></i>Hosting</a>
                <a href="support.php" class="hover:text-amberRed transition block <?php echo ($current_page == 'support.php') ? 'text-amberRed' : ''; ?>"><i class="fa fa-headset w-6 text-center mr-2"></i>Support</a>

                <div class="border-t border-gray-200 pt-4 mt-2">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?php echo $nav_dashboard_url; ?>" class="block text-center bg-amberRed text-white px-5 py-3 rounded-lg hover:bg-red-700 transition shadow-md">Go to Dashboard</a>
                    <?php else: ?>
                        <a href="login.php" class="block text-center bg-gray-800 text-white px-5 py-3 rounded-lg hover:bg-amberRed transition shadow-md"><i class="fa fa-user mr-2"></i> Login / Register</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <script>
        const btn = document.getElementById('mobile-menu-btn');
        const menu = document.getElementById('mobile-menu');
        const icon = document.getElementById('mobile-icon');

        btn.addEventListener('click', () => {
            menu.classList.toggle('hidden');

            // Icon change logic (Bars to Times)
            if (menu.classList.contains('hidden')) {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            } else {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            }
        });
    </script>
</body>

</html>