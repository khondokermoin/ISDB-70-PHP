<?php
// File Include / File Import
require_once __DIR__ . '/student.php';
require_once __DIR__ . '/studentRepository.php';

// message variables
$message = '';
$messageType = '';

if (isset($_POST['btnSubmit'])) {
    $id = trim($_POST['txtId']);
    $name = trim($_POST['txtName']);
    $course = trim($_POST['txtCourse']);
    $phone = trim($_POST['txtPhone']);

    // simple validation
    if ($id == '' || $name == '' || $course == '' || $phone == '') {
        $message = 'All fields are required.';
        $messageType = 'error';
    } elseif (!preg_match('/^[0-9+]{11,14}$/', $phone)) {
        $message = 'Invalid phone number.';
        $messageType = 'error';
    } else {
        // object creation
        $student = new Student($id, $name, $course, $phone);
        $repo = new StudentRepository();

        $repo->save($student); // parameter pass

        $message = 'Student saved successfully.';
        $messageType = 'success';
    }
}
