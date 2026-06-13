<?php

include_once("db.php");

$sql = $conn->query("SELECT * FROM users");

$rawData = [];

while ($row = $sql->fetch_assoc()) {
    $rawData[] = $row;
}


echo json_encode($rawData);
