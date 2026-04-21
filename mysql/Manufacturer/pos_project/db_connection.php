<?php
$host = "localhost";
$user = "root";
$pass = ""; // Leave blank if you haven't set a password in XAMPP
$dbname = "pos_db";

$db = new mysqli($host, $user, $pass, $dbname);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
?>
