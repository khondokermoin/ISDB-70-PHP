<?php
$db = new mysqli("localhost", "root", "", "erp_evidence");

if(isset($_POST['btnManufacturer'])){
	$mname = $_POST['m_name'];
	$maddress = $_POST['m_address'];
	$mnumbers = $_POST['m_numbers'];
	$db->query(" call add_manufacturer('$mname','$maddress','$mnumbers') ");
}

if (isset($_POST['addProduct'])) {
    $pname = $_POST['p_name'];
    $pprice = $_POST['p_price'];
    $m_brand_id = $_POST['m_brand_id'];

    $db->query(" call add_product('$pname','$pprice','$m_brand_id') ");
    /* $db->query("INSERT INTO product (p_name, p_price, m_brand_id) 
                VALUES ('$pname', '$pprice', '$m_brand_id')"); */
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP Simple Form</title>
</head>
<body>

    <h2>Add Manufacturer</h2>
    <form action="" method="post">
        <label>Manufacturer Name:</label><br>
        <input type="text" name="m_name"><br><br>

        <label>Address:</label><br>
        <input type="text" name="m_address"><br><br>

        <label>Contact Number:</label><br>
        <input type="text" name="m_numbers"><br><br>

        <input type="submit" name="btnManufacturer" value="Add Manufacturer">
    </form>

    <hr>

    <h2>Add Product</h2>
    <form method="post">
        <label>Product Name:</label><br>
        <input type="text" name="p_name"><br><br>

        <label>Product Price:</label><br>
        <input type="text" name="p_price"><br><br>

        <label>Manufacturer Name:</label><br>
        <select name="m_brand_id">
            <option value="">Select Manufacturer</option>

            <?php
            $result = $db->query("SELECT * FROM manufacturer");

            while (list($_mid,$_mname,$_maddress)= $result->fetch_row()){
                echo "<option value = '$_mid'>$_mname</option>";
            }
            ?>
        </select><br><br>

        <input type="submit" name="addProduct" value="Add Product">
    </form>

</body>
</html>