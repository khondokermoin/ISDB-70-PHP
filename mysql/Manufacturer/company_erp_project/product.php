<?php
require_once __DIR__ . '/db.php';

if (isset($_POST['addProduct'])) {
    $name = trim($_POST['pname'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $stock = trim($_POST['stock_qty'] ?? '');
    $mid = (int)($_POST['manufacturer_id'] ?? 0);

    if ($name === '' || $price === '' || $stock === '' || $mid <= 0) {
        set_flash('Product name, price, stock and manufacturer are required.', 'error');
    } elseif (!is_numeric($price) || (float)$price < 0) {
        set_flash('Price must be a valid positive number.', 'error');
    } elseif (!ctype_digit($stock) || (int)$stock < 0) {
        set_flash('Stock must be a valid whole number.', 'error');
    } else {
        $priceValue = (float)$price;
        $stockValue = (int)$stock;
        $stmt = $db->prepare('CALL add_product(?, ?, ?, ?)');
        if ($stmt) {
            $stmt->bind_param('sdii', $name, $priceValue, $stockValue, $mid);
            if ($stmt->execute()) {
                set_flash('Product added successfully.');
            } else {
                set_flash('Failed to add product.', 'error');
            }
            $stmt->close();
            while ($db->more_results() && $db->next_result()) {
                $temp = $db->store_result();
                if ($temp instanceof mysqli_result) {
                    $temp->free();
                }
            }
        } else {
            set_flash('Stored procedure not found. Run setup.sql first.', 'error');
        }
    }
    redirect_to('product.php');
}

$pageTitle = 'Product';
$manufacturers = $db->query('SELECT id, name FROM manufacturer ORDER BY name ASC');
$products = $db->query('SELECT * FROM view_product ORDER BY id DESC');
require_once __DIR__ . '/header.php';
?>

<div class="card">
    <h2>Add Product</h2>
    <form method="post">
        <div class="form-grid">
            <div>
                <label for="pname">Product Name</label>
                <input type="text" id="pname" name="pname" required>
            </div>
            <div>
                <label for="price">Sale Price</label>
                <input type="number" id="price" name="price" min="0" step="0.01" required>
            </div>
            <div>
                <label for="stock_qty">Opening Stock</label>
                <input type="number" id="stock_qty" name="stock_qty" min="0" step="1" required>
            </div>
            <div>
                <label for="manufacturer_id">Manufacturer</label>
                <select id="manufacturer_id" name="manufacturer_id" required>
                    <option value="">Select Manufacturer</option>
                    <?php if ($manufacturers instanceof mysqli_result): ?>
                        <?php while ($m = $manufacturers->fetch_assoc()): ?>
                            <option value="<?php echo (int)$m['id']; ?>"><?php echo htmlspecialchars($m['name']); ?></option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>
        <div style="margin-top:14px;">
            <input type="submit" name="addProduct" value="Save Product">
        </div>
    </form>
</div>

<div class="card">
    <h2>Product List</h2>
    <div class="table-wrap">
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Manufacturer</th>
                <th>Contact</th>
            </tr>
            <?php if ($products instanceof mysqli_result && $products->num_rows > 0): ?>
                <?php while ($row = $products->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo (int)$row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td>৳ <?php echo format_money($row['price']); ?></td>
                        <td><?php echo (int)$row['stock_qty']; ?></td>
                        <td><?php echo htmlspecialchars($row['manufacturer_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['manufacturer_contact']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">No product found.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
