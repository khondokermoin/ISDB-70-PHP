<?php
session_start();
require_once '../config/database.php';

// যদি আগে থেকেই লগইন করা থাকে
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin.php");
    } elseif ($_SESSION['role'] === 'staff') {
        header("Location: staff_dashboard.php");
    } else {
        header("Location: user_dashboard.php");
    }
    exit;
}

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $db = (new Database())->getConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        if ($user['status'] !== 'active') {
            $error = "Your account is currently inactive. Contact support.";
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            // Role অনুযায়ী রিডাইরেক্ট
            if ($user['role'] === 'admin') {
                header("Location: admin.php");
            } elseif ($user['role'] === 'staff') {
                header("Location: staff_dashboard.php");
            } else {
                header("Location: user_dashboard.php");
            }
            exit;
        }
    } else {
        $error = "Invalid email or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Login - Amar IT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md border-t-4 border-red-600">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold text-red-600">AMAR <span class="text-gray-800">IT</span></h1>
            <p class="text-gray-500 mt-2">Sign in to your account</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm"><i class="fa fa-exclamation-circle"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div>
                <label class="block text-gray-700 font-bold mb-1">Email Address</label>
                <input type="email" name="email" required class="w-full border px-4 py-2 rounded-lg focus:ring-2 focus:ring-red-500 outline-none">
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-1">Password</label>
                <input type="password" name="password" required class="w-full border px-4 py-2 rounded-lg focus:ring-2 focus:ring-red-500 outline-none">
            </div>
            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg transition shadow-md">
                Secure Login
            </button>
        </form>
    </div>
</body>

</html>