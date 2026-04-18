<?php
session_start();

$file = __DIR__ . '/data.txt';
$message = '';

if (isset($_POST['btnLogin'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $message = 'All fields are required.';
    } else {
        $loginSuccess = false;

        if (file_exists($file)) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                $parts = explode('|', $line);

                if (count($parts) >= 2) {
                    $savedUsername = $parts[0];
                    $savedPassword = $parts[1];

                    if ($savedUsername === $username && password_verify($password, $savedPassword)) {
                        $_SESSION['username'] = $username;
                        $loginSuccess = true;
                        header('Location: file_uplode.php');
                        exit();
                    }
                }
            }
        }

        if (!$loginSuccess) {
            $message = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f2f2f2; }
        .box { width: 360px; margin: 40px auto; background: white; padding: 20px; border-radius: 8px; }
        input[type=text], input[type=password] { width: 100%; padding: 10px; margin-bottom: 12px; box-sizing: border-box; }
        input[type=submit] { padding: 10px 16px; }
        .message { margin-top: 10px; color: red; }
    </style>
</head>
<body>
<?php require_once 'navbar.php'; ?>
<div class="box">
    <h2>Login</h2>
    <form method="post">
        <input type="text" name="username" placeholder="Enter username">
        <input type="password" name="password" placeholder="Enter password">
        <input type="submit" name="btnLogin" value="Login">
    </form>
    <p><a href="register.php">Create new account</a></p>
    <?php if ($message !== '') { ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php } ?>
</div>
</body>
</html>
