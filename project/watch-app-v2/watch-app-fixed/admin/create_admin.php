<?php
require_once '../config/db.php';

$error = '';
$success_message = '';
$is_success = false;

try {
    // ১. ডাটাবেজে আগে থেকে কোনো এডমিন আছে কিনা চেক করা
    $checkStmt = $pdo->query("SELECT COUNT(*) FROM admins");
    $admin_count = $checkStmt->fetchColumn();

    // যদি এডমিন থেকে থাকে (এবং এই মুহূর্তে নতুন তৈরি না হয়ে থাকে)
    if ($admin_count > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        die('
        <!DOCTYPE html>
        <html lang="en">
        <head><meta charset="UTF-8"><title>Access Denied</title><script src="https://cdn.tailwindcss.com"></script></head>
        <body class="bg-gray-100 h-screen flex items-center justify-center font-sans">
            <div class="bg-white p-8 rounded-lg shadow-md text-center max-w-md">
                <h2 class="text-2xl font-bold text-red-600 mb-4">⚠️ Access Denied!</h2>
                <p class="text-gray-700 mb-6">আপনার সিস্টেমে আগে থেকেই একটি এডমিন একাউন্ট রয়েছে।<br><br>সিকিউরিটির স্বার্থে দয়া করে <strong>create_admin.php</strong> ফাইলটি এখনই ডিলিট করে দিন।</p>
                <a href="login.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded transition">লগইন পেজে যান</a>
            </div>
        </body>
        </html>
        ');
    }

    // ২. ফর্ম সাবমিট হলে ডাটা প্রসেস করা
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = "ইউজারনেম এবং পাসওয়ার্ড দিতে হবে!";
        } elseif (strlen($password) < 6) {
            $error = "পাসওয়ার্ড অন্তত ৬ অক্ষরের হতে হবে!";
        } elseif ($password !== $confirm_password) {
            $error = "পাসওয়ার্ড দুটি মিলেনি! আবার চেষ্টা করুন।";
        } else {
            // পাসওয়ার্ড হ্যাশ করা
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // ডাটাবেজে সেভ করা
            $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (:u, :p)");
            $stmt->execute([':u' => $username, ':p' => $hashed_password]);

            $is_success = true;
            $success_message = "✅ এডমিন একাউন্ট সফলভাবে তৈরি হয়েছে!";
        }
    }
} catch (PDOException $e) {
    $error = "Database Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Admin Account</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 h-screen flex items-center justify-center font-sans">

    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md border-t-4 border-gray-800">

        <?php if ($is_success): ?>
            <div class="text-center">
                <h2 class="text-2xl font-bold text-green-600 mb-4"><?= $success_message ?></h2>

                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 text-left">
                    <strong>⚠️ অত্যন্ত জরুরি:</strong><br>
                    আপনার সিস্টেমটি এখন ব্যবহারের জন্য প্রস্তুত। হ্যাকারদের হাত থেকে বাঁচতে এখনই আপনার ফাইল ম্যানেজার থেকে <strong>create_admin.php</strong> ফাইলটি সম্পূর্ণ ডিলিট করে দিন!
                </div>

                <a href="login.php" class="w-full inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded transition">
                    লগইন পেজে যান
                </a>
            </div>

        <?php else: ?>
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">Setup Admin</h2>
            <p class="text-center text-gray-500 text-sm mb-6">প্রথমবারের মতো এডমিন একাউন্ট তৈরি করুন</p>

            <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-5 text-sm rounded">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-1">Username</label>
                    <input type="text" name="username" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-800" placeholder="e.g. admin">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-1">Password</label>
                    <input type="password" name="password" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-800" placeholder="Min 6 characters">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-1">Confirm Password</label>
                    <input type="password" name="confirm_password" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-800" placeholder="Type password again">
                </div>

                <button type="submit" class="w-full bg-gray-800 hover:bg-gray-900 text-white font-bold py-2.5 px-4 rounded-md transition duration-300 mt-2">
                    একাউন্ট তৈরি করুন
                </button>
            </form>
        <?php endif; ?>

    </div>

</body>

</html>