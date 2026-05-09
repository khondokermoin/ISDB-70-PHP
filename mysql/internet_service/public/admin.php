<?php
session_start();

// 🔥 Security Check: Only Admins can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// 1. Include Database & Model
require_once '../config/database.php';
require_once '../src/Models/Package.php';

$database = new Database();
$db = $database->getConnection();
$packageModel = new Package($db);

// 2. Handle Actions (Create, Update, Delete)
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'delete_package' && isset($_GET['id'])) {
    if ($packageModel->delete($_GET['id'])) {
        header("Location: admin.php?page=packages&msg=deleted");
        exit;
    }
}

// --- TICKET STATUS UPDATE ACTION ---
if ($action == 'update_ticket' && isset($_GET['id']) && isset($_GET['status'])) {
    $stmt = $db->prepare("UPDATE tickets SET status = :status WHERE ticket_id = :id");
    $stmt->execute([':status' => $_GET['status'], ':id' => $_GET['id']]);
    header("Location: admin.php?page=tickets");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $quota_type = isset($_POST['quota_type']) ? $_POST['quota_type'] : 'limited';
    $quota_gb = ($quota_type === 'unlimited') ? NULL : $_POST['quota_gb'];
    $features = isset($_POST['features']) ? $_POST['features'] : NULL;

    if ($action == 'update_package') {
        $id = $_POST['package_id'];
        $query = "UPDATE packages SET name=:name, type=:type, features=:features, speed_mbps=:speed, price=:price, quota_gb=:quota, duration_days=:duration, status=:status WHERE package_id=:id";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':name' => $_POST['name'],
            ':type' => $_POST['type'],
            ':features' => $features,
            ':speed' => $_POST['speed_mbps'],
            ':price' => $_POST['price'],
            ':quota' => $quota_gb,
            ':duration' => $_POST['duration_days'],
            ':status' => $_POST['status'],
            ':id' => $id
        ]);
        header("Location: admin.php?page=packages&msg=updated");
        exit;
    }

    if (isset($_GET['page']) && $_GET['page'] == 'create_package') {
        $query = "INSERT INTO packages (name, type, features, speed_mbps, price, quota_gb, duration_days, status) VALUES (:name, :type, :features, :speed, :price, :quota, :duration, :status)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':name' => $_POST['name'],
            ':type' => $_POST['type'],
            ':features' => $features,
            ':speed' => $_POST['speed_mbps'],
            ':price' => $_POST['price'],
            ':quota' => $quota_gb,
            ':duration' => $_POST['duration_days'],
            ':status' => $_POST['status']
        ]);
        header("Location: admin.php?page=packages&msg=created");
        exit;
    }
}

// 3. Routing Logic
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Include the Admin Header
include '../views/layouts/admin_header.php';

// Load the requested view
if ($page == 'dashboard') {
    include '../views/admin/dashboard.php';
} elseif ($page == 'packages') {
    $packages = $packageModel->getAll();
    include '../views/admin/packages.php';
} elseif ($page == 'create_package') {
    include '../views/admin/create_package.php';
} elseif ($page == 'edit_package' && isset($_GET['id'])) {
    $packageData = $packageModel->getById($_GET['id']);
    include '../views/admin/edit_package.php';
}

// ==========================================
// 🔥 NEW MODULES: Users, Billings, Tickets
// ==========================================

elseif ($page == 'users') {
    // Fetch Users from Database
    $users = $db->query("SELECT * FROM users ORDER BY user_id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-4"><i class="fa fa-users text-blue-500 mr-2"></i> Manage Users</h2>
        <table class="min-w-full bg-white text-left">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-4">ID</th>
                    <th class="py-3 px-4">Name</th>
                    <th class="py-3 px-4">Phone</th>
                    <th class="py-3 px-4">Role</th>
                    <th class="py-3 px-4">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-2 px-4">#<?php echo $u['user_id']; ?></td>
                        <td class="py-2 px-4 font-bold text-gray-700"><?php echo htmlspecialchars($u['full_name']); ?><br><span class="text-xs font-normal text-gray-500"><?php echo htmlspecialchars($u['email']); ?></span></td>
                        <td class="py-2 px-4"><?php echo htmlspecialchars($u['phone']); ?></td>
                        <td class="py-2 px-4"><span class="px-2 py-1 text-xs rounded-full <?php echo ($u['role'] == 'admin') ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700'; ?>"><?php echo strtoupper($u['role']); ?></span></td>
                        <td class="py-2 px-4"><span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700"><?php echo strtoupper($u['status']); ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php
} elseif ($page == 'billings') {
    // Fetch Invoices from Database
    $invoices = $db->query("SELECT i.*, u.full_name FROM invoices i JOIN users u ON i.user_id = u.user_id ORDER BY i.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-4"><i class="fa fa-file-invoice-dollar text-green-500 mr-2"></i> Billings & Invoices</h2>
        <table class="min-w-full bg-white text-left">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-4">Inv No.</th>
                    <th class="py-3 px-4">Customer</th>
                    <th class="py-3 px-4">Amount</th>
                    <th class="py-3 px-4">Due Date</th>
                    <th class="py-3 px-4">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $inv): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-2 px-4 font-bold text-gray-600"><?php echo $inv['invoice_number']; ?></td>
                        <td class="py-2 px-4"><?php echo htmlspecialchars($inv['full_name']); ?></td>
                        <td class="py-2 px-4 font-bold text-red-600">৳<?php echo number_format($inv['amount']); ?></td>
                        <td class="py-2 px-4"><?php echo date("d M Y", strtotime($inv['due_date'])); ?></td>
                        <td class="py-2 px-4">
                            <span class="px-2 py-1 text-xs rounded-full font-bold <?php echo ($inv['status'] == 'paid') ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700'; ?>"><?php echo strtoupper($inv['status']); ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php
} elseif ($page == 'tickets') {
    // Fetch Tickets from Database
    $tickets = $db->query("SELECT t.*, u.full_name FROM tickets t JOIN users u ON t.user_id = u.user_id ORDER BY t.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-4"><i class="fa fa-headset text-red-500 mr-2"></i> Customer Support Tickets</h2>
        <table class="min-w-full bg-white text-left">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-4">TKT ID</th>
                    <th class="py-3 px-4">Customer</th>
                    <th class="py-3 px-4">Subject</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4 text-center">Change Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $t): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-2 px-4 font-bold">#<?php echo $t['ticket_id']; ?></td>
                        <td class="py-2 px-4"><?php echo htmlspecialchars($t['full_name']); ?></td>
                        <td class="py-2 px-4 font-semibold text-gray-700"><?php echo htmlspecialchars($t['subject']); ?><br><span class="text-xs font-normal text-blue-500"><?php echo htmlspecialchars($t['category']); ?></span></td>
                        <td class="py-2 px-4">
                            <?php
                            if ($t['status'] == 'open') echo '<span class="px-2 py-1 text-xs rounded-full font-bold bg-red-100 text-red-600 border border-red-200">OPEN</span>';
                            elseif ($t['status'] == 'processing') echo '<span class="px-2 py-1 text-xs rounded-full font-bold bg-blue-100 text-blue-600 border border-blue-200">PROCESSING</span>';
                            else echo '<span class="px-2 py-1 text-xs rounded-full font-bold bg-green-100 text-green-600 border border-green-200">RESOLVED</span>';
                            ?>
                        </td>
                        <td class="py-2 px-4 text-center space-x-2">
                            <?php if ($t['status'] != 'processing'): ?>
                                <a href="admin.php?action=update_ticket&id=<?php echo $t['ticket_id']; ?>&status=processing" title="Mark as Processing" class="text-blue-500 hover:text-blue-700"><i class="fa fa-spinner"></i></a>
                            <?php endif; ?>

                            <?php if ($t['status'] != 'resolved'): ?>
                                <a href="admin.php?action=update_ticket&id=<?php echo $t['ticket_id']; ?>&status=resolved" title="Mark as Resolved" class="text-green-500 hover:text-green-700"><i class="fa fa-check-circle"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php
} else {
    echo "<div class='bg-white p-10 text-center mt-6 rounded-xl border'><h2 class='text-4xl font-bold text-red-500'>404</h2><p class='text-gray-600'>Page Not Found</p></div>";
}

// Close tags
echo "</main></div></body></html>";
?>