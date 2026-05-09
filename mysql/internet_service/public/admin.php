<?php
session_start();

// 🔥 Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';
require_once '../src/Models/Package.php';

$database = new Database();
$db = $database->getConnection();
$packageModel = new Package($db);

$action = isset($_GET['action']) ? $_GET['action'] : '';

// ==========================================
// 🔥 CONNECTION & BILLING ACTIONS
// ==========================================
if ($action == 'activate_user' && isset($_GET['id'])) {
    $uid = (int)$_GET['id'];
    $db->prepare("UPDATE users SET status = 'active' WHERE user_id = ?")->execute([$uid]);
    $sub = $db->prepare("SELECT s.subscription_id, p.duration_days FROM subscriptions s JOIN packages p ON s.package_id = p.package_id WHERE s.user_id = ? ORDER BY s.subscription_id DESC LIMIT 1");
    $sub->execute([$uid]);
    $subData = $sub->fetch();
    if($subData) {
        $db->prepare("UPDATE subscriptions SET status = 'active', start_date = CURDATE(), end_date = DATE_ADD(CURDATE(), INTERVAL ? DAY) WHERE subscription_id = ?")->execute([$subData['duration_days'], $subData['subscription_id']]);
    }
    header("Location: admin.php?page=users&msg=activated"); exit;
}

if ($action == 'suspend_user' && isset($_GET['id'])) {
    $uid = (int)$_GET['id'];
    $db->prepare("UPDATE users SET status = 'suspended' WHERE user_id = ?")->execute([$uid]);
    $db->prepare("UPDATE subscriptions SET status = 'suspended' WHERE user_id = ? AND status = 'active'")->execute([$uid]);
    header("Location: admin.php?page=users&msg=suspended"); exit;
}

if ($action == 'generate_monthly_bills') {
    $subs = $db->query("SELECT s.*, p.price FROM subscriptions s JOIN packages p ON s.package_id = p.package_id")->fetchAll();
    foreach($subs as $s) {
        $check = $db->prepare("SELECT invoice_id FROM invoices WHERE subscription_id = ? AND status = 'unpaid'");
        $check->execute([$s['subscription_id']]);
        if($check->rowCount() == 0) {
            $inv_no = "INV-" . strtoupper(uniqid());
            $db->prepare("INSERT INTO invoices (user_id, subscription_id, invoice_number, amount, due_date, status) VALUES (?, ?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'unpaid')")->execute([$s['user_id'], $s['subscription_id'], $inv_no, $s['price']]);
        }
    }
    header("Location: admin.php?page=billings&msg=bills_generated"); exit;
}

if ($action == 'mark_paid' && isset($_GET['id'])) {
    $inv_id = (int)$_GET['id'];
    $inv = $db->prepare("SELECT i.user_id, i.amount, i.subscription_id, p.duration_days FROM invoices i JOIN subscriptions s ON i.subscription_id = s.subscription_id JOIN packages p ON s.package_id = p.package_id WHERE i.invoice_id = ?");
    $inv->execute([$inv_id]);
    $invData = $inv->fetch();
    if($invData) {
        $db->prepare("UPDATE invoices SET status = 'paid' WHERE invoice_id = ?")->execute([$inv_id]);
        $db->prepare("INSERT INTO payments (invoice_id, user_id, amount, method, transaction_ref) VALUES (?, ?, ?, 'Cash', 'RENEWAL')")->execute([$inv_id, $invData['user_id'], $invData['amount']]);
        $db->prepare("UPDATE subscriptions SET status = 'active', end_date = DATE_ADD(IFNULL(IF(end_date < CURDATE(), CURDATE(), end_date), CURDATE()), INTERVAL ? DAY) WHERE subscription_id = ?")->execute([$invData['duration_days'], $invData['subscription_id']]);
        $db->prepare("UPDATE users SET status = 'active' WHERE user_id = ?")->execute([$invData['user_id']]);
    }
    header("Location: admin.php?page=billings&msg=renewed"); exit;
}

