<?php
include 'db.php';

if(isset($_POST['submit'])){
    $name = $_POST['name'];
    $price = $_POST['price'];
    $brand_id = $_POST['brand_id'];

    $image = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];

    move_uploaded_file($tmp, "uploads/".$image);

    $sql = "INSERT INTO products (name, price, brand_id, product_image)
            VALUES ('$name', '$price', '$brand_id', '$image')";

    if($conn->query($sql)){
        header("Location: view_products.php");
        exit;
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
        .form-wrapper {
            max-width: 480px;
        }
        .form-wrapper h2 {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 24px;
        }
        .form-card {
            background: #ffffff;
            padding: 32px;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.07);
        }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: 600;
            color: #475569;
            font-size: 13px;
        }
        .form-control {
            width: 100%;
            padding: 11px 13px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            color: #334155;
            box-sizing: border-box;
            font-family: inherit;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
        }
        select.form-control {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg' width%3D'12' height%3D'12' viewBox%3D'0 0 12 12'%3E%3Cpath fill%3D'%23475569' d%3D'M6 8L1 3h10z'%2F%3E%3C%2Fsvg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
        }
        .btn-submit {
            width: 100%;
            padding: 13px;
            background-color: #0f172a;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease;
            margin-top: 8px;
            font-family: inherit;
        }
        .btn-submit:hover { background-color: #1e293b; }
        .error-msg {
            background-color: #fee2e2;
            color: #ef4444;
            padding: 10px 14px;
            border-radius: 7px;
            margin-bottom: 18px;
            font-size: 13px;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 16px;
            color: #64748b;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
        }
        .back-link:hover { color: #0f172a; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="form-wrapper">
    <h2>Add New Product</h2>
    <div class="form-card">

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

        <!-- <a href="view_products.php" class="back-link">← Back to Product List</a> -->
    </div>
</div>

</div><!-- .main-content -->
</body>
</html>
