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
            $items[] = [
                'file' => $parts[0],
                'name' => $parts[1],
                'type' => $parts[2],
                'size' => $parts[3],
                'price' => $parts[4] ?? ''
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
    }

    if ($bytes >= 1024) {
        return round($bytes / 1024, 2) . ' KB';
    }

    return $bytes . ' B';
}

if (isset($_POST['btnsubmit'])) {
    $price = trim($_POST['price'] ?? '');

    if (!isset($_FILES['f']) || $_FILES['f']['error'] === UPLOAD_ERR_NO_FILE) {
        $_SESSION['upload_message'] = "Please select an image.";
    } elseif ($price === '' || !is_numeric($price) || $price < 0) {
        $_SESSION['upload_message'] = "Please enter a valid price.";
    } else {
        $originalName = basename($_FILES['f']['name']);
        $tmp = $_FILES['f']['tmp_name'];
        $size = (int) $_FILES['f']['size'];
        $mimeType = $_FILES['f']['type'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $maxSize = 4000 * 1024;
        $allowedTypes = ['jpg', 'jpeg', 'png'];

        if ($size > $maxSize) {
            $_SESSION['upload_message'] = "Image is too large. Max 4000 KB allowed.";
        } elseif (!in_array($ext, $allowedTypes, true)) {
            $_SESSION['upload_message'] = "Only JPG, JPEG, and PNG images are allowed.";
        } else {
            $newFileName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
            $path = $imgDir . '/' . $newFileName;

            if (move_uploaded_file($tmp, $path)) {
                $line = $newFileName . '|' . $originalName . '|' . $mimeType . '|' . $size . '|' . $price . PHP_EOL;
                file_put_contents($metaFile, $line, FILE_APPEND | LOCK_EX);
                $_SESSION['upload_message'] = "Image added successfully.";
            } else {
                $_SESSION['upload_message'] = "Upload failed.";
            }
        }
    }

    header('Location: file_uplode.php');
    exit();
}

$items = readImageInfo($metaFile);
$items = array_reverse($items);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f2f2f2;
        }

        .container {
            width: 90%;
            margin: 30px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .logout {
            background: #dc3545;
            color: white;
            padding: 8px 14px;
            text-decoration: none;
            border-radius: 5px;
        }

        form {
            margin-bottom: 20px;
            padding: 15px;
            background: #fafafa;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        input {
            padding: 9px;
            margin: 6px;
        }

        input[type="number"] {
            width: 160px;
        }

        input[type="submit"] {
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .message {
            padding: 10px;
            background: #e9ecef;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        th {
            background: #333;
            color: white;
        }

        img {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="top">
        <h2>Admin Panel</h2>
        <a class="logout" href="logout.php">Logout</a>
    </div>

    <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></p>

    <?php if ($message !== "") { ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php } ?>

    <form method="post" enctype="multipart/form-data">
        <label>Image:</label>
        <input type="file" name="f" accept=".jpg,.jpeg,.png" required>

        <label>Price:</label>
        <input type="number" name="price" placeholder="Enter price" min="0" step="0.01" required>

        <input type="submit" name="btnsubmit" value="Add">
    </form>

    <table>
        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Image Name</th>
            <th>Type</th>
            <th>Size</th>
            <th>Price</th>
        </tr>

        <?php if (count($items) > 0) { ?>
            <?php $id = 1; ?>
            <?php foreach ($items as $item) { ?>
                <tr>
                    <td><?php echo $id++; ?></td>
                    <td>
                        <img src="img/<?php echo htmlspecialchars($item['file']); ?>" alt="Image">
                    </td>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo htmlspecialchars($item['type']); ?></td>
                    <td><?php echo htmlspecialchars(formatSize($item['size'])); ?></td>
                    <td><?php echo htmlspecialchars($item['price'] === '' ? 'N/A' : $item['price']); ?></td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="6">No data found.</td>
            </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
