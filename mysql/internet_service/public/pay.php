<?php
session_start();
if (!isset($_GET['id'])) {
    header("Location: user_dashboard.php");
    exit;
}
require_once '../config/database.php';
$db = (new Database())->getConnection();

// পেমেন্ট স্ট্যাটাস আপডেট লজিক
$invoice_id = $_GET['id'];
$query = "UPDATE invoices SET status = 'paid' WHERE invoice_id = :id AND user_id = :uid";
$stmt = $db->prepare($query);
if ($stmt->execute([':id' => $invoice_id, ':uid' => $_SESSION['user_id']])) {
    // পেমেন্ট হলে সাবস্ক্রিপশন একটিভ করার জন্য পেমেন্ট টেবিল এ ডেটা রাখা উচিত
    header("Location: user_dashboard.php?msg=paid");
}
