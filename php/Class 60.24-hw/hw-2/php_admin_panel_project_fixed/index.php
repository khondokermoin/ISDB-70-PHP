<?php
session_start();
$file = __DIR__ . '/data.txt';
$message = '';

if (isset($_POST['btnLogin'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $message = 'Username and password are required.';
    } else {
        if (file_exists($file)) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $parts = explode('|', $line);
                if (count($parts) >= 7) {
                    $savedId = $parts[0];
                    $savedFullName = $parts[1];
                    $savedEmail = $parts[2];
                    $savedPassword = $parts[3];
                    $savedAddress = $parts[4];
                    $savedContact = $parts[5];
                    $savedUsername = $parts[6];

                    if ($savedUsername === $username && password_verify($password, $savedPassword)) {
                        $_SESSION['id'] = $savedId;
                        $_SESSION['full_name'] = $savedFullName;
                        $_SESSION['email'] = $savedEmail;
                        $_SESSION['address'] = $savedAddress;
                        $_SESSION['contact'] = $savedContact;
                        $_SESSION['username'] = $savedUsername;
                        header('Location: file_uplode.php');
                        exit();
                    }
                }
            }
        }
        $message = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body{font-family:Arial,sans-serif;margin:0;background:#f2f2f2}
        .box{width:380px;margin:40px auto;background:#fff;padding:22px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.1)}
        input[type=text],input[type=password]{width:100%;padding:10px;margin-bottom:12px;box-sizing:border-box}
        input[type=submit]{padding:10px 16px;cursor:pointer}
        .message{margin-top:10px;color:red}
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
