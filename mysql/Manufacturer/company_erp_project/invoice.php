<?php
require_once __DIR__ . '/db.php';
$invoiceId = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare('SELECT * FROM invoice WHERE id = ?');
$stmt->bind_param('i', $invoiceId);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$invoice) {
    set_flash('Invoice not found.', 'error');
    redirect_to('invoices.php');
}

$itemStmt = $db->prepare('SELECT ii.*, p.name AS product_name FROM invoice_item ii INNER JOIN product p ON ii.product_id = p.id WHERE ii.invoice_id = ? ORDER BY ii.id ASC');
$itemStmt->bind_param('i', $invoiceId);
$itemStmt->execute();
$items = $itemStmt->get_result();
$itemStmt->close();

$returnStmt = $db->prepare('SELECT pr.*, p.name AS product_name FROM product_return pr INNER JOIN product p ON pr.product_id = p.id WHERE pr.invoice_id = ? ORDER BY pr.id DESC');
$returnStmt->bind_param('i', $invoiceId);
$returnStmt->execute();
$returns = $returnStmt->get_result();
$returnStmt->close();

$pageTitle = 'Invoice ' . $invoice['invoice_no'];
require_once __DIR__ . '/header.php';
?>

<div class="invoice-box">
    <div class="invoice-header">
        <div>
            <h2>Sales Invoice</h2>
            <p><strong>Invoice No:</strong> <?php echo htmlspecialchars($invoice['invoice_no']); ?></p>
            <p><strong>Customer:</strong> <?php echo htmlspecialchars($invoice['customer_name']); ?></p>
        </div>
        <div class="right">
            <p><strong>Date:</strong> <?php echo htmlspecialchars($invoice['sale_date']); ?></p>
            <div class="row-actions no-print" style="justify-content:flex-end;">
                <button type="button" onclick="window.print()">Print Invoice</button>
                <a class="btn" href="return.php?id=<?php echo (int)$invoice['id']; ?>">Return Product</a>
            </div>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Returned Qty</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
            <?php $sl = 1; while ($item = $items->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $sl++; ?></td>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td><?php echo (int)$item['qty']; ?></td>
                    <td><?php echo (int)$item['returned_qty']; ?></td>
                    <td>৳ <?php echo format_money($item['unit_price']); ?></td>
                    <td>৳ <?php echo format_money($item['line_total']); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div style="margin-top:20px; max-width:360px; margin-left:auto;">
        <table>
            <tr><th>Gross Total</th><td>৳ <?php echo format_money($invoice['total_amount']); ?></td></tr>
            <tr><th>Return Amount</th><td class="text-danger">৳ <?php echo format_money($invoice['returned_amount']); ?></td></tr>
            <tr><th>Net Payable</th><td class="text-success"><strong>৳ <?php echo format_money($invoice['total_amount'] - $invoice['returned_amount']); ?></strong></td></tr>
        </table>
    </div>

    <div style="margin-top:24px;">
        <h3>Return History</h3>
        <div class="table-wrap">
            <table>
                <tr><th>Date</th><th>Product</th><th>Qty</th><th>Amount</th><th>Reason</th></tr>
                <?php if ($returns instanceof mysqli_result && $returns->num_rows > 0): ?>
                    <?php while ($return = $returns->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($return['return_date']); ?></td>
                            <td><?php echo htmlspecialchars($return['product_name']); ?></td>
                            <td><?php echo (int)$return['return_qty']; ?></td>
                            <td>৳ <?php echo format_money($return['return_amount']); ?></td>
                            <td><?php echo htmlspecialchars($return['reason']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">No return for this invoice.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
