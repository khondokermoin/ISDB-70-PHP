<?php
$conn = mysqli_connect("localhost", "root", "", "crud");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$student = null;
if(isset($_GET['id'])){
    $oid = $_GET['id'];
    $sql = "SELECT * FROM student WHERE sid = $oid";
    $result = mysqli_query($conn, $sql);
     $student = mysqli_fetch_assoc($result);


}

// SHOW button
if (isset($_POST['show'])) {
    $id = $_POST['sid'];

    $sql = "SELECT * FROM student WHERE sid = $id";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $student = mysqli_fetch_assoc($result);
    } else {
        echo "<p style='color:red; text-align:center;'>No record found!</p>";
    }
}

// UPDATE button
if (isset($_POST['update'])) {

    $id = $_POST['sid'];
    $name = $_POST['sname'];
    $address = $_POST['saddress'];
    $class = $_POST['sclass'];
    $phone = $_POST['sphpne'];

    $sql = "UPDATE student SET 
            sname='$name',
            saddress='$address',
            sclass='$class',
            sphpne='$phone'
            WHERE sid=$id";

    if (mysqli_query($conn, $sql)) {
        header("Location:inde.php");
        /* echo "<p style='color:green; text-align:center;'>Record Updated Successfully!</p>"; */
    } else {
        echo "<p style='color:red; text-align:center;'>Error: " . mysqli_error($conn) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Record</title>

<style>
body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; }
.header { background-color: #bc761a; color: white; text-align: center; padding: 15px 0; font-size: 32px; font-style: italic; font-weight: bold; }
.navbar { background-color: #333; overflow: hidden; }
.navbar a { float: left; display: block; color: white; text-align: center; padding: 12px 20px; text-decoration: none; font-weight: bold; }
.navbar a:hover { background-color: #555; }
.container { width: 80%; margin: 20px auto; background-color: white; padding: 20px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); min-height: 400px; }
.form-wrapper { background-color: #f2f2f2; padding: 30px; width: 50%; margin: 0 auto; border-radius: 5px; }
.form-group { display: flex; align-items: center; margin-bottom: 15px; }
.form-group label { width: 100px; font-weight: bold; }
.form-group input, .form-group select { flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 3px; }
.btn-action { background-color: #333; color: white; padding: 8px 20px; border: none; font-weight: bold; cursor: pointer; margin-left: 100px; border-radius: 3px; }
.divider { border-bottom: 1px solid #e0e0e0; margin: 25px 0; }
</style>

</head>
<body>

<div class="header">CRUD</div>

<div class="navbar">
    <a href="inde.php">HOME</a>
    <a href="add.php">ADD</a>
    <a href="update.php">UPDATE</a>
    <a href="delete.php">DELETE</a>
</div>

<div class="container">
    <h2>Edit Record</h2>

    <div class="form-wrapper">

        <!-- SHOW FORM -->
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <div class="form-group">
                <label>ID</label>
                <input type="text" name="sid" value="<?php echo $student['sid'] ?? ''; ?>" required>
            </div>
            <button class="btn-action" name="show">SHOW</button>
        </form>

        <div class="divider"></div>

        <!-- UPDATE FORM -->
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">

            <input type="hidden" name="sid" value="<?php echo $student['sid'] ?? ''; ?>">

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="sname" value="<?php echo $student['sname'] ?? ''; ?>">
            </div>

            <div class="form-group">
                <label>Address</label>
                <input type="text" name="saddress" value="<?php echo $student['saddress'] ?? ''; ?>">
            </div>

            <div class="form-group">
                <label>Class</label>
                <select name="sclass">
                    <option value="">Select</option>
                    <option value="BCA" <?php if(($student['sclass'] ?? '')=='BCA') echo 'selected'; ?>>BCA</option>
                    <option value="BCOM" <?php if(($student['sclass'] ?? '')=='BCOM') echo 'selected'; ?>>BCOM</option>
                    <option value="BSC" <?php if(($student['sclass'] ?? '')=='BSC') echo 'selected'; ?>>BSC</option>
                </select>
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="sphpne" value="<?php echo $student['sphpne'] ?? ''; ?>">
            </div>

            <button class="btn-action" name="update">UPDATE</button>

        </form>

    </div>
</div>

</body>
</html>