// ==========================================
// 🔥 STAFF MANAGEMENT ACTIONS
// ==========================================
if ($action == 'add_staff' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $db->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->execute([$email]);
    if($check->rowCount() > 0) {
        header("Location: admin.php?page=staff&msg=email_exists"); exit;
    }
    $db->prepare("INSERT INTO users (full_name, email, password_hash, role, phone, address, status) VALUES (?, ?, ?, 'staff', ?, ?, 'active')")->execute([$name, $email, $pass, $phone, $address]);
    header("Location: admin.php?page=staff&msg=added"); exit;
}

if ($action == 'toggle_staff' && isset($_GET['id']) && isset($_GET['status'])) {
    $db->prepare("UPDATE users SET status = ? WHERE user_id = ? AND role = 'staff'")->execute([$_GET['status'], $_GET['id']]);
    header("Location: admin.php?page=staff&msg=status_updated"); exit;
}

if ($action == 'delete_staff' && isset($_GET['id'])) {
    $db->prepare("DELETE FROM users WHERE user_id = ? AND role = 'staff'")->execute([$_GET['id']]);
    header("Location: admin.php?page=staff&msg=deleted"); exit;
}

// ==========================================
// 🔥 PACKAGES & TICKETS ACTIONS
// ==========================================
if ($action == 'delete_package' && isset($_GET['id'])) {
    if ($packageModel->delete($_GET['id'])) { header("Location: admin.php?page=packages&msg=deleted"); exit; }
}

if ($action == 'update_ticket' && isset($_GET['id']) && isset($_GET['status'])) {
    $stmt = $db->prepare("UPDATE tickets SET status = :status WHERE ticket_id = :id");
    $stmt->execute([':status' => $_GET['status'], ':id' => $_GET['id']]);
    header("Location: admin.php?page=tickets"); exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && ($action == 'update_package' || (isset($_GET['page']) && $_GET['page'] == 'create_package'))) {
    $quota_type = isset($_POST['quota_type']) ? $_POST['quota_type'] : 'limited';
    $quota_gb = ($quota_type === 'unlimited') ? NULL : $_POST['quota_gb'];
    $features = isset($_POST['features']) ? $_POST['features'] : NULL;
    
    if ($action == 'update_package') {
        $id = $_POST['package_id'];
        $query = "UPDATE packages SET name=:name, type=:type, features=:features, speed_mbps=:speed, price=:price, quota_gb=:quota, duration_days=:duration, status=:status WHERE package_id=:id";
        $stmt = $db->prepare($query);
        $stmt->execute([':name' => $_POST['name'], ':type' => $_POST['type'], ':features' => $features, ':speed' => $_POST['speed_mbps'], ':price' => $_POST['price'], ':quota' => $quota_gb, ':duration' => $_POST['duration_days'], ':status' => $_POST['status'], ':id' => $id]);
        header("Location: admin.php?page=packages&msg=updated"); exit; 
    }

    if (isset($_GET['page']) && $_GET['page'] == 'create_package') {
        $query = "INSERT INTO packages (name, type, features, speed_mbps, price, quota_gb, duration_days, status) VALUES (:name, :type, :features, :speed, :price, :quota, :duration, :status)";
        $stmt = $db->prepare($query);
        $stmt->execute([':name' => $_POST['name'], ':type' => $_POST['type'], ':features' => $features, ':speed' => $_POST['speed_mbps'], ':price' => $_POST['price'], ':quota' => $quota_gb, ':duration' => $_POST['duration_days'], ':status' => $_POST['status']]);
        header("Location: admin.php?page=packages&msg=created"); exit; 
    }
}

// Routing Logic
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
include '../views/layouts/admin_header.php';

// ==========================================
// 🔥 DASHBOARD (Stats + Tracker)
// ==========================================
if ($page == 'dashboard') { 
    include '../views/admin/dashboard.php'; // আপনার আগের ড্যাশবোর্ড
    
    $totalCust = $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
    $totalStaff = $db->query("SELECT COUNT(*) FROM users WHERE role = 'staff'")->fetchColumn();
    
    $active_subs = $db->query("SELECT u.full_name, u.phone, p.name as package_name, s.end_date, DATEDIFF(s.end_date, CURDATE()) as days_left FROM subscriptions s JOIN users u ON s.user_id = u.user_id JOIN packages p ON s.package_id = p.package_id WHERE s.status = 'active' ORDER BY days_left ASC")->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="mt-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between border-l-4 border-blue-500">
                <div>
                    <h4 class="text-gray-500 text-xs font-bold uppercase">Total Customers</h4>
                    <p class="text-2xl font-bold text-blue-600"><?php echo $totalCust; ?></p>
                </div>
                <i class="fa fa-users text-3xl text-gray-200"></i>
            </div>
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between border-l-4 border-purple-500">
                <div>
                    <h4 class="text-gray-500 text-xs font-bold uppercase">Total Staff</h4>
                    <p class="text-2xl font-bold text-purple-600"><?php echo $totalStaff; ?></p>
                </div>
                <i class="fa fa-user-tie text-3xl text-gray-200"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 border-t-4 border-red-500">
            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fa fa-stopwatch text-red-500 mr-2"></i> Live Expiry Tracker (Active Connections)</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-gray-100">
                        <tr><th class="py-2 px-4">Customer</th><th class="py-2 px-4">Package</th><th class="py-2 px-4">Expiry Date</th><th class="py-2 px-4">Days Left</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($active_subs as $sub): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-4 font-bold text-gray-700"><?php echo htmlspecialchars($sub['full_name']); ?> <br><span class="text-xs text-gray-400"><?php echo htmlspecialchars($sub['phone']); ?></span></td>
                            <td class="py-2 px-4 uppercase text-gray-600 font-semibold"><?php echo htmlspecialchars($sub['package_name']); ?></td>
                            <td class="py-2 px-4 text-gray-600"><?php echo date("d M Y", strtotime($sub['end_date'])); ?></td>
                            <td class="py-2 px-4">
                                <?php if($sub['days_left'] < 0): ?>
                                    <span class="bg-gray-200 text-gray-700 font-bold px-3 py-1 rounded-full text-xs">EXPIRED</span>
                                <?php elseif($sub['days_left'] <= 3): ?>
                                    <span class="bg-red-100 text-red-700 font-bold px-3 py-1 rounded-full text-xs animate-pulse"><?php echo $sub['days_left']; ?> Days</span>
                                <?php elseif($sub['days_left'] <= 7): ?>
                                    <span class="bg-orange-100 text-orange-700 font-bold px-3 py-1 rounded-full text-xs"><?php echo $sub['days_left']; ?> Days</span>
                                <?php else: ?>
                                    <span class="bg-green-100 text-green-700 font-bold px-3 py-1 rounded-full text-xs"><?php echo $sub['days_left']; ?> Days</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($active_subs)) echo "<tr><td colspan='4' class='text-center py-4 text-gray-400'>No active connections found.</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
} 

