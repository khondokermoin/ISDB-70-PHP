<div style="background:#333;padding:12px;">
    <a href="index.php" style="color:white;margin-right:15px;text-decoration:none;">Login</a>
    <a href="register.php" style="color:white;margin-right:15px;text-decoration:none;">Register</a>
    <?php if (isset($_SESSION['username'])) { ?>
        <a href="file_uplode.php" style="color:white;margin-right:15px;text-decoration:none;">Gallery</a>
        <a href="logout.php" style="color:white;text-decoration:none;">Logout</a>
    <?php } ?>
</div>
