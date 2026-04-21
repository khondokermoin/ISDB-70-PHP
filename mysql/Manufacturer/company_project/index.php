<?php
$db = new mysqli('localhost', 'root', '', 'company');

if ($db->connect_error) {
    die('Database connection failed: ' . $db->connect_error);
}

$db->set_charset('utf8mb4');

$message = '';
$messageType = '';

function clearProcedureResults(mysqli $db): void
{
    while ($db->more_results() && $db->next_result()) {
        $extraResult = $db->store_result();
        if ($extraResult instanceof mysqli_result) {
            $extraResult->free();
        }
    }
}

if (isset($_POST['btnSubmit'])) {
    $mname = trim($_POST['mname'] ?? '');
    $contact = trim($_POST['contact'] ?? '');

    if ($mname === '' || $contact === '') {
        $message = 'Manufacturer name and contact are required.';
        $messageType = 'error';
    } elseif (!preg_match('/^[0-9+]{11,14}$/', $contact)) {
        $message = 'Contact number must be 11 to 14 digits or +.';
        $messageType = 'error';
    } else {
        $stmt = $db->prepare('CALL add_manufacture(?, ?)');
        if ($stmt) {
            $stmt->bind_param('ss', $mname, $contact);
            if ($stmt->execute()) {
                $message = 'Manufacturer added successfully.';
                $messageType = 'success';
            } else {
                $message = 'Failed to add manufacturer.';
                $messageType = 'error';
            }
            $stmt->close();
            clearProcedureResults($db);
        } else {
            $message = 'Stored procedure add_manufacture not found. Run setup.sql first.';
            $messageType = 'error';
        }
    }
}

if (isset($_POST['addProduct'])) {
    $pname = trim($_POST['pname'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $mid = (int)($_POST['manufac'] ?? 0);

    if ($pname === '' || $price === '' || $mid <= 0) {
        $message = 'Product name, price and manufacturer are required.';
        $messageType = 'error';
    } elseif (!is_numeric($price) || $price < 0) {
        $message = 'Price must be a valid positive number.';
        $messageType = 'error';
    } else {
        $priceValue = (float)$price;
        $stmt = $db->prepare('CALL add_product(?, ?, ?)');
        if ($stmt) {
            $stmt->bind_param('sdi', $pname, $priceValue, $mid);
            if ($stmt->execute()) {
                $message = 'Product added successfully.';
                $messageType = 'success';
            } else {
                $message = 'Failed to add product.';
                $messageType = 'error';
            }
            $stmt->close();
            clearProcedureResults($db);
        } else {
            $message = 'Stored procedure add_product not found. Run setup.sql first.';
            $messageType = 'error';
        }
    }
}

if (isset($_POST['delmanufact'])) {
    $mid = (int)($_POST['manufac'] ?? 0);

    if ($mid <= 0) {
        $message = 'Please select a manufacturer.';
        $messageType = 'error';
    } else {
        $stmt = $db->prepare('DELETE FROM manufacturer WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('i', $mid);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $message = 'Manufacturer deleted successfully. Related products were removed by trigger.';
                    $messageType = 'success';
                } else {
                    $message = 'Manufacturer not found.';
                    $messageType = 'error';
                }
            } else {
                $message = 'Failed to delete manufacturer.';
                $messageType = 'error';
            }
            $stmt->close();
        }
    }
}

$manufacturerResult = $db->query('SELECT id, name FROM manufacturer ORDER BY name ASC');
$manufacturers = [];
if ($manufacturerResult instanceof mysqli_result) {
    while ($row = $manufacturerResult->fetch_assoc()) {
        $manufacturers[] = $row;
    }
    $manufacturerResult->free();
}

$productView = $db->query('SELECT * FROM view_product ORDER BY id DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Project</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .card {
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }
        h1, h3 {
            margin-top: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td, th {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        input[type="text"], input[type="number"], select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background: #1d4ed8;
        }
        .message {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .success {
            background: #dcfce7;
            color: #166534;
        }
        .error {
            background: #fee2e2;
            color: #991b1b;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Company Management Project</h1>

    <?php if ($message !== ''): ?>
        <div class="message <?php echo htmlspecialchars($messageType); ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="grid">
        <div class="card">
            <h3>Manufacturer Table</h3>
            <form action="#" method="post">
                <table>
                    <tr>
                        <td><label for="mname">Name</label></td>
                        <td><input type="text" name="mname" id="mname"></td>
                    </tr>
                    <tr>
                        <td><label for="contact">Contact</label></td>
                        <td><input type="text" name="contact" id="contact"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><input type="submit" name="btnSubmit" value="Submit"></td>
                    </tr>
                </table>
            </form>
        </div>

        <div class="card">
            <h3>Product Table</h3>
            <form action="#" method="post">
                <table>
                    <tr>
                        <td><label for="pname">Name</label></td>
                        <td><input type="text" name="pname" id="pname"></td>
                    </tr>
                    <tr>
                        <td><label for="price">Price</label></td>
                        <td><input type="number" name="price" id="price" min="0" step="0.01"></td>
                    </tr>
                    <tr>
                        <td><label for="manufac_add">Manufacturer Name</label></td>
                        <td>
                            <select name="manufac" id="manufac_add">
                                <option value="">Select Manufacturer</option>
                                <?php foreach ($manufacturers as $manufacturer): ?>
                                    <option value="<?php echo (int)$manufacturer['id']; ?>">
                                        <?php echo htmlspecialchars($manufacturer['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><input type="submit" name="addProduct" value="Submit"></td>
                    </tr>
                </table>
            </form>
        </div>
    </div>

    <div class="card">
        <h3>Delete Manufacturer</h3>
        <form action="#" method="post">
            <table>
                <tr>
                    <td><label for="manufac_delete">Manufacturer</label></td>
                    <td>
                        <select name="manufac" id="manufac_delete">
                            <option value="">Select Manufacturer</option>
                            <?php foreach ($manufacturers as $manufacturer): ?>
                                <option value="<?php echo (int)$manufacturer['id']; ?>">
                                    <?php echo htmlspecialchars($manufacturer['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="submit" name="delmanufact" value="Delete"></td>
                </tr>
            </table>
        </form>
    </div>

    <div class="card">
        <h3>View Product</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Price</th>
                <th>Manufacturer</th>
                <th>Contact</th>
            </tr>
            <?php if ($productView instanceof mysqli_result && $productView->num_rows > 0): ?>
                <?php while ($row = $productView->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo (int)$row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['price']); ?></td>
                        <td><?php echo htmlspecialchars($row['manufacturer_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['manufacturer_contact']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No product found.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</div>
</body>
</html>
<?php
if ($productView instanceof mysqli_result) {
    $productView->free();
}
$db->close();
?>
