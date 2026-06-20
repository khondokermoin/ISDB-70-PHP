<?php
// FIX (minor): session cookie কে httponly + samesite দিয়ে secure করো
// XSS থেকে cookie চুরি এবং CSRF উভয়ই আটকায়
session_set_cookie_params([
    'httponly' => true,
    'secure'   => true,       // HTTPS only — local dev-এ false করো
    'samesite' => 'Strict',
]);
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

// FIX (minor): CSRF token generate করো — Pay Now form-এ ব্যবহার হবে
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// ==========================================================
// 🔥 AUTO RENEW LOGIC (Pay OR View Invoice)
// ==========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['auto_renew']) || isset($_POST['auto_renew_view']))) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("Security Token Mismatch!");
    }

    // চেক করা হচ্ছে আগে থেকেই বকেয়া ইনভয়েস আছে কিনা
    $checkInv = $db->prepare("SELECT invoice_id FROM invoices WHERE user_id = :uid AND status IN ('unpaid', 'pending') LIMIT 1");
    $checkInv->execute([':uid' => $user_id]);
    $existing_inv = $checkInv->fetch(PDO::FETCH_ASSOC);

    $target_invoice_id = null;

    if ($existing_inv) {
        $target_invoice_id = $existing_inv['invoice_id'];
    } else {
        // বকেয়া না থাকলে নতুন ইনভয়েস তৈরি করা
        $invoice_number = 'INV-' . strtoupper(bin2hex(random_bytes(4)));
        $amount = (float)$_POST['renew_amount'];
        $sub_id = (int)$_POST['sub_id'];

        $stmtSub = $db->prepare("SELECT end_date FROM subscriptions WHERE subscription_id = ?");
        $stmtSub->execute([$sub_id]);
        $subData = $stmtSub->fetch();

        $due_date = ($subData && !empty($subData['end_date']) && strtotime($subData['end_date']) > time())
            ? $subData['end_date']
            : date('Y-m-d');

        $insertInv = $db->prepare("
            INSERT INTO invoices (user_id, subscription_id, invoice_number, amount, due_date, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'unpaid', NOW())
        ");
        $insertInv->execute([$user_id, $sub_id, $invoice_number, $amount, $due_date]);
        $target_invoice_id = $db->lastInsertId();
    }

    // 🔥 ম্যাজিক: কাস্টমার কোন বাটনে ক্লিক করেছে তার ওপর ভিত্তি করে রিডাইরেক্ট
    if (isset($_POST['auto_renew_view'])) {
        header("Location: view_invoice.php?invoice_id=" . $target_invoice_id);
    } else {
        header("Location: pay_invoice.php?invoice_id=" . $target_invoice_id);
    }
    exit;
}
// ==========================================================

