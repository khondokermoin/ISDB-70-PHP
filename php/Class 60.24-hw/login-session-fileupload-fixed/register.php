<?php
session_start();

if (isset($_SESSION['username'])) {
    header('Location: file_uplode.php');
    exit();
}

$message = '';
$messageType = '';

if (isset($_POST['register'])) {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['pass'] ?? '');
    $dataFile = __DIR__ . '/data.txt';

    if ($user === '' || $pass === '') {
        $message = 'Username and password are required.';
        $messageType = 'error';
    } else {
        if (!file_exists($dataFile)) {
            file_put_contents($dataFile, '');
        }

        $users = file($dataFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $exists = false;

        foreach ($users as $line) {
            $parts = explode(',', $line);
            if (count($parts) >= 1) {
                $savedUser = trim($parts[0]);
                if ($savedUser === $user) {
                    $exists = true;
                    break;
                }
            }
        }

        if ($exists) {
            $message = 'This username already exists. Try another username.';
            $messageType = 'error';
        } else {
            file_put_contents($dataFile, $user . ',' . $pass . PHP_EOL, FILE_APPEND | LOCK_EX);
            $message = 'Registration successful. Now login.';
            $messageType = 'success';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #ddd;
        }

        .register-box {
            width: 320px;
            background: white;
            padding: 30px;
            margin: 70px auto;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        input {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background: #ff5e62;
            color: white;
            border: none;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background: #e84c50;
        }

        .error {
            background: #ff4d4d;
            color: white;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .success {
            background: #28a745;
            color: white;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .link-box {
            margin-top: 15px;
        }

        a {
            color: #e84c50;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>


<div class="register-box">
    <h2>Register</h2>

    <?php if ($message !== '') { ?>
        <div class="<?php echo htmlspecialchars($messageType); ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php } ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="pass" placeholder="Password" required>
        <input type="submit" name="register" value="Register">
    </form>

    <div class="link-box">
        Already have an account? <a href="index.php">Login</a>
    </div>
</div>

</body>
</html>
