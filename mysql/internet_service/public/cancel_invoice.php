<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';

// POST রিকোয়েস্ট ছাড়া অন্য কিছু এক্সেপ্ট করবে না
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $invoice_id = isset($_POST['invoice_id']) ? (int)$_POST['invoice_id'] : 0;

    // CSRF Token Security Check
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // টোকেন না মিললে ড্যাশবোর্ডে এরর মেসেজ পাঠাবে
        header("Location: user_dashboard.php?msg=invalid_token");
        exit;
    }

    if ($invoice_id > 0) {
        $db = (new Database())->getConnection();

        // চেক করা হচ্ছে ইনভয়েসটি এই ইউজারের কিনা এবং এটি আসলেই একটি Upgrade ইনভয়েস কিনা
        $checkStmt = $db->prepare("SELECT status, invoice_number FROM invoices WHERE invoice_id = ? AND user_id = ?");
        $checkStmt->execute([$invoice_id, $user_id]);
        $inv = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($inv && $inv['status'] == 'unpaid' && strpos($inv['invoice_number'], 'UPG-') === 0) {

            // ডাটাবেস থেকে ডিলিট না করে স্ট্যাটাস cancelled করে দেওয়া হচ্ছে
            $updateStmt = $db->prepare("UPDATE invoices SET status = 'cancelled' WHERE invoice_id = ? AND user_id = ?");
            if ($updateStmt->execute([$invoice_id, $user_id])) {
                // সফলভাবে ক্যানসেল হলে ড্যাশবোর্ডে সাকসেস মেসেজ নিয়ে যাবে
                header("Location: user_dashboard.php?msg=upgrade_cancelled");
                exit;
            }
        }
    }
}

// কোনো কারণে লজিক ফেইল করলে সাধারণ রিডাইরেক্ট
header("Location: user_dashboard.php");
exit;
