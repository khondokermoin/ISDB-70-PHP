<?php

/**
 * Amar IT - Root Redirector
 */

// public/index.php ফাইলটিকে রিকয়ার করা হচ্ছে
// require __DIR__ . '/public/index.php';


// ইউজারকে সরাসরি public ফোল্ডারে পাঠিয়ে দেবে
header("Location: public/index.php");
exit;
