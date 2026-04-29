<?php
include 'db.php';

// URL থেকে ID নেওয়া হচ্ছে
if(isset($_GET['id'])){
    $id = $_GET['id'];
    $res = $conn->query("SELECT * FROM products WHERE id=$id");
    
    if($res->num_rows > 0){
        $data = $res->fetch_assoc();
    } else {
        echo "<h3 style='text-align:center; color:red;'>Product not found!</h3>";
        exit;
    }
} else {
    header("Location: view_products.php");
    exit;
}

// আপডেট লজিক
if(isset($_POST['update'])){
    $name = $_POST['name'];
    $price = $_POST['price'];
    $brand_id = $_POST['brand_id'];

    $image = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];

    if($image != ""){
        // যদি নতুন ছবি আপলোড করা হয়
        move_uploaded_file($tmp, "uploads/".$image);
        $sql = "UPDATE products 
                SET name='$name', price='$price', brand_id='$brand_id', product_image='$image' 
                WHERE id=$id";
    } else {
        // ছবি পরিবর্তন না করলে আগের ছবিই থাকবে
        $sql = "UPDATE products 
                SET name='$name', price='$price', brand_id='$brand_id' 
                WHERE id=$id";
    }

    if($conn->query($sql)){
        header("Location: view_products.php");
        exit;
    } else {
        $error_message = "Error updating product: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product</title>
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px 0;
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
        .current-img {
            margin: 10px 0;
            display: block;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            background-color: #ffc107; 
            color: #212529;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        .btn-submit:hover {
            background-color: #e0a800;
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
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="form-container">
    <div class="form-header">
        <h2>Update Product</h2>
    </div>

    <?php if(isset($error_message)): ?>
        <div class="error-msg" style="color:red; text-align:center; margin-bottom:10px;"><?= $error_message ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Product Name</label>
            <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($data['name']) ?>" required>
        </div>

        <div class="form-group">
            <label for="price">Price (৳)</label>
            <input type="number" id="price" name="price" class="form-control" value="<?= htmlspecialchars($data['price']) ?>" required>
        </div>

        <div class="form-group">
            <label for="brand_id">Brand</label>
            <select id="brand_id" name="brand_id" class="form-control" required>
                <?php
                $brands = $conn->query("SELECT * FROM brand");
                while($b = $brands->fetch_assoc()){
                    // কারেন্ট ব্র্যান্ডটি সিলেক্টেড থাকবে
                    $selected = ($b['id'] == $data['brand_id']) ? "selected" : "";
                    echo "<option value='{$b['id']}' $selected>{$b['name']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label>Current Image</label>
            <img src="uploads/<?= $data['product_image'] ?>" width="100" class="current-img">
            
            <label for="image">Change Image (Optional)</label>
            <input type="file" id="image" name="image" class="form-control" accept="image/*">
        </div>

        <button type="submit" name="update" class="btn-submit">Update Product</button>
    </form>

    <a href="view_products.php" class="back-link">Cancel and return</a>
</div>

</body>
</html>