<?php
session_start();

if (isset($_SESSION['username'])) {
    header('Location: file_uplode.php');
    exit();
}

$error = "";

if (isset($_POST['login'])) {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['pass'] ?? '');
    $dataFile = __DIR__ . '/data.txt';

    if ($user === '' || $pass === '') {
        $error = 'Username and password are required.';
    } elseif (!file_exists($dataFile)) {
        $error = 'User data file not found.';
    } else {
        $users = file($dataFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $isValid = false;

        foreach ($users as $line) {
            $parts = explode(',', $line);
            if (count($parts) >= 2) {
                $savedUser = trim($parts[0]);
                $savedPass = trim($parts[1]);

                if ($savedUser === $user && $savedPass === $pass) {
                    $isValid = true;
                    break;
                }
            }
        }

        if ($isValid) {
            $_SESSION['username'] = $user;
            header('Location: file_uplode.php');
            exit();
        } else {
            $error = 'Invalid username or password!';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login System</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #36d1dc, #5b86e5);
        }

        .login-box {
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
            background: #5b86e5;
            color: white;
            border: none;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background: #3f6ed3;
        }

        .error {
            background: #ff4d4d;
            color: white;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .link-box {
            margin-top: 15px;
        }

        a {
            color: #3f6ed3;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>


<div class="login-box">
    <h2>Login</h2>

    <?php if ($error !== "") { ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="pass" placeholder="Password" required>
        <input type="submit" name="login" value="Login">
    </form>

    <div class="link-box">
        Don't have an account? <a href="register.php">Register</a>
    </div>
</div>

</body>
</html>
