<?php
require_once __DIR__ . '/student.php';
require_once __DIR__ . '/studentRepository.php';

// message দেখানোর জন্য variable
$message = '';
$messageType = '';

// form submit হয়েছে কি না check
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnSubmit'])) {

    // form input নেওয়া হচ্ছে
    $id = trim($_POST['txtId'] ?? '');
    $name = trim($_POST['txtName'] ?? '');
    $course = trim($_POST['txtCourse'] ?? '');
    $phone = trim($_POST['txtPhone'] ?? '');

    // validation 1 = empty field check
    if ($id === '' || $name === '' || $course === '' || $phone === '') {
        $message = 'All fields are required.';
        $messageType = 'error';

    // validation 2 = phone number check
    // শুধু number বা + থাকবে, length 11 থেকে 14
    } elseif (!preg_match('/^[0-9+]{11,14}$/', $phone)) {
        $message = 'Invalid phone number. Use 11 to 14 digits or +.';
        $messageType = 'error';

    } else {
        // object creation = Student object
        $student = new Student($id, $name, $course, $phone);

        // object creation = Repository object
        $repo = new StudentRepository();

        // save method call
        $repo->save($student);

        $message = 'Student saved successfully.';
        $messageType = 'success';
    }
}
