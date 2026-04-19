<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "crud"; 

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// [FIX]: dstudent() এর বদলে GetAllStudents() কল করতে হবে
$sql = "CALL GetAllStudents()"; 
$result = mysqli_query($conn, $sql);

// যদি error চেক করতে চান, তাহলে নিচের লাইনটা সাময়িকভাবে ব্যবহার করতে পারেন
if (!$result) {
    echo "Error: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD - All Records</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
        .header { background-color: #bc761a; color: white; text-align: center; padding: 15px 0; font-size: 32px; font-style: italic; font-weight: bold; }
        .navbar { background-color: #333; overflow: hidden; }
        .navbar a { float: left; display: block; color: white; text-align: center; padding: 12px 20px; text-decoration: none; font-weight: bold; font-size: 14px; }
        .navbar a:hover { background-color: #555; }
        .container { width: 80%; margin: 20px auto; background-color: white; padding: 20px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #111; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #555; }
        th, td { padding: 10px; text-align: left; }
        th { background-color: #333; color: white; }
        .btn { padding: 5px 10px; color: white; border: none; cursor: pointer; font-weight: bold; border-radius: 3px; text-decoration: none; display: inline-block; }
        .btn-edit { background-color: #f39c12; }
        .btn-delete { background-color: #e74c3c; }
    </style>
</head>
<body>

    <div class="header">CRUD</div>
    <div class="navbar">
        <a href="index.php">HOME</a>
        <a href="add.php">ADD</a>
        <a href="update.php">UPDATE</a>
        <a href="delete.php">DELETE</a>
    </div>

    <div class="container">
        <h2>All Records</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>NAME</th>
                    <th>ADDRESS</th>
                    <th>CLASS</th>
                    <th>PHONE</th>
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // যদি কুয়েরি ঠিকমতো রান করে এবং ডেটা থাকে
                if ($result && mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        ?>
                        <tr>
                            <td><?php echo $row['sid']; ?></td>
                            <td><?php echo $row['sname']; ?></td>
                            <td><?php echo $row['saddress']; ?></td>
                            <td><?php echo $row['sclass']; ?></td> 
                            <td><?php echo $row['sphpne']; ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo $row['sid']; ?>" class="btn btn-edit">EDIT</a>
                                <a href="delete-inline.php?id=<?php echo $row['sid']; ?>" class="btn btn-delete">DELETE</a>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center;'>No Records Found</td></tr>";
                }
                mysqli_close($conn);
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>