<?php
// ডাটাবেজ কানেকশন
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "moin_crud";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_GET['id'])) {
    $delete_id = $_GET['id'];

    mysqli_query($conn, "DELETE FROM student_infos WHERE id =$delete_id");
}
// টেবিল থেকে ডাটা সিলেক্ট করার কুয়েরি
$sql = "SELECT * FROM student_infos";
$result = mysqli_query($conn, $sql);



?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Document</title>
</head>

<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary">Student Information</h2>
            <!-- আপনার দেওয়া লিঙ্ক অনুযায়ী বাটন -->
            <a href="insert.php" class="btn btn-success">
                + Add Student Info
            </a>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">id</th>
                    <th scope="col">name</th>
                    <th scope="col">contact</th>
                    <th scope="col">action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // ডাটাবেজে ডাটা আছে কিনা চেক করা
                if (mysqli_num_rows($result) > 0) {
                    // লুপের মাধ্যমে ডাটা প্রিন্ট করা
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . $row['name'] . "</td>";
                        echo "<td>" . $row['contact'] . "</td>";
                        echo "<td>
                                <a href='edit.php?id=" . $row['id'] . "' class='btn btn-sm btn-info'>Edit</a>
                                <a href='?id=" . $row['id'] . "' class='btn btn-sm btn-danger'>Delete</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center'>No Data Found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>

<?php
// কানেকশন বন্ধ করা
mysqli_close($conn);
?>