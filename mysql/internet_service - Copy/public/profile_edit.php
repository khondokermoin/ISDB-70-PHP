<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $query = "UPDATE users SET full_name = :name, phone = :phone, address = :address WHERE user_id = :uid";
    $stmt = $db->prepare($query);
    if ($stmt->execute([':name' => $_POST['full_name'], ':phone' => $_POST['phone'], ':address' => $_POST['address'], ':uid' => $user_id])) {
        $_SESSION['user_name'] = $_POST['full_name'];
        $msg = "Profile updated successfully!";
    }
}

$user = $db->query("SELECT * FROM users WHERE user_id = $user_id")->fetch(PDO::FETCH_ASSOC);
include '../views/layouts/header.php';
?>
<div class="container mx-auto max-w-2xl py-10 px-4">
    <div class="bg-white p-8 rounded-xl shadow-lg border">
        <h2 class="text-2xl font-bold mb-6">Edit Profile</h2>
        <?php if ($msg): ?> <p class="bg-green-100 text-green-700 p-3 rounded mb-4"><?php echo $msg; ?></p> <?php endif; ?>
        <form method="POST" class="space-y-4">
            <div><label class="block mb-1">Full Name</label><input type="text" name="full_name" value="<?php echo $user['full_name']; ?>" class="w-full border p-2 rounded"></div>
            <div><label class="block mb-1">Phone</label><input type="text" name="phone" value="<?php echo $user['phone']; ?>" class="w-full border p-2 rounded"></div>
            <div><label class="block mb-1">Connection Address</label><textarea name="address" class="w-full border p-2 rounded"><?php echo $user['address']; ?></textarea></div>
            <button type="submit" class="bg-amberRed text-white px-6 py-2 rounded font-bold">Update Profile</button>
            <a href="user_dashboard.php" class="text-gray-500 ml-4">Back to Dashboard</a>
        </form>
    </div>
</div>