<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload</title>
</head>

<?php 
if (isset($_POST['btnsubmit'])) {

    $fileName = $_FILES['f']['name'];       
    $tmp = $_FILES['f']['tmp_name'];
    $size = $_FILES['f']['size'];
    $typ = pathinfo($fileName, PATHINFO_EXTENSION);
    $path = "img/" . $fileName;            
    $kb = $size /1024;

    if($kb >400){
        echo "File is to large";
    }
    elseif ($_FILES['f']['error'] == 0) {        
        move_uploaded_file($tmp, $path);
        echo "File uploaded successfully";
       echo "<img src='$path' width='150'><br>";
    } else {
        echo "Upload failed";
    }
}
?>

<body>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="f">
        <input type="submit" name="btnsubmit">
    </form>
</body>
</html>