<?php

declare(strict_types=1);

/**
 * Admin Login Check
 */
function require_admin_login(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (
        !isset($_SESSION['admin_logged_in']) ||
        $_SESSION['admin_logged_in'] !== true
    ) {
        header(
            "Location: " .
                (
                    strpos($_SERVER['PHP_SELF'], '/admin/') !== false
                    ? 'login.php'
                    : 'admin/login.php'
                )
        );

        exit;
    }
}

/**
 * Generate CSRF Token
 */
function generate_csrf_token(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verify_csrf_token(string $token): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return isset($_SESSION['csrf_token']) &&
        hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Upload Watch Images
 */
function upload_images(array $files, PDO $pdo, int $watch_id): array
{
    $uploadDir = __DIR__ . '/../assets/uploads/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $allowedMimes = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif'
    ];

    $maxSize = 5 * 1024 * 1024;

    $uploaded = [];
    $errors   = [];

    $count = count($files['name']);

    for ($i = 0; $i < $count; $i++) {

        if (
            $files['error'][$i] !== UPLOAD_ERR_OK ||
            empty($files['tmp_name'][$i])
        ) {
            continue;
        }

        // File size validation
        if ($files['size'][$i] > $maxSize) {

            $errors[] =
                $files['name'][$i] .
                " ফাইলটি 5MB এর বেশি।";

            continue;
        }

        // MIME validation
        $finfo = new finfo(FILEINFO_MIME_TYPE);

        $mime = $finfo->file($files['tmp_name'][$i]);

        if (!in_array($mime, $allowedMimes, true)) {

            $errors[] =
                $files['name'][$i] .
                " — শুধু JPG, PNG, WEBP, GIF অনুমোদিত।";

            continue;
        }

        // Extension mapping
        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
            default      => 'jpg'
        };

        // Unique filename
        $filename = uniqid('watch_', true) . '.' . $ext;

        $uploadPath = $uploadDir . $filename;

        $dbPath = 'assets/uploads/' . $filename;

        // Upload file
        if (
            move_uploaded_file(
                $files['tmp_name'][$i],
                $uploadPath
            )
        ) {

            $stmt = $pdo->prepare("
                INSERT INTO watch_images
                (
                    watch_id,
                    image_url,
                    sort_order
                )
                VALUES
                (
                    :wid,
                    :url,
                    :sort
                )
            ");

            $stmt->execute([
                ':wid'  => $watch_id,
                ':url'  => $dbPath,
                ':sort' => $i
            ]);

            $uploaded[] = $dbPath;
        }
    }

    return [
        'uploaded' => $uploaded,
        'errors'   => $errors
    ];
}
