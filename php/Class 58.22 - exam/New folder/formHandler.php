<?php
require_once __DIR__ . '/student.php';
require_once __DIR__ . '/studentRepository.php';

$message = '';
$messageType = '';
$resultMessage = '';

if (isset($_POST['btnSubmit'])) {
    $id = trim($_POST['txtId']);
    $name = trim($_POST['txtName']);
    $batch = trim($_POST['txtBatch']);

    if ($id == '' || $name == '' || $batch == '') {
        $message = 'All fields are required.';
        $messageType = 'error';
    } else {
        $student = new Student($id, $name, $batch);
        $repo = new StudentRepository();
        $repo->save($student);

        $message = 'Student saved successfully.';
        $messageType = 'success';
    }
}

if (isset($_POST['btnSearch'])) {
    $searchId = trim($_POST['searchId']);

    if ($searchId == '') {
        $resultMessage = 'Please enter an ID.';
    } else {
        ob_start();
        $student = new Student('', '', '');
        $student->result($searchId);
        $resultMessage = ob_get_clean();
    }
}
