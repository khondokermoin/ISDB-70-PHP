<?php
session_start();
$file = __DIR__ . '/data.txt';
$message = "";

if (isset($_POST['btnLogin'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username == "" || $password == "") {
        $message = "All fields are required.";
    } else {
        if (file_exists($file)) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                $parts = explode('|', $line);
                if (count($parts) >= 2) {
                    if ($parts[0] === $username && password_verify($password, $parts[1])) {
                        $_SESSION['username'] = $username;
                        header("Location: file_uplode.php");
                        exit();
                    }
                }
            }
        }
        $message = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<body>
<?php require_once 'navbar.php'; ?>
<h2>Login</h2>
<form method="post">
<input type="text" name="username" placeholder="Username"><br><br>
<input type="password" name="password" placeholder="Password"><br><br>
<input type="submit" name="btnLogin" value="Login">
</form>
<p><?php echo $message; ?></p>
</body>
</html>