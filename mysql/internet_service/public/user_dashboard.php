<?php
session_start();
// ইউজার লগইন করা না থাকলে হোমপেজে পাঠিয়ে দেবে
if (!isset($_SESSION['user_id'])) { 
    header("Location: index.php"); 
    exit; 
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// ইউজার প্রোফাইল ডাটা আনা
$userQuery = "SELECT * FROM users WHERE user_id = :uid";
$stmtUser = $db->prepare($userQuery);
$stmtUser->execute([':uid' => $user_id]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

// কাস্টমারের বর্তমান প্যাকেজ এবং সাবস্ক্রিপশন তথ্য আনা
$query = "SELECT s.*, p.name as package_name, p.speed_mbps, p.price 
          FROM subscriptions s 
          JOIN packages p ON s.package_id = p.package_id 
          WHERE s.user_id = :uid ORDER BY s.subscription_id DESC LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute([':uid' => $user_id]);
$sub = $stmt->fetch(PDO::FETCH_ASSOC);

// ইনভয়েস বা পেমেন্ট স্ট্যাটাস আনা
$invQuery = "SELECT * FROM invoices WHERE user_id = :uid ORDER BY created_at DESC LIMIT 1";
$stmtInv = $db->prepare($invQuery);
$stmtInv->execute([':uid' => $user_id]);
$invoice = $stmtInv->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Customer Dashboard - Amar IT</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">
    
    <nav class="bg-white shadow-md p-4">
        <div class="container mx-auto max-w-6xl flex justify-between items-center">
            <h1 class="text-2xl font-bold text-red-600">AMAR <span class="text-gray-800">IT</span> <span class="text-gray-400 text-sm font-normal">| Client Portal</span></h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-700 hidden md:block">Welcome, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></span>
                <a href="logout.php" class="bg-red-50 text-red-600 border border-red-200 px-4 py-2 rounded font-semibold hover:bg-red-100 transition">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto max-w-6xl py-10 px-4">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8 border-t-4 border-t-red-500">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fa fa-wifi text-red-500 mr-2"></i> Current Subscription</h3>
                    
                    <?php if($sub): ?>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-500 text-sm">Package Plan</p>
                                <h2 class="text-2xl font-bold text-gray-800 uppercase"><?php echo htmlspecialchars($sub['package_name']); ?></h2>
                                <p class="text-gray-500 text-sm mt-1">Speed: <?php echo ($sub['speed_mbps'] == 0) ? 'Custom' : $sub['speed_mbps'] . ' Mbps'; ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-gray-500 text-sm mb-1">Connection Status</p>
                                <span class="px-4 py-1 rounded-full text-xs font-bold <?php echo ($sub['status'] == 'active') ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-orange-100 text-orange-700 border border-orange-300'; ?>">
                                    <?php echo strtoupper($sub['status']); ?>
                                </span>
                            </div>
                        </div>

                        <div class="mt-8 bg-gray-50 border border-gray-200 p-5 rounded-lg">
                            <p class="text-gray-600 font-semibold mb-2">Expiry Information:</p>
                            <?php if($sub['status'] == 'active' && !empty($sub['end_date'])): 
                                $days_left = (strtotime($sub['end_date']) - time()) / (60 * 60 * 24);
                                ?>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Valid Until: <strong><?php echo date("d M Y", strtotime($sub['end_date'])); ?></strong></span>
                                    <span class="text-xl font-extrabold <?php echo ($days_left <= 3) ? 'text-red-600' : 'text-green-600'; ?>">
                                        <?php echo max(0, ceil($days_left)); ?> Days Remaining
                                    </span>
                                </div>
                            <?php else: ?>
                                <div class="flex items-center text-orange-600 bg-orange-50 p-3 rounded border border-orange-100">
                                    <i class="fa fa-clock text-xl mr-3"></i>
                                    <p class="text-sm">Your connection is pending admin activation. Your billing cycle will start once activated.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mt-6 flex space-x-4">
                            <a href="#" class="bg-gray-800 text-white px-5 py-2 rounded text-sm font-semibold hover:bg-black transition"><i class="fa fa-arrow-up mr-2"></i> Upgrade Plan</a>
                            <a href="#" class="border border-gray-300 text-gray-700 px-5 py-2 rounded text-sm font-semibold hover:bg-gray-100 transition"><i class="fa fa-headset mr-2"></i> Support Ticket</a>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500">No active subscription found.</p>
                    <?php endif; ?>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 border-t-4 border-t-blue-500">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fa fa-file-invoice-dollar text-blue-500 mr-2"></i> Latest Invoice</h3>
                    <?php if($invoice): ?>
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Invoice No: <strong><?php echo htmlspecialchars($invoice['invoice_number']); ?></strong></p>
                                <p class="text-3xl font-extrabold text-gray-800">৳<?php echo number_format($invoice['amount']); ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500 mb-2">Due Date: <strong><?php echo date("d M Y", strtotime($invoice['due_date'])); ?></strong></p>
                                <?php if($invoice['status'] == 'unpaid'): ?>
                                    <a href="#" class="bg-green-600 text-white px-8 py-2 rounded-full font-bold shadow hover:bg-green-700 transition inline-block">PAY NOW</a>
                                <?php else: ?>
                                    <span class="text-green-600 font-bold bg-green-50 px-4 py-2 rounded border border-green-200"><i class="fa fa-check-circle mr-1"></i> PAID</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500">No invoices generated yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="w-full">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-10 border-t-4 border-t-gray-800">
                    <div class="text-center mb-6 border-b pb-6">
                        <div class="w-24 h-24 bg-gray-100 rounded-full mx-auto mb-4 flex items-center justify-center text-4xl text-gray-400 border-2 border-gray-200">
                            <i class="fa fa-user"></i>
                        </div>
                        <h4 class="font-bold text-xl text-gray-800"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                        <span class="inline-block mt-1 px-3 py-1 bg-gray-100 text-gray-600 text-xs rounded-full font-semibold">Customer ID: #<?php echo $user['user_id']; ?></span>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-start text-sm text-gray-600">
                            <i class="fa fa-envelope w-6 mt-1 text-gray-400"></i> 
                            <span class="break-all"><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div class="flex items-start text-sm text-gray-600">
                            <i class="fa fa-phone w-6 mt-1 text-gray-400"></i> 
                            <span><?php echo htmlspecialchars($user['phone']); ?></span>
                        </div>
                        <div class="flex items-start text-sm text-gray-600">
                            <i class="fa fa-map-marker-alt w-6 mt-1 text-gray-400"></i> 
                            <span><?php echo htmlspecialchars($user['address']); ?></span>
                        </div>
                    </div>
                    
                    <div class="mt-6 text-center">
                        <a href="#" class="text-blue-600 text-sm font-semibold hover:underline">Edit Profile Details</a>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h5 class="text-sm font-bold text-gray-700 mb-3">Need Help?</h5>
                        <a href="tel:09611123123" class="block text-center bg-red-50 text-red-600 font-bold py-3 rounded-lg border border-red-100 hover:bg-red-100 transition">
                            <i class="fa fa-phone-alt mr-2"></i> 09611123123
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>