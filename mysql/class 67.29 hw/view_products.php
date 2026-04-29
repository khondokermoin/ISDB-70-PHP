<?php
include 'db.php';

// Updated query to fetch both brand name and brand contact
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
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f8f9fa; 
            padding: 20px; 
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Header section with flexbox for alignment */
        .page-header {
            display: flex;
            justify-content: right;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
            gap: 5px;
        }
        .page-header1 {
            
            justify-content: left;
            align-items: center;
            
   
        }
        h2 { 
            color: #2c3e50; 
            margin: 0;
        }
        
        /* Add Product Button Styling */
        .btn-add {
            background-color: #3b82f6; /* Modern Blue */
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            transition: background-color 0.3s ease, transform 0.1s ease;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
        }
        .btn-add:hover {
            background-color: #2563eb;
        }
        .btn-add:active {
            transform: scale(0.98);
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
            background: #ffffff; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
            border-radius: 8px;
            overflow: hidden;
        }
        th, td { 
            padding: 15px; 
            text-align: left; 
            border-bottom: 1px solid #e9ecef; 
        }
        th { 
            background-color: #343a40; 
            color: #ffffff; 
            font-weight: 500;
        }
        tr:hover { background-color: #f1f3f5; }
        .product-img {
            border-radius: 4px;
            object-fit: cover;
        }
        .action-links a { 
            margin-right: 8px; 
            text-decoration: none; 
            padding: 6px 12px; 
            border-radius: 4px; 
            color: white; 
            font-size: 13px;
            display: inline-block;
            transition: opacity 0.2s;
        }
        .action-links a:hover { opacity: 0.8; }
        .btn-view { background-color: #17a2b8; } 
        .btn-edit { background-color: #ffc107; color: #212529 !important; } 
        .btn-delete { background-color: #dc3545; } 
    </style>
</head>
<body>


<div class="page-header1">
    <h2>Product List</h2>
</div>

    
<div class="page-header">
    <a href="add_brand.php" class="btn-add btn-brand">+ Add New Brand</a>
    <a href="add_product.php" class="btn-add">+ Add New Product</a>
</div>

<table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Price</th>
        <th>Image</th>
        <th>Brand</th>
        <th>Contact</th>
        <th>Action</th>
    </tr>

    <?php while($row = $result->fetch_assoc()){ ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= $row['name'] ?></td>
        <td>৳ <?= $row['price'] ?></td>
        <td>
            <?php if(!empty($row['product_image'])): ?>
                <img src="uploads/<?= $row['product_image'] ?>" width="60" height="60" class="product-img" alt="<?= $row['name'] ?>">
            <?php else: ?>
                <span>No Image</span>
            <?php endif; ?>
        </td>
        <td><?= $row['brand_name'] ?></td>
        <td><?= $row['contact'] ?></td>
        <td class="action-links">
            <a href="view_single.php?id=<?= $row['id'] ?>" class="btn-view">View</a>
            <a href="update.php?id=<?= $row['id'] ?>" class="btn-edit">Edit</a>
            <a href="delete.php?id=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
        </td>
    </tr>
    <?php } ?>
</table>

</body>
</html>