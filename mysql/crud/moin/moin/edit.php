<?php
$conn = mysqli_connect("localhost", "root", "", "moin_crud");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$message = "";

// 👉 Get ID from URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $result = mysqli_query($conn, "SELECT * FROM student_infos WHERE id=$id");
    $row = mysqli_fetch_assoc($result);
}

// 👉 Update data
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);

    $sql = "UPDATE student_infos SET name='$name', contact='$contact' WHERE id=$id";

    if (mysqli_query($conn, $sql)) {
        header("Location: view.php");
        exit();
    } else {
        $message = "<div class='alert alert-danger'>Update failed!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <div class="card shadow-lg border-0 rounded-4">

                    <div class="card-header bg-warning text-dark text-center rounded-top-4">
                        <h4 class="mb-0">Edit Student</h4>
                    </div>

                    <div class="card-body p-4">

                        <?php echo $message; ?>

                        <form method="POST">

                            <!-- Hidden ID -->
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Full Name</label>
                                <input type="text" name="name" class="form-control"
                                    value="<?php echo $row['name']; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Contact Number</label>
                                <input type="text" name="contact" class="form-control"
                                    value="<?php echo $row['contact']; ?>" required>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" name="update" class="btn btn-warning btn-lg">
                                    Update Student
                                </button>

                                <a href="view.php" class="btn btn-outline-secondary">
                                    Back to List
                                </a>
                            </div>

                        </form>

                    </div>

                </div>

            </div>
        </div>
    </div>

</body>

</html>