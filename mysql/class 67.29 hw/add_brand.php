<?php
include 'db.php';

if(isset($_POST['submit'])){
    $name = $_POST['name'];
    $contact = $_POST['contact'];

    $sql = "INSERT INTO brand (name, contact) VALUES ('$name', '$contact')";
    
    if($conn->query($sql)){
        header("Location: add_product.php");
        exit; // Always exit after a header redirect
    } else {
        $error_message = "Error adding brand: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Brand</title>
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
            max-width: 400px;
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
        .btn-submit {
            width: 100%;
            padding: 14px;
            background-color: #0f172a; 
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
        <h2>Add New Brand</h2>
    </div>

    <?php if(isset($error_message)): ?>
        <div class="error-msg"><?= $error_message ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="name">Brand Name</label>
            <input type="text" id="name" name="name" class="form-control" placeholder="Enter brand name" required>
        </div>

        <div class="form-group">
            <label for="contact">Contact Info</label>
            <input type="text" id="contact" name="contact" class="form-control" placeholder="Enter contact details" required>
        </div>

        <button type="submit" name="submit" class="btn-submit">Add Brand</button>
    </form>

    <a href="add_product.php" class="back-link">← Back to Add Product</a>
</div>

</body>
</html>