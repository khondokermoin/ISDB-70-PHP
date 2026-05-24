<?php
// admin/logout.php
session_start();
session_unset();    // সেশনের সব ডাটা ক্লিয়ার করা
session_destroy();  // সেশন ধ্বংস করা

// লগইন পেজে পাঠিয়ে দেওয়া
header("Location: login.php");
exit;
