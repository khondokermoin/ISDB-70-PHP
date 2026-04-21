<?php
require_once __DIR__ . '/db.php';

$pageTitle = 'Dashboard';
$totalProducts = (int)($db->query('SELECT COUNT(*) AS total FROM product')->fetch_assoc()['total'] ?? 0);
$totalManufacturers = (int)($db->query('SELECT COUNT(*) AS total FROM manufacturer')->fetch_assoc()['total'] ?? 0);
$stockValue = (float)($db->query('SELECT COALESCE(SUM(price * stock_qty), 0) AS total FROM product')->fetch_assoc()['total'] ?? 0);
$todaySales = (float)($db->query("SELECT COALESCE(SUM(total_amount - returned_amount), 0) AS total FROM invoice WHERE DATE(sale_date) = CURDATE()")->fetch_assoc()['total'] ?? 0);
$recentInvoices = $db->query('SELECT id, invoice_no, customer_name, sale_date, total_amount, returned_amount FROM invoice ORDER BY id DESC LIMIT 5');
$lowStock = $db->query('SELECT id, name, stock_qty FROM product WHERE stock_qty <= 5 ORDER BY stock_qty ASC, name ASC LIMIT 5');
require_once __DIR__ . '/header.php';
?>

<div class="grid">
    <div class="card">
        <h3>Total Products</h3>
        <div class="stat"><?php echo $totalProducts; ?></div>
        <div class="small">Products available in stock list</div>
    </div>
    <div class="card">
        <h3>Total Manufacturers</h3>
        <div class="stat"><?php echo $totalManufacturers; ?></div>
        <div class="small">Suppliers added in the system</div>
    </div>
    <div class="card">
        <h3>Stock Value</h3>
        <div class="stat">৳ <?php echo format_money($stockValue); ?></div>
        <div class="small">Price × current stock quantity</div>
    </div>
    <div class="card">
        <h3>Today Net Sales</h3>
        <div class="stat">৳ <?php echo format_money($todaySales); ?></div>
        <div class="small">Sales after return deduction</div>
    </div>
</div>

<div class="grid">
    <div class="card">
        <h2>Quick Work</h2>
        <div class="row-actions">
            <a class="btn" href="manufacturer.php">Add Manufacturer</a>
            <a class="btn" href="product.php">Add Product</a>
            <a class="btn" href="sell.php">Create Sale</a>
            <a class="btn" href="invoices.php">Manage Invoices</a>
        </div>
        <div class="info-box" style="margin-top:16px;">
            Flow: Manufacturer → Product → Sell → Print Invoice → Return Product
        </div>
    </div>

    <div class="card">
        <h2>Low Stock Products</h2>
        <div class="table-wrap">
            <table>
                <tr><th>Name</th><th>Stock</th></tr>
                <?php if ($lowStock instanceof mysqli_result && $lowStock->num_rows > 0): ?>
                    <?php while ($row = $lowStock->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo (int)$row['stock_qty']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="2">No low stock product.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <h2>Recent Invoices</h2>
    <div class="table-wrap">
        <table>
            <tr>
                <th>Invoice No</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Net Total</th>
                <th>Action</th>
            </tr>
            <?php if ($recentInvoices instanceof mysqli_result && $recentInvoices->num_rows > 0): ?>
                <?php while ($invoice = $recentInvoices->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($invoice['invoice_no']); ?></td>
                        <td><?php echo htmlspecialchars($invoice['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($invoice['sale_date']); ?></td>
                        <td>৳ <?php echo format_money($invoice['total_amount'] - $invoice['returned_amount']); ?></td>
                        <td class="row-actions">
                            <a class="btn" href="invoice.php?id=<?php echo (int)$invoice['id']; ?>">View</a>
                            <a class="btn" href="return.php?id=<?php echo (int)$invoice['id']; ?>">Return</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">No invoice found.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
