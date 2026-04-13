<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first = $_POST['first'];
    $second = $_POST['second'];
    $third = $_POST['third'];

    if ($first >= $second && $first >= $third) {
        $largest = $first;
    } elseif ($second >= $first && $second >= $third) {
        $largest = $second;
    } else {
        $largest = $third;
    }
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Number Form</title>
</head>
<body>
    <form action="" method="post">
        <label>Enter First Number</label> 
        <input type="number" name="first" required><br><br>

        <label>Enter Second Number</label> 
        <input type="number" name="second" required><br><br>

        <label>Enter Third Number</label> 
        <input type="number" name="third" required><br><br>

        <button type="submit">Submit</button>
    </form>
</body>
</html>
<?php 
echo "<br>";
echo "<br>";
echo "First Number is: ".$first. "<br>";
echo "Second Number is: ".$second. "<br>";
echo " Third Number is: ".$third. "<br>";
echo "<br>";
echo "So Largest number is: " . $largest;
 ?>