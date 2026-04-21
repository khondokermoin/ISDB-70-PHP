<?php
require_once __DIR__ . '/helpers.php';

$db = new mysqli('localhost', 'root', '', 'company');
if ($db->connect_error) {
    die('Database connection failed: ' . $db->connect_error);
}
$db->set_charset('utf8mb4');
