<?php
session_start(); // 🔥 session start (must be top)

$error = "";

if (isset($_POST['login'])) {
    $user = $_POST['username'];
    $pass = $_POST['pass'];

    $users = file("data.txt", FILE_IGNORE_NEW_LINES);

    $input = $user . "," . $pass;

    if (in_array($input, $users)) {

        // ✅ store username in session
        $_SESSION['username'] = $user;

        header("Location: main.php");
        exit();

    } else {
        $error = "⚠️ Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login System</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial;
            background: linear-gradient(to right, #36d1dc, #5b86e5);
        }

        .login-box {
            width: 320px;
            background: white;
            padding: 30px;
            margin: 100px auto;
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
    </style>
</head>

<body>

<div class="login-box">
    <h2>Login</h2>

    <?php if($error != "") { ?>
        <div class="error"><?php echo $error; ?></div>
    <?php } ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="pass" placeholder="Password" required>
        <input type="submit" name="login" value="Login">
    </form>
</div>

</body>
</html>