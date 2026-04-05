<?php
require_once __DIR__ . '/student.php';
require_once __DIR__ . '/studentRepository.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnSubmit'])) {
    $id = trim($_POST['txtId'] ?? '');
    $name = trim($_POST['txtName'] ?? '');
    $course = trim($_POST['txtCourse'] ?? '');
    $phone = trim($_POST['txtPhone'] ?? '');

    if ($id === '' || $name === '' || $course === '' || $phone === '') {
        $message = 'All fields are required.';
        $messageType = 'error';
    } elseif (!preg_match('/^[0-9+]{11,14}$/', $phone)) {
        $message = 'Invalid phone number. Use 11 to 14 digits or +.';
        $messageType = 'error';
    } else {
        $student = new Student($id, $name, $course, $phone);
        $repo = new StudentRepository();
        $repo->save($student);

        $message = 'Student saved successfully.';
        $messageType = 'success';
    }
}
