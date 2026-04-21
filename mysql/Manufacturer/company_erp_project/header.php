<?php
if (!isset($pageTitle)) {
    $pageTitle = 'Company ERP Project';
}
[$flashMessage, $flashType] = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrapper">
    <header class="topbar">
        <div>
            <h1>Company Mini ERP</h1>
            <p class="subtitle">Add Product, Sell Product, Print Invoice, Return Product</p>
        </div>
    </header>

    <nav class="navbar">
        <a href="index.php">Dashboard</a>
        <a href="manufacturer.php">Manufacturer</a>
        <a href="product.php">Product</a>
        <a href="sell.php">Sell Product</a>
        <a href="invoices.php">Invoices</a>
    </nav>

    <?php if ($flashMessage !== ''): ?>
        <div class="message <?php echo htmlspecialchars($flashType); ?>">
            <?php echo htmlspecialchars($flashMessage); ?>
        </div>
    <?php endif; ?>
