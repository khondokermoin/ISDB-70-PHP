<?php
session_start();
$file = __DIR__ . '/data.txt';
$message = '';
$messageClass = 'error';

if (!file_exists($file)) {
    file_put_contents($file, '');
}

function oldValue($name)
{
    return htmlspecialchars($_POST[$name] ?? '');
}

if (isset($_POST['btnRegister'])) {
    $id = trim($_POST['id'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $retypePassword = trim($_POST['retype_password'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $username = trim($_POST['username'] ?? '');

    $emailPattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
    $passwordPattern = '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@#$%!&*]{6,20}$/';
    $contactPattern = '/^(\+8801|01)[3-9]\d{8}$/';

    if ($id === '' || $fullName === '' || $email === '' || $password === '' || $retypePassword === '' || $address === '' || $contact === '' || $username === '') {
        $message = 'All fields are required.';
    } elseif (!preg_match($emailPattern, $email)) {
        $message = 'Invalid email format.';
    } elseif (!preg_match($passwordPattern, $password)) {
        $message = 'Password must be 6-20 characters and include letters and numbers.';
    } elseif ($password !== $retypePassword) {
        $message = 'Password and re-type password do not match.';
    } elseif (!preg_match($contactPattern, $contact)) {
        $message = 'Invalid contact number. Use 01XXXXXXXXX or +8801XXXXXXXXX.';
    } else {
        $exists = false;
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $parts = explode('|', $line);
            if (count($parts) >= 7) {
                if ($parts[0] === $id) {
                    $message = 'ID already exists.';
                    $exists = true;
                    break;
                }
                if ($parts[6] === $username) {
                    $message = 'Username already exists.';
                    $exists = true;
                    break;
                }
                if ($parts[2] === $email) {
                    $message = 'Email already exists.';
                    $exists = true;
                    break;
                }
            }
        }

        if (!$exists) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $saveLine = $id . '|' . $fullName . '|' . $email . '|' . $hashedPassword . '|' . $address . '|' . $contact . '|' . $username . PHP_EOL;
            file_put_contents($file, $saveLine, FILE_APPEND | LOCK_EX);
            $message = 'Registration successful. Now login.';
            $messageClass = 'success';
            $_POST = [];
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
        body{font-family:Arial,sans-serif;margin:0;background:#f2f2f2}
        .box{width:500px;margin:30px auto;background:#fff;padding:22px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.1)}
        input[type=text],input[type=password],textarea{width:100%;padding:10px;margin-bottom:12px;box-sizing:border-box}
        textarea{height:90px;resize:vertical}
        input[type=submit]{padding:10px 16px;cursor:pointer}
        .error{margin-top:10px;color:red}
        .success{margin-top:10px;color:green}
        .note{font-size:13px;color:#555;margin-top:-6px;margin-bottom:10px}
    </style>
</head>
<body>
<?php require_once 'navbar.php'; ?>
<div class="box">
    <h2>Registration Form</h2>
    <form method="post">
        <input type="text" name="id" placeholder="Enter ID" value="<?php echo oldValue('id'); ?>">
        <input type="text" name="full_name" placeholder="Enter full name" value="<?php echo oldValue('full_name'); ?>">
        <input type="text" name="email" placeholder="Enter email" value="<?php echo oldValue('email'); ?>">
        <div class="note">Email validation uses regular expression.</div>
        <input type="password" name="password" placeholder="Enter password">
        <div class="note">Password regex: 6-20 chars, at least one letter and one number.</div>
        <input type="password" name="retype_password" placeholder="Re-type password">
        <textarea name="address" placeholder="Enter address"><?php echo oldValue('address'); ?></textarea>
        <input type="text" name="contact" placeholder="Enter contact number" value="<?php echo oldValue('contact'); ?>">
        <input type="text" name="username" placeholder="Enter username" value="<?php echo oldValue('username'); ?>">
        <input type="submit" name="btnRegister" value="Register">
    </form>
    <p><a href="index.php">Back to login</a></p>
    <?php if ($message !== '') { ?>
        <div class="<?php echo htmlspecialchars($messageClass); ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php } ?>
</div>
</body>
</html>
