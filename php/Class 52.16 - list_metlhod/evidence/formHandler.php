<?php
if (isset($_POST['btnSubmit'])) {

    $id = $_POST['txtId'] ?? '';
    $name = $_POST['txtName'] ?? '';
    $course = $_POST['txtCourse'] ?? '';
    $phone = $_POST['txtPhone'] ?? '';

    if (!empty($id) && !empty($name) && !empty($phone)) {

        if (preg_match("/^[0-9+]{11,14}$/", $phone)) {

            $student = new Student($id, $name, $course, $phone);
            $repo = new StudentRepository();

            $repo->save($student);

            echo "Saved Successfully!";
        } else {
            echo "Invalid phone!";
        }

    } else {
        echo "All fields required!";
    }
}