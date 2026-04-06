<?php
// Student class include করা হচ্ছে
require_once __DIR__ . '/student.php';

// StudentRepository class include করা হচ্ছে
require_once __DIR__ . '/studentRepository.php';

// Message variable → success বা error message রাখবে
$message = '';

// Message type → success বা error CSS class এর জন্য
$messageType = '';

// Check → form POST method দিয়ে submit হয়েছে কিনা
// REQUEST_METHOD === 'POST' → form submit হয়েছে
// isset(btnSubmit) → submit button click হয়েছে
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnSubmit'])) {

    // Form থেকে input নেওয়া হচ্ছে
    // trim() → extra space remove করে clean input নিচ্ছে
    $id = trim($_POST['txtId'] ?? '');
    $name = trim($_POST['txtName'] ?? '');
    $course = trim($_POST['txtCourse'] ?? '');
    $phone = trim($_POST['txtPhone'] ?? '');

    // Validation 1 → empty field check
    // যদি কোনো field empty হয়
    if ($id === '' || $name === '' || $course === '' || $phone === '') {

        // Error message set
        $message = 'All fields are required.';
        $messageType = 'error';

    // Validation 2 → phone number regex check
    // /^[0-9+]{11,14}$/ → শুধু number বা +, length 11-14
    } elseif (!preg_match('/^[0-9+]{11,14}$/', $phone)) {

        // Phone invalid হলে error message
        $message = 'Invalid phone number. Use 11 to 14 digits or +.';
        $messageType = 'error';

    } else {
        // Validation pass হলে

        // Student object তৈরি
        $student = new Student($id, $name, $course, $phone);

        // Repository object তৈরি
        $repo = new StudentRepository();

        // Student data file এ save
        $repo->save($student);

        // Success message
        $message = 'Student saved successfully.';
        $messageType = 'success';
    }
}



/* Form Submit
     ↓
POST Request Check
     ↓
Input নেওয়া
     ↓
trim() → input clean
     ↓
Empty Validation
     ↓
Phone Validation (Regex)
     ↓
new Student() → Object
     ↓
new StudentRepository()
     ↓
save()
     ↓
data.txt
     ↓
Success/Error Message */