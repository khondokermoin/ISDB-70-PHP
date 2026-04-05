<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fibonacci</title>
</head>
<body>

<form method="post">
    <label>Enter how many numbers:</label>
    <input type="number" name="num" required><br><br>
    <button type="submit">Generate</button>
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $n = $_POST['num'];

    $a = 0;
    $b = 1;

    echo "<h3>Fibonacci Series:</h3>";

    for ($i = 0; $i < $n; $i++) {
        echo $a . " ";

        $b = $a + $b;
        $a = $b - $a;
    }
}
?>

</body>
</html>