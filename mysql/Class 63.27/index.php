<?php
$db = new mysqli("localhost", "root", "", "class_63");

if (isset($_POST['btnDelete'])) {
    $mid = $_POST['m_id'];
    $db->query("DELETE FROM manufacturer WHERE m_id = '$mid'");
}

if(isset($_POST['btnManufacturer'])){
	$mname = $_POST['m_name'];
	$maddress = $_POST['m_address'];
	$mnumbers = $_POST['m_numbers'];
	$db->query(" call add_manufacturers('$mname','$maddress','$mnumbers') ");
}

if (isset($_POST['addProduct'])) {
    $pname = $_POST['p_name'];
    $pprice = $_POST['p_price'];
    $m_brand_id = $_POST['m_brand_id'];

    $db->query(" call add_products('$pname','$pprice','$m_brand_id') ");
}

$filter_price = isset($_POST['price_limit']) ? $_POST['price_limit'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP Simple Form</title>
    <style>
        table { border-collapse: collapse; width: 50%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        
    </style>
</head>
<body>

    <h2>Add Manufacturer</h2>
    <form action="" method="post">
        <label>Manufacturer Name:</label><br>
        <input type="text" name="m_name" required><br><br>
        <label>Address:</label><br>
        <input type="text" name="m_address"><br><br>
        <label>Contact Number:</label><br>
        <input type="text" name="m_numbers"><br><br>
        <input type="submit" name="btnManufacturer" value="Add Manufacturer">
    </form>

    <hr>

<h2>Delete Manufacturer</h2>
<form method="post" onsubmit="return confirm('Are you sure? This will delete the manufacturer and its products!')">
    <table>
        <tr>
            <td rowspan="2"><label for="manufac">Manufacturer</label></td>
            <td>
                <select name="m_id" id="manufac">
                    <option value="">--- Select Manufacturer ---</option>
                    <?php
                    $manufac = $db->query("select * from manufacturer");
                    while (list($_mid, $_mname) = $manufac->fetch_row()) {
                        echo "<option value='$_mid'>$_mname</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            
            <td><input type="submit" name="btnDelete" value="delete" /></td>
        </tr>
    </table>
</form>
<hr>


    <h2>Add Product</h2>
    <form method="post">
        <label>Product Name:</label><br>
        <input type="text" name="p_name" required><br><br>
        <label>Product Price:</label><br>
        <input type="text" name="p_price" required><br><br>
        <label>Manufacturer Name:</label><br>
        <select name="m_brand_id" required>
            <option value="">Select Manufacturer</option>
            <?php
            $result = $db->query("SELECT * FROM manufacturer");
            while (list($_mid,$_mname)= $result->fetch_row()){
                echo "<option value='$_mid'>$_mname</option>";
            }
            ?>
        </select><br><br>
        <input type="submit" name="addProduct" value="Add Product">
    </form>


    <hr>
    <h2>Product View with Price Filter</h2>

    <form method="post">
        <label>Select Minimum Price: </label>
        <select name="price_limit" onchange="this.form.submit()">
            <option value="0">All Products</option>
            <option value="5000" <?php if($filter_price == 5000) echo 'selected'; ?>>Above 5000</option>
            <option value="6000" <?php if($filter_price == 6000) echo 'selected'; ?>>Above 6000</option>
            <option value="7000" <?php if($filter_price == 7000) echo 'selected'; ?>>Above 7000</option>
        </select>
    </form>

    <table>
        <tr>
            <th>ID</th>
            <th>Product Name</th>
            <th>Price</th>
            <th>Manufacturer</th>
            <th>Contact</th>
        </tr>

        <?php
        if ($filter_price > 0) {
            $sql = "SELECT * FROM high_price_products WHERE p_price > $filter_price";
        } else {
            $sql = "SELECT * FROM high_price_products";
        }

        $result = $db->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['p_id']}</td>
                        <td>{$row['p_name']}</td>
                        <td>{$row['p_price']}</td>
                        <td>{$row['m_name']}</td>
                        <td>{$row['m_contact_no']}</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No products found!</td></tr>";
        }
        ?>
    </table>

</body>
</html>
