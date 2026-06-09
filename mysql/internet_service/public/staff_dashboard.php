<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';
$db = (new Database())->getConnection();
$staff_id = $_SESSION['user_id'];
$designation = $_SESSION['designation'] ?? '';

$unpaid_invoices = [];
$pending_works = [];
$active_users = 0;
$counts = ['todays_tasks' => 0, 'total_pending' => 0, 'total_resolved' => 0];

// 🔥 স্মার্ট আপডেট: শুধুমাত্র গত ২৪ ঘণ্টার নতুন নোটিফিকেশন দেখাবে
/* $notifQuery = $db->prepare("SELECT message FROM notifications WHERE user_id = :uid AND sent_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) ORDER BY sent_at DESC LIMIT 1");
$notifQuery->execute([':uid' => $staff_id]);
$notification = $notifQuery->fetch(PDO::FETCH_ASSOC); */

// update staf notification fix errors
// এখানে নতুন notification code paste করো
$notifQuery = $db->prepare("
    SELECT notification_id, message
    FROM notifications
    WHERE user_id = :uid
      AND is_read = 0
      AND sent_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
    ORDER BY sent_at DESC
    LIMIT 1
");
$notifQuery->execute([':uid' => $staff_id]);
$notification = $notifQuery->fetch(PDO::FETCH_ASSOC);

if ($notification) {
    $markRead = $db->prepare("
        UPDATE notifications
        SET is_read = 1
        WHERE notification_id = ?
    ");
    $markRead->execute([$notification['notification_id']]);
}

// =====================================
// 🔥 BILLING MANAGER LOGIC
// =====================================
if ($designation === 'Billing Manager') {
    $action = isset($_GET['action']) ? $_GET['action'] : '';

    if ($action == 'generate_monthly_bills') {
        $subs = $db->query("SELECT s.*, p.price FROM subscriptions s JOIN packages p ON s.package_id = p.package_id")->fetchAll();
        foreach ($subs as $s) {
            $check = $db->prepare("SELECT invoice_id FROM invoices WHERE subscription_id = ? AND status = 'unpaid'");
            $check->execute([$s['subscription_id']]);
            if ($check->rowCount() == 0) {
                $inv_no = "INV-" . strtoupper(uniqid());
                $db->prepare("INSERT INTO invoices (user_id, subscription_id, invoice_number, amount, due_date, status) VALUES (?, ?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'unpaid')")->execute([$s['user_id'], $s['subscription_id'], $inv_no, $s['price']]);
            }
        }
        header("Location: staff_dashboard.php?msg=bills_generated");
        exit;
    }

    if ($action == 'mark_paid' && isset($_GET['id'])) {
        $inv_id = (int)$_GET['id'];
        $inv = $db->prepare("SELECT i.user_id, i.amount, i.subscription_id, p.duration_days FROM invoices i JOIN subscriptions s ON i.subscription_id = s.subscription_id JOIN packages p ON s.package_id = p.package_id WHERE i.invoice_id = ?");
        $inv->execute([$inv_id]);
        $invData = $inv->fetch();
        if ($invData) {
            $db->prepare("UPDATE invoices SET status = 'paid' WHERE invoice_id = ?")->execute([$inv_id]);
            $db->prepare("INSERT INTO payments (invoice_id, user_id, amount, method, transaction_ref) VALUES (?, ?, ?, 'Cash', 'BM-RENEWAL')")->execute([$inv_id, $invData['user_id'], $invData['amount']]);
            $db->prepare("UPDATE subscriptions SET status = 'active', end_date = DATE_ADD(IFNULL(IF(end_date < CURDATE(), CURDATE(), end_date), CURDATE()), INTERVAL ? DAY) WHERE subscription_id = ?")->execute([$invData['duration_days'], $invData['subscription_id']]);
            $db->prepare("UPDATE users SET status = 'active' WHERE user_id = ?")->execute([$invData['user_id']]);
        }
        header("Location: staff_dashboard.php?msg=renewed");
        exit;
    }

    $active_users = $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer' AND status = 'active'")->fetchColumn();
    $unpaid_invoices = $db->query("SELECT i.*, u.full_name, u.phone FROM invoices i JOIN users u ON i.user_id = u.user_id WHERE i.status IN ('unpaid', 'pending') ORDER BY i.created_at ASC")->fetchAll(PDO::FETCH_ASSOC);
}
// =====================================
// 🔥 TECHNICAL STAFF LOGIC
// =====================================
else {
    $stats = $db->prepare("
        SELECT 
            COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as todays_tasks,
            COUNT(CASE WHEN status != 'resolved' THEN 1 END) as total_pending,
            COUNT(CASE WHEN status = 'resolved' THEN 1 END) as total_resolved
        FROM tickets WHERE assigned_to = ?
    ");
    $stats->execute([$staff_id]);
    $counts = $stats->fetch(PDO::FETCH_ASSOC);

    $query = "SELECT t.*, u.full_name as customer_name, u.address, u.phone 
              FROM tickets t 
              JOIN users u ON t.user_id = u.user_id 
              WHERE t.assigned_to = :sid AND t.status != 'resolved' 
              ORDER BY t.status DESC, t.created_at ASC";
    $stmt = $db->prepare($query);
    $stmt->execute([':sid' => $staff_id]);
    $pending_works = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>My Works - Staff Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100 font-sans">
    <nav class="bg-gray-900 text-white p-4 shadow-lg sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold text-red-500">AMAR IT <span class="text-gray-400 text-sm font-normal">| <?php echo $designation ? htmlspecialchars($designation) : 'Technician'; ?> Portal</span></h1>
            <div class="flex items-center space-x-4">
                <span class="text-sm hidden md:block">Hello, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></span>
                <a href="logout.php" class="bg-red-600 px-4 py-1.5 rounded text-sm font-bold hover:bg-red-700 transition">Logout</a>
                <a href="staff_profile_edit.php" class="text-blue-400 hover:text-blue-300 text-sm font-bold mr-4"><i class="fa fa-user-edit"></i> Edit Profile</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto max-w-6xl py-8 px-4">

        <?php if ($notification): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm flex items-start animate-fade-in-down">
                <i class="fa fa-bell text-xl mr-3 mt-1 animate-pulse"></i>
                <div>
                    <h4 class="font-bold">New Notification!</h4>
                    <p class="text-sm"><?php echo htmlspecialchars($notification['message']); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($designation === 'Billing Manager'): ?>
            <div class="mb-8 flex justify-between items-center border-b pb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Billing Management</h2>
                    <p class="text-gray-500 mt-1">Total Active Customers: <strong class="text-green-600"><?php echo $active_users; ?></strong></p>
                </div>
                <a href="staff_dashboard.php?action=generate_monthly_bills" onclick="return confirm('Generate bills for all active users?');" class="bg-gray-800 text-white px-5 py-2.5 rounded-lg shadow hover:bg-black font-bold transition">
                    <i class="fa fa-magic mr-2"></i> Generate Monthly Bills
                </a>
            </div>

            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'renewed') echo "<p class='bg-green-100 text-green-700 p-4 rounded mb-6 font-bold border border-green-200 shadow-sm'><i class='fa fa-check-circle mr-2'></i> Cash collected and package renewed successfully!</p>"; ?>
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'bills_generated') echo "<p class='bg-blue-100 text-blue-700 p-4 rounded mb-6 font-bold border border-blue-200 shadow-sm'><i class='fa fa-file-invoice mr-2'></i> Monthly bills generated successfully for all active customers!</p>"; ?>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 border-t-4 border-t-green-500">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fa fa-hand-holding-dollar text-green-500 mr-2"></i> Pending Payments & Renewals</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-3 px-4">Invoice No.</th>
                                <th class="py-3 px-4">Customer Details</th>
                                <th class="py-3 px-4">Amount Due</th>
                                <th class="py-3 px-4">Due Date</th>
                                <th class="py-3 px-4 text-center">Collection Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($unpaid_invoices as $inv): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4 font-bold text-gray-600"><?php echo htmlspecialchars($inv['invoice_number']); ?></td>
                                    <td class="py-3 px-4">
                                        <p class="font-bold text-gray-800"><?php echo htmlspecialchars($inv['full_name']); ?></p>
                                        <p class="text-xs text-gray-500 mt-0.5"><i class="fa fa-phone mr-1"></i> <?php echo htmlspecialchars($inv['phone']); ?></p>
                                    </td>
                                    <td class="py-3 px-4 font-extrabold text-red-600 text-base">৳<?php echo number_format($inv['amount']); ?></td>
                                    <td class="py-3 px-4 text-gray-600 font-semibold"><?php echo date("d M Y", strtotime($inv['due_date'])); ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <a href="staff_dashboard.php?action=mark_paid&id=<?php echo $inv['invoice_id']; ?>" onclick="return confirm('Did you collect cash from this customer? Mark as PAID to RENEW line?');" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-xs font-bold shadow transition block w-max mx-auto">
                                            <i class="fa fa-check mr-1"></i> Collect & Renew
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($unpaid_invoices)) echo "<tr><td colspan='5' class='text-center py-10 text-gray-400 font-bold'><i class='fa fa-smile-beam text-4xl mb-3 block text-gray-300'></i> All clear! No pending bills to collect.</td></tr>"; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php else: ?>
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800">My Daily Works Overview</h2>
                <p class="text-gray-500">Date: <?php echo date('l, d F Y'); ?></p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-red-500 flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase tracking-wide">Tasks Assigned Today</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $counts['todays_tasks']; ?></p>
                    </div>
                    <i class="fa fa-calendar-day text-4xl text-red-100"></i>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-blue-500 flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase tracking-wide">Total Pending Works</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $counts['total_pending']; ?></p>
                    </div>
                    <i class="fa fa-spinner text-4xl text-blue-100"></i>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-500 flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase tracking-wide">Total Resolved</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $counts['total_resolved']; ?></p>
                    </div>
                    <i class="fa fa-check-circle text-4xl text-green-100"></i>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b bg-gray-50 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-800"><i class="fa fa-list-check text-blue-500 mr-2"></i> Pending Tasks (To-Do)</h2>
                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold"><?php echo count($pending_works); ?> Jobs Left</span>
                </div>

                <div class="p-6">
                    <?php if (count($pending_works) > 0): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php foreach ($pending_works as $job): ?>
                                <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition relative <?php echo ($job['status'] == 'open') ? 'bg-white border-l-4 border-l-red-500' : 'bg-blue-50 border-l-4 border-l-blue-500'; ?>">

                                    <div class="flex justify-between items-start mb-3">
                                        <span class="px-2 py-1 text-[10px] font-bold uppercase rounded <?php echo ($job['status'] == 'open') ? 'bg-red-100 text-red-700' : 'bg-blue-200 text-blue-800'; ?>">
                                            <?php echo $job['status']; ?>
                                        </span>
                                        <span class="text-xs text-gray-400 font-semibold"><i class="fa fa-clock"></i> <?php echo date("h:i A (d M)", strtotime($job['created_at'])); ?></span>
                                    </div>

                                    <h3 class="font-bold text-lg text-gray-800 mb-1"><?php echo htmlspecialchars($job['subject']); ?></h3>
                                    <p class="text-sm text-gray-600 mb-4 line-clamp-2">"<?php echo htmlspecialchars($job['message']); ?>"</p>

                                    <div class="bg-gray-100 p-3 rounded text-sm text-gray-700 mb-4">
                                        <p><i class="fa fa-user mr-2 text-gray-400"></i> <strong><?php echo htmlspecialchars($job['customer_name']); ?></strong></p>
                                        <p class="mt-1"><i class="fa fa-map-marker-alt mr-2 text-gray-400"></i> <?php echo htmlspecialchars($job['address']); ?></p>
                                    </div>

                                    <div class="flex items-center justify-between border-t pt-4">
                                        <a href="https://wa.me/88<?php echo htmlspecialchars($job['phone']); ?>" target="_blank" class="text-green-600 hover:text-green-800 font-bold text-sm">
                                            <i class="fab fa-whatsapp text-lg mr-1"></i> Contact
                                        </a>
                                        <a href="staff_view_ticket.php?id=<?php echo $job['ticket_id']; ?>" class="bg-gray-800 text-white px-4 py-2 rounded font-bold text-sm hover:bg-black transition">
                                            Start Work <i class="fa fa-arrow-right ml-1"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-10">
                            <i class="fa fa-mug-hot text-5xl text-gray-300 mb-4"></i>
                            <h3 class="text-xl font-bold text-gray-700">All caught up!</h3>
                            <p class="text-gray-500">You have no pending works for today. Great job!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</body>

</html>