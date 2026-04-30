<?php
session_start();
if (!isset($_SESSION["username"])) { header("Location: index.php"); exit(); }
include 'db.php';

$id = $_GET['id'];

$conn->query("DELETE FROM products WHERE id=$id");

header("Location: view_products.php");
?>