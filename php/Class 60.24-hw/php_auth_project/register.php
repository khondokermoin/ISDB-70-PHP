<?php
$file = __DIR__ . '/data.txt';
$message = "";

if (isset($_POST['btnRegister'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username == "" || $password == "") {
        $message = "All fields are required.";
    } else {
        $exists = false;
        if (file_exists($file)) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $parts = explode('|', $line);
                if ($parts[0] === $username) {
                    $exists = true;
                    break;
                }
            }
        }

        if ($exists) {
            $message = "Username already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            file_put_contents($file, $username . '|' . $hash . PHP_EOL, FILE_APPEND | LOCK_EX);
            $message = "Registration successful.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<body>
<?php require_once 'navbar.php'; ?>
<h2>Register</h2>
<form method="post">
<input type="text" name="username"><br><br>
<input type="password" name="password"><br><br>
<input type="submit" name="btnRegister" value="Register">
</form>
<p><?php echo $message; ?></p>
</body>
</html>