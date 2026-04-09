<?php

if ($_FILES) {

    $files = [];

    foreach ($_FILES['files']['name'] as $i => $name) {

        $file = [
            "name" => $name,
            "type" => $_FILES['files']['type'][$i],
            "size" => $_FILES['files']['size'][$i],
            "tmp_name" => $_FILES['files']['tmp_name'][$i],
            "error" => $_FILES['files']['error'][$i],
            "full_path" => "uploads/" . $name
        ];

        // save into array
        $files[] = $file;

        // move file
        move_uploaded_file($file["tmp_name"], $file["full_path"]);
    }

    echo "<pre>";
    print_r($files);
    echo "</pre>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>File Upload</title>
</head>
<body>

<form method="post" enctype="multipart/form-data">
    <input type="file" name="files[]" multiple>
    <button type="submit">Upload</button>
</form>

</body>
</html>