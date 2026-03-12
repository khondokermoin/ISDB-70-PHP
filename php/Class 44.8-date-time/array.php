<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Initialize an empty array
    $numbers = [];

    // Get input from form
    $input = $_POST['numbers']; // e.g., "1,2,3,4,5"

    // Convert comma-separated string into an array
    $numbers = explode(",", $input);

    echo "You entered these numbers:<br>";
    foreach ($numbers as $num) {
        echo htmlspecialchars($num) . "<br>";
    }
}
?>

<form method="post">
    Enter numbers separated by commas: <input type="text" name="numbers">
    <input type="submit" value="Submit">
</form>