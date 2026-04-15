<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$message = "";

$imgDir = __DIR__ . '/img';
if (!is_dir($imgDir)) {
    mkdir($imgDir, 0777, true);
}

if (isset($_POST['btnsubmit'])) {
    if (!isset($_FILES['f']) || $_FILES['f']['error'] === UPLOAD_ERR_NO_FILE) {
        $message = "Please select a file.";
    } else {
        $fileName = basename($_FILES['f']['name']);
        $tmp = $_FILES['f']['tmp_name'];
        $size = $_FILES['f']['size'];
        $typ = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $maxSize = 400 * 1024;
        $allowedTypes = ['jpg', 'jpeg', 'png'];

        if ($size > $maxSize) {
            $message = "File is too large. Max 400 KB allowed.";
        } elseif (!in_array($typ, $allowedTypes, true)) {
            $message = "Only JPG, JPEG, and PNG files are allowed.";
        } else {
            $newFileName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName);
            $path = $imgDir . '/' . $newFileName;

            if (move_uploaded_file($tmp, $path)) {
                $message = "File uploaded successfully.";
            } else {
                $message = "Upload failed.";
            }
        }
    }
}

$files = glob($imgDir . '/*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .message {
            margin: 15px 0;
            padding: 10px;
            background: #f2f2f2;
            border-radius: 5px;
        }

        .gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .gallery img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 1px solid #ccc;
            /* border-radius: 6px; */
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
        <a href="logout.php">Logout</a>
    </div>

    <form method="post" enctype="multipart/form-data">
        <input type="file" name="f" accept=".jpg,.jpeg,.png" required>
        <input type="submit" name="btnsubmit" value="Upload">
    </form>

    <?php if ($message !== "") { ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php } ?>

    <h3>Gallery</h3>
    <?php if ($files) { ?>
        <div class="gallery">
            <?php foreach ($files as $file) { ?>
                <img src="img/<?php echo htmlspecialchars(basename($file)); ?>" alt="Uploaded Image">
            <?php } ?>
        </div>
    <?php } else { ?>
        <p>No images uploaded yet.</p>
    <?php } ?>
</body>
</html>
