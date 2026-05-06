<?php 
/** @var PDO $db */

// ১. ডাটাবেস থেকে ড্যাশবোর্ডের জন্য রিয়েল-টাইম ডেটা আনা হচ্ছে
// Packages Stats
$totalPackages = $db->query("SELECT COUNT(*) FROM packages")->fetchColumn();
$activePackages = $db->query("SELECT COUNT(*) FROM packages WHERE status='active'")->fetchColumn();

// ... বাকি কোড ...

// ১. ডাটাবেস থেকে ড্যাশবোর্ডের জন্য রিয়েল-টাইম ডেটা আনা হচ্ছে
// Packages Stats
$totalPackages = $db->query("SELECT COUNT(*) FROM packages")->fetchColumn();
$activePackages = $db->query("SELECT COUNT(*) FROM packages WHERE status='active'")->fetchColumn();

// Users Stats
$totalUsers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$activeUsers = $db->query("SELECT COUNT(*) FROM users WHERE status='active'")->fetchColumn();

// Financial Stats (Invoices & Payments)
$unpaidInvoices = $db->query("SELECT COUNT(*) FROM invoices WHERE status='unpaid'")->fetchColumn();
$totalRevenue = $db->query("SELECT SUM(amount) FROM payments")->fetchColumn();
$totalRevenue = $totalRevenue ? $totalRevenue : 0; // যদি কোনো পেমেন্ট না থাকে তবে 0 দেখাবে

// Support Tickets Stats
$openTickets = $db->query("SELECT COUNT(*) FROM tickets WHERE status='open'")->fetchColumn();
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">System Overview</h2>
    <p class="text-gray-500">Welcome back, Admin! Here is your ISP business summary.</p>
</div>

<!-- Stats Grid (Top row) -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    
    <!-- Users Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center border-l-4 border-blue-500 hover:shadow-md transition">
        <div class="bg-blue-100 text-blue-600 p-4 rounded-lg mr-4">
            <i class="fa fa-users text-2xl"></i>
        </div>
        <div>
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider">Total Users</h3>
            <p class="text-2xl font-bold text-gray-800"><?php echo $totalUsers; ?> <span class="text-sm font-normal text-green-500">(<?php echo $activeUsers; ?> Active)</span></p>
        </div>
    </div>

    <!-- Revenue Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center border-l-4 border-green-500 hover:shadow-md transition">
        <div class="bg-green-100 text-green-600 p-4 rounded-lg mr-4">
            <i class="fa fa-wallet text-2xl"></i>
        </div>
        <div>
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider">Total Revenue</h3>
            <p class="text-2xl font-bold text-gray-800">৳<?php echo number_format($totalRevenue, 2); ?></p>
        </div>
    </div>

    <!-- Unpaid Invoices Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center border-l-4 border-orange-500 hover:shadow-md transition">
        <div class="bg-orange-100 text-orange-600 p-4 rounded-lg mr-4">
            <i class="fa fa-file-invoice-dollar text-2xl"></i>
        </div>
        <div>
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider">Unpaid Bills</h3>
            <p class="text-2xl font-bold text-gray-800"><?php echo $unpaidInvoices; ?></p>
        </div>
    </div>

    <!-- Support Tickets Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center border-l-4 border-red-500 hover:shadow-md transition">
        <div class="bg-red-100 text-red-600 p-4 rounded-lg mr-4">
            <i class="fa fa-headset text-2xl"></i>
        </div>
        <div>
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider">Open Tickets</h3>
            <p class="text-2xl font-bold text-gray-800"><?php echo $openTickets; ?></p>
        </div>
    </div>
</div>

<!-- Bottom Section: Grid for Packages & Quick Actions -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    
    <!-- Packages Summary -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Package Overview</h3>
            <a href="admin.php?page=packages" class="text-red-500 text-sm hover:underline">Manage</a>
        </div>
        <div class="flex items-center space-x-8">
            <div class="text-center">
                <p class="text-4xl font-extrabold text-gray-700"><?php echo $totalPackages; ?></p>
                <p class="text-sm text-gray-500 mt-1">Total Packages</p>
            </div>
            <div class="text-center">
                <p class="text-4xl font-extrabold text-green-500"><?php echo $activePackages; ?></p>
                <p class="text-sm text-gray-500 mt-1">Active Packages</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions Panel -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 gap-4">
            <a href="admin.php?page=create_package" class="bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 py-3 px-4 rounded-lg text-center transition flex flex-col items-center justify-center">
                <i class="fa fa-plus-circle text-red-500 text-xl mb-2"></i>
                <span class="text-sm font-semibold">New Package</span>
            </a>
            <a href="#" class="bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 py-3 px-4 rounded-lg text-center transition flex flex-col items-center justify-center">
                <i class="fa fa-user-plus text-blue-500 text-xl mb-2"></i>
                <span class="text-sm font-semibold">Add User</span>
            </a>
            <a href="#" class="bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 py-3 px-4 rounded-lg text-center transition flex flex-col items-center justify-center">
                <i class="fa fa-file-invoice text-orange-500 text-xl mb-2"></i>
                <span class="text-sm font-semibold">Generate Invoice</span>
            </a>
            <a href="#" class="bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 py-3 px-4 rounded-lg text-center transition flex flex-col items-center justify-center">
                <i class="fa fa-ticket-alt text-purple-500 text-xl mb-2"></i>
                <span class="text-sm font-semibold">View Tickets</span>
            </a>
        </div>
    </div>
</div>