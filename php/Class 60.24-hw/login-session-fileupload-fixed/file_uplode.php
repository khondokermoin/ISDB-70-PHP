<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$message = "";

if (isset($_SESSION['upload_message'])) {
    $message = $_SESSION['upload_message'];
    unset($_SESSION['upload_message']);
}

$imgDir = __DIR__ . '/img';
$metaFile = __DIR__ . '/image_data.txt';

if (!is_dir($imgDir)) {
    mkdir($imgDir, 0777, true);
}

if (!file_exists($metaFile)) {
    file_put_contents($metaFile, '');
}

function readImageInfo($metaFile)
{
    $items = [];

    if (!file_exists($metaFile)) {
        return $items;
    }

    $lines = file($metaFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $parts = explode('|', $line);

        if (count($parts) >= 4) {
            $items[$parts[0]] = [
                'name' => $parts[1],
                'type' => $parts[2],
                'size' => $parts[3]
            ];
        }
    }

    return $items;
}

function formatSize($bytes)
{
    $bytes = (int)$bytes;

    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return round($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' B';
    }
}

if (isset($_POST['btnsubmit'])) {
    if (!isset($_FILES['f']) || $_FILES['f']['error'] === UPLOAD_ERR_NO_FILE) {
        $_SESSION['upload_message'] = "Please select a file.";
    } else {
        $originalName = basename($_FILES['f']['name']);
        $tmp = $_FILES['f']['tmp_name'];
        $size = (int) $_FILES['f']['size'];
        $mimeType = $_FILES['f']['type'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $maxSize = 4000 * 1024;
        $allowedTypes = ['jpg', 'jpeg', 'png'];

        if ($size > $maxSize) {
            $_SESSION['upload_message'] = "File is too large. Max 4000 KB allowed.";
        } elseif (!in_array($ext, $allowedTypes, true)) {
            $_SESSION['upload_message'] = "Only JPG, JPEG, and PNG files are allowed.";
        } else {
            $newFileName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
            $path = $imgDir . '/' . $newFileName;

            if (move_uploaded_file($tmp, $path)) {
                $line = $newFileName . '|' . $originalName . '|' . $mimeType . '|' . $size . PHP_EOL;
                file_put_contents($metaFile, $line, FILE_APPEND | LOCK_EX);
                $_SESSION['upload_message'] = "File uploaded successfully.";
            } else {
                $_SESSION['upload_message'] = "Upload failed.";
            }
        }
    }

    header('Location: file_uplode.php');
    exit();
}

$files = glob($imgDir . '/*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);
rsort($files);
$imageInfo = readImageInfo($metaFile);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #ddd;
        }

        .container {
            margin: 25px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
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
            gap: 15px;
            margin-top: 15px;
        }

        .item {
            width: 180px;
            background: #fafafa;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
        }

        .item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border: 1px solid #ccc;
            background: white;
            margin-bottom: 8px;
        }

        .info {
            font-size: 13px;
            line-height: 1.6;
            word-break: break-word;
        }
    </style>
</head>
<body>
<?php require_once 'navbar.php'; ?>

<div class="container">
    <div class="top-bar">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
    </div>

    <div class="card">
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="f" accept=".jpg,.jpeg,.png" required>
            <input type="submit" name="btnsubmit" value="Upload">
        </form>

        <?php if ($message !== "") { ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php } ?>
    </div>

    <div class="card">
        <h3>Gallery</h3>
        <?php if ($files) { ?>
            <div class="gallery">
                <?php foreach ($files as $file) { ?>
                    <?php $savedName = basename($file); ?>
                    <?php
                    $meta = $imageInfo[$savedName] ?? [
                        'name' => $savedName,
                        'type' => mime_content_type($file),
                        'size' => filesize($file)
                    ];
                    ?>
                    <div class="item">
                        <img src="img/<?php echo htmlspecialchars($savedName); ?>" alt="Uploaded Image">
                        <div class="info">
                            name: <?php echo htmlspecialchars($meta['name']); ?><br>
                            type: <?php echo htmlspecialchars((string)$meta['type']); ?><br>
                            size: <?php echo htmlspecialchars(formatSize($meta['size'])); ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <p>No images uploaded yet.</p>
        <?php } ?>
    </div>
</div>
</body>
</html>