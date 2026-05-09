<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') { 
    header("Location: login.php"); 
    exit; 
}

require_once '../config/database.php';
$db = (new Database())->getConnection();
$staff_id = $_SESSION['user_id'];

$msg = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = $_POST['password'];

    // ১. চেক করা হচ্ছে এই ইমেইলটি অন্য কোনো ইউজারের আছে কি না
    $checkEmail = $db->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $checkEmail->execute([$email, $staff_id]);
    
    if ($checkEmail->rowCount() > 0) {
        $error = "This email is already in use by another account!";
    } else {
        // ২. পাসওয়ার্ড দিয়েছে নাকি দেয়নি তার উপর ভিত্তি করে আপডেট লজিক
        if (!empty($password)) {
            // যদি নতুন পাসওয়ার্ড দেয়
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET full_name=?, email=?, phone=?, address=?, password_hash=? WHERE user_id=?");
            $stmt->execute([$name, $email, $phone, $address, $hashed_password, $staff_id]);
        } else {
            // যদি পাসওয়ার্ড ফাঁকা রাখে (শুধু অন্যান্য তথ্য আপডেট হবে)
            $stmt = $db->prepare("UPDATE users SET full_name=?, email=?, phone=?, address=? WHERE user_id=?");
            $stmt->execute([$name, $email, $phone, $address, $staff_id]);
        }
        
        $_SESSION['user_name'] = $name; // সেশনের নাম আপডেট করা
        $msg = "Profile updated successfully!";
    }
}

// বর্তমান ডাটা তুলে আনা (যাতে ফর্মে আগে থেকে ফিলাপ থাকে)
$staff = $db->prepare("SELECT * FROM users WHERE user_id=?");
$staff->execute([$staff_id]);
$staff = $staff->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Staff Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans py-10">
    <div class="container mx-auto max-w-xl px-4">
        <div class="bg-white p-8 rounded-xl shadow-lg border-t-4 border-red-500">
            <div class="flex justify-between items-center border-b pb-4 mb-6">
                <h2 class="text-2xl font-bold text-gray-800"><i class="fa fa-user-edit mr-2 text-red-500"></i> Edit My Profile</h2>
                <a href="staff_dashboard.php" class="text-blue-600 hover:underline text-sm font-bold"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
            
            <?php if($msg): ?> 
                <p class="bg-green-100 text-green-700 p-3 rounded mb-4 font-bold border border-green-200"><i class="fa fa-check-circle"></i> <?php echo $msg; ?></p> 
            <?php endif; ?>
            <?php if($error): ?> 
                <p class="bg-red-100 text-red-700 p-3 rounded mb-4 font-bold border border-red-200"><i class="fa fa-exclamation-circle"></i> <?php echo $error; ?></p> 
            <?php endif; ?>
            
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block font-bold text-gray-600 mb-1">Full Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($staff['full_name']); ?>" required class="w-full border px-4 py-2 rounded focus:ring-2 focus:ring-red-500 outline-none">
                </div>
                <div>
                    <label class="block font-bold text-gray-600 mb-1">Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($staff['email']); ?>" required class="w-full border px-4 py-2 rounded focus:ring-2 focus:ring-red-500 outline-none bg-yellow-50">
                </div>
                <div>
                    <label class="block font-bold text-gray-600 mb-1">Phone Number</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($staff['phone']); ?>" required class="w-full border px-4 py-2 rounded focus:ring-2 focus:ring-red-500 outline-none">
                </div>
                <div>
                    <label class="block font-bold text-gray-600 mb-1">Designation / Short Address</label>
                    <textarea name="address" class="w-full border px-4 py-2 rounded focus:ring-2 focus:ring-red-500 outline-none"><?php echo htmlspecialchars($staff['address']); ?></textarea>
                </div>
                <div class="p-4 bg-gray-50 border rounded-lg mt-4">
                    <label class="block font-bold text-gray-800 mb-1"><i class="fa fa-lock text-gray-400 mr-1"></i> Change Password</label>
                    <p class="text-xs text-gray-500 mb-2">Leave blank if you don't want to change your current password.</p>
                    <input type="password" name="password" placeholder="Enter new password..." class="w-full border px-4 py-2 rounded focus:ring-2 focus:ring-red-500 outline-none">
                </div>
                
                <button type="submit" class="w-full bg-red-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-red-700 shadow-md transition mt-4"><i class="fa fa-save mr-2"></i> Update Profile</button>
            </form>
        </div>
    </div>
</body>
</html>