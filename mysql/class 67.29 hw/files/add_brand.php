<?php
include 'db.php';

if(isset($_POST['submit'])){
    $name = $_POST['name'];
    $contact = $_POST['contact'];

    $sql = "INSERT INTO brand (name, contact) VALUES ('$name', '$contact')";
    
    if($conn->query($sql)){
        header("Location: add_product.php");
        exit;
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
        .form-wrapper {
            max-width: 440px;
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
    <h2>Add New Brand</h2>
    <div class="form-card">

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

        <!-- <a href="add_product.php" class="back-link">← Back to Add Product</a> -->
    </div>
</div>

</div><!-- .main-content -->
</body>
</html>
