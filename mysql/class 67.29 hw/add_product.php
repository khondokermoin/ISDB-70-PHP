<?php
include 'db.php';

if(isset($_POST['submit'])){
    $name = $_POST['name'];
    $price = $_POST['price'];
    $brand_id = $_POST['brand_id'];

    $image = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];

    // Ensure the uploads directory exists
    move_uploaded_file($tmp, "uploads/".$image);

    $sql = "INSERT INTO products (name, price, brand_id, product_image)
            VALUES ('$name', '$price', '$brand_id', '$image')";

    if($conn->query($sql)){
        header("Location: view_products.php");
        exit; // Always exit after a header redirect
    } else {
        $error_message = "Error adding product: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product</title>
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
        .form-container {
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 450px;
        }
        .form-header {
            margin-bottom: 24px;
            text-align: center;
        }
        .form-header h2 {
            margin: 0;
            color: #1e293b;
            font-size: 24px;
            font-weight: 700;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #475569;
            font-size: 14px;
        }
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 15px;
            color: #334155;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
        select.form-control {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23475569%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.4-12.8z%22%2F%3E%3C%2Fsvg%3E");
            background-repeat: no-repeat;
            background-position: right 12px top 50%;
            background-size: 12px auto;
        }
        input[type="file"].form-control {
            padding: 9px;
            background-color: #f8fafc;
            cursor: pointer;
        }
        input[type="file"]::file-selector-button {
            background-color: #e2e8f0;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            color: #334155;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-right: 10px;
            font-weight: 500;
        }
        input[type="file"]::file-selector-button:hover {
            background-color: #cbd5e1;
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            background-color: #0f172a; /* Deep Developer Dark */
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.1s ease;
            margin-top: 10px;
        }
        .btn-submit:hover {
            background-color: #1e293b;
        }
        .btn-submit:active {
            transform: scale(0.98);
        }
        .error-msg {
            background-color: #fee2e2;
            color: #ef4444;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        .back-link:hover {
            color: #0f172a;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="form-container">
    <div class="form-header">
        <h2>Add New Product</h2>
    </div>

    <?php if(isset($error_message)): ?>
        <div class="error-msg"><?= $error_message ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Product Name</label>
            <input type="text" id="name" name="name" class="form-control" placeholder="Enter product name" required>
        </div>

        <div class="form-group">
            <label for="price">Price (৳)</label>
            <input type="number" id="price" name="price" class="form-control" placeholder="Enter price" required>
        </div>

        <div class="form-group">
            <label for="brand_id">Brand</label>
            <select id="brand_id" name="brand_id" class="form-control" required>
                <option value="" disabled selected>Select a brand</option>
                <?php
                $res = $conn->query("SELECT * FROM brand");
                while($row = $res->fetch_assoc()){
                    echo "<option value='{$row['id']}'>{$row['name']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="image">Product Image</label>
            <input type="file" id="image" name="image" class="form-control" accept="image/*" required>
        </div>

        <button type="submit" name="submit" class="btn-submit">Add Product</button>
    </form>

    <a href="view_products.php" class="back-link">Cancel and return to list</a>
</div>

</body>
</html>