// ইউজার প্রোফাইল ডাটা আনা
// SELECT * এর বদলে নির্দিষ্ট column (performance + security)
$userQuery = "SELECT user_id, full_name, email, phone, address, role FROM users WHERE user_id = :uid";
$stmtUser  = $db->prepare($userQuery);
$stmtUser->execute([':uid' => $user_id]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

// $user null হলে session destroy করে login পেজে পাঠাও — নাহলে PHP fatal error
if (!$user) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// সবসময় DB থেকে role refresh করো (stale session এড়াতে)
$_SESSION['role'] = $user['role'];

// শুধুমাত্র customer role-এর জন্য এই পেজ accessible
if ($_SESSION['role'] !== 'customer') {
    header("Location: ../admin/dashboard.php");
    exit;
}

// কাস্টমারের বর্তমান প্যাকেজ এবং সাবস্ক্রিপশন তথ্য আনা
// SELECT * এর বদলে নির্দিষ্ট column
$query = "SELECT s.subscription_id, s.status, s.end_date, s.package_id, s.user_id,
          p.name as package_name, p.speed_mbps, p.price,
          DATEDIFF(s.end_date, CURDATE()) as days_left 
          FROM subscriptions s 
          JOIN packages p ON s.package_id = p.package_id 
          WHERE s.user_id = :uid ORDER BY s.subscription_id DESC LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute([':uid' => $user_id]);
$sub = $stmt->fetch(PDO::FETCH_ASSOC);

// শুধুমাত্র বকেয়া (Unpaid) ইনভয়েস আনা এবং প্যাকেজের নাম (package_name) যুক্ত করা
$unpaidInvQuery = "SELECT i.invoice_id, i.invoice_number, i.amount, i.due_date, i.status, p.name as package_name
                   FROM invoices i
                   LEFT JOIN subscriptions s ON i.subscription_id = s.subscription_id
                   LEFT JOIN packages p ON s.package_id = p.package_id
                   WHERE i.user_id = :uid AND i.status IN ('unpaid', 'pending')
                   ORDER BY i.created_at DESC";
$stmtUnpaid = $db->prepare($unpaidInvQuery);
$stmtUnpaid->execute([':uid' => $user_id]);
$unpaid_invoices = $stmtUnpaid->fetchAll(PDO::FETCH_ASSOC);

// FIX (medium): $db->query() সরাসরি chain করলে query fail হলে fatal error হয়
// এখন false check করে safe fallback দেওয়া হচ্ছে
$pkgResult    = $db->query("SELECT package_id, name, price FROM packages");
$packages_data = $pkgResult ? $pkgResult->fetchAll(PDO::FETCH_ASSOC) : [];

// FIX (medium): $pkgByPrice-এ দুটো package একই price হলে শেষেরটা প্রথমেরটাকে
// overwrite করে ভুল নাম দেখাত। এখন collision detect করে 'Custom' দেখানো হবে।
$pkgById    = [];
$pkgByPrice = [];
foreach ($packages_data as $p) {
    $pkgById[$p['package_id']] = $p['name'];
    $price = (int)$p['price'];
    if (!isset($pkgByPrice[$price])) {
        $pkgByPrice[$price] = $p['name'];
    } elseif ($pkgByPrice[$price] !== $p['name']) {
        // একই দামে দুটো আলাদা package → ambiguous, 'Custom' দেখাও
        $pkgByPrice[$price] = 'Custom';
    }
}

// 🔥 FIX: Upgrade ইনভয়েস (UPG-xxx) হলে প্যাকেজের আসল নাম (যেটাতে আপগ্রেড হবে) বসানো
foreach ($unpaid_invoices as $key => $inv) {
    if (strpos($inv['invoice_number'], 'UPG-') === 0) {
        $parts = explode('-', $inv['invoice_number']);
        // $parts[1] এ নতুন প্যাকেজের ID আছে
        if (isset($parts[1]) && is_numeric($parts[1])) {
            $target_pkg_id = (int)$parts[1];
            // প্যাকেজটি ডাটাবেসে থাকলে সেটির নাম দিয়ে আপডেট করা
            if (isset($pkgById[$target_pkg_id])) {
                $unpaid_invoices[$key]['package_name'] = $pkgById[$target_pkg_id];
            }
        }
    }
}

// শুধু Invoices টেবিল থেকে PAID ডাটা আনা
$allInvQuery = "SELECT invoice_number, created_at, amount, status 
                FROM invoices 
                WHERE user_id = :uid AND status = 'paid' 
                ORDER BY created_at DESC";
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

// FIX (critical): WhatsApp-এর জন্য phone number sanitize করো
// আগে '88' prefix দেওয়া হত যা ভুল — Bangladesh-এর country code হল 880
// 01711123456  →  8801711123456  ✓
// 1711123456   →  8801711123456  ✓
// 8801711123456→  8801711123456  ✓ (unchanged)
function buildWhatsAppNumber(string $phone): string
{
    $phone = preg_replace('/\D/', '', $phone); // শুধু digit রাখো
    if (str_starts_with($phone, '880')) {
        return $phone;                           // ইতিমধ্যে country code আছে
    }
    if (str_starts_with($phone, '0')) {
        return '880' . substr($phone, 1);        // FIX: '88' ছিল → '880' করা হয়েছে
    }
    return '880' . $phone;                       // FIX: '88' ছিল → '880' করা হয়েছে
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- FIX (minor): charset ছিল না — Bengali ৳ character ঠিকমতো render হত না -->
    <meta charset="UTF-8">
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
                <!-- $_SESSION['user_name'] এর বদলে DB থেকে আসা $user['full_name'] ব্যবহার -->
                <!-- profile update করলে navbar-এও সাথে সাথে নতুন নাম দেখাবে -->
                <span class="text-gray-700 hidden md:block">Welcome, <strong><?php echo htmlspecialchars($user['full_name']); ?></strong></span>
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
                                <p class="text-gray-500 text-sm mt-1">Speed: <?php echo empty($sub['speed_mbps']) ? 'Custom' : $sub['speed_mbps'] . ' Mbps'; ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-gray-500 text-sm mb-1">Connection Status</p>
                                <?php
                                // ডায়নামিক স্ট্যাটাস কালার লজিক
                                $statusClass = 'bg-gray-100 text-gray-700 border-gray-300';
                                if ($sub['status'] == 'active') {
                                    $statusClass = 'bg-green-100 text-green-700 border-green-300';
                                } elseif ($sub['status'] == 'pending') {
                                    $statusClass = 'bg-blue-100 text-blue-700 border-blue-300';
                                } elseif (in_array($sub['status'], ['expired', 'suspended'])) {
                                    $statusClass = 'bg-red-100 text-red-700 border-red-300';
                                }
                                ?>
                                <span class="px-4 py-1 rounded-full text-xs font-bold border <?php echo $statusClass; ?>">
                                    <?php echo strtoupper(htmlspecialchars($sub['status'])); ?>
                                </span>
                            </div>
                        </div>

                        <div class="mt-8 bg-gray-50 border border-gray-200 p-5 rounded-lg">
                            <p class="text-gray-600 font-semibold mb-2">Expiry Information:</p>

                            <?php if ($sub['status'] == 'active' && !empty($sub['end_date'])):
                                $days_left = ($sub['days_left'] !== null) ? (int)$sub['days_left'] : null;
                            ?>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Valid Until: <strong><?php echo date("d M Y", strtotime($sub['end_date'])); ?></strong></span>
                                    <?php if ($days_left !== null): ?>
                                        <span class="text-xl font-extrabold <?php echo ($days_left <= 3) ? 'text-red-600' : (($days_left <= 7) ? 'text-orange-500' : 'text-green-600'); ?>">
                                            <?php if ($days_left > 0): ?>
                                                <?php echo $days_left; ?> Days Remaining
                                            <?php elseif ($days_left === 0): ?>
                                                Expires Today
                                            <?php else: ?>
                                                Expired
                                            <?php endif; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-400 italic">Expiry date not set</span>
                                    <?php endif; ?>
                                </div>

                            <?php elseif ($sub['status'] == 'pending'): ?>
                                <div class="flex items-center text-blue-600 bg-blue-50 p-3 rounded border border-blue-100">
                                    <i class="fa fa-user-cog text-xl mr-3 animate-pulse"></i>
                                    <p class="text-sm">Your connection is pending physical installation. Billing cycle will start once activated by admin.</p>
                                </div>

                            <?php else: ?>
                                <div class="flex items-center text-red-600 bg-red-50 p-3 rounded border border-red-100">
                                    <i class="fa fa-exclamation-triangle text-xl mr-3"></i>
                                    <p class="text-sm">Your connection is currently suspended/expired. Please clear pending invoices to reactivate.</p>
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

                                <!-- staff_phone null/empty হলে Call ও WhatsApp লিঙ্ক রেন্ডার করা যাবে না -->
                                <?php if (!empty($assigned_tech['staff_phone'])): ?>
                                    <div class="flex space-x-3 mt-4 pt-4 border-t border-blue-200">
                                        <a href="tel:<?php echo htmlspecialchars($assigned_tech['staff_phone']); ?>" class="flex-1 text-center bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-bold shadow transition text-sm">
                                            <i class="fa fa-phone-alt mr-1"></i> Call
                                        </a>
                                        <!-- FIX (critical): buildWhatsAppNumber() এখন সঠিক 880 country code ব্যবহার করছে -->
                                        <a href="https://wa.me/<?php echo htmlspecialchars(buildWhatsAppNumber($assigned_tech['staff_phone'])); ?>" target="_blank" class="flex-1 text-center bg-green-500 hover:bg-green-600 text-white py-2 rounded-lg font-bold shadow transition text-sm">
                                            <i class="fab fa-whatsapp text-lg mr-1"></i> WhatsApp
                                        </a>
                                        <a href="view_ticket.php?id=<?php echo (int)$assigned_tech['ticket_id']; ?>" class="flex-1 text-center bg-gray-800 hover:bg-gray-900 text-white py-2 rounded-lg font-bold shadow transition text-sm">
                                            <i class="fa fa-comments mr-1"></i> Chat Setup
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <?php if ($sub && $sub['status'] == 'pending'): ?>
                                <div class="mt-6 bg-gray-50 border border-dashed border-gray-300 rounded-xl p-6 text-center text-gray-400">
                                    <i class="fa fa-user-clock mb-2 text-2xl"></i>
                                    <p class="text-sm">Waiting for a technician to be assigned for your installation.</p>
                                </div>
                            <?php elseif ($sub && in_array($sub['status'], ['expired', 'suspended'])): ?>
                                <div class="mt-6 bg-red-50 border border-red-200 rounded-xl p-6 text-center text-red-600 shadow-sm">
                                    <i class="fa fa-exclamation-triangle mb-3 text-4xl animate-pulse"></i>
                                    <h4 class="font-bold text-lg mb-1">Payment Required!</h4>
                                    <p class="text-sm text-red-500">Your connection is currently offline. Please check the <strong>Pending Payments</strong> section to clear your dues and reactivate.</p>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                    <?php else: ?>
                        <p class="text-gray-500">No active subscription found.</p>
                    <?php endif; ?>

                    <!-- বাটন দুটো if ($sub) ব্লকের বাইরে রাখা হয়েছে।
                         Subscription না থাকলেও customer Support Ticket দিতে পারবে।
                         Upgrade Plan শুধু subscription থাকলে দেখাবে। -->
                    <div class="mt-6 flex space-x-4">
                        <?php if ($sub): ?>
                            <a href="upgrade.php" class="bg-gray-800 text-white px-5 py-2 rounded text-sm font-semibold hover:bg-black transition"><i class="fa fa-arrow-up mr-2"></i> Upgrade Plan</a>
                        <?php endif; ?>
                        <a href="support.php" class="border border-gray-300 text-gray-700 px-5 py-2 rounded text-sm font-semibold hover:bg-gray-100 transition"><i class="fa fa-headset mr-2"></i> Support Ticket</a>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8 border-t-4 border-t-orange-500">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h3 class="text-lg font-bold text-gray-800"><i class="fa fa-exclamation-circle text-orange-500 mr-2"></i> Pending Payments</h3>
                    </div>

                    <?php
                    // লজিক: প্যাকেজ কি ৫ দিনের মধ্যে শেষ হবে বা অলরেডি শেষ?
                    $is_expiring_soon = ($sub && $sub['days_left'] !== null && $sub['days_left'] <= 5 && $sub['days_left'] >= 0);
                    $is_expired = ($sub && in_array($sub['status'], ['expired', 'suspended']));
                    $needs_renewal = ($is_expiring_soon || $is_expired);
                    ?>

                    <?php if (count($unpaid_invoices) > 0): ?>
                        <div class="space-y-4 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                            <?php foreach ($unpaid_invoices as $inv): ?>
                                <div class="p-5 bg-gradient-to-br from-orange-50 to-white border border-orange-200 rounded-xl relative overflow-hidden hover:shadow-md transition-shadow duration-300">

                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">INV: <?php echo htmlspecialchars($inv['invoice_number'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            <h4 class="text-sm font-extrabold text-gray-800 mt-0.5">
                                                <?php echo htmlspecialchars($inv['package_name'] ?? 'Internet Subscription', ENT_QUOTES, 'UTF-8'); ?>
                                            </h4>
                                        </div>
                                        <?php if ($inv['status'] == 'pending'): ?>
                                            <span class="bg-blue-500 text-white px-2.5 py-1 rounded-md text-[10px] font-bold animate-pulse shadow-sm">VERIFYING</span>
                                        <?php else: ?>
                                            <span class="bg-red-500 text-white px-2.5 py-1 rounded-md text-[10px] font-bold animate-pulse shadow-sm">UNPAID</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="flex justify-between items-end mt-3 mb-1">
                                        <div>
                                            <p class="text-2xl font-black text-gray-900">৳<?php echo number_format($inv['amount']); ?></p>
                                            <p class="text-[11px] text-gray-500 font-medium mt-0.5">
                                                Due: <?php echo !empty($inv['due_date']) ? date("d M Y", strtotime($inv['due_date'])) : 'N/A'; ?>
                                            </p>
                                        </div>
                                        <?php if (strpos($inv['invoice_number'], 'UPG-') === 0): ?>
                                            <span class="text-[10px] bg-purple-100 text-purple-700 font-bold px-2 py-1 rounded-full border border-purple-200">
                                                <i class="fa fa-arrow-up mr-1"></i>Upgrade
                                            </span>
                                        <?php else: ?>
                                            <span class="text-[10px] bg-emerald-100 text-emerald-700 font-bold px-2 py-1 rounded-full border border-emerald-200">
                                                <i class="fa fa-sync-alt mr-1"></i>Renewal
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($inv['status'] == 'pending'): ?>
                                        <button disabled class="w-full mt-4 bg-gray-300 text-gray-600 py-2.5 rounded-lg text-sm font-bold cursor-not-allowed flex justify-center items-center">
                                            <i class="fa fa-spinner fa-spin mr-2"></i> Payment Processing
                                        </button>
                                    <?php else: ?>
                                        <div class="mt-4 flex gap-2">
                                            <a href="pay_invoice.php?invoice_id=<?php echo (int)$inv['invoice_id']; ?>" class="flex-1 text-center bg-orange-600 text-white py-2.5 rounded-lg text-sm font-bold shadow-md hover:bg-orange-700 hover:shadow-lg transition-all">
                                                <i class="fa fa-credit-card mr-1"></i> Pay Now
                                            </a>

                                            <a href="view_invoice.php?invoice_id=<?php echo (int)$inv['invoice_id']; ?>" class="bg-white border border-gray-300 text-gray-600 py-2.5 px-4 rounded-lg text-sm font-bold shadow-sm hover:bg-gray-50 hover:text-orange-600 transition-all" title="View Invoice">
                                                <i class="fa fa-file-invoice"></i>
                                            </a>

                                            <?php if (strpos($inv['invoice_number'], 'UPG-') === 0): ?>
                                                <form action="cancel_invoice.php" method="POST" onsubmit="return confirm('Are you sure you want to cancel this package upgrade request?');">
                                                    <input type="hidden" name="invoice_id" value="<?php echo (int)$inv['invoice_id']; ?>">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <button type="submit" class="bg-red-50 border border-red-200 text-red-600 py-2.5 px-4 rounded-lg text-sm font-bold shadow-sm hover:bg-red-100 hover:text-red-700 transition-all" title="Cancel Upgrade">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    <?php elseif ($needs_renewal): ?>
                        <div class="space-y-4 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                            <?php $card_color = $is_expired ? 'red' : 'orange'; ?>

                            <div class="p-5 bg-gradient-to-br from-<?php echo $card_color; ?>-50 to-white border border-<?php echo $card_color; ?>-200 rounded-xl relative overflow-hidden shadow-sm">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <span class="text-[10px] font-bold text-<?php echo $card_color; ?>-500 uppercase tracking-wider">
                                            <?php echo $is_expired ? 'ACTION REQUIRED' : 'ADVANCE RENEWAL'; ?>
                                        </span>
                                        <h4 class="text-sm font-extrabold text-gray-800 mt-0.5">
                                            <?php echo htmlspecialchars($sub['package_name']); ?> (Next Month)
                                        </h4>
                                    </div>
                                    <span class="bg-<?php echo $card_color; ?>-600 text-white px-2.5 py-1 rounded-md text-[10px] font-bold shadow-sm">
                                        <?php echo $is_expired ? 'EXPIRED' : 'DUE SOON'; ?>
                                    </span>
                                </div>

                                <div class="flex justify-between items-end mt-3 mb-1">
                                    <div>
                                        <p class="text-2xl font-black text-gray-900">৳<?php echo number_format($sub['price']); ?></p>
                                        <p class="text-[11px] text-<?php echo $card_color; ?>-500 font-medium mt-0.5">
                                            <?php echo $is_expired ? 'Your internet has expired!' : 'Package expires in ' . $sub['days_left'] . ' day(s).'; ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-4 flex gap-2">
                                    <form method="POST" action="" class="flex-1">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                        <input type="hidden" name="auto_renew" value="1">
                                        <input type="hidden" name="sub_id" value="<?php echo (int)$sub['subscription_id']; ?>">
                                        <input type="hidden" name="renew_amount" value="<?php echo (float)$sub['price']; ?>">

                                        <button type="submit" class="w-full text-center bg-<?php echo $card_color; ?>-600 text-white py-2.5 rounded-lg text-sm font-bold shadow-md hover:bg-<?php echo $card_color; ?>-700 transition-all">
                                            <i class="fa fa-credit-card mr-1"></i> <?php echo $is_expired ? 'Pay to Reactivate' : 'Pay Advance & Renew'; ?>
                                        </button>
                                    </form>

                                    <form method="POST" action="">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                        <input type="hidden" name="auto_renew_view" value="1">
                                        <input type="hidden" name="sub_id" value="<?php echo (int)$sub['subscription_id']; ?>">
                                        <input type="hidden" name="renew_amount" value="<?php echo (float)$sub['price']; ?>">

                                        <button type="submit" class="bg-white border border-gray-300 text-gray-600 py-2.5 px-4 rounded-lg text-sm font-bold shadow-sm hover:bg-gray-50 hover:text-<?php echo $card_color; ?>-600 transition-all" title="Generate & View Invoice">
                                            <i class="fa fa-file-invoice"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="text-center py-8 text-gray-400 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                            <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fa fa-check text-2xl text-green-500"></i>
                            </div>
                            <p class="text-base font-bold text-gray-600">All bills are cleared!</p>
                            <p class="text-xs text-gray-400 mt-1">You have <?php echo $sub['days_left'] ?? 'N/A'; ?> days remaining.</p>
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
                                    <th class="py-3 px-4">Package</th>
                                    <th class="py-3 px-4">Date</th>
                                    <th class="py-3 px-4">Amount</th>
                                    <th class="py-3 px-4">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($invoice_history as $hist):
                                    $pkgName = 'Custom';
                                    $inv_no  = $hist['invoice_number'];
                                    $amt     = (int)$hist['amount'];

                                    // স্মার্ট প্যাকেজ ফাইন্ডার লজিক
                                    if (strpos($inv_no, 'UPG-') === 0) {
                                        // আপগ্রেড ইনভয়েস (UPG-2-XXX) → Package ID দিয়ে নাম বের করবে
                                        $parts = explode('-', $inv_no);
                                        $pid   = isset($parts[1]) ? (int)$parts[1] : 0;
                                        if (isset($pkgById[$pid])) {
                                            $pkgName = $pkgById[$pid];
                                        }
                                    } else {
                                        // সাধারণ ইনভয়েস → price দিয়ে নাম বের করবে
                                        // FIX (medium): collision হলে $pkgByPrice[$amt] === 'Custom' দেখাবে
                                        if (isset($pkgByPrice[$amt])) {
                                            $pkgName = $pkgByPrice[$amt];
                                        }
                                    }
                                ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-3 px-4 font-bold text-gray-600"><?php echo htmlspecialchars($inv_no, ENT_QUOTES, 'UTF-8'); ?></td>

                                        <!-- ডায়নামিক প্যাকেজের নাম -->
                                        <td class="py-3 px-4 font-bold text-blue-600 text-xs uppercase">
                                            <?php echo htmlspecialchars($pkgName, ENT_QUOTES, 'UTF-8'); ?>
                                        </td>

                                        <td class="py-3 px-4 text-gray-500"><?php echo date("d M Y", strtotime($hist['created_at'])); ?></td>
                                        <td class="py-3 px-4 font-bold text-gray-800">৳<?php echo number_format($hist['amount']); ?></td>
                                        <td class="py-3 px-4">
                                            <?php
                                            // FIX (minor): এই query শুধু 'paid' rows আনে,
                                            // তাই শুধু paid class রাখাই যথেষ্ট — dead branches সরানো হয়েছে
                                            $statusClass = 'bg-green-50 text-green-600 border-green-200';
                                            ?>
                                            <span class="px-2 py-1 rounded text-[10px] font-bold border <?php echo $statusClass; ?>">
                                                <?php echo strtoupper(htmlspecialchars($hist['status'])); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($invoice_history)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-6 text-gray-400 italic">No payment history available.</td>
                                    </tr>
                                <?php endif; ?>
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
                        <!-- user_id integer cast করো — অতিরিক্ত সতর্কতা -->
                        <span class="inline-block mt-1 px-3 py-1 bg-gray-100 text-gray-600 text-xs rounded-full font-semibold">Customer ID: #<?php echo (int)$user['user_id']; ?></span>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-start text-sm text-gray-600">
                            <i class="fa fa-envelope w-6 mt-1 text-gray-400"></i>
                            <span class="break-all"><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div class="flex items-start text-sm text-gray-600">
                            <i class="fa fa-phone w-6 mt-1 text-gray-400"></i>
                            <!-- FIX (medium): phone NULL হলে PHP 8.1+ TypeError দিত — ?? '' দিয়ে safe করা হয়েছে -->
                            <span><?php echo htmlspecialchars($user['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="flex items-start text-sm text-gray-600">
                            <i class="fa fa-map-marker-alt w-6 mt-1 text-gray-400"></i>
                            <!-- FIX (medium): address NULL হলে PHP 8.1+ TypeError দিত — ?? '' দিয়ে safe করা হয়েছে -->
                            <span><?php echo htmlspecialchars($user['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
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