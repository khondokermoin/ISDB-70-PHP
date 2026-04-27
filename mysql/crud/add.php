<?php
// ১. ডাটাবেস কানেকশন সেটআপ
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "crud";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ২. ফর্ম সাবমিট হলে কী ঘটবে তা নির্ধারণ করা
if (isset($_POST['submit'])) {
    
    $did = NULL; 
    $dname = $_POST['name'];
    $daddress = $_POST['address'];
    $dclass = $_POST['class'];
    $dphpne = $_POST['phone'];

    // ৩. dstudent Stored Procedure কল করা
    /* $sql = "CALL dstudent('$did', '$dname', '$daddress', '$dclass', '$dphpne')"; */

    $sql = "INSERT INTO student (sname, saddress, sclass, sphpne) 
        VALUES ('$dname', '$daddress', '$dclass', '$dphpne')";

    if (mysqli_query($conn, $sql)) {
        header("Location: index.php");
        exit();
    } else {
        $error_message = "Error saving data: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD - Add Record</title>
    <style>
        /* আপনার দেওয়া সম্পূর্ণ CSS */
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
        .header { background-color: #bc761a; color: white; text-align: center; padding: 15px 0; font-size: 32px; font-style: italic; font-weight: bold; }
        .navbar { background-color: #333; overflow: hidden; }
        .navbar a { float: left; display: block; color: white; text-align: center; padding: 12px 20px; text-decoration: none; font-weight: bold; font-size: 14px; }
        .navbar a:hover { background-color: #555; }
        .container { width: 80%; margin: 20px auto; background-color: white; padding: 20px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #111; }
        
        /* Form Specific Styles */
        .form-wrapper { background-color: #f2f2f2; padding: 30px; width: 50%; margin: 0 auto; border-radius: 5px; }
        .form-group { display: flex; align-items: center; margin-bottom: 15px; }
        .form-group label { width: 100px; font-weight: bold; color: #000; }
        .form-group input[type="text"], .form-group select { flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 3px; }
        .btn-save { background-color: #333; color: white; padding: 8px 20px; border: none; font-weight: bold; cursor: pointer; margin-left: 100px; }
        .btn-save:hover { background-color: #555; }
        .error-msg { color: red; text-align: center; margin-bottom: 15px; font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">CRUD</div>
    <div class="navbar">
        <a href="inde.php">HOME</a>
        <a href="add.php">ADD</a>
        <a href="./update.php">UPDATE</a>
        <a href="./delete.php">DELETE</a>
    </div>

    <div class="container">
        <h2>Add New Record</h2>
        
        <?php if(isset($error_message)) { echo "<div class='error-msg'>$error_message</div>"; } ?>
        
        <div class="form-wrapper">
            <form action="" method="POST">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" required>
                </div>
                
                <div class="form-group">
                    <label for="class">Class</label>
                    <select id="class" name="class" required>
                        <option value="" disabled selected>Select Class</option>
                        <option value="BBA">BBA</option>
                        <option value="CSE">CSE</option>
                        <option value="EEE">EEE</option>
                        <option value="LAW">LAW</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" required>
                </div>
                
                <button type="submit" class="btn-save" name="submit">SAVE</button>
            </form>
        </div>
    </div>

</body>
</html>