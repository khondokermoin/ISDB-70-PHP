<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Largest Number</title>
</head>
<body>

<form method="post">
    <label>First Number</label>
    <input type="number" name="first" required><br><br>

    <label>Second Number</label>
    <input type="number" name="second" required><br><br>

    <label>Third Number</label>
    <input type="number" name="third" required><br><br>

    <button type="submit">Find Largest</button>
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first = $_POST['first'];
    $second = $_POST['second'];
    $third = $_POST['third'];

    if ($first >= $second && $first >= $third) {
        echo "<h3>Largest number is: $first</h3>";
    } elseif ($second >= $first && $second >= $third) {
        echo "<h3>Largest number is: $second</h3>";
    } else {
        echo "<h3>Largest number is: $third</h3>";
    }
}
?>

</body>
</html>