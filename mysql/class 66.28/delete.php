<?php
include 'db.php'; 

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); 


    $sql = "DELETE FROM students WHERE id = $id";

    if ($conn->query($sql) === TRUE) {

        header("Location: view.php?msg=deleted");
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {

    header("Location: view.php");
    exit();
}

$conn->close();
?>
