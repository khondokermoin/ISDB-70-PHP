<?php
session_start();

// check login
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload</title>
    <style>
        .gallery img {
            width: 150px;
            margin: 5px;
            border: 1px solid #ccc;
        }
    </style>
</head>

<body>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="f">
        <input type="submit" name="btnsubmit">
    </form>

    <a href="logout.php">Logout</a>
</body>

<?php 
if (isset($_POST['btnsubmit'])) {

    $fileName = $_FILES['f']['name'];       
    $tmp = $_FILES['f']['tmp_name'];
    $size = $_FILES['f']['size'];
    $typ = pathinfo($fileName, PATHINFO_EXTENSION);
    $path = "img/" . $fileName;            
    $kb = $size /1024;
    $maxSize = 400*1024;

    if($kb >400){
        echo "File is too large";
    }
    elseif (($typ == "jpg" || $typ == "png") && ($size<=$maxSize)) {        
        move_uploaded_file($tmp, $path);
        echo "File uploaded successfully";
        // echo "<img src='$path' width='150'><br>";
    } else {
        echo "Upload failed";
    }

    // ----------- Gallery Feature -----------
    $files = glob("img/*.{jpg,png}", GLOB_BRACE); 
    if($files){
        echo "<h3>Gallery:</h3><div class='gallery'>";
        foreach($files as $file){
            echo "<img src='$file'>";
        }
        echo "</div>";
    }
}
?>



</html>