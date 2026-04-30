<?php
session_start();
if (!isset($_SESSION['username'])) { header('Location: index.php'); exit(); }
include 'db.php';

// Check if ID is provided in the URL
if(isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Fetch product and brand details using JOIN
    $sql = "SELECT products.*, brand.name AS brand_name, brand.contact 
            FROM products 
            JOIN brand ON products.brand_id = brand.id 
            WHERE products.id = $id";
            
    $result = $conn->query($sql);
    
    if($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "<h3 style='text-align:center; color:red;'>Product not found!</h3>";
        exit;
    }
} else {
    // Redirect back if no ID is passed
    header("Location: view_products.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Product - <?= htmlspecialchars($product['name']) ?></title>
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            width: 420px;
            max-width: 90%;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-image {
            width: 100%;
            height: 280px;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid #eee;
        }
        .card-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }
        .card-body {
            padding: 25px;
        }
        .product-title {
            font-size: 24px;
            font-weight: 700;
            color: #2b2b2b;
            margin: 0 0 10px;
        }
        .product-price {
            font-size: 22px;
            font-weight: 600;
            color: #10b981; /* Modern Emerald Green */
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 15px;
        }
        .info-row:last-of-type {
            border-bottom: none;
        }
        .info-label {
            color: #64748b;
            font-weight: 500;
        }
        .info-value {
            color: #334155;
            font-weight: 600;
            text-align: right;
        }
        .btn-back {
            display: block;
            width: 100%;
            text-align: center;
            background-color: #1e293b;
            color: #ffffff;
            text-decoration: none;
            padding: 12px;
            border-radius: 8px;
            margin-top: 20px;
            font-weight: 600;
            transition: background-color 0.2s ease;
            box-sizing: border-box;
        }
        .btn-back:hover {
            background-color: #0f172a;
        }
        .no-image {
            color: #94a3b8;
            font-style: italic;
        }
    </style>
</head>
<body>

    <div class="card">
        <div class="card-image">
            <?php if(!empty($product['product_image'])): ?>
                <img src="uploads/<?= $product['product_image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <?php else: ?>
                <span class="no-image">No Image Available</span>
            <?php endif; ?>
        </div>
        
        <div class="card-body">
            <h2 class="product-title"><?= htmlspecialchars($product['name']) ?></h2>
            <div class="product-price">৳ <?= htmlspecialchars($product['price']) ?></div>
            
            <div class="info-row">
                <span class="info-label">Product ID</span>
                <span class="info-value">#<?= $product['id'] ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Brand</span>
                <span class="info-value"><?= htmlspecialchars($product['brand_name']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Brand Contact</span>
                <span class="info-value"><?= htmlspecialchars($product['contact']) ?></span>
            </div>
            
            <a href="view_products.php" class="btn-back">← Back to Products</a>
        </div>
    </div>

</body>
</html>