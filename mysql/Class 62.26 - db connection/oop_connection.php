<?php

$hostname = "localhost";
$user = "root";
$password = "";
$dbname = "office";
$conn = new mysqli($hostname, $user, $password, $dbname);

if($conn->connect_error){
    die ("connection failed..".$conn->connect_error);
}echo "connection succefully";

?>