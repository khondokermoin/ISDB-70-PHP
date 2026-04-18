<?php session_start(); ?>
<div style="background:#333;padding:10px;">
<a href="index.php" style="color:white;margin-right:10px;">Login</a>
<a href="register.php" style="color:white;margin-right:10px;">Register</a>
<?php if(isset($_SESSION['username'])) { ?>
<a href="file_uplode.php" style="color:white;margin-right:10px;">Dashboard</a>
<a href="logout.php" style="color:white;">Logout</a>
<?php } ?>
</div>