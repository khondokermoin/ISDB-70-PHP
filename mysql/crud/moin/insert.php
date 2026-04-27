<?php
$conn = mysqli_connect("localhost", "root", "", "moin_crud");
$message = "";

if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);

    $sql = "INSERT INTO student_infos (name, contact) VALUES ('$name', '$contact')";
    if (mysqli_query($conn, $sql)) {
        $message = "<div class='alert alert-success border-0 shadow-sm'>🎉 Student added successfully!</div>";
        header("Location:view.php");
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student</title>

    <!-- ✅ Correct Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <div class="card shadow-lg border-0 rounded-4">

                    <div class="card-header bg-primary text-white text-center rounded-top-4">
                        <h4 class="mb-0">Add New Student</h4>
                    </div>

                    <div class="card-body p-4">

                        <?php echo $message; ?>

                        <form action="" method="POST">

                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold">Full Name</label>
                                <input type="text" name="name" id="name" class="form-control" placeholder="Enter name" required>
                            </div>

                            <div class="mb-3">
                                <label for="contact" class="form-label fw-semibold">Contact Number</label>
                                <input type="text" name="contact" id="contact" class="form-control" placeholder="Enter phone number" required>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" name="submit" class="btn btn-success btn-lg">
                                    Save Student
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