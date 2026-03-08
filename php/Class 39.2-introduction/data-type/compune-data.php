<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compune data type</title>
</head>

<body>
    <?php
    // Simple Array Example
    $name = array("moin", "khondoker", 3);
    var_dump($name);
    echo "<br>";

    // Short Array Syntax (Modern)
    $colors = ["Red", "Green", "Blue"];
    var_dump($colors);


    //Loop দিয়ে Array Print
    $fruits = ["Apple", "Mango", "Banana"];
    foreach ($fruits as $fruit) {
        echo $fruit . "<br>";
    }

    // object
    class Student2
    {
        public $name = "Lucky";
    }
    $obj2 = new Student2();
    var_dump($obj2)

    ?>
    <script>
        setInterval(function() {
            location.reload();
        }, 1000);
    </script>
</body>

</html>