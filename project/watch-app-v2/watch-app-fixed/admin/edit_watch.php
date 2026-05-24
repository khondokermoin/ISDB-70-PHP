<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once '../config/db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$flash = null;
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

// GET এবং POST দুটো থেকেই ID নেওয়া
$id = 0;
if (!empty($_POST['watch_id'])) {
    $id = (int)$_POST['watch_id'];
} elseif (!empty($_GET['id'])) {
    $id = (int)$_GET['id'];
}

if (!$id) {
    header("Location: dashboard.php");
    exit;
}

$error   = '';
$success = '';

// ── UPDATE ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {

    $name          = trim($_POST['name']          ?? '');
    $brand         = trim($_POST['brand']         ?? '');
    $model         = trim($_POST['model']         ?? '');
    $buying_price  = (float)($_POST['buying_price']  ?? 0);
    $selling_price = (float)($_POST['selling_price'] ?? 0);
    $quantity      = (int)($_POST['quantity']        ?? 0);
    $description   = trim($_POST['description']   ?? '');

    if ($name === '' || $model === '') {
        $error = 'Watch Name এবং Model Number দিতে হবে।';
    } else {
        try {
            // ── Info update ──
            $stmt = $pdo->prepare("
                UPDATE watches
                SET name          = :name,
                    brand         = :brand,
                    model         = :model,
                    buying_price  = :buying_price,
                    selling_price = :selling_price,
                    quantity      = :quantity,
                    description   = :description
                WHERE id = :id
            ");
            $stmt->execute([
                ':name'          => $name,
                ':brand'         => $brand,
                ':model'         => $model,
                ':buying_price'  => $buying_price,
                ':selling_price' => $selling_price,
                ':quantity'      => $quantity,
                ':description'   => $description,
                ':id'            => $id,
            ]);

            // ── Image upload ──
            $uploadErrors = [];

            if (!empty($_FILES['new_images']['name'][0])) {
                $uploadDir = '../assets/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                $count = count($_FILES['new_images']['name']);

                for ($i = 0; $i < $count; $i++) {
                    $tmpName   = $_FILES['new_images']['tmp_name'][$i];
                    $origName  = $_FILES['new_images']['name'][$i];
                    $fileSize  = $_FILES['new_images']['size'][$i];
                    $errCode   = $_FILES['new_images']['error'][$i];

                    if ($errCode !== UPLOAD_ERR_OK || empty($tmpName)) {
                        if ($errCode !== UPLOAD_ERR_NO_FILE) {
                            $uploadErrors[] = "File #" . ($i + 1) . ": upload error code $errCode";
                        }
                        continue;
                    }

                    // MIME type check
                    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $tmpName);
                    finfo_close($finfo);

                    if (!in_array($mimeType, $allowedTypes)) {
                        $uploadErrors[] = "$origName: unsupported type ($mimeType)";
                        continue;
                    }

                    // Size check (max 5MB)
                    if ($fileSize > 5 * 1024 * 1024) {
                        $uploadErrors[] = "$origName: file too large (max 5MB)";
                        continue;
                    }

                    $ext        = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                    $newName    = uniqid('watch_') . '_' . time() . '_' . $i . '.' . $ext;
                    $uploadPath = $uploadDir . $newName;
                    $dbPath     = 'assets/uploads/' . $newName;

                    if (move_uploaded_file($tmpName, $uploadPath)) {
                        try {
                            $imgStmt = $pdo->prepare(
                                "INSERT INTO watch_images (watch_id, image_url, sort_order)
                                 VALUES (:watch_id, :image_url,
                                     COALESCE((SELECT MAX(sort_order) FROM watch_images wi2 WHERE wi2.watch_id = :watch_id2), 0) + 1
                                 )"
                            );
                            $imgStmt->execute([
                                ':watch_id'  => $id,
                                ':image_url' => $dbPath,
                                ':watch_id2' => $id,
                            ]);
                        } catch (PDOException $e2) {
                            $imgStmt = $pdo->prepare(
                                "INSERT INTO watch_images (watch_id, image_url) VALUES (:watch_id, :image_url)"
                            );
                            $imgStmt->execute([':watch_id' => $id, ':image_url' => $dbPath]);
                        }
                    } else {
                        $uploadErrors[] = "$origName: move_uploaded_file failed";
                    }
                }
            }

            if (!empty($uploadErrors)) {
                $_SESSION['flash'] = [
                    'type'    => 'warning',
                    'message' => 'Info updated! কিন্তু কিছু ছবি upload হয়নি: ' . implode('; ', $uploadErrors),
                ];
            } else {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Watch successfully updated!'];
            }

            header("Location: edit_watch.php?id=$id");
            exit;
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// ── FETCH current data ──
$stmt = $pdo->prepare("SELECT * FROM watches WHERE id = :id");
$stmt->execute([':id' => $id]);
$watch = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$watch) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Watch not found.'];
    header("Location: dashboard.php");
    exit;
}

try {
    $imgStmt = $pdo->prepare("SELECT * FROM watch_images WHERE watch_id = :id ORDER BY sort_order ASC, id ASC");
    $imgStmt->execute([':id' => $id]);
} catch (PDOException $e) {
    $imgStmt = $pdo->prepare("SELECT * FROM watch_images WHERE watch_id = :id ORDER BY id ASC");
    $imgStmt->execute([':id' => $id]);
}
$images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Watch — Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 min-h-screen p-6">

    <div class="max-w-4xl mx-auto bg-white p-8 rounded-xl shadow-sm border border-gray-200 mt-6">

        <div class="flex justify-between items-center mb-8 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Edit Watch Details</h2>
                <p class="text-sm text-gray-500 mt-1">Watch ID: #<?= $id ?></p>
            </div>
            <a href="dashboard.php"
                class="text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-md transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back
            </a>
        </div>

        <?php if ($flash): ?>
            <?php
            $flashClass = match ($flash['type']) {
                'success' => 'bg-green-50 border-green-300 text-green-800',
                'warning' => 'bg-yellow-50 border-yellow-300 text-yellow-800',
                default   => 'bg-red-50 border-red-300 text-red-800',
            };
            ?>
            <div class="<?= $flashClass ?> border px-4 py-3 rounded-md mb-6 text-sm font-medium">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-md mb-6 text-sm flex gap-3 items-start">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="watch_id" value="<?= $id ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Watch Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="<?= htmlspecialchars($watch['name']) ?>" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-gray-800 focus:border-gray-800 transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Brand</label>
                    <input type="text" name="brand" value="<?= htmlspecialchars($watch['brand'] ?? '') ?>"
                        placeholder="e.g. Casio, Rolex"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-gray-800 focus:border-gray-800 transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Model Number <span class="text-red-500">*</span></label>
                    <input type="text" name="model" value="<?= htmlspecialchars($watch['model']) ?>" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-gray-800 focus:border-gray-800 transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Stock Quantity <span class="text-red-500">*</span></label>
                    <input type="number" name="quantity" value="<?= (int)($watch['quantity'] ?? 0) ?>" required min="0"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-gray-800 focus:border-gray-800 transition">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-5 rounded-lg border border-gray-100">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Buying Price (৳) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="buying_price"
                        value="<?= htmlspecialchars($watch['buying_price']) ?>" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-gray-800 focus:border-gray-800 transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Selling Price (৳) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="selling_price"
                        value="<?= htmlspecialchars($watch['selling_price']) ?>" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-gray-800 focus:border-gray-800 transition">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Description <span class="text-red-500">*</span></label>
                <textarea name="description" rows="8" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-gray-800 focus:border-gray-800 transition font-mono text-sm"><?= htmlspecialchars($watch['description']) ?></textarea>
                <p class="text-xs text-gray-500 mt-1.5">Staff এই details copy করবে।</p>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <label class="block text-sm font-semibold text-gray-700 mb-3">
                    Current Images
                    <span class="text-gray-400 font-normal">(<?= count($images) ?> টি)</span>
                </label>

                <div class="flex flex-wrap gap-4 mb-6">
                    <?php if (empty($images)): ?>
                        <p class="text-sm text-gray-400 italic">এখনো কোনো ছবি নেই।</p>
                    <?php else: ?>
                        <?php foreach ($images as $img): ?>
                            <div class="relative group w-28 h-28 rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                                <img src="../<?= htmlspecialchars($img['image_url'], ENT_QUOTES, 'UTF-8') ?>"
                                    class="w-full h-full object-cover"
                                    onerror="this.src='https://placehold.co/112x112/f3f4f6/9ca3af?text=No+img'">
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <button type="button"
                                        onclick="if(confirm('এই ছবিটি delete করবেন?')) document.getElementById('del-img-<?= $img['id'] ?>').submit();"
                                        class="bg-red-600 hover:bg-red-700 text-white p-2 rounded-full shadow transition"
                                        title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Upload New Images
                        <span class="text-gray-400 font-normal">(Optional — JPG/PNG/WebP, max 5MB each)</span>
                    </label>
                    <input type="file" name="new_images[]" multiple accept="image/*"
                        id="imageInput"
                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0
                           file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700
                           hover:file:bg-gray-200 transition cursor-pointer border border-gray-200 rounded-md p-1">
                    <div id="previewBox" class="flex flex-wrap gap-3 mt-3"></div>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-200">
                <button type="submit"
                    class="w-full bg-gray-900 hover:bg-black text-white font-semibold py-3 px-4 rounded-md transition flex justify-center items-center gap-2 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Update Watch Details
                </button>
            </div>
        </form>
    </div>

    <?php foreach ($images as $img): ?>
        <form id="del-img-<?= $img['id'] ?>" action="delete_image.php" method="POST" class="hidden">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="img_id" value="<?= (int)$img['id'] ?>">
            <input type="hidden" name="watch_id" value="<?= $id ?>">
        </form>
    <?php endforeach; ?>

    <script>
        document.getElementById('imageInput').addEventListener('change', function() {
            const box = document.getElementById('previewBox');
            box.innerHTML = '';
            Array.from(this.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = e => {
                    const div = document.createElement('div');
                    div.className = 'w-20 h-20 rounded-md overflow-hidden border border-gray-300 shadow-sm';
                    div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                    box.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        });
    </script>

</body>

</html>