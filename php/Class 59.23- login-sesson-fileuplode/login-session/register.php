<?php
session_start();

$file = __DIR__ . '/data.txt';
$message = '';

if (isset($_SESSION['username'])) {
    header('Location: file_uplode.php');
    exit();
}

if (!file_exists($file)) {
    file_put_contents($file, '');
}

if (isset($_POST['btnRegister'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $message = 'All fields are required.';
    } else {
        $exists = false;
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (strpos($line, '|') !== false) {
                $parts = explode('|', $line);
            } else {
                $parts = explode(',', $line);
            }

            if (count($parts) >= 2 && trim($parts[0]) === $username) {
                $exists = true;
                break;
            }
        }

        if ($exists) {
            $message = 'Username already exists.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $saveLine = $username . '|' . $hashedPassword . PHP_EOL;
            file_put_contents($file, $saveLine, FILE_APPEND | LOCK_EX);
            $message = 'Registration successful. Now login.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f2f2f2; }
        .box { width: 360px; margin: 40px auto; background: white; padding: 20px; border-radius: 8px; }
        input[type=text], input[type=password] { width: 100%; padding: 10px; margin-bottom: 12px; box-sizing: border-box; }
        input[type=submit] { padding: 10px 16px; }
        .message { margin-top: 10px; color: green; }
        .error { color: red; }
    </style>
</head>
<body>
<div class="box">
    <h2>Register</h2>
    <form method="post">
        <input type="text" name="username" placeholder="Enter username">
        <input type="password" name="password" placeholder="Enter password">
        <input type="submit" name="btnRegister" value="Register">
    </form>
    <p><a href="index.php">Back to login</a></p>
    <?php if ($message !== '') { ?>
        <div class="<?php echo strpos($message, 'successful') !== false ? 'message' : 'message error'; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php } ?>
</div>
</body>
</html>
