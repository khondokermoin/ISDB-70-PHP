<?php
include 'db.php';

$sql = "SELECT products.id, products.name, products.price, products.product_image, 
               brand.name AS brand_name, brand.contact 
        FROM products 
        JOIN brand ON products.brand_id = brand.id";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Products</title>
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .page-header h2 {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }
        .btn-add {
            background-color: #3b82f6;
            color: white;
            text-decoration: none;
            padding: 9px 18px;
            border-radius: 7px;
            font-weight: 600;
            font-size: 13px;
            transition: background-color 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-add:hover { background-color: #2563eb; }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
            box-shadow: 0 1px 4px rgba(0,0,0,0.07);
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }
        th {
            background-color: #f8fafc;
            color: #64748b;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background-color: #f8fafc; }
        .product-img {
            border-radius: 6px;
            object-fit: cover;
            border: 1px solid #e2e8f0;
        }
        .action-links a {
            margin-right: 6px;
            text-decoration: none;
            padding: 5px 11px;
            border-radius: 5px;
            color: white;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            transition: opacity 0.2s;
        }
        .action-links a:hover { opacity: 0.8; }
        .btn-view   { background-color: #0ea5e9; }
        .btn-edit   { background-color: #f59e0b; color: #1c1917 !important; }
        .btn-delete { background-color: #ef4444; }
        .price-badge {
            font-family: 'Space Mono', monospace;
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="page-header">
    <h2>Product List</h2>
    <div style="display:flex;gap:8px;">
        <a href="add_brand.php" class="btn-add" style="background:#475569;">+ Add Brand</a>
        <a href="add_product.php" class="btn-add">+ Add Product</a>
    </div>
</div>

<table>
    <tr>
        <th>ID</th>
        <th>Image</th>
        <th>Name</th>
        <th>Price</th>
        <th>Brand</th>
        <th>Contact</th>
        <th>Actions</th>
    </tr>

    <?php while($row = $result->fetch_assoc()){ ?>
    <tr>
        <td style="color:#94a3b8;font-size:12px;">#<?= $row['id'] ?></td>
        <td>
            <?php if(!empty($row['product_image'])): ?>
                <img src="uploads/<?= $row['product_image'] ?>" width="48" height="48" class="product-img" alt="<?= $row['name'] ?>">
            <?php else: ?>
                <div style="width:48px;height:48px;background:#f1f5f9;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:10px;">No img</div>
            <?php endif; ?>
        </td>
        <td style="font-weight:600;color:#1e293b;"><?= $row['name'] ?></td>
        <td><span class="price-badge">৳ <?= number_format($row['price'], 2) ?></span></td>
        <td><?= $row['brand_name'] ?></td>
        <td style="color:#64748b;font-size:13px;"><?= $row['contact'] ?></td>
        <td class="action-links">
            <a href="view_single.php?id=<?= $row['id'] ?>" class="btn-view">View</a>
            <a href="update.php?id=<?= $row['id'] ?>" class="btn-edit">Edit</a>
            <a href="delete.php?id=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Delete this product?');">Delete</a>
        </td>
    </tr>
    <?php } ?>
</table>

</div><!-- .main-content -->
</body>
</html>
