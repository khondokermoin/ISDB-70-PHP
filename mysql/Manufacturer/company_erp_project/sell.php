<?php
require_once __DIR__ . '/db.php';

function loadSellProducts(mysqli $db): array
{
    $items = [];
    $result = $db->query('SELECT id, name, price, stock_qty FROM product WHERE stock_qty > 0 ORDER BY name ASC');
    if ($result instanceof mysqli_result) {
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        $result->free();
    }
    return $items;
}

if (isset($_POST['createSale'])) {
    $customerName = trim($_POST['customer_name'] ?? 'Walk-in Customer');
    $productIds = $_POST['product_id'] ?? [];
    $quantities = $_POST['qty'] ?? [];

    $cleanItems = [];
    for ($i = 0; $i < count($productIds); $i++) {
        $pid = (int)($productIds[$i] ?? 0);
        $qty = (int)($quantities[$i] ?? 0);
        if ($pid > 0 && $qty > 0) {
            $cleanItems[] = ['product_id' => $pid, 'qty' => $qty];
        }
    }

    if (empty($cleanItems)) {
        set_flash('Select at least one product with quantity.', 'error');
        redirect_to('sell.php');
    }

    $db->begin_transaction();
    try {
        $invoiceNo = generate_invoice_no();
        $saleDate = date('Y-m-d H:i:s');
        $invoiceStmt = $db->prepare('INSERT INTO invoice (invoice_no, customer_name, sale_date, total_amount, returned_amount) VALUES (?, ?, ?, 0, 0)');
        $invoiceStmt->bind_param('sss', $invoiceNo, $customerName, $saleDate);
        if (!$invoiceStmt->execute()) {
            throw new Exception('Failed to create invoice.');
        }
        $invoiceId = $invoiceStmt->insert_id;
        $invoiceStmt->close();

        $productStmt = $db->prepare('SELECT id, name, price, stock_qty FROM product WHERE id = ? FOR UPDATE');
        $itemStmt = $db->prepare('INSERT INTO invoice_item (invoice_id, product_id, qty, unit_price, line_total, returned_qty) VALUES (?, ?, ?, ?, ?, 0)');
        $stockStmt = $db->prepare('UPDATE product SET stock_qty = stock_qty - ? WHERE id = ?');

        $grandTotal = 0.0;

        foreach ($cleanItems as $item) {
            $pid = $item['product_id'];
            $qty = $item['qty'];

            $productStmt->bind_param('i', $pid);
            if (!$productStmt->execute()) {
                throw new Exception('Failed to read product.');
            }
            $productResult = $productStmt->get_result();
            $product = $productResult->fetch_assoc();
            $productResult->free();

            if (!$product) {
                throw new Exception('Product not found.');
            }
            if ((int)$product['stock_qty'] < $qty) {
                throw new Exception('Not enough stock for ' . $product['name'] . '.');
            }

            $unitPrice = (float)$product['price'];
            $lineTotal = $unitPrice * $qty;
            $grandTotal += $lineTotal;

            $itemStmt->bind_param('iiidd', $invoiceId, $pid, $qty, $unitPrice, $lineTotal);
            if (!$itemStmt->execute()) {
                throw new Exception('Failed to insert invoice item.');
            }

            $stockStmt->bind_param('ii', $qty, $pid);
            if (!$stockStmt->execute()) {
                throw new Exception('Failed to update stock.');
            }
        }

        $updateInvoice = $db->prepare('UPDATE invoice SET total_amount = ? WHERE id = ?');
        $updateInvoice->bind_param('di', $grandTotal, $invoiceId);
        if (!$updateInvoice->execute()) {
            throw new Exception('Failed to update invoice total.');
        }
        $updateInvoice->close();

        $productStmt->close();
        $itemStmt->close();
        $stockStmt->close();

        $db->commit();
        set_flash('Sale completed successfully. Invoice created: ' . $invoiceNo);
        redirect_to('invoice.php?id=' . $invoiceId);
    } catch (Throwable $e) {
        $db->rollback();
        set_flash($e->getMessage(), 'error');
        redirect_to('sell.php');
    }
}

$products = loadSellProducts($db);
$pageTitle = 'Sell Product';
require_once __DIR__ . '/header.php';
?>

<div class="card">
    <h2>Create Sale Invoice</h2>
    <form method="post" id="saleForm">
        <div class="form-grid">
            <div>
                <label for="customer_name">Customer Name</label>
                <input type="text" id="customer_name" name="customer_name" value="Walk-in Customer">
            </div>
        </div>

        <div class="table-wrap" style="margin-top:16px;">
            <table id="saleTable">
                <tr>
                    <th style="width:60%;">Product</th>
                    <th>Qty</th>
                    <th>Action</th>
                </tr>
                <tr>
                    <td>
                        <select name="product_id[]" required>
                            <option value="">Select Product</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?php echo (int)$product['id']; ?>">
                                    <?php echo htmlspecialchars($product['name']); ?> | Price: ৳ <?php echo format_money($product['price']); ?> | Stock: <?php echo (int)$product['stock_qty']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="number" name="qty[]" min="1" step="1" value="1" required></td>
                    <td><button type="button" onclick="removeRow(this)">Remove</button></td>
                </tr>
            </table>
        </div>

        <div class="row-actions" style="margin-top:14px;">
            <button type="button" class="btn" onclick="addRow()">+ Add More Product</button>
            <input type="submit" name="createSale" value="Save Sale & Generate Invoice">
        </div>
    </form>
</div>

<div class="card">
    <h3>Available Products for Sale</h3>
    <div class="table-wrap">
        <table>
            <tr><th>Product</th><th>Price</th><th>Stock</th></tr>
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td>৳ <?php echo format_money($product['price']); ?></td>
                        <td><?php echo (int)$product['stock_qty']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="3">No stock available to sell.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<script>
const productOptions = `
<option value="">Select Product</option>
<?php foreach ($products as $product): ?>
<option value="<?php echo (int)$product['id']; ?>"><?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?> | Price: ৳ <?php echo format_money($product['price']); ?> | Stock: <?php echo (int)$product['stock_qty']; ?></option>
<?php endforeach; ?>`;

function addRow() {
    const table = document.getElementById('saleTable');
    const row = table.insertRow(-1);
    row.innerHTML = `
        <td><select name="product_id[]" required>${productOptions}</select></td>
        <td><input type="number" name="qty[]" min="1" step="1" value="1" required></td>
        <td><button type="button" onclick="removeRow(this)">Remove</button></td>
    `;
}

function removeRow(button) {
    const table = document.getElementById('saleTable');
    if (table.rows.length <= 2) {
        return;
    }
    button.closest('tr').remove();
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
