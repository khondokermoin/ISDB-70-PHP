<?php
session_start();
require_once '../config/db.php';

// যদি আগে থেকেই লগইন করা থাকে, তাহলে সরাসরি ড্যাশবোর্ডে পাঠিয়ে দিবে
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

// সিকিউরিটি: বেশিবার ভুল পাসওয়ার্ড দিলে রেট লিমিট করা
if (!isset($_SESSION['login_attempt'])) {
    $_SESSION['login_attempt'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($_SESSION['login_attempt'] >= 5) {
        $error = "অনেকবার ভুল চেষ্টা করা হয়েছে। কিছুক্ষণ পরে আবার চেষ্টা করুন।";
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $error = "ইউজারনেম এবং পাসওয়ার্ড পূরণ করতে হবে।";
        } else {
            // ডাটাবেজ থেকে ইউজার চেক করা
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            // পাসওয়ার্ড ভেরিফাই করা
            if ($admin && password_verify($password, $admin['password'])) {

                // সফল হলে লগইন এটেম্পট জিরো করে দেওয়া
                $_SESSION['login_attempt'] = 0;

                // সেশন হাইজ্যাকিং রোধে নতুন সেশন আইডি তৈরি
                session_regenerate_id(true);

                // সেশন ডাটা সেট করা
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username']  = $admin['username'];
                $_SESSION['admin_id']        = $admin['id'];

                // ড্যাশবোর্ডে রিডাইরেক্ট
                header("Location: dashboard.php");
                exit;
            } else {
                $_SESSION['login_attempt']++;
                $error = "ইউজারনেম অথবা পাসওয়ার্ড সঠিক নয়।";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Watch Inventory</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 h-screen flex items-center justify-center font-sans">

    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md border-t-4 border-gray-800">

        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Admin Login</h2>
            <p class="text-gray-500 text-sm">Watch Inventory Management</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-5 text-sm rounded">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-1">Username</label>
                <input type="text" name="username" required autocomplete="username"
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-800"
                    placeholder="e.g. admin">
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-1">Password</label>
                <input type="password" name="password" required autocomplete="current-password"
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-800"
                    placeholder="••••••••">
            </div>

            <button type="submit" class="w-full bg-gray-800 hover:bg-gray-900 text-white font-bold py-2.5 px-4 rounded-md transition duration-300 mt-2">
                লগইন করুন
            </button>
        </form>

        <p class="text-center text-xs text-gray-400 mt-6">
            <a href="../index.php" class="hover:underline hover:text-gray-600 transition">
                ← মেইন সাইটে ফিরুন
            </a>
        </p>

    </div>

</body>

</html>