<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

$message = '';
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
            $items[] = [
                'saved_name' => $parts[0],
                'original_name' => $parts[1],
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
    }
    return $bytes . ' B';
}

if (isset($_POST['btnsubmit'])) {
    if (!isset($_FILES['f']) || $_FILES['f']['error'] === UPLOAD_ERR_NO_FILE) {
        $_SESSION['upload_message'] = 'Please select a file.';
    } else {
        $originalName = basename($_FILES['f']['name']);
        $tmp = $_FILES['f']['tmp_name'];
        $size = (int)$_FILES['f']['size'];
        $mimeType = $_FILES['f']['type'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $maxSize = 4000 * 1024;
        $allowedTypes = ['jpg', 'jpeg', 'png'];

        if ($size > $maxSize) {
            $_SESSION['upload_message'] = 'File is too large. Max 4000 KB allowed.';
        } elseif (!in_array($ext, $allowedTypes, true)) {
            $_SESSION['upload_message'] = 'Only JPG, JPEG, and PNG files are allowed.';
        } else {
            $newFileName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
            $path = $imgDir . '/' . $newFileName;
            if (move_uploaded_file($tmp, $path)) {
                $line = $newFileName . '|' . $originalName . '|' . $mimeType . '|' . $size . PHP_EOL;
                file_put_contents($metaFile, $line, FILE_APPEND | LOCK_EX);
                $_SESSION['upload_message'] = 'File uploaded successfully.';
            } else {
                $_SESSION['upload_message'] = 'Upload failed.';
            }
        }
    }
    header('Location: file_uplode.php');
    exit();
}

$images = readImageInfo($metaFile);
$images = array_reverse($images);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <style>
        body{font-family:Arial,sans-serif;margin:0;background:#ececec}
        .container{padding:25px}
        .card{background:#fff;padding:20px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.08);margin-bottom:20px}
        .message{margin-top:12px;padding:10px;background:#f2f2f2;border-radius:5px}
        input[type=file],input[type=submit]{padding:10px;margin-top:5px}
        table{width:100%;border-collapse:collapse;margin-top:15px;background:#fff}
        th,td{border:1px solid #ddd;padding:10px;text-align:left;vertical-align:middle}
        th{background:#f7f7f7}
        .thumb{width:80px;height:60px;object-fit:cover;border:1px solid #ccc}
        .profile td:first-child{width:180px;font-weight:bold;background:#fafafa}
    </style>
</head>
<body>
<?php require_once 'navbar.php'; ?>
<div class="container">
    <div class="card">
        <h2>Admin Panel</h2>
        <table class="profile">
            <tr><td>ID</td><td><?php echo htmlspecialchars($_SESSION['id']); ?></td></tr>
            <tr><td>Full Name</td><td><?php echo htmlspecialchars($_SESSION['full_name']); ?></td></tr>
            <tr><td>Email</td><td><?php echo htmlspecialchars($_SESSION['email']); ?></td></tr>
            <tr><td>Address</td><td><?php echo htmlspecialchars($_SESSION['address']); ?></td></tr>
            <tr><td>Contact Number</td><td><?php echo htmlspecialchars($_SESSION['contact']); ?></td></tr>
            <tr><td>User Name</td><td><?php echo htmlspecialchars($_SESSION['username']); ?></td></tr>
        </table>
    </div>

    <div class="card">
        <h3>Upload Image</h3>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="f" accept=".jpg,.jpeg,.png" required>
            <input type="submit" name="btnsubmit" value="Upload">
        </form>
        <?php if ($message !== '') { ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php } ?>
    </div>

    <div class="card">
        <h3>Image Table</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Image Name</th>
                <th>Size</th>
            </tr>
            <?php if (!empty($images)) { ?>
                <?php $serial = 1; ?>
                <?php foreach ($images as $row) { ?>
                    <tr>
                        <td><?php echo $serial++; ?></td>
                        <td><img class="thumb" src="img/<?php echo htmlspecialchars($row['saved_name']); ?>" alt="Image"></td>
                        <td><?php echo htmlspecialchars($row['original_name']); ?></td>
                        <td><?php echo htmlspecialchars(formatSize($row['size'])); ?></td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="4">No image uploaded yet.</td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>
</body>
</html>