// ==========================================
// 🔥 MANAGE STAFF
// ==========================================
elseif ($page == 'staff') {
    $staffList = $db->query("SELECT * FROM users WHERE role = 'staff' ORDER BY user_id DESC")->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 h-fit">
            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fa fa-user-plus text-purple-500 mr-2"></i> Add New Staff</h3>
            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'email_exists') echo "<p class='text-red-500 text-sm mb-3 font-bold'>Email already exists!</p>"; ?>
            <form action="admin.php?action=add_staff" method="POST" class="space-y-4">
                <div><label class="block text-xs font-bold text-gray-600 mb-1">Full Name</label><input type="text" name="full_name" required class="w-full border px-3 py-2 rounded outline-none focus:border-purple-500"></div>
                <div><label class="block text-xs font-bold text-gray-600 mb-1">Email</label><input type="email" name="email" required class="w-full border px-3 py-2 rounded outline-none focus:border-purple-500"></div>
                <div><label class="block text-xs font-bold text-gray-600 mb-1">Phone</label><input type="text" name="phone" required class="w-full border px-3 py-2 rounded outline-none focus:border-purple-500"></div>
                <div><label class="block text-xs font-bold text-gray-600 mb-1">Designation / Address</label><input type="text" name="address" required class="w-full border px-3 py-2 rounded outline-none focus:border-purple-500"></div>
                <div><label class="block text-xs font-bold text-gray-600 mb-1">Password</label><input type="password" name="password" required class="w-full border px-3 py-2 rounded outline-none focus:border-purple-500"></div>
                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 rounded shadow transition">Create Account</button>
            </form>
        </div>
        <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fa fa-user-tie text-gray-500 mr-2"></i> Staff Directory</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-gray-100">
                        <tr><th class="py-3 px-4">Info</th><th class="py-3 px-4">Contact</th><th class="py-3 px-4">Status</th><th class="py-3 px-4 text-center">Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($staffList as $s): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <p class="font-bold text-gray-800"><?php echo htmlspecialchars($s['full_name']); ?></p>
                                <p class="text-[10px] text-gray-500 uppercase"><?php echo htmlspecialchars($s['address']); ?></p>
                            </td>
                            <td class="py-3 px-4 text-xs text-gray-600">
                                <i class="fa fa-envelope w-4"></i> <?php echo htmlspecialchars($s['email']); ?><br>
                                <i class="fa fa-phone w-4 mt-1"></i> <?php echo htmlspecialchars($s['phone']); ?>
                            </td>
                            <td class="py-3 px-4">
                                <?php if($s['status'] == 'active'): ?>
                                    <span class="bg-green-100 text-green-700 font-bold px-2 py-1 rounded text-[10px] uppercase">Active</span>
                                <?php else: ?>
                                    <span class="bg-red-100 text-red-700 font-bold px-2 py-1 rounded text-[10px] uppercase">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-center space-x-1">
                                <?php if($s['status'] == 'active'): ?>
                                    <a href="admin.php?action=toggle_staff&id=<?php echo $s['user_id']; ?>&status=suspended" class="text-orange-500 hover:text-orange-700" title="Suspend"><i class="fa fa-ban text-lg"></i></a>
                                <?php else: ?>
                                    <a href="admin.php?action=toggle_staff&id=<?php echo $s['user_id']; ?>&status=active" class="text-green-500 hover:text-green-700" title="Activate"><i class="fa fa-check-circle text-lg"></i></a>
                                <?php endif; ?>
                                <a href="admin.php?action=delete_staff&id=<?php echo $s['user_id']; ?>" onclick="return confirm('Are you sure you want to DELETE this staff?');" class="text-red-500 hover:text-red-700 ml-2" title="Delete"><i class="fa fa-trash text-lg"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}

// ==========================================
// 🔥 MANAGE PACKAGES
// ==========================================
elseif ($page == 'packages') { $packages = $packageModel->getAll(); include '../views/admin/packages.php'; } 
elseif ($page == 'create_package') { include '../views/admin/create_package.php'; } 
elseif ($page == 'edit_package' && isset($_GET['id'])) { $packageData = $packageModel->getById($_GET['id']); include '../views/admin/edit_package.php'; } 

// ==========================================
// 🔥 MANAGE CUSTOMERS
// ==========================================
elseif ($page == 'users') {
    $users = $db->query("SELECT * FROM users WHERE role = 'customer' ORDER BY user_id DESC")->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-4"><i class="fa fa-users text-blue-500 mr-2"></i> Manage Customers</h2>
        <table class="min-w-full bg-white text-left text-sm">
            <thead class="bg-gray-100">
                <tr><th class="py-3 px-4">ID</th><th class="py-3 px-4">Customer Info</th><th class="py-3 px-4">Address</th><th class="py-3 px-4">Status</th><th class="py-3 px-4 text-center">Connection Action</th></tr>
            </thead>
            <tbody>
                <?php foreach($users as $u): ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-2 px-4 font-bold text-gray-500">#<?php echo $u['user_id']; ?></td>
                    <td class="py-2 px-4">
                        <p class="font-bold text-gray-800"><?php echo htmlspecialchars($u['full_name']); ?></p>
                        <p class="text-xs text-gray-500"><i class="fa fa-phone text-gray-400"></i> <?php echo htmlspecialchars($u['phone']); ?></p>
                    </td>
                    <td class="py-2 px-4 text-gray-600 truncate max-w-xs"><?php echo htmlspecialchars($u['address']); ?></td>
                    <td class="py-2 px-4">
                        <?php if($u['status'] == 'active'): ?>
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700 font-bold border border-green-200"><i class="fa fa-check-circle"></i> ACTIVE</span>
                        <?php else: ?>
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700 font-bold border border-red-200"><i class="fa fa-ban"></i> SUSPENDED</span>
                        <?php endif; ?>
                    </td>
                    <td class="py-2 px-4 text-center space-x-2">
                        <?php if($u['status'] != 'active'): ?>
                            <a href="admin.php?action=activate_user&id=<?php echo $u['user_id']; ?>" onclick="return confirm('ACTIVATE this connection?');" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded text-xs font-bold shadow transition">Activate</a>
                        <?php else: ?>
                            <a href="admin.php?action=suspend_user&id=<?php echo $u['user_id']; ?>" onclick="return confirm('SUSPEND this connection?');" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded text-xs font-bold shadow transition">Suspend</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// ==========================================
// 🔥 BILLINGS & INVOICES
// ==========================================
elseif ($page == 'billings') {
    $invoices = $db->query("SELECT i.*, u.full_name FROM invoices i JOIN users u ON i.user_id = u.user_id ORDER BY i.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h2 class="text-2xl font-bold text-gray-800"><i class="fa fa-file-invoice-dollar text-green-500 mr-2"></i> Billings & Invoices</h2>
            <a href="admin.php?action=generate_monthly_bills" onclick="return confirm('Generate new invoices for all customers?');" class="bg-gray-800 text-white px-5 py-2 rounded-lg shadow hover:bg-black font-bold transition">
                <i class="fa fa-magic mr-2"></i> Generate Monthly Bills
            </a>
        </div>
        <table class="min-w-full bg-white text-left text-sm">
            <thead class="bg-gray-100">
                <tr><th class="py-3 px-4">Inv No.</th><th class="py-3 px-4">Customer</th><th class="py-3 px-4">Amount</th><th class="py-3 px-4">Due Date</th><th class="py-3 px-4">Status</th><th class="py-3 px-4 text-center">Payment Action</th></tr>
            </thead>
            <tbody>
                <?php foreach($invoices as $inv): ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-2 px-4 font-bold text-gray-600"><?php echo $inv['invoice_number']; ?></td>
                    <td class="py-2 px-4 font-semibold"><?php echo htmlspecialchars($inv['full_name']); ?></td>
                    <td class="py-2 px-4 font-bold text-red-600">৳<?php echo number_format($inv['amount']); ?></td>
                    <td class="py-2 px-4"><?php echo date("d M Y", strtotime($inv['due_date'])); ?></td>
                    <td class="py-2 px-4">
                        <span class="px-2 py-1 text-xs rounded-full font-bold border <?php echo ($inv['status'] == 'paid') ? 'bg-green-50 text-green-700 border-green-200' : 'bg-orange-50 text-orange-700 border-orange-200'; ?>"><?php echo strtoupper($inv['status']); ?></span>
                    </td>
                    <td class="py-2 px-4 text-center">
                        <?php if($inv['status'] == 'unpaid'): ?>
                            <a href="admin.php?action=mark_paid&id=<?php echo $inv['invoice_id']; ?>" onclick="return confirm('Did you receive the cash payment? Mark as PAID?');" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs font-bold shadow transition"><i class="fa fa-hand-holding-dollar mr-1"></i> Mark as Paid</a>
                        <?php else: ?>
                            <span class="text-gray-400 font-bold text-xs"><i class="fa fa-check"></i> Completed</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// ==========================================
// 🔥 SUPPORT TICKETS
// ==========================================
elseif ($page == 'tickets') {
    $tickets = $db->query("SELECT t.*, u.full_name, s.full_name as assigned_name FROM tickets t JOIN users u ON t.user_id = u.user_id LEFT JOIN users s ON t.assigned_to = s.user_id ORDER BY t.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-4"><i class="fa fa-headset text-red-500 mr-2"></i> Support Tickets</h2>
        <table class="min-w-full bg-white text-left text-sm">
            <thead class="bg-gray-100">
                <tr><th class="py-3 px-4">TKT ID</th><th class="py-3 px-4">Customer</th><th class="py-3 px-4">Subject</th><th class="py-3 px-4">Assigned To</th><th class="py-3 px-4">Status</th><th class="py-3 px-4 text-center">Action</th></tr>
            </thead>
            <tbody>
                <?php foreach($tickets as $t): ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-2 px-4 font-bold">#<?php echo $t['ticket_id']; ?></td>
                    <td class="py-2 px-4"><?php echo htmlspecialchars($t['full_name']); ?></td>
                    <td class="py-2 px-4 font-semibold text-gray-700"><?php echo htmlspecialchars($t['subject']); ?></td>
                    <td class="py-2 px-4">
                        <?php echo $t['assigned_name'] ? '<span class="text-blue-600 font-bold">'.htmlspecialchars($t['assigned_name']).'</span>' : '<span class="text-red-400 italic">Unassigned</span>'; ?>
                    </td>
                    <td class="py-2 px-4">
                        <span class="px-2 py-1 text-xs rounded-full font-bold border <?php echo ($t['status'] == 'open') ? 'bg-red-50 text-red-600' : (($t['status'] == 'processing') ? 'bg-blue-50 text-blue-600' : 'bg-green-50 text-green-600'); ?>"><?php echo strtoupper($t['status']); ?></span>
                    </td>
                    <td class="py-2 px-4 text-center space-x-2">
                        <a href="admin.php?page=view_ticket&id=<?php echo $t['ticket_id']; ?>" class="bg-gray-800 hover:bg-black text-white px-3 py-1 rounded text-xs transition shadow"><i class="fa fa-eye"></i> View</a>
                        <?php if($t['status'] != 'processing'): ?>
                            <a href="admin.php?action=update_ticket&id=<?php echo $t['ticket_id']; ?>&status=processing" class="text-blue-500 hover:text-blue-700"><i class="fa fa-spinner"></i></a>
                        <?php endif; ?>
                        <?php if($t['status'] != 'resolved'): ?>
                            <a href="admin.php?action=update_ticket&id=<?php echo $t['ticket_id']; ?>&status=resolved" class="text-green-500 hover:text-green-700"><i class="fa fa-check-circle"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// ==========================================
// 🔥 VIEW TICKET
// ==========================================
elseif ($page == 'view_ticket' && isset($_GET['id'])) {
    $ticket_id = (int)$_GET['id'];

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_staff'])) {
        $staff_id = $_POST['staff_id'] ?: NULL;
        $db->prepare("UPDATE tickets SET assigned_to = ? WHERE ticket_id = ?")->execute([$staff_id, $ticket_id]);
        header("Location: admin.php?page=view_ticket&id=$ticket_id"); exit;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reply_message'])) {
        $msg = trim($_POST['reply_message']);
        if (!empty($msg)) {
            $db->prepare("INSERT INTO ticket_replies (ticket_id, user_id, message) VALUES (?, ?, ?)")->execute([$ticket_id, $_SESSION['user_id'], $msg]);
            $db->query("UPDATE tickets SET status='processing' WHERE ticket_id=$ticket_id AND status='open'");
            header("Location: admin.php?page=view_ticket&id=$ticket_id"); exit;
        }
    }

    $stmt = $db->prepare("SELECT t.*, u.full_name, u.email, s.full_name as staff_name FROM tickets t JOIN users u ON t.user_id = u.user_id LEFT JOIN users s ON t.assigned_to = s.user_id WHERE t.ticket_id = ?");
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmtRep = $db->prepare("SELECT r.*, u.full_name, u.role FROM ticket_replies r JOIN users u ON r.user_id = u.user_id WHERE r.ticket_id = ? ORDER BY r.replied_at ASC");
    $stmtRep->execute([$ticket_id]);
    $replies = $stmtRep->fetchAll(PDO::FETCH_ASSOC);

    $staffs = $db->query("SELECT user_id, full_name FROM users WHERE role = 'staff' AND status = 'active'")->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="max-w-4xl mx-auto mt-6">
        <a href="admin.php?page=tickets" class="text-blue-600 hover:underline mb-4 inline-block font-semibold"><i class="fa fa-arrow-left"></i> Back to Tickets</a>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6 border-t-4 border-red-500">
            <div class="flex justify-between items-start mb-4 border-b pb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($ticket['subject']); ?></h2>
                    <p class="text-sm text-gray-500 mt-1">Ticket #<?php echo $ticket['ticket_id']; ?> | Customer: <strong><?php echo htmlspecialchars($ticket['full_name']); ?></strong></p>
                </div>
                
                <form method="POST" class="flex items-center space-x-2 bg-gray-50 p-2 rounded border border-gray-200 shadow-sm">
                    <label class="text-xs font-bold text-gray-600"><i class="fa fa-user-cog mr-1"></i> Assign:</label>
                    <select name="staff_id" class="text-sm border-gray-300 rounded outline-none py-1 px-2">
                        <option value="">-- Unassigned --</option>
                        <?php foreach($staffs as $s): ?>
                            <option value="<?php echo $s['user_id']; ?>" <?php echo ($ticket['assigned_to'] == $s['user_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="assign_staff" class="bg-blue-600 text-white text-xs px-3 py-1.5 rounded font-bold hover:bg-blue-700 transition">Set</button>
                </form>
            </div>
            
            <div class="text-gray-700 p-4 bg-gray-50 rounded-lg border border-gray-100 text-sm leading-relaxed mb-3">
                <?php echo nl2br(htmlspecialchars($ticket['message'])); ?>
            </div>
        </div>

        <h3 class="text-lg font-bold text-gray-700 mb-4">Conversation History</h3>
        <div class="space-y-4 mb-6">
            <?php foreach($replies as $reply): ?>
                <div class="p-4 rounded-xl shadow-sm border <?php echo ($reply['role'] == 'admin' || $reply['role'] == 'staff') ? 'bg-red-50 border-red-200 ml-10' : 'bg-white border-gray-200 mr-10'; ?>">
                    <div class="flex justify-between items-center mb-2">
                        <strong class="<?php echo ($reply['role'] == 'admin' || $reply['role'] == 'staff') ? 'text-red-600' : 'text-blue-600'; ?>">
                            <?php echo htmlspecialchars($reply['full_name']); ?> <?php echo ($reply['role'] == 'admin') ? '(Admin)' : (($reply['role'] == 'staff') ? '(Staff)' : '(Customer)'); ?>
                        </strong>
                        <span class="text-xs text-gray-400"><?php echo date("d M Y, h:i A", strtotime($reply['replied_at'])); ?></span>
                    </div>
                    <p class="text-gray-700 text-sm"><?php echo nl2br(htmlspecialchars($reply['message'])); ?></p>
                </div>
            <?php endforeach; ?>
            <?php if(empty($replies)) echo "<p class='text-gray-500 text-center text-sm py-4'>No replies yet.</p>"; ?>
        </div>

        <?php if($ticket['status'] != 'resolved'): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-10">
                <form action="admin.php?page=view_ticket&id=<?php echo $ticket_id; ?>" method="POST">
                    <textarea name="reply_message" rows="4" required placeholder="Type your response here..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 outline-none mb-4"></textarea>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded shadow transition">Send Message</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <?php
} 
else { echo "<div class='bg-white p-10 text-center mt-6 rounded-xl border'><h2 class='text-4xl font-bold text-red-500'>404</h2><p class='text-gray-600'>Page Not Found</p></div>"; }

echo "</main></div></body></html>";
?>