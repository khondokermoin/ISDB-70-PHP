<?php
// 1. Include Database & Model
require_once '../config/database.php';
require_once '../src/Models/Package.php';

$database = new Database();
$db = $database->getConnection();
$packageModel = new Package($db);

// 2. Handle Actions (Create, Update, Delete)
$action = isset($_GET['action']) ? $_GET['action'] : '';

// --- DELETE ACTION ---
if ($action == 'delete_package' && isset($_GET['id'])) {
    if ($packageModel->delete($_GET['id'])) {
        header("Location: admin.php?page=packages&msg=deleted");
        exit;
    }
}

// --- POST REQUESTS (Create & Update) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Quota Logic (Unlimited or Limited)
    $quota_type = isset($_POST['quota_type']) ? $_POST['quota_type'] : 'limited';
    $quota_gb = ($quota_type === 'unlimited') ? NULL : $_POST['quota_gb'];

    // Features Logic (Only for Corporate)
    $features = isset($_POST['features']) ? $_POST['features'] : NULL;
    
    // UPDATE PACKAGE
    if ($action == 'update_package') {
        $id = $_POST['package_id'];
        $query = "UPDATE packages SET name=:name, type=:type, features=:features, speed_mbps=:speed, price=:price, quota_gb=:quota, duration_days=:duration, status=:status WHERE package_id=:id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $_POST['name']);
        $stmt->bindParam(':type', $_POST['type']);
        $stmt->bindParam(':features', $features);
        $stmt->bindParam(':speed', $_POST['speed_mbps']);
        $stmt->bindParam(':price', $_POST['price']);
        $stmt->bindParam(':quota', $quota_gb);
        $stmt->bindParam(':duration', $_POST['duration_days']);
        $stmt->bindParam(':status', $_POST['status']);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            // FIX RELOAD BUG: Redirect with success message
            header("Location: admin.php?page=packages&msg=updated");
            exit; // Must call exit after header
        }
    }

    // CREATE PACKAGE
    if (isset($_GET['page']) && $_GET['page'] == 'create_package') {
        $query = "INSERT INTO packages (name, type, features, speed_mbps, price, quota_gb, duration_days, status) VALUES (:name, :type, :features, :speed, :price, :quota, :duration, :status)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $_POST['name']);
        $stmt->bindParam(':type', $_POST['type']);
        $stmt->bindParam(':features', $features);
        $stmt->bindParam(':speed', $_POST['speed_mbps']);
        $stmt->bindParam(':price', $_POST['price']);
        $stmt->bindParam(':quota', $quota_gb);
        $stmt->bindParam(':duration', $_POST['duration_days']);
        $stmt->bindParam(':status', $_POST['status']);

        if ($stmt->execute()) {
            // FIX RELOAD BUG: Redirect to packages list instead of just showing message
            header("Location: admin.php?page=packages&msg=created");
            exit; // Stop execution to prevent form resubmission
        }
    }
}

// 3. Routing Logic
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Include the Admin Header
include '../views/layouts/admin_header.php';

// Load the requested view
if ($page == 'dashboard') {
    // FIX: Include the dashboard file here instead of echo
    include '../views/admin/dashboard.php';
} 
elseif ($page == 'packages') {
    // Fetch all packages for the list view
    $packages = $packageModel->getAll();
    include '../views/admin/packages.php';
} 
elseif ($page == 'create_package') {
    include '../views/admin/create_package.php';
} 
elseif ($page == 'edit_package' && isset($_GET['id'])) {
    // Fetch specific package data to populate the edit form
    $packageData = $packageModel->getById($_GET['id']);
    include '../views/admin/edit_package.php';
} 
else {
    echo "<h2>404 - Page Not Found</h2>";
}

// Close tags
echo "</main></div></body></html>";
?>