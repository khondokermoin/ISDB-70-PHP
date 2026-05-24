<?php
// app/controllers/AuthController.php

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // সেশন শুরু করা না থাকলে শুরু করবে
}

// ডাটাবেস কানেকশন এবং User মডেল যুক্ত করা হলো
require_once '../config/database.php';
require_once '../models/User.php';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // User মডেলের একটি অবজেক্ট তৈরি করা এবং কানেকশন ($conn) পাস করা
    $userModel = new User($conn);

    // মডেলের ফাংশন ব্যবহার করে ইউজারনেম অনুযায়ী ডাটা খুঁজে বের করা
    $user = $userModel->getUserByUsername($username);

    if ($user) {
        // ডাটাবেসের হ্যাশ করা পাসওয়ার্ডের সাথে ইনপুট করা পাসওয়ার্ড ভেরিফাই করা
        if (password_verify($password, $user['password'])) {
            // লগিন সফল হলে Session সেট করা
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // ড্যাশবোর্ডে যাওয়ার সঠিক পাথ (controllers থেকে এক ধাপ পেছনে গিয়ে views)
            header("Location: ../views/dashboard/index.php");
            exit();
        } else {
            // পাসওয়ার্ড ভুল হলে
            $_SESSION['error'] = "Incorrect password!";
            header("Location: ../views/auth/login.php");
            exit();
        }
    } else {
        // ইউজারনেম ডাটাবেসে না থাকলে
        $_SESSION['error'] = "User not found!";
        header("Location: ../views/auth/login.php");
        exit();
    }
} else {
    // ডিরেক্ট লিংকে ঢুকতে চাইলে লগিন পেজে পাঠিয়ে দিবে
    header("Location: ../views/auth/login.php");
    exit();
}
