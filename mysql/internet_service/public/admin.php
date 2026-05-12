<?php
ob_start();
session_start();

// ==========================================
// 🔥 Security Check
// ==========================================
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

/* =========================================================
   🔥 NOTIFICATION READ & REDIRECT SYSTEM
   FIX: This block now also lives here (not just admin_actions.php)
   so that admin_header.php can link to admin.php?action=read_and_redirect
========================================================= */
if ($action == 'read_and_redirect' && isset($_GET['notif_id'])) {

    $notif_id = (int)$_GET['notif_id'];

    // Mark as read — scoped to logged-in admin only
    $db->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?")
       ->execute([$notif_id, $_SESSION['user_id']]);

    // Fetch message
    $stmt = $db->prepare("SELECT message FROM notifications WHERE notification_id = ? AND user_id = ?");
    $stmt->execute([$notif_id, $_SESSION['user_id']]);
    $nData = $stmt->fetch(PDO::FETCH_ASSOC);

    $url = "admin.php?page=dashboard";

    if ($nData) {
        $m = strtolower($nData['message']);

        if (strpos($m, 'payment') !== false || strpos($m, 'ticket') !== false || strpos($m, 'support') !== false) {
            $url = "admin.php?page=tickets";
        } elseif (strpos($m, 'invoice') !== false || strpos($m, 'bill') !== false) {
            $url = "admin.php?page=billings";
        } elseif (strpos($m, 'customer') !== false || strpos($m, 'user') !== false) {
            $url = "admin.php?page=users";
        } elseif (strpos($m, 'staff') !== false || strpos($m, 'technician') !== false) {
            $url = "admin.php?page=staff";
        } elseif (strpos($m, 'package') !== false || strpos($m, 'plan') !== false) {
            $url = "admin.php?page=packages";
        }
    }

    header("Location: " . $url);
    exit;
}

/* =========================================================
   🔥 EXPENSE ENTRY LOGIC
   FIX: Added amount validation (must be > 0)
========================================================= */
if ($action == 'add_expense' && $_SERVER['REQUEST_METHOD'] == 'POST') {

    $category    = trim($_POST['category']);
    $amount      = (float)$_POST['amount'];
    $description = trim($_POST['description']);
    $date        = $_POST['expense_date'];

    // FIX #10: Validate amount
    if ($amount <= 0) {
        header("Location: admin.php?page=expenses&msg=invalid_amount");
        exit;
    }

    $db->prepare("
        INSERT INTO expenses (category, amount, description, expense_date) 
        VALUES (?, ?, ?, ?)
    ")->execute([$category, $amount, $description, $date]);

    header("Location: admin.php?page=expenses&msg=added");
    exit;
}

/* =========================================================
   🔥 COVERAGE ZONE CRUD LOGIC
========================================================= */
if (isset($_GET['action'])) {

    $action = $_GET['action'];

    if ($action == 'add_zone' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $district = trim($_POST['district']);
        $upazila  = trim($_POST['upazila']);
        $desc     = trim($_POST['description']);
        // FIX: Whitelist status values
        $status   = in_array($_POST['status'], ['active', 'upcoming']) ? $_POST['status'] : 'upcoming';

        $db->prepare("
            INSERT INTO coverage_zones (district, upazila, description, status) 
            VALUES (?, ?, ?, ?)
        ")->execute([$district, $upazila, $desc, $status]);

        header("Location: admin.php?page=coverage_admin&msg=zone_added");
        exit;
    }

    if ($action == 'delete_zone' && isset($_GET['id'])) {
        $db->prepare("DELETE FROM coverage_zones WHERE notification_id = ?")
           ->execute([(int)$_GET['id']]);

        header("Location: admin.php?page=coverage_admin&msg=zone_deleted");
        exit;
    }

    if ($action == 'edit_zone' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $id       = (int)$_POST['id'];
        $district = trim($_POST['district']);
        $upazila  = trim($_POST['upazila']);
        $desc     = trim($_POST['description']);
        // FIX: Whitelist status values
        $status   = in_array($_POST['status'], ['active', 'upcoming']) ? $_POST['status'] : 'upcoming';

        $db->prepare("
            UPDATE coverage_zones 
            SET district = ?, upazila = ?, description = ?, status = ?
            WHERE notification_id = ?
        ")->execute([$district, $upazila, $desc, $status, $id]);

        header("Location: admin.php?page=coverage_admin&msg=zone_updated");
        exit;
    }

    // FIX #2: Mark All Notifications Read — scoped to logged-in admin only
    if ($action == 'mark_notifs_read') {
        $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0")
           ->execute([$_SESSION['user_id']]);

        header("Location: admin.php");
        exit;
    }
}

// ==========================================
// 🔥 CORE ISP LOGIC (ACTIVATE, SUSPEND)
// ==========================================
if ($action == 'activate_user' && isset($_GET['id'])) {
    $uid = (int)$_GET['id'];

    $db->prepare("UPDATE users SET status = 'active' WHERE user_id = ?")->execute([$uid]);

    $sub = $db->prepare("
        SELECT s.subscription_id, p.duration_days 
        FROM subscriptions s 
        JOIN packages p ON s.package_id = p.package_id 
        WHERE s.user_id = ? 
        ORDER BY s.subscription_id DESC LIMIT 1
    ");
    $sub->execute([$uid]);
    $subData = $sub->fetch();

    if ($subData) {
        // FIX #6: Use COALESCE so start_date is only set if it was never set before
        $db->prepare("
            UPDATE subscriptions 
            SET status = 'active',
                start_date = COALESCE(start_date, CURDATE()),
                end_date = DATE_ADD(CURDATE(), INTERVAL ? DAY)
            WHERE subscription_id = ?
        ")->execute([$subData['duration_days'], $subData['subscription_id']]);
    }

    $redirect = isset($_GET['from']) ? $_GET['from'] : 'users';
    header("Location: admin.php?page=$redirect&msg=activated");
    exit;
}

if ($action == 'suspend_user' && isset($_GET['id'])) {
    $uid = (int)$_GET['id'];
    $db->prepare("UPDATE users SET status = 'suspended' WHERE user_id = ?")->execute([$uid]);
    $db->prepare("UPDATE subscriptions SET status = 'suspended' WHERE user_id = ? AND status = 'active'")->execute([$uid]);
    header("Location: admin.php?page=users&msg=suspended");
    exit;
}

// 🔥 Assign Tech + Send Notification to Staff
if ($action == 'assign_tech' && isset($_GET['uid']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $uid      = (int)$_GET['uid'];
    $staff_id = (int)$_POST['staff_id'];
    $redirect = isset($_GET['from']) ? $_GET['from'] : 'users';

    if ($staff_id > 0) {
        $check = $db->prepare("
            SELECT ticket_id FROM tickets 
            WHERE user_id = ? AND category = 'New Installation' AND status != 'resolved'
        ");
        $check->execute([$uid]);

        if ($check->rowCount() == 0) {
            $msg = "Please go to the customer's address, setup the new internet connection, configure router and pull the fiber cable.";
            $db->prepare("
                INSERT INTO tickets (user_id, subject, category, message, status, assigned_to) 
                VALUES (?, 'New Internet Connection Setup', 'New Installation', ?, 'open', ?)
            ")->execute([$uid, $msg, $staff_id]);
        } else {
            $db->prepare("
                UPDATE tickets SET assigned_to = ? 
                WHERE user_id = ? AND category = 'New Installation' AND status != 'resolved'
            ")->execute([$staff_id, $uid]);
        }

        $notif_msg = "🚨 New Job: You have been assigned for a New Connection Setup (Customer ID: #$uid). Please check your pending tasks.";
        $db->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")
           ->execute([$staff_id, $notif_msg]);
    }

    header("Location: admin.php?page=$redirect&msg=tech_assigned");
    exit;
}

// FIX #7: Generate bills only for ACTIVE subscriptions
if ($action == 'generate_monthly_bills') {
    $subs = $db->query("
        SELECT s.*, p.price 
        FROM subscriptions s 
        JOIN packages p ON s.package_id = p.package_id
        WHERE s.status = 'active'
    ")->fetchAll();

    foreach ($subs as $s) {
        $check = $db->prepare("SELECT invoice_id FROM invoices WHERE subscription_id = ? AND status = 'unpaid'");
        $check->execute([$s['subscription_id']]);

        if ($check->rowCount() == 0) {
            $inv_no = "INV-" . strtoupper(uniqid());
            $db->prepare("
                INSERT INTO invoices (user_id, subscription_id, invoice_number, amount, due_date, status) 
                VALUES (?, ?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'unpaid')
            ")->execute([$s['user_id'], $s['subscription_id'], $inv_no, $s['price']]);
        }
    }

    header("Location: admin.php?page=billings&msg=bills_generated");
    exit;
}

if ($action == 'mark_paid' && isset($_GET['id'])) {
    $inv_id = (int)$_GET['id'];

    $inv = $db->prepare("
        SELECT i.user_id, i.amount, i.subscription_id, s.end_date, p.duration_days 
        FROM invoices i 
        JOIN subscriptions s ON i.subscription_id = s.subscription_id 
        JOIN packages p ON s.package_id = p.package_id 
        WHERE i.invoice_id = ?
    ");
    $inv->execute([$inv_id]);
    $invData = $inv->fetch();

    if ($invData) {
        $db->prepare("UPDATE invoices SET status = 'paid' WHERE invoice_id = ?")->execute([$inv_id]);
        $db->prepare("
            INSERT INTO payments (invoice_id, user_id, amount, method, transaction_ref) 
            VALUES (?, ?, ?, 'Cash', 'RENEWAL')
        ")->execute([$inv_id, $invData['user_id'], $invData['amount']]);

        $current_expiry = $invData['end_date'];
        $base_date = ($current_expiry && strtotime($current_expiry) > time()) ? $current_expiry : date('Y-m-d');

        $db->prepare("
            UPDATE subscriptions 
            SET status = 'active', end_date = DATE_ADD(?, INTERVAL ? DAY) 
            WHERE subscription_id = ?
        ")->execute([$base_date, $invData['duration_days'], $invData['subscription_id']]);

        $db->prepare("UPDATE users SET status = 'active' WHERE user_id = ?")->execute([$invData['user_id']]);
    }

    // FIX #5: Correct ? vs & in redirect URL
    $base_redirect = (basename($_SERVER['PHP_SELF']) == 'admin.php') ? 'admin.php?page=billings' : 'staff_dashboard.php';
    $separator = (strpos($base_redirect, '?') !== false) ? '&' : '?';
    header("Location: $base_redirect{$separator}msg=renewed");
    exit;
}

// ==========================================
// 🔥 STAFF MANAGEMENT ACTIONS
// ==========================================
if ($action == 'add_staff' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $name        = trim($_POST['full_name']);
    $designation = trim($_POST['designation']);
    $email       = trim($_POST['email']);
    $phone       = trim($_POST['phone']);
    $address     = trim($_POST['address']);
    $pass        = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $db->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        header("Location: admin.php?page=staff&msg=email_exists");
        exit;
    }

    $db->prepare("
        INSERT INTO users (full_name, designation, email, password_hash, role, phone, address, status) 
        VALUES (?, ?, ?, ?, 'staff', ?, ?, 'active')
    ")->execute([$name, $designation, $email, $pass, $phone, $address]);

    header("Location: admin.php?page=staff&msg=added");
    exit;
}

// FIX #3: Whitelist allowed status values for toggle_staff
if ($action == 'toggle_staff' && isset($_GET['id']) && isset($_GET['status'])) {
    $allowed_statuses = ['active', 'suspended'];
    $new_status = in_array($_GET['status'], $allowed_statuses) ? $_GET['status'] : 'suspended';

    $db->prepare("UPDATE users SET status = ? WHERE user_id = ? AND role = 'staff'")
       ->execute([$new_status, (int)$_GET['id']]);

    header("Location: admin.php?page=staff&msg=status_updated");
    exit;
}

// FIX #13: Before deleting staff, unassign them from open tickets
if ($action == 'delete_staff' && isset($_GET['id'])) {
    $staff_id = (int)$_GET['id'];

    // Unassign from any open tickets before deleting
    $db->prepare("UPDATE tickets SET assigned_to = NULL WHERE assigned_to = ? AND status != 'resolved'")
       ->execute([$staff_id]);

    $db->prepare("DELETE FROM users WHERE user_id = ? AND role = 'staff'")
       ->execute([$staff_id]);

    header("Location: admin.php?page=staff&msg=deleted");
    exit;
}

// ==========================================
// 🔥 PACKAGES & TICKETS ACTIONS
// ==========================================
if ($action == 'delete_package' && isset($_GET['id'])) {
    if ($packageModel->delete($_GET['id'])) {
        header("Location: admin.php?page=packages&msg=deleted");
        exit;
    }
}

// FIX #4: Whitelist ticket status values
if ($action == 'update_ticket' && isset($_GET['id']) && isset($_GET['status'])) {
    $allowed_statuses = ['open', 'processing', 'resolved'];
    $ticket_status = in_array($_GET['status'], $allowed_statuses) ? $_GET['status'] : 'open';

    $stmt = $db->prepare("UPDATE tickets SET status = ? WHERE ticket_id = ?");
    $stmt->execute([$ticket_status, (int)$_GET['id']]);

    header("Location: admin.php?page=tickets");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && ($action == 'update_package' || (isset($_GET['page']) && $_GET['page'] == 'create_package'))) {
    $quota_type = isset($_POST['quota_type']) ? $_POST['quota_type'] : 'limited';
    $quota_gb   = ($quota_type === 'unlimited') ? NULL : (int)$_POST['quota_gb'];
    $features   = isset($_POST['features']) ? $_POST['features'] : NULL;

    if ($action == 'update_package') {
        $id    = (int)$_POST['package_id'];
        $query = "UPDATE packages SET name=:name, type=:type, features=:features, speed_mbps=:speed, price=:price, quota_gb=:quota, duration_days=:duration, status=:status WHERE package_id=:id";
        $stmt  = $db->prepare($query);
        $stmt->execute([
            ':name'     => $_POST['name'],
            ':type'     => $_POST['type'],
            ':features' => $features,
            ':speed'    => (int)$_POST['speed_mbps'],
            ':price'    => (float)$_POST['price'],
            ':quota'    => $quota_gb,
            ':duration' => (int)$_POST['duration_days'],
            ':status'   => $_POST['status'],
            ':id'       => $id
        ]);
        header("Location: admin.php?page=packages&msg=updated");
        exit;
    }

    if (isset($_GET['page']) && $_GET['page'] == 'create_package') {
        $query = "INSERT INTO packages (name, type, features, speed_mbps, price, quota_gb, duration_days, status) VALUES (:name, :type, :features, :speed, :price, :quota, :duration, :status)";
        $stmt  = $db->prepare($query);
        $stmt->execute([
            ':name'     => $_POST['name'],
            ':type'     => $_POST['type'],
            ':features' => $features,
            ':speed'    => (int)$_POST['speed_mbps'],
            ':price'    => (float)$_POST['price'],
            ':quota'    => $quota_gb,
            ':duration' => (int)$_POST['duration_days'],
            ':status'   => $_POST['status']
        ]);
        header("Location: admin.php?page=packages&msg=created");
        exit;
    }
}

// ==========================================
// Routing Logic
// ==========================================
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
include '../views/layouts/admin_header.php';

// ==========================================
// 🔥 DASHBOARD
// ==========================================
if ($page == 'dashboard') {

    $totalCust    = $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
    $totalStaff   = $db->query("SELECT COUNT(*) FROM users WHERE role = 'staff'")->fetchColumn();
    $totalIncome  = $db->query("SELECT SUM(amount) FROM payments")->fetchColumn() ?: 0;
    $totalExpense = $db->query("SELECT SUM(amount) FROM expenses")->fetchColumn() ?: 0;
    $netProfit    = $totalIncome - $totalExpense;

    $activeStaff = $db->query("
        SELECT user_id, full_name, designation 
        FROM users WHERE role = 'staff' AND status = 'active'
    ")->fetchAll(PDO::FETCH_ASSOC);

    // FIX #8: Improved query logic — proper grouping of OR conditions
    $new_requests = $db->query("
        SELECT u.user_id, u.full_name, u.phone, u.address, p.name as package_name 
        FROM users u 
        JOIN subscriptions s ON u.user_id = s.user_id 
        JOIN packages p ON s.package_id = p.package_id 
        WHERE (s.start_date IS NULL AND s.status = 'pending') OR u.status = 'pending'
        ORDER BY u.user_id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $active_subs = $db->query("
        SELECT u.full_name, u.phone, p.name as package_name, s.end_date, 
               DATEDIFF(s.end_date, CURDATE()) as days_left 
        FROM subscriptions s 
        JOIN users u ON s.user_id = u.user_id 
        JOIN packages p ON s.package_id = p.package_id 
        WHERE s.status = 'active' AND s.start_date IS NOT NULL 
        ORDER BY days_left ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $adminNotifs = $db->prepare("
        SELECT message FROM notifications 
        WHERE user_id = ? ORDER BY sent_at DESC LIMIT 2
    ");
    $adminNotifs->execute([$_SESSION['user_id']]);
    $admin_notifications = $adminNotifs->fetchAll(PDO::FETCH_ASSOC);

    // FIX #11: Include dashboard view AFTER all variables are ready
    include '../views/admin/dashboard.php';
?>
    <div class="mt-8">

        <?php if (count($admin_notifications) > 0): ?>
            <div class="bg-gray-800 text-white rounded-xl shadow-md p-5 mb-8 border-l-4 border-red-500 relative">
                <div class="absolute top-0 right-0 bg-red-600 text-white font-bold px-3 py-1 text-xs rounded-bl-lg">LIVE ALERTS</div>
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-bold text-lg"><i class="fa fa-bell text-red-400 animate-pulse mr-2"></i> Recent Updates</h3>
                    <button onclick="toggleNotifModal()" class="text-xs bg-gray-700 hover:bg-gray-600 px-3 py-1.5 rounded text-gray-200 transition font-bold"><i class="fa fa-list mr-1"></i> View All</button>
                </div>
                <ul class="space-y-2 text-sm text-gray-300">
                    <?php foreach ($admin_notifications as $n): ?>
                        <li class="bg-gray-700 p-2.5 rounded border border-gray-600">
                            <i class="fa fa-angle-right mr-2 text-red-400"></i> <?php echo htmlspecialchars($n['message']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-5 rounded-xl border-l-4 border-green-500 shadow-sm">
                <h4 class="text-gray-500 text-xs font-bold uppercase">Total Income (Received)</h4>
                <p class="text-2xl font-bold text-green-600">৳<?php echo number_format($totalIncome); ?></p>
            </div>
            <div class="bg-white p-5 rounded-xl border-l-4 border-red-500 shadow-sm">
                <h4 class="text-gray-500 text-xs font-bold uppercase">Total Expense</h4>
                <p class="text-2xl font-bold text-red-600">৳<?php echo number_format($totalExpense); ?></p>
            </div>
            <div class="bg-white p-5 rounded-xl border-l-4 <?php echo ($netProfit >= 0) ? 'border-blue-500' : 'border-pink-600'; ?> shadow-sm">
                <h4 class="text-gray-500 text-xs font-bold uppercase">Net Profit / Loss</h4>
                <p class="text-2xl font-bold <?php echo ($netProfit >= 0) ? 'text-blue-600' : 'text-pink-600'; ?>">
                    ৳<?php echo number_format($netProfit); ?>
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
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

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'tech_assigned'): ?>
            <p class='text-green-600 mb-4 font-bold p-3 bg-green-50 border border-green-200 rounded'>
                <i class='fa fa-check-circle mr-2'></i> Technician successfully assigned!
            </p>
        <?php endif; ?>

        <?php if (count($new_requests) > 0): ?>
            <div class="bg-white rounded-xl shadow-sm border border-yellow-300 p-6 border-t-4 border-t-yellow-500 mb-8 relative overflow-hidden">
                <div class="absolute top-0 right-0 bg-yellow-500 text-white font-bold px-4 py-1 rounded-bl-lg text-xs animate-pulse">PRIORITY ACTION REQUIRED</div>
                <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2"><i class="fa fa-bolt text-yellow-500 mr-2"></i> New Internet Connection Requests</h3>

                <div class="overflow-x-auto max-h-[350px] overflow-y-auto rounded-lg border border-gray-100">
                    <table class="min-w-full text-left text-sm relative">
                        <thead class="bg-yellow-100 sticky top-0 z-10 shadow-sm">
                            <tr>
                                <th class="py-3 px-4">Customer Info</th>
                                <th class="py-3 px-4">Address</th>
                                <th class="py-3 px-4">Package Ordered</th>
                                <th class="py-3 px-4 text-center">Assign Tech</th>
                                <th class="py-3 px-4 text-center">Activate Line</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($new_requests as $req): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4 font-bold text-gray-700">
                                        <?php echo htmlspecialchars($req['full_name']); ?><br>
                                        <span class="text-xs text-gray-500"><i class="fa fa-phone"></i> <?php echo htmlspecialchars($req['phone']); ?></span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600"><?php echo htmlspecialchars($req['address']); ?></td>
                                    <td class="py-3 px-4 uppercase text-blue-600 font-bold"><?php echo htmlspecialchars($req['package_name']); ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <form action="admin.php?action=assign_tech&uid=<?php echo $req['user_id']; ?>&from=dashboard" method="POST" class="flex items-center space-x-1 justify-center">
                                            <select name="staff_id" required class="border border-gray-300 rounded px-2 py-1.5 text-xs outline-none focus:ring-1 focus:ring-purple-500 bg-gray-50 w-40">
                                                <option value="">-- Select Tech --</option>
                                                <?php foreach ($activeStaff as $staff): ?>
                                                    <option value="<?php echo $staff['user_id']; ?>">
                                                        <?php echo htmlspecialchars($staff['full_name']); ?> (<?php echo !empty($staff['designation']) ? htmlspecialchars($staff['designation']) : 'No Designation'; ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded shadow text-xs font-bold transition"><i class="fa fa-paper-plane"></i></button>
                                        </form>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <a href="admin.php?action=activate_user&id=<?php echo $req['user_id']; ?>&from=dashboard" onclick="return confirm('Activate connection for this customer?');" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded text-xs font-bold shadow transition"><i class="fa fa-plug mr-1"></i> Activate Now</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 border-t-4 border-red-500">
            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fa fa-stopwatch text-red-500 mr-2"></i> Live Expiry Tracker (Active Connections)</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-2 px-4">Customer</th>
                            <th class="py-2 px-4">Package</th>
                            <th class="py-2 px-4">Expiry Date</th>
                            <th class="py-2 px-4">Days Left</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($active_subs as $sub): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2 px-4 font-bold text-gray-700">
                                    <?php echo htmlspecialchars($sub['full_name']); ?><br>
                                    <span class="text-xs text-gray-400"><?php echo htmlspecialchars($sub['phone']); ?></span>
                                </td>
                                <td class="py-2 px-4 uppercase text-gray-600 font-semibold"><?php echo htmlspecialchars($sub['package_name']); ?></td>
                                <td class="py-2 px-4 text-gray-600"><?php echo date("d M Y", strtotime($sub['end_date'])); ?></td>
                                <td class="py-2 px-4">
                                    <?php if ($sub['days_left'] < 0): ?>
                                        <span class="bg-gray-200 text-gray-700 font-bold px-3 py-1 rounded-full text-xs">EXPIRED</span>
                                    <?php elseif ($sub['days_left'] <= 3): ?>
                                        <span class="bg-red-100 text-red-700 font-bold px-3 py-1 rounded-full text-xs animate-pulse"><?php echo $sub['days_left']; ?> Days</span>
                                    <?php elseif ($sub['days_left'] <= 7): ?>
                                        <span class="bg-orange-100 text-orange-700 font-bold px-3 py-1 rounded-full text-xs"><?php echo $sub['days_left']; ?> Days</span>
                                    <?php else: ?>
                                        <span class="bg-green-100 text-green-700 font-bold px-3 py-1 rounded-full text-xs"><?php echo $sub['days_left']; ?> Days</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($active_subs)) echo "<tr><td colspan='4' class='text-center py-4 text-gray-400'>No active connections found.</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php
}

// ==========================================
// 🔥 COVERAGE ZONES
// ==========================================
elseif ($page == 'coverage_admin') {
    $zones = $db->query("SELECT * FROM coverage_zones ORDER BY district ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
    <div class="mt-6">
        <div class="flex justify-between items-center bg-white p-5 rounded-xl shadow-sm border border-gray-200 mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-800"><i class="fa fa-map-marked-alt mr-2"></i> Coverage Zones</h2>
                <p class="text-sm text-gray-500">Manage all your active and upcoming network areas</p>
            </div>
            <button onclick="openAddModal()" class="bg-gray-900 hover:bg-black text-white font-bold py-2 px-5 rounded-lg shadow transition flex items-center">
                <i class="fa fa-plus-circle mr-2"></i> Add New Zone
            </button>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="py-4 px-5">District</th>
                            <th class="py-4 px-5">Area / Upazila</th>
                            <th class="py-4 px-5 w-1/3">Description</th>
                            <th class="py-4 px-5 text-center">Status</th>
                            <th class="py-4 px-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($zones as $z): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-4 px-5 font-bold text-gray-800"><?php echo htmlspecialchars($z['district']); ?></td>
                                <td class="py-4 px-5 font-semibold text-gray-700"><?php echo htmlspecialchars($z['upazila']); ?></td>
                                <td class="py-4 px-5 text-gray-500 text-xs"><?php echo htmlspecialchars($z['description'] ?: 'N/A'); ?></td>
                                <td class="py-4 px-5 text-center">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-extrabold tracking-wider <?php echo $z['status'] == 'active' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700'; ?>">
                                        <?php echo strtoupper($z['status']); ?>
                                    </span>
                                </td>
                                <td class="py-4 px-5 text-right space-x-3">
                                    <button onclick="openEditModal(this)"
                                        data-id="<?php echo $z['id']; ?>"
                                        data-district="<?php echo htmlspecialchars($z['district']); ?>"
                                        data-upazila="<?php echo htmlspecialchars($z['upazila']); ?>"
                                        data-status="<?php echo $z['status']; ?>"
                                        data-desc="<?php echo htmlspecialchars($z['description']); ?>"
                                        class="text-blue-500 hover:text-blue-700 transition" title="Edit">
                                        <i class="fa fa-edit text-lg"></i>
                                    </button>
                                    <a href="admin.php?action=delete_zone&id=<?php echo $z['id']; ?>" onclick="return confirm('Are you sure you want to delete this zone?')" class="text-red-500 hover:text-red-700 transition" title="Delete">
                                        <i class="fa fa-trash-alt text-lg"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (count($zones) == 0): ?>
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-500">No coverage zones found. Click "Add New Zone" to create one.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Zone Modal -->
    <div id="addModal" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-60 flex items-center justify-center backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="flex justify-between items-center bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-800"><i class="fa fa-plus-circle mr-2"></i> Add New Zone</h3>
                <button onclick="closeAddModal()" class="text-gray-400 hover:text-red-500 transition"><i class="fa fa-times text-xl"></i></button>
            </div>
            <form action="admin.php?action=add_zone" method="POST" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">District Name <span class="text-red-500">*</span></label>
                        <input type="text" name="district" placeholder="e.g. Dhaka" required class="w-full border border-gray-300 px-4 py-2 rounded-lg outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Area / Upazila <span class="text-red-500">*</span></label>
                        <input type="text" name="upazila" placeholder="e.g. Dhanmondi" required class="w-full border border-gray-300 px-4 py-2 rounded-lg outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-300 px-4 py-2 rounded-lg outline-none bg-gray-50">
                        <option value="active">Active Now</option>
                        <option value="upcoming">Coming Soon</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Description</label>
                    <textarea name="description" rows="3" placeholder="Coverage details..." class="w-full border border-gray-300 px-4 py-2 rounded-lg outline-none"></textarea>
                </div>
                <div class="pt-4 flex justify-end space-x-3">
                    <button type="button" onclick="closeAddModal()" class="px-5 py-2 text-gray-700 bg-white border border-gray-300 hover:bg-gray-100 rounded-lg font-bold transition">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-gray-900 hover:bg-black text-white rounded-lg font-bold shadow-md transition">Save Zone</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Zone Modal -->
    <div id="editModal" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-60 flex items-center justify-center backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="flex justify-between items-center bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-800"><i class="fa fa-edit text-blue-500 mr-2"></i> Edit Zone</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-red-500 transition"><i class="fa fa-times text-xl"></i></button>
            </div>
            <form action="admin.php?action=edit_zone" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="id" id="edit_id">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">District Name <span class="text-red-500">*</span></label>
                        <input type="text" name="district" id="edit_district" required class="w-full border border-gray-300 px-4 py-2 rounded-lg outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Area / Upazila <span class="text-red-500">*</span></label>
                        <input type="text" name="upazila" id="edit_upazila" required class="w-full border border-gray-300 px-4 py-2 rounded-lg outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Status</label>
                    <select name="status" id="edit_status" class="w-full border border-gray-300 px-4 py-2 rounded-lg outline-none bg-gray-50">
                        <option value="active">Active Now</option>
                        <option value="upcoming">Coming Soon</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Description</label>
                    <textarea name="description" id="edit_description" rows="3" class="w-full border border-gray-300 px-4 py-2 rounded-lg outline-none"></textarea>
                </div>
                <div class="pt-4 flex justify-end space-x-3">
                    <button type="button" onclick="closeEditModal()" class="px-5 py-2 text-gray-700 bg-white border border-gray-300 hover:bg-gray-100 rounded-lg font-bold transition">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold shadow-md transition">Update Zone</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal()  { document.getElementById('addModal').classList.remove('hidden'); }
        function closeAddModal() { document.getElementById('addModal').classList.add('hidden'); }
        function openEditModal(button) {
            document.getElementById('edit_id').value          = button.getAttribute('data-id');
            document.getElementById('edit_district').value    = button.getAttribute('data-district');
            document.getElementById('edit_upazila').value     = button.getAttribute('data-upazila');
            document.getElementById('edit_status').value      = button.getAttribute('data-status');
            document.getElementById('edit_description').value = button.getAttribute('data-desc');
            document.getElementById('editModal').classList.remove('hidden');
        }
        function closeEditModal() { document.getElementById('editModal').classList.add('hidden'); }
    </script>
<?php
}

// ==========================================
// 🔥 MANAGE STAFF
// ==========================================
elseif ($page == 'staff') {
    $staffList    = $db->query("SELECT * FROM users WHERE role = 'staff' ORDER BY user_id DESC")->fetchAll(PDO::FETCH_ASSOC);
    $designations = $db->query("SELECT title FROM designations ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 h-fit">
            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fa fa-user-plus text-purple-500 mr-2"></i> Add New Staff</h3>
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'email_exists'): ?>
                <p class="text-red-500 text-sm mb-3 font-bold">Email already exists!</p>
            <?php endif; ?>
            <form action="admin.php?action=add_staff" method="POST" class="space-y-4">
                <div><label class="block text-xs font-bold text-gray-600 mb-1">Full Name</label><input type="text" name="full_name" required class="w-full border px-3 py-2 rounded outline-none focus:border-purple-500"></div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Designation</label>
                    <select name="designation" required class="w-full border px-3 py-2 rounded outline-none focus:border-purple-500 bg-gray-50">
                        <option value="">-- Select Designation --</option>
                        <?php foreach ($designations as $desig): ?>
                            <option value="<?php echo htmlspecialchars($desig['title']); ?>"><?php echo htmlspecialchars($desig['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div><label class="block text-xs font-bold text-gray-600 mb-1">Working Area</label><input type="text" name="address" required class="w-full border px-3 py-2 rounded outline-none focus:border-purple-500"></div>
                <div><label class="block text-xs font-bold text-gray-600 mb-1">Email</label><input type="email" name="email" required class="w-full border px-3 py-2 rounded outline-none focus:border-purple-500"></div>
                <div><label class="block text-xs font-bold text-gray-600 mb-1">Phone</label><input type="text" name="phone" required class="w-full border px-3 py-2 rounded outline-none focus:border-purple-500"></div>
                <div><label class="block text-xs font-bold text-gray-600 mb-1">Password</label><input type="password" name="password" required class="w-full border px-3 py-2 rounded outline-none focus:border-purple-500"></div>
                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 rounded shadow transition">Create Account</button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fa fa-user-tie text-gray-500 mr-2"></i> Staff Directory</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-3 px-4">Staff Details</th>
                            <th class="py-3 px-4">Contact</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staffList as $s): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3 px-4">
                                    <p class="text-gray-800">
                                        <strong class="text-base"><?php echo htmlspecialchars($s['full_name']); ?></strong>
                                        <span class="text-gray-500 text-xs font-semibold">(<?php echo htmlspecialchars($s['designation']); ?>)</span>
                                    </p>
                                    <p class="text-xs text-blue-600 font-bold mt-0.5 italic"><i class="fa fa-map-marker-alt mr-1"></i><?php echo htmlspecialchars($s['address']); ?></p>
                                </td>
                                <td class="py-3 px-4 text-xs text-gray-600">
                                    <i class="fa fa-envelope w-4"></i> <?php echo htmlspecialchars($s['email']); ?><br>
                                    <i class="fa fa-phone w-4 mt-1"></i> <?php echo htmlspecialchars($s['phone']); ?>
                                </td>
                                <td class="py-3 px-4">
                                    <?php if ($s['status'] == 'active'): ?>
                                        <span class="bg-green-100 text-green-700 font-bold px-2 py-1 rounded text-[10px] uppercase">Active</span>
                                    <?php else: ?>
                                        <span class="bg-red-100 text-red-700 font-bold px-2 py-1 rounded text-[10px] uppercase">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-center space-x-1">
                                    <?php if ($s['status'] == 'active'): ?>
                                        <a href="admin.php?action=toggle_staff&id=<?php echo $s['user_id']; ?>&status=suspended" class="text-orange-500 hover:text-orange-700" title="Suspend"><i class="fa fa-ban text-lg"></i></a>
                                    <?php else: ?>
                                        <a href="admin.php?action=toggle_staff&id=<?php echo $s['user_id']; ?>&status=active" class="text-green-500 hover:text-green-700" title="Activate"><i class="fa fa-check-circle text-lg"></i></a>
                                    <?php endif; ?>
                                    <a href="admin.php?action=delete_staff&id=<?php echo $s['user_id']; ?>" onclick="return confirm('Are you sure you want to DELETE this staff? Their open tickets will be unassigned.');" class="text-red-500 hover:text-red-700 ml-2" title="Delete"><i class="fa fa-trash text-lg"></i></a>
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
// 🔥 EXPENSES
// ==========================================
elseif ($page == 'expenses') {
    $expenses = $db->query("SELECT * FROM expenses ORDER BY expense_date DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 h-fit">
            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fa fa-minus-circle text-red-500 mr-2"></i> Add Daily Expense</h3>
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'invalid_amount'): ?>
                <p class="text-red-500 text-sm mb-3 font-bold">Invalid amount. Must be greater than 0.</p>
            <?php endif; ?>
            <form action="admin.php?action=add_expense" method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Category</label>
                    <select name="category" required class="w-full border px-3 py-2 rounded outline-none focus:border-red-500 bg-gray-50">
                        <option value="Bandwidth Bill">Bandwidth Bill</option>
                        <option value="Staff Salary">Staff Salary</option>
                        <option value="Electricity Bill">Electricity Bill</option>
                        <option value="Office Rent">Office Rent</option>
                        <option value="Fiber Maintenance">Fiber Maintenance</option>
                        <option value="Others">Others</option>
                    </select>
                </div>
                <div><label class="block text-xs font-bold text-gray-600 mb-1">Amount (৳)</label><input type="number" name="amount" min="1" required class="w-full border px-3 py-2 rounded outline-none"></div>
                <div><label class="block text-xs font-bold text-gray-600 mb-1">Date</label><input type="date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full border px-3 py-2 rounded outline-none"></div>
                <div><label class="block text-xs font-bold text-gray-600 mb-1">Short Note</label><textarea name="description" class="w-full border px-3 py-2 rounded outline-none"></textarea></div>
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 rounded shadow transition">Save Expense</button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fa fa-list text-gray-500 mr-2"></i> Expense List</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4">Note</th>
                            <th class="py-3 px-4 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expenses as $ex): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3 px-4"><?php echo date('d M Y', strtotime($ex['expense_date'])); ?></td>
                                <td class="py-3 px-4 font-bold text-red-600"><?php echo htmlspecialchars($ex['category']); ?></td>
                                <td class="py-3 px-4 text-gray-500"><?php echo htmlspecialchars($ex['description']); ?></td>
                                <td class="py-3 px-4 text-right font-bold">৳<?php echo number_format($ex['amount']); ?></td>
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
elseif ($page == 'packages') {
    $packages = $packageModel->getAll();
    include '../views/admin/packages.php';
} elseif ($page == 'create_package') {
    include '../views/admin/create_package.php';
} elseif ($page == 'edit_package' && isset($_GET['id'])) {
    $packageData = $packageModel->getById($_GET['id']);
    include '../views/admin/edit_package.php';
}

// ==========================================
// 🔥 MANAGE CUSTOMERS
// ==========================================
elseif ($page == 'users') {
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

    $where = "WHERE u.role = 'customer'";
    if ($filter == 'new') {
        $where .= " AND (u.status = 'pending' OR s.start_date IS NULL OR s.status = 'pending')";
    } elseif ($filter == 'active') {
        $where .= " AND u.status = 'active' AND s.start_date IS NOT NULL";
    } elseif ($filter == 'suspended') {
        $where .= " AND u.status = 'suspended'";
    }

    $usersQuery = "
        SELECT u.*, 
        (SELECT status FROM subscriptions WHERE user_id = u.user_id ORDER BY subscription_id DESC LIMIT 1) as sub_status,
        (SELECT start_date FROM subscriptions WHERE user_id = u.user_id ORDER BY subscription_id DESC LIMIT 1) as start_date
        FROM users u 
        LEFT JOIN subscriptions s ON u.user_id = s.user_id
        $where 
        GROUP BY u.user_id
        ORDER BY u.user_id DESC
    ";

    $users       = $db->query($usersQuery)->fetchAll(PDO::FETCH_ASSOC);
    $activeStaff = $db->query("SELECT user_id, full_name, designation FROM users WHERE role = 'staff' AND status = 'active'")->fetchAll(PDO::FETCH_ASSOC);
?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
        <div class="flex justify-between items-center mb-4 border-b pb-4">
            <h2 class="text-2xl font-bold text-gray-800"><i class="fa fa-users text-blue-500 mr-2"></i> Manage Customers</h2>
        </div>

        <div class="flex space-x-2 mb-6 overflow-x-auto">
            <a href="admin.php?page=users&filter=all"       class="px-4 py-2 rounded font-bold text-sm transition <?php echo ($filter == 'all')       ? 'bg-blue-600 text-white shadow'   : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">All Customers</a>
            <a href="admin.php?page=users&filter=new"       class="px-4 py-2 rounded font-bold text-sm transition <?php echo ($filter == 'new')       ? 'bg-yellow-500 text-white shadow' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">New Requests</a>
            <a href="admin.php?page=users&filter=active"    class="px-4 py-2 rounded font-bold text-sm transition <?php echo ($filter == 'active')    ? 'bg-green-600 text-white shadow'  : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">Active</a>
            <a href="admin.php?page=users&filter=suspended" class="px-4 py-2 rounded font-bold text-sm transition <?php echo ($filter == 'suspended') ? 'bg-red-600 text-white shadow'    : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">Suspended</a>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'tech_assigned'): ?>
            <p class="text-green-600 mb-4 font-bold p-3 bg-green-50 border border-green-200 rounded">
                <i class="fa fa-check-circle mr-2"></i> Technician successfully assigned for line setup!
            </p>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white text-left text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4">ID</th>
                        <th class="py-3 px-4">Customer Info</th>
                        <th class="py-3 px-4">Address</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Assign Setup Tech</th>
                        <th class="py-3 px-4 text-center">Line Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u):
                        $is_new = ($u['start_date'] === NULL || $u['sub_status'] == 'pending' || $u['status'] == 'pending');
                    ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-4 font-bold text-gray-500">#<?php echo $u['user_id']; ?></td>
                            <td class="py-2 px-4">
                                <p class="font-bold text-gray-800"><?php echo htmlspecialchars($u['full_name']); ?></p>
                                <p class="text-xs text-gray-500"><i class="fa fa-phone text-gray-400"></i> <?php echo htmlspecialchars($u['phone']); ?></p>
                            </td>
                            <td class="py-2 px-4 text-gray-600 truncate max-w-xs"><?php echo htmlspecialchars($u['address']); ?></td>
                            <td class="py-2 px-4">
                                <?php if ($u['status'] == 'suspended'): ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700 font-bold border border-red-200"><i class="fa fa-ban"></i> SUSPENDED</span>
                                <?php elseif ($is_new): ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700 font-bold border border-yellow-300"><i class="fa fa-clock"></i> NEW / PENDING</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700 font-bold border border-green-200"><i class="fa fa-check-circle"></i> ACTIVE</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-2 px-4 text-center">
                                <form action="admin.php?action=assign_tech&uid=<?php echo $u['user_id']; ?>" method="POST" class="flex items-center space-x-1 justify-center">
                                    <select name="staff_id" required class="border border-gray-300 rounded px-2 py-1.5 text-xs outline-none focus:ring-1 focus:ring-purple-500 bg-gray-50 w-40">
                                        <option value="">-- Tech --</option>
                                        <?php foreach ($activeStaff as $staff): ?>
                                            <option value="<?php echo $staff['user_id']; ?>">
                                                <?php echo htmlspecialchars($staff['full_name']); ?> (<?php echo !empty($staff['designation']) ? htmlspecialchars($staff['designation']) : 'No Designation'; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-2 py-1.5 rounded shadow text-xs font-bold transition" title="Send to Technician"><i class="fa fa-paper-plane"></i></button>
                                </form>
                            </td>
                            <td class="py-2 px-4 text-center space-x-2">
                                <?php if ($u['status'] != 'active' || $is_new): ?>
                                    <a href="admin.php?action=activate_user&id=<?php echo $u['user_id']; ?>" onclick="return confirm('ACTIVATE this connection?');" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded text-xs font-bold shadow transition">Activate</a>
                                <?php else: ?>
                                    <a href="admin.php?action=suspend_user&id=<?php echo $u['user_id']; ?>" onclick="return confirm('SUSPEND this connection?');" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded text-xs font-bold shadow transition">Suspend</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)) echo "<tr><td colspan='6' class='text-center py-6 text-gray-500'>No customers found for this filter.</td></tr>"; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php
}

// ==========================================
// 🔥 BILLINGS & INVOICES
// ==========================================
elseif ($page == 'billings') {
    $invoices = $db->query("
        SELECT i.*, u.full_name 
        FROM invoices i 
        JOIN users u ON i.user_id = u.user_id 
        ORDER BY i.created_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h2 class="text-2xl font-bold text-gray-800"><i class="fa fa-file-invoice-dollar text-green-500 mr-2"></i> Billings & Invoices</h2>
            <a href="admin.php?action=generate_monthly_bills" onclick="return confirm('Generate new invoices for all active customers?');" class="bg-gray-800 text-white px-5 py-2 rounded-lg shadow hover:bg-black font-bold transition">
                <i class="fa fa-magic mr-2"></i> Generate Monthly Bills
            </a>
        </div>
        <table class="min-w-full bg-white text-left text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-4">Inv No.</th>
                    <th class="py-3 px-4">Customer</th>
                    <th class="py-3 px-4">Amount</th>
                    <th class="py-3 px-4">Due Date</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4 text-center">Payment Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $inv): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-2 px-4 font-bold text-gray-600"><?php echo htmlspecialchars($inv['invoice_number']); ?></td>
                        <td class="py-2 px-4 font-semibold"><?php echo htmlspecialchars($inv['full_name']); ?></td>
                        <td class="py-2 px-4 font-bold text-red-600">৳<?php echo number_format($inv['amount']); ?></td>
                        <td class="py-2 px-4"><?php echo date("d M Y", strtotime($inv['due_date'])); ?></td>
                        <td class="py-2 px-4">
                            <span class="px-2 py-1 text-xs rounded-full font-bold border <?php echo ($inv['status'] == 'paid') ? 'bg-green-50 text-green-700 border-green-200' : 'bg-orange-50 text-orange-700 border-orange-200'; ?>">
                                <?php echo strtoupper($inv['status']); ?>
                            </span>
                        </td>
                        <td class="py-2 px-4 text-center">
                            <?php if ($inv['status'] == 'unpaid' || $inv['status'] == 'pending'): ?>
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
    $tickets = $db->query("
        SELECT t.*, u.full_name, s.full_name as assigned_name 
        FROM tickets t 
        JOIN users u ON t.user_id = u.user_id 
        LEFT JOIN users s ON t.assigned_to = s.user_id 
        ORDER BY t.created_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-4"><i class="fa fa-headset text-red-500 mr-2"></i> Support Tickets</h2>
        <table class="min-w-full bg-white text-left text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-4">TKT ID</th>
                    <th class="py-3 px-4">Customer</th>
                    <th class="py-3 px-4">Subject</th>
                    <th class="py-3 px-4">Assigned To</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4 text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $t): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-2 px-4 font-bold">#<?php echo $t['ticket_id']; ?></td>
                        <td class="py-2 px-4"><?php echo htmlspecialchars($t['full_name']); ?></td>
                        <td class="py-2 px-4 font-semibold text-gray-700"><?php echo htmlspecialchars($t['subject']); ?></td>
                        <td class="py-2 px-4">
                            <?php echo $t['assigned_name']
                                ? '<span class="text-blue-600 font-bold">' . htmlspecialchars($t['assigned_name']) . '</span>'
                                : '<span class="text-red-400 italic">Unassigned</span>'; ?>
                        </td>
                        <td class="py-2 px-4">
                            <span class="px-2 py-1 text-xs rounded-full font-bold border <?php echo ($t['status'] == 'open') ? 'bg-red-50 text-red-600' : (($t['status'] == 'processing') ? 'bg-blue-50 text-blue-600' : 'bg-green-50 text-green-600'); ?>">
                                <?php echo strtoupper($t['status']); ?>
                            </span>
                        </td>
                        <td class="py-2 px-4 text-center space-x-2">
                            <a href="admin.php?page=view_ticket&id=<?php echo $t['ticket_id']; ?>" class="bg-gray-800 hover:bg-black text-white px-3 py-1 rounded text-xs transition shadow"><i class="fa fa-eye"></i> View</a>
                            <?php if ($t['status'] != 'processing'): ?>
                                <a href="admin.php?action=update_ticket&id=<?php echo $t['ticket_id']; ?>&status=processing" class="text-blue-500 hover:text-blue-700"><i class="fa fa-spinner"></i></a>
                            <?php endif; ?>
                            <?php if ($t['status'] != 'resolved'): ?>
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
        $staff_id = !empty($_POST['staff_id']) ? (int)$_POST['staff_id'] : NULL;
        $db->prepare("UPDATE tickets SET assigned_to = ? WHERE ticket_id = ?")
           ->execute([$staff_id, $ticket_id]);
        header("Location: admin.php?page=view_ticket&id=$ticket_id");
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reply_message'])) {
        $msg = trim($_POST['reply_message']);
        if (!empty($msg)) {
            $db->prepare("INSERT INTO ticket_replies (ticket_id, user_id, message) VALUES (?, ?, ?)")
               ->execute([$ticket_id, $_SESSION['user_id'], $msg]);

            // FIX #9: Use prepared statement instead of raw variable in query
            $db->prepare("UPDATE tickets SET status = 'processing' WHERE ticket_id = ? AND status = 'open'")
               ->execute([$ticket_id]);

            header("Location: admin.php?page=view_ticket&id=$ticket_id");
            exit;
        }
    }

    $stmt = $db->prepare("
        SELECT t.*, u.full_name, u.email, s.full_name as staff_name 
        FROM tickets t 
        JOIN users u ON t.user_id = u.user_id 
        LEFT JOIN users s ON t.assigned_to = s.user_id 
        WHERE t.ticket_id = ?
    ");
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) {
        echo "<div class='bg-white p-10 text-center mt-6 rounded-xl border'><h2 class='text-4xl font-bold text-red-500'>404</h2><p class='text-gray-600'>Ticket Not Found</p></div>";
        echo "</main></div></body></html>";
        exit;
    }

    $stmtRep = $db->prepare("
        SELECT r.*, u.full_name, u.role 
        FROM ticket_replies r 
        JOIN users u ON r.user_id = u.user_id 
        WHERE r.ticket_id = ? 
        ORDER BY r.replied_at ASC
    ");
    $stmtRep->execute([$ticket_id]);
    $replies = $stmtRep->fetchAll(PDO::FETCH_ASSOC);

    $staffs = $db->query("SELECT user_id, full_name, designation FROM users WHERE role = 'staff' AND status = 'active'")->fetchAll(PDO::FETCH_ASSOC);
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
                        <?php foreach ($staffs as $s): ?>
                            <option value="<?php echo $s['user_id']; ?>" <?php echo ($ticket['assigned_to'] == $s['user_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['full_name']); ?> (<?php echo !empty($s['designation']) ? htmlspecialchars($s['designation']) : 'Staff'; ?>)
                            </option>
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
            <?php foreach ($replies as $reply): ?>
                <div class="p-4 rounded-xl shadow-sm border <?php echo ($reply['role'] == 'admin' || $reply['role'] == 'staff') ? 'bg-red-50 border-red-200 ml-10' : 'bg-white border-gray-200 mr-10'; ?>">
                    <div class="flex justify-between items-center mb-2">
                        <strong class="<?php echo ($reply['role'] == 'admin' || $reply['role'] == 'staff') ? 'text-red-600' : 'text-blue-600'; ?>">
                            <?php echo htmlspecialchars($reply['full_name']); ?>
                            <?php echo ($reply['role'] == 'admin') ? '(Admin)' : (($reply['role'] == 'staff') ? '(Staff)' : '(Customer)'); ?>
                        </strong>
                        <span class="text-xs text-gray-400"><?php echo date("d M Y, h:i A", strtotime($reply['replied_at'])); ?></span>
                    </div>
                    <p class="text-gray-700 text-sm"><?php echo nl2br(htmlspecialchars($reply['message'])); ?></p>
                </div>
            <?php endforeach; ?>
            <?php if (empty($replies)) echo "<p class='text-gray-500 text-center text-sm py-4'>No replies yet.</p>"; ?>
        </div>

        <?php if ($ticket['status'] != 'resolved'): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-10">
                <form action="admin.php?page=view_ticket&id=<?php echo $ticket_id; ?>" method="POST">
                    <textarea name="reply_message" rows="4" required placeholder="Type your response here..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 outline-none mb-4"></textarea>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded shadow transition">Send Message</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
<?php
} else {
    echo "<div class='bg-white p-10 text-center mt-6 rounded-xl border'><h2 class='text-4xl font-bold text-red-500'>404</h2><p class='text-gray-600'>Page Not Found</p></div>";
}

echo "</main></div></body></html>";
?>