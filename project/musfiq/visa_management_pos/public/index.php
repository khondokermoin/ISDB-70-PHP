<?php
// এটি প্রজেক্টের মূল এন্ট্রি পয়েন্ট বা Front Controller
session_start();

// ১. যদি ইউজার আগে থেকেই লগিন করা থাকে, তাহলে সরাসরি ড্যাশবোর্ডে পাঠিয়ে দেবে
if (isset($_SESSION['user_id'])) {
    header("Location: ../app/views/dashboard/index.php");
    exit();
}
// ২. যদি লগিন করা না থাকে, তাহলে লগিন পেজে পাঠিয়ে দেবে
else {
    header("Location: ../app/views/auth/login.php");
    exit();
}
