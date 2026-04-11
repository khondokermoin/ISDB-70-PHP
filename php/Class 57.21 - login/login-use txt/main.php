<?php
session_start();

// check login
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
?>

<h2>Welcome, <?php echo $_SESSION['username']; ?> </h2>

<a href="logout.php">Logout</a>