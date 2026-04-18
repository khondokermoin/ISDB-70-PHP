<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<style>
    .navbar {
        background: #222;
        padding: 12px 20px;
        display: flex;
        gap: 15px;
        align-items: center;
        flex-wrap: wrap;
    }

    .navbar a {
        color: white;
        text-decoration: none;
        font-weight: bold;
    }

    .navbar a:hover {
        color: #ffd54f;
    }

    .navbar .right {
        margin-left: auto;
        color: #ddd;
        font-size: 14px;
    }
</style>

<div class="navbar">
    <a href="index.php">Login</a>
    <a href="register.php">Register</a>

    <?php if (isset($_SESSION['username'])) { ?>
        <a href="file_uplode.php">Gallery</a>
        <a href="logout.php">Logout</a>
        <span class="right">User: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
    <?php } ?>
</div>
