<?php 
// Detect the current page to highlight the active menu item
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard - Amar IT</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- FontAwesome & Tailwind -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans flex">

    <!-- Sidebar START -->
    <div class="w-64 bg-gray-900 text-white min-h-screen flex flex-col shadow-xl">
        <div class="p-6 border-b border-gray-800 text-center">
            <h2 class="text-2xl font-bold text-red-500">AMAR <span class="text-white">IT</span></h2>
            <p class="text-xs text-gray-400 mt-1">Admin Panel</p>
        </div>
        
        <nav class="flex-1 px-4 py-6 space-y-2">
            <!-- Dashboard Link -->
            <a href="admin.php?page=dashboard" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage == 'dashboard') ? 'bg-gray-800 text-white border-l-4 border-red-500' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fa fa-tachometer-alt w-6"></i> Dashboard
            </a>
            
            <!-- Packages Link (Fixed: now points to page=packages) -->
            <a href="admin.php?page=packages" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage == 'packages' || $currentPage == 'create_package' || $currentPage == 'edit_package') ? 'bg-gray-800 text-white border-l-4 border-red-500' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fa fa-box w-6"></i> Packages
            </a>
            
            <!-- Users Link -->
            <a href="#" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage == 'users') ? 'bg-gray-800 text-white border-l-4 border-red-500' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fa fa-users w-6"></i> Users
            </a>
            
            <!-- Billings Link -->
            <a href="#" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage == 'billings') ? 'bg-gray-800 text-white border-l-4 border-red-500' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fa fa-file-invoice-dollar w-6"></i> Billings
            </a>
        </nav>
        
        <div class="p-4 border-t border-gray-800">
            <a href="logout.php" class="flex items-center px-4 py-2 text-red-400 hover:text-red-500 transition">
                <i class="fa fa-sign-out-alt w-6"></i> Logout
            </a>
        </div>
    </div>
    <!-- Sidebar END -->

    <!-- Main Content Area START -->
    <div class="flex-1 flex flex-col">
        <!-- Top Navbar -->
        <header class="bg-white shadow px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-semibold text-gray-800">Control Panel</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600"><i class="fa fa-user-circle mr-2"></i> Admin</span>
            </div>
        </header>
        
        <!-- Page Content Wrapper -->
        <main class="p-6 flex-1 overflow-y-auto">