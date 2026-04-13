<?php
$result = "";

if (isset($_POST['btnCheck'])) {
    $num = $_POST['number'];
    $isPrime = true;

    if ($num <= 1) {
        $isPrime = false;
    } else {
        for ($i = 2; $i < $num; $i++) {
            if ($num % $i == 0) {
                $isPrime = false;
                break;
            }
        }
    }

    if ($isPrime) {
        $result = "$num is a prime number";
    } else {
        $result = "$num is not a prime number";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Prime Number Check</title>
</head>
<body>
    <form method="post">
        <label for="">Enter Number: </label>
        <input type="number" name="number" required>
        <button type="submit" name="btnCheck">Check</button>
    </form>

    <?php
    if ($result != "") {
        echo "<h3>$result</h3>";
    }
    ?>
</body>
</html>