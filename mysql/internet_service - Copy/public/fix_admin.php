<!-- 
require_once '../config/database.php';
$db = (new Database())->getConnection();

$email = 'admin@amarit.com';
$password = 'admin123';

// একদম সঠিক এবং ভ্যালিড একটি হ্যাশ তৈরি করা হচ্ছে
$hash = password_hash($password, PASSWORD_DEFAULT);

// ডাটাবেসে ডামি হ্যাশটির বদলে আসল হ্যাশটি আপডেট করে দেওয়া হচ্ছে
$stmt = $db->prepare("UPDATE users SET password_hash = :hash WHERE email = :email");
$stmt->execute([':hash' => $hash, ':email' => $email]);

echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
echo "<h2 style='color: green;'>✅ Admin password successfully fixed!</h2>";
echo "<p>You can now log in using:</p>";
echo "<p>Email: <b>admin@amarit.com</b> <br> Password: <b>admin123</b></p>";
echo "<br><a href='login.php' style='padding: 10px 20px; background: red; color: white; text-decoration: none; border-radius: 5px;'>Go to Login Page</a>";
echo "</div>"; -->