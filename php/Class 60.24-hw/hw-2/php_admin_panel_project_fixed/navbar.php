<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<style>
.navbar{background:#333;padding:12px 20px;display:flex;gap:15px;align-items:center;flex-wrap:wrap}
.navbar a{color:#fff;text-decoration:none;padding:6px 10px;border-radius:4px}
.navbar a:hover,.navbar a.active{background:#555}
.navbar .right{margin-left:auto;color:#fff}
</style>
<div class="navbar">
    <a href="index.php" class="<?php echo $current==='index.php' ? 'active' : ''; ?>">Login</a>
    <a href="register.php" class="<?php echo $current==='register.php' ? 'active' : ''; ?>">Register</a>
    <?php if (isset($_SESSION['username'])) { ?>
        <a href="file_uplode.php" class="<?php echo $current==='file_uplode.php' ? 'active' : ''; ?>">Admin Panel</a>
        <a href="logout.php">Logout</a>
        <div class="right">User: <?php echo htmlspecialchars($_SESSION['username']); ?></div>
    <?php } ?>
</div>
