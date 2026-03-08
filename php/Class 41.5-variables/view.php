<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>

    <form action="#" method="post">
        <label for="">Enter Name:</label><br>
        <input type="text" name="uname"><br><br>

        <input type="submit" value="submit">
    </form>
    <?php
    if (isset($_REQUEST['uname'])) {
        echo "Entered Name: " . htmlspecialchars($_REQUEST['uname']);
    }
    ?>
</body>

</html>