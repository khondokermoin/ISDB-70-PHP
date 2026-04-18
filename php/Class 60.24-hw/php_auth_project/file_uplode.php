<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<body>
<?php require_once 'navbar.php'; ?>
<h2>Welcome <?php echo $_SESSION['username']; ?></h2>
<p>Upload system working.</p>
</body>
</html>