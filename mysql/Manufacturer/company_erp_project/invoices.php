<?php
require_once __DIR__ . '/db.php';
$pageTitle = 'Invoices';
$list = $db->query('SELECT id, invoice_no, customer_name, sale_date, total_amount, returned_amount FROM invoice ORDER BY id DESC');
require_once __DIR__ . '/header.php';
?>

<div class="card">
    <h2>Invoice List</h2>
    <div class="table-wrap">
        <table>
            <tr>
                <th>ID</th>
                <th>Invoice No</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Gross Total</th>
                <th>Returned</th>
                <th>Net Total</th>
                <th>Action</th>
            </tr>
            <?php if ($list instanceof mysqli_result && $list->num_rows > 0): ?>
                <?php while ($row = $list->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo (int)$row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['invoice_no']); ?></td>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['sale_date']); ?></td>
                        <td>৳ <?php echo format_money($row['total_amount']); ?></td>
                        <td class="text-danger">৳ <?php echo format_money($row['returned_amount']); ?></td>
                        <td class="text-success">৳ <?php echo format_money($row['total_amount'] - $row['returned_amount']); ?></td>
                        <td class="row-actions">
                            <a class="btn" href="invoice.php?id=<?php echo (int)$row['id']; ?>">View</a>
                            <a class="btn" href="return.php?id=<?php echo (int)$row['id']; ?>">Return</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8">No invoice found.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
