<?php
// 1. Force these headers to send immediately
header("Access-Control-Allow-Origin: http://localhost:3000"); // Better to specify your exact React origin
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// 2. CRITICAL: Catch and terminate the preflight OPTIONS request successfully
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

$host = "localhost";
$user = "root"; 
$pass = "";     
$db   = "react_crud";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}
?>
