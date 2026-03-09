<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <!-- $_REQUEST,$_GET,$_POST -->

    <?php
    // echo $_GET['n'];
    echo $_POST['n'];
    // echo $_REQUEST['n'];
    $n = 50;

    if (isset($n)) {
        echo "this is live";
    } else {
        echo "this is not live";
    }

    ?>

    <form action="" method="post">
        <label for="">Name </label>
        <input type="text" placeholder="enter name" name="n"><input type="submit">

    </form>
</body>

</html>