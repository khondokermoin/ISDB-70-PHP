<?php
session_start();
// ইউজার লগইন করা না থাকলে হোমপেজে পাঠিয়ে দেবে
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

// সেশনে 'role' মিসিং থাকলে ডাটাবেস থেকে অটোমেটিক বসিয়ে দেবে
if (!isset($_SESSION['role']) && $user) {
    $_SESSION['role'] = $user['role'];
}

// কাস্টমারের বর্তমান প্যাকেজ এবং সাবস্ক্রিপশন তথ্য আনা
$query = "SELECT s.*, p.name as package_name, p.speed_mbps, p.price 
          FROM subscriptions s 
          JOIN packages p ON s.package_id = p.package_id 
          WHERE s.user_id = :uid ORDER BY s.subscription_id DESC LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute([':uid' => $user_id]);
$sub = $stmt->fetch(PDO::FETCH_ASSOC);

// 🔥 আপডেট ১: শুধুমাত্র বকেয়া (Unpaid) ইনভয়েস আনা
$unpaidInvQuery = "SELECT * FROM invoices WHERE user_id = :uid AND status IN ('unpaid', 'pending') ORDER BY created_at DESC";
$stmtUnpaid = $db->prepare($unpaidInvQuery);
$stmtUnpaid->execute([':uid' => $user_id]);
$unpaid_invoices = $stmtUnpaid->fetchAll(PDO::FETCH_ASSOC);

// 🔥 আপডেট ২: সব ইনভয়েস হিস্ট্রি আনা (Payment History এর জন্য)
$allInvQuery = "SELECT * FROM invoices WHERE user_id = :uid ORDER BY created_at DESC";
$stmtAll = $db->prepare($allInvQuery);
$stmtAll->execute([':uid' => $user_id]);
$invoice_history = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

// নোটিফিকেশন আনা (Expiry Warning)
$notifQuery = $db->prepare("SELECT message FROM notifications WHERE user_id = :uid ORDER BY sent_at DESC LIMIT 1");
$notifQuery->execute([':uid' => $user_id]);
$notification = $notifQuery->fetch(PDO::FETCH_ASSOC);

// কাস্টমারের জন্য অ্যাসাইন করা টেকনিশিয়ান খোঁজা (সর্বশেষ রানিং টিকিট থেকে)
$techQuery = $db->prepare("SELECT t.ticket_id, t.category, s.full_name as staff_name, s.phone as staff_phone 
                           FROM tickets t 
                           JOIN users s ON t.assigned_to = s.user_id 
                           WHERE t.user_id = :uid AND t.status != 'resolved' 
                           ORDER BY t.created_at DESC LIMIT 1");
$techQuery->execute([':uid' => $user_id]);
$assigned_tech = $techQuery->fetch(PDO::FETCH_ASSOC);
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

        <?php if ($notification): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm flex items-start">
                <i class="fa fa-bell text-xl mr-3 mt-1 animate-pulse"></i>
                <div>
                    <h4 class="font-bold">Important Notice!</h4>
                    <p class="text-sm"><?php echo htmlspecialchars($notification['message']); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8 border-t-4 border-t-red-500">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fa fa-wifi text-red-500 mr-2"></i> Current Subscription</h3>

                    <?php if ($sub): ?>
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
                            <?php if ($sub['status'] == 'active' && !empty($sub['end_date'])):
                                // 🔥 দিনের সঠিক ক্যালকুলেশন (Date Object ব্যবহার করে)
                                $today = new DateTime(date('Y-m-d'));
                                $expiry = new DateTime($sub['end_date']);
                                $interval = $today->diff($expiry);
                                $days_left = $interval->format('%r%a'); // পজিটিভ বা নেগেটিভ দিন বের করবে
                            ?>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Valid Until: <strong><?php echo date("d M Y", strtotime($sub['end_date'])); ?></strong></span>
                                    <span class="text-xl font-extrabold <?php echo ($days_left <= 3) ? 'text-red-600' : 'text-green-600'; ?>">
                                        <?php echo ($days_left > 0) ? $days_left . ' Days Remaining' : 'Expired'; ?>
                                    </span>
                                </div>
                            <?php else: ?>
                                <div class="flex items-center text-orange-600 bg-orange-50 p-3 rounded border border-orange-100">
                                    <i class="fa fa-clock text-xl mr-3"></i>
                                    <p class="text-sm">Your connection is pending admin activation. Your billing cycle will start once activated.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($assigned_tech): ?>
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mt-6 shadow-sm">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center">
                                        <div class="bg-blue-200 text-blue-700 w-12 h-12 rounded-full flex items-center justify-center text-xl mr-4 shadow-inner">
                                            <i class="fa <?php echo ($assigned_tech['category'] == 'New Installation') ? 'fa-tools' : 'fa-user-shield'; ?>"></i>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-blue-600 font-bold uppercase tracking-widest">
                                                <?php echo ($assigned_tech['category'] == 'New Installation') ? 'Installation Technician' : 'Support Assistant'; ?>
                                            </p>
                                            <p class="text-lg font-extrabold text-gray-800"><?php echo htmlspecialchars($assigned_tech['staff_name']); ?></p>
                                            <p class="text-xs text-gray-500 font-semibold"><i class="fa fa-info-circle mr-1"></i> Assigned for: <?php echo htmlspecialchars($assigned_tech['category']); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex space-x-3 mt-4 pt-4 border-t border-blue-200">
                                    <a href="tel:<?php echo htmlspecialchars($assigned_tech['staff_phone']); ?>" class="flex-1 text-center bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-bold shadow transition text-sm">
                                        <i class="fa fa-phone-alt mr-1"></i> Call
                                    </a>
                                    <a href="https://wa.me/88<?php echo htmlspecialchars($assigned_tech['staff_phone']); ?>" target="_blank" class="flex-1 text-center bg-green-500 hover:bg-green-600 text-white py-2 rounded-lg font-bold shadow transition text-sm">
                                        <i class="fab fa-whatsapp text-lg mr-1"></i> WhatsApp
                                    </a>
                                    <a href="view_ticket.php?id=<?php echo $assigned_tech['ticket_id']; ?>" class="flex-1 text-center bg-gray-800 hover:bg-gray-900 text-white py-2 rounded-lg font-bold shadow transition text-sm">
                                        <i class="fa fa-comments mr-1"></i> Chat Setup
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php if ($sub && $sub['status'] != 'active'): ?>
                                <div class="mt-6 bg-gray-50 border border-dashed border-gray-300 rounded-xl p-6 text-center text-gray-400">
                                    <i class="fa fa-user-clock mb-2 text-2xl"></i>
                                    <p class="text-sm">Waiting for a technician to be assigned for your installation.</p>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        <div class="mt-6 flex space-x-4">
                            <a href="upgrade.php" class="bg-gray-800 text-white px-5 py-2 rounded text-sm font-semibold hover:bg-black transition"><i class="fa fa-arrow-up mr-2"></i> Upgrade Plan</a>
                            <a href="support.php" class="border border-gray-300 text-gray-700 px-5 py-2 rounded text-sm font-semibold hover:bg-gray-100 transition"><i class="fa fa-headset mr-2"></i> Support Ticket</a>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500">No active subscription found.</p>
                    <?php endif; ?>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8 border-t-4 border-t-orange-500">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fa fa-exclamation-circle text-orange-500 mr-2"></i> Pending Payments</h3>
                    <?php if (count($unpaid_invoices) > 0): ?>
                        <div class="space-y-4 max-h-[250px] overflow-y-auto pr-2">
                            <?php foreach ($unpaid_invoices as $inv): ?>
                                <div class="p-4 bg-orange-50 border border-orange-200 rounded-lg relative overflow-hidden">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-[10px] font-bold text-gray-500 uppercase">INV: <?php echo $inv['invoice_number']; ?></span>
                                        <?php if ($inv['status'] == 'pending'): ?>
                                            <span class="bg-blue-500 text-white px-2 py-0.5 rounded text-[10px] font-bold animate-pulse">VERIFYING</span>
                                        <?php else: ?>
                                            <span class="bg-red-500 text-white px-2 py-0.5 rounded text-[10px] font-bold animate-pulse">UNPAID</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-2xl font-black text-gray-800">৳<?php echo number_format($inv['amount']); ?></p>
                                    <p class="text-xs text-gray-500 mt-1 italic">Due: <?php echo date("d M Y", strtotime($inv['due_date'])); ?></p>

                                    <?php if ($inv['status'] == 'pending'): ?>
                                        <button disabled class="w-full mt-4 bg-gray-400 text-white py-2 rounded-lg text-sm font-bold shadow cursor-not-allowed"><i class="fa fa-spinner fa-spin mr-1"></i> Processing</button>
                                    <?php else: ?>
                                        <a href="pay_invoice.php?invoice_id=<?php echo $inv['invoice_id']; ?>" class="block text-center mt-4 bg-orange-600 text-white py-2 rounded-lg text-sm font-bold shadow hover:bg-orange-700 transition">Pay Now</a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-6 text-gray-400 bg-gray-50 rounded-lg border border-dashed border-gray-200">
                            <i class="fa fa-check-circle text-4xl mb-2 text-green-300"></i>
                            <p class="text-sm font-bold text-gray-500">All bills are cleared!</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 border-t-4 border-t-blue-500">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fa fa-history text-blue-500 mr-2"></i> Payment History</h3>
                    <div class="overflow-x-auto max-h-[300px] overflow-y-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="bg-gray-50 text-gray-500 uppercase text-[10px] font-bold sticky top-0">
                                <tr>
                                    <th class="py-3 px-4">Invoice No.</th>
                                    <th class="py-3 px-4">Date</th>
                                    <th class="py-3 px-4">Amount</th>
                                    <th class="py-3 px-4">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($invoice_history as $hist): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-3 px-4 font-bold text-gray-600"><?php echo $hist['invoice_number']; ?></td>
                                        <td class="py-3 px-4 text-gray-500"><?php echo date("d M Y", strtotime($hist['created_at'])); ?></td>
                                        <td class="py-3 px-4 font-bold text-gray-800">৳<?php echo number_format($hist['amount']); ?></td>
                                        <td class="py-3 px-4">
                                            <span class="px-2 py-1 rounded text-[10px] font-bold border <?php echo ($hist['status'] == 'paid') ? 'bg-green-50 text-green-600 border-green-200' : 'bg-red-50 text-red-600 border-red-200'; ?>">
                                                <?php echo strtoupper($hist['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($invoice_history)) echo "<tr><td colspan='4' class='text-center py-6 text-gray-400 italic'>No invoice history available.</td></tr>"; ?>
                            </tbody>
                        </table>
                    </div>
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
                        <a href="profile_edit.php" class="text-blue-600 text-sm font-semibold hover:underline">Edit Profile Details</a>
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