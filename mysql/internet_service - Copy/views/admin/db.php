<?php
/**
 * config/database.php
 *
 * Secure PDO connection for the ISP Manager project.
 *
 * HOW TO USE IN ANY PAGE:
 *   require_once __DIR__ . '/../config/database.php';
 *   // $db is now available as a PDO instance.
 *
 * CREDENTIALS:
 *   Set server environment variables (recommended for production),
 *   or edit the fallback values below for local development.
 *
 *   cPanel → Software → PHP Config  → Environment Variables
 *   Or add to your .htaccess (outside webroot is safer):
 *     SetEnv DB_HOST 127.0.0.1
 *     SetEnv DB_NAME u951246149_isp_manager
 *     SetEnv DB_USER your_db_user
 *     SetEnv DB_PASS your_db_password
 */

// ─── Credentials ──────────────────────────────────────────────────────────────
$_db_host = getenv('DB_HOST') ?: 'localhost';
$_db_port = getenv('DB_PORT') ?: '3306';
$_db_name = getenv('DB_NAME') ?: 'isp_manager';
$_db_user = getenv('DB_USER') ?: 'root';   // ← change for local dev
$_db_pass = getenv('DB_PASS') ?: '';   // ← change for local dev

// ─── DSN & Options ────────────────────────────────────────────────────────────
$_dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $_db_host,
    $_db_port,
    $_db_name
);

$_pdo_options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // throw exceptions on error
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // arrays by default
    PDO::ATTR_EMULATE_PREPARES   => false,                    // real prepared statements
    PDO::ATTR_PERSISTENT         => false,                    // no persistent connections
    PDO::MYSQL_ATTR_FOUND_ROWS   => true,                     // accurate rowCount()
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'",
];

// ─── Connect ──────────────────────────────────────────────────────────────────
try {
    $db = new PDO($_dsn, $_db_user, $_db_pass, $_pdo_options);
} catch (PDOException $e) {
    // Log the real error; show nothing sensitive to the browser.
    error_log('[ISP Manager] DB connection failed: ' . $e->getMessage());

    http_response_code(503);
    exit('<!DOCTYPE html><html><head><title>Service Unavailable</title></head><body>'
       . '<h2 style="font-family:sans-serif;color:#b91c1c">Database unavailable.</h2>'
       . '<p style="font-family:sans-serif">Please try again later or contact the administrator.</p>'
       . '</body></html>');
}

// Clean up credential variables so they are not accessible later in the script.
unset($_db_host, $_db_port, $_db_name, $_db_user, $_db_pass, $_dsn, $_pdo_options);

// ─── Shared query helper (optional — used by dashboard.php) ──────────────────
/**
 * Run a prepared statement and return the PDOStatement.
 *
 * Examples:
 *   $count = dbq($db, "SELECT COUNT(*) FROM users WHERE status = ?", ['active'])->fetchColumn();
 *   $rows  = dbq($db, "SELECT * FROM tickets ORDER BY created_at DESC LIMIT 10")->fetchAll();
 */
if (!function_exists('dbq')) {
    function dbq(PDO $db, string $sql, array $params = []): PDOStatement
    {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}