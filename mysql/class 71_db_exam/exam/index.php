<?php
// Database configuration based on the provided SQL dump
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "exam_database";

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Handle Manufacturer Insertion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_manufacturer'])) {
    $m_name = $_POST['m_name'];
    $m_address = $_POST['m_address'];
    $m_contact = $_POST['m_contact'];

    // Call the stored procedure insert_manufacturer
    $stmt = $conn->prepare("CALL insert_manufacturer(?, ?, ?)");
    $stmt->bind_param("sss", $m_name, $m_address, $m_contact);
    
    if ($stmt->execute()) {
        $message = "<p style='color: green;'>Manufacturer added successfully!</p>";
    } else {
        $message = "<p style='color: red;'>Error adding manufacturer: " . $conn->error . "</p>";
    }
    $stmt->close();
    // REDIRECT to the same page to clear POST data
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle Product Insertion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    $p_name = $_POST['p_name']; // Note: Database schema defines this as int(50)
    $p_price = $_POST['p_price'];
    $p_manufacturer_id = $_POST['p_manufacturer_id'];

    // Call the stored procedure insert_product
    $stmt = $conn->prepare("CALL insert_product(?, ?, ?)");
    $stmt->bind_param("sii", $p_name, $p_price, $p_manufacturer_id);
    
    if ($stmt->execute()) {
        $message = "<p style='color: green;'>Product added successfully!</p>";
    } else {
        $message = "<p style='color: red;'>Error adding product: " . $conn->error . "</p>";
    }
    $stmt->close();
    // REDIRECT to the same page to clear POST data
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// --- NEW DELETE LOGIC ADDED HERE ---
// Handle Manufacturer Deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_manufacturer'])) {
    $del_m_id = $_POST['del_m_id'];

    // Delete query - The MySQL trigger will automatically handle deleting the linked products
    $stmt = $conn->prepare("DELETE FROM manufacturer WHERE id = ?");
    $stmt->bind_param("i", $del_m_id);
    
    if ($stmt->execute()) {
        $message = "<p style='color: green;'>Manufacturer and associated products deleted successfully!</p>";
    } else {
        $message = "<p style='color: red;'>Error deleting manufacturer: " . $conn->error . "</p>";
    }
    $stmt->close();
}
// -----------------------------------
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Database Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-container { margin-bottom: 30px; padding: 15px; border: 1px solid #ccc; background-color: #f9f9f9; }
        .form-group { margin-bottom: 10px; }
        label { display: inline-block; width: 150px; }
        input[type="text"], input[type="number"], select { padding: 5px; width: 200px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        /* Added a small style for the delete button */
        .btn-danger { background-color: #ff4d4d; color: white; border: 1px solid #cc0000; padding: 5px 10px; cursor: pointer; }
        .btn-danger:hover { background-color: #cc0000; }
    </style>
</head>
<body>

    <h1>Exam Database System</h1>
    <?= $message ?>

    <div class="form-container">
        <h2>Add New Manufacturer</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="m_name">Name:</label>
                <input type="text" id="m_name" name="m_name" required>
            </div>
            <div class="form-group">
                <label for="m_address">Address:</label>
                <input type="text" id="m_address" name="m_address" required>
            </div>
            <div class="form-group">
                <label for="m_contact">Contact No:</label>
                <input type="text" id="m_contact" name="m_contact" required>
            </div>
            <button type="submit" name="add_manufacturer">Add Manufacturer</button>
        </form>
    </div>

    <div class="form-container">
        <h2>Add New Product</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="p_name">Product Name:</label>
                <input type="text" id="p_name" name="p_name" required> 
            </div>
            <div class="form-group">
                <label for="p_price">Price:</label>
                <input type="number" id="p_price" name="p_price" required>
            </div>
            <div class="form-group">
                <label for="p_manufacturer_id">Manufacturer:</label>
                <select id="p_manufacturer_id" name="p_manufacturer_id" required>
                    <option value="">Select Manufacturer</option>
                    <?php
                    // Fetch manufacturers for the dropdown
                    $m_result = $conn->query("SELECT id, name FROM manufacturer");
                    while ($m_row = $m_result->fetch_assoc()) {
                        echo "<option value='" . $m_row['id'] . "'>" . htmlspecialchars($m_row['name']) . " </option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" name="add_product">Add Product</button>
        </form>
    </div>

    <!-- --- NEW DELETE FORM ADDED HERE --- -->
    <div class="form-container" style="border-color: #ffcccc; background-color: #fff0f0;">
        <h2 style="color: #cc0000;">Delete Manufacturer</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="del_m_id">Select Manufacturer:</label>
                <select id="del_m_id" name="del_m_id" required>
                    <option value="">Select Manufacturer</option>
                    <?php
                    // Fetch manufacturers for the dropdown
                    $m_result = $conn->query("SELECT id, name FROM manufacturer");
                    while ($m_row = $m_result->fetch_assoc()) {
                        echo "<option value='" . $m_row['id'] . "'>" . htmlspecialchars($m_row['name']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" name="delete_manufacturer" class="btn-danger">Delete Manufacturer</button>
        </form>
        <p style="font-size: 12px; color: #666; margin-top: 10px;">*Note: Deleting a manufacturer will automatically delete all products linked to them via the database trigger.</p>
    </div>
    <!-- ---------------------------------- -->

    <h2>Expensive Products View (> 5000)</h2>
    <?php
    // Query the specific view provided in the SQL dump
    $sql_view = "SELECT * FROM vw_expensive_products";
    $result_view = $conn->query($sql_view);

    if ($result_view->num_rows > 0) {
        echo "<table>";
        echo "<tr>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Price</th>
                <th>Manufacturer Name</th>
                <th>Contact No</th>
              </tr>";
        
        while($row = $result_view->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row["p_id"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["p_name"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["p_price"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["m_name"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["m_contact_no"]) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No expensive products found in the view.</p>";
    }

    $conn->close();
    ?>

</body>
</html>