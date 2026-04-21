<?php
require_once __DIR__ . '/db.php';
$invoiceId = (int)($_GET['id'] ?? $_POST['invoice_id'] ?? 0);

$stmt = $db->prepare('SELECT * FROM invoice WHERE id = ?');
$stmt->bind_param('i', $invoiceId);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$invoice) {
    set_flash('Invoice not found.', 'error');
    redirect_to('invoices.php');
}

if (isset($_POST['saveReturn'])) {
    $reason = trim($_POST['reason'] ?? '');
    $returnQtyInput = $_POST['return_qty'] ?? [];
    $db->begin_transaction();

    try {
        $itemSelect = $db->prepare('SELECT ii.id, ii.invoice_id, ii.product_id, ii.qty, ii.returned_qty, ii.unit_price, p.name AS product_name FROM invoice_item ii INNER JOIN product p ON ii.product_id = p.id WHERE ii.id = ? AND ii.invoice_id = ? FOR UPDATE');
        $updateItem = $db->prepare('UPDATE invoice_item SET returned_qty = returned_qty + ? WHERE id = ?');
        $updateStock = $db->prepare('UPDATE product SET stock_qty = stock_qty + ? WHERE id = ?');
        $insertReturn = $db->prepare('INSERT INTO product_return (invoice_id, invoice_item_id, product_id, return_qty, return_amount, return_date, reason) VALUES (?, ?, ?, ?, ?, NOW(), ?)');

        $totalReturnAmount = 0.0;
        $processed = 0;

        foreach ($returnQtyInput as $itemId => $qtyValue) {
            $itemId = (int)$itemId;
            $returnQty = (int)$qtyValue;
            if ($itemId <= 0 || $returnQty <= 0) {
                continue;
            }

            $itemSelect->bind_param('ii', $itemId, $invoiceId);
            if (!$itemSelect->execute()) {
                throw new Exception('Failed to read invoice item.');
            }
            $result = $itemSelect->get_result();
            $item = $result->fetch_assoc();
            $result->free();

            if (!$item) {
                throw new Exception('Invalid invoice item selected.');
            }

            $availableQty = (int)$item['qty'] - (int)$item['returned_qty'];
            if ($returnQty > $availableQty) {
                throw new Exception('Return qty is too high for ' . $item['product_name'] . '. Available return qty: ' . $availableQty);
            }

            $returnAmount = $returnQty * (float)$item['unit_price'];
            $productId = (int)$item['product_id'];

            $updateItem->bind_param('ii', $returnQty, $itemId);
            if (!$updateItem->execute()) {
                throw new Exception('Failed to update returned quantity.');
            }

            $updateStock->bind_param('ii', $returnQty, $productId);
            if (!$updateStock->execute()) {
                throw new Exception('Failed to update stock on return.');
            }

            $insertReturn->bind_param('iiiids', $invoiceId, $itemId, $productId, $returnQty, $returnAmount, $reason);
            if (!$insertReturn->execute()) {
                throw new Exception('Failed to save return information.');
            }

            $totalReturnAmount += $returnAmount;
            $processed++;
        }

        if ($processed === 0) {
            throw new Exception('Enter at least one valid return quantity.');
        }

        $updateInvoice = $db->prepare('UPDATE invoice SET returned_amount = returned_amount + ? WHERE id = ?');
        $updateInvoice->bind_param('di', $totalReturnAmount, $invoiceId);
        if (!$updateInvoice->execute()) {
            throw new Exception('Failed to update invoice return amount.');
        }
        $updateInvoice->close();

        $itemSelect->close();
        $updateItem->close();
        $updateStock->close();
        $insertReturn->close();

        $db->commit();
        set_flash('Product return saved successfully.');
        redirect_to('invoice.php?id=' . $invoiceId);
    } catch (Throwable $e) {
        $db->rollback();
        set_flash($e->getMessage(), 'error');
        redirect_to('return.php?id=' . $invoiceId);
    }
}

$itemStmt = $db->prepare('SELECT ii.*, p.name AS product_name FROM invoice_item ii INNER JOIN product p ON ii.product_id = p.id WHERE ii.invoice_id = ? ORDER BY ii.id ASC');
$itemStmt->bind_param('i', $invoiceId);
$itemStmt->execute();
$items = $itemStmt->get_result();
$itemStmt->close();

$pageTitle = 'Return Product';
require_once __DIR__ . '/header.php';
?>

<div class="card">
    <h2>Return Product</h2>
    <p><strong>Invoice No:</strong> <?php echo htmlspecialchars($invoice['invoice_no']); ?></p>
    <p><strong>Customer:</strong> <?php echo htmlspecialchars($invoice['customer_name']); ?></p>
    <p><strong>Current Net Total:</strong> ৳ <?php echo format_money($invoice['total_amount'] - $invoice['returned_amount']); ?></p>
</div>

<div class="card">
    <form method="post">
        <input type="hidden" name="invoice_id" value="<?php echo (int)$invoiceId; ?>">
        <div class="table-wrap">
            <table>
                <tr>
                    <th>Product</th>
                    <th>Sold Qty</th>
                    <th>Returned Qty</th>
                    <th>Available Return</th>
                    <th>Return Now</th>
                    <th>Unit Price</th>
                </tr>
                <?php while ($item = $items->fetch_assoc()): ?>
                    <?php $available = (int)$item['qty'] - (int)$item['returned_qty']; ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo (int)$item['qty']; ?></td>
                        <td><?php echo (int)$item['returned_qty']; ?></td>
                        <td><?php echo $available; ?></td>
                        <td>
                            <input type="number" name="return_qty[<?php echo (int)$item['id']; ?>]" min="0" max="<?php echo $available; ?>" value="0">
                        </td>
                        <td>৳ <?php echo format_money($item['unit_price']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <div style="margin-top:16px; max-width:400px;">
            <label for="reason">Return Reason</label>
            <textarea id="reason" name="reason" placeholder="Optional reason"></textarea>
        </div>

        <div class="row-actions" style="margin-top:14px;">
            <input type="submit" name="saveReturn" value="Save Return">
            <a class="btn" href="invoice.php?id=<?php echo (int)$invoiceId; ?>">Back to Invoice</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
