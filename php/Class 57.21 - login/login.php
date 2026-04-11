<?php
$error = "";

if (isset($_POST['login'])){
    $user = $_POST["username"];
    $p = $_POST["pass"];

    if($user == "admin" && $p == "123"){
        header("location:main.php");
        exit();
    } else {
        $error = "⚠️ Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Page</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #4facfe, #00f2fe);
        }

        .login-box {
            width: 320px;
            padding: 30px;
            background: #fff;
            margin: 100px auto;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            text-align: center;
        }

        .login-box h2 {
            margin-bottom: 20px;
        }

        .login-box input[type="text"],
        .login-box input[type="password"] {
            width: 92%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
        }

        .login-box input[type="submit"] {
            width: 96%;
            padding: 10px;
            background: #4facfe;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }

        .login-box input[type="submit"]:hover {
            background: #007bff;
        }

        .error {
            background: #ff4d4d;
            color: white;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-size: 14px;
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
        <input type="text" name="username" placeholder="Enter Username" required>
        <input type="password" name="pass" placeholder="Enter Password" required>
        <input type="submit" name="login" value="Login">
    </form>
</div>

</body>
</html>