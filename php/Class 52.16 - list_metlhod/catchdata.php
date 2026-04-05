<?php
class Student
{
    public $id;
    public $name;

    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function formatData()
    {
        return $this->id . "," . $this->name . PHP_EOL;
    }
}

class StudentFileHandler
{
    private $fileName;

    public function __construct($fileName)
    {
        $this->fileName = $fileName;
    }

    public function saveStudent(Student $student)
    {
        file_put_contents($this->fileName, $student->formatData(), FILE_APPEND);
    }
}

$alert = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = trim($_POST["id"]);
    $name = trim($_POST["name"]);

    if ($id != "" && $name != "") {
        $student = new Student($id, $name);
        $fileHandler = new StudentFileHandler("studentdata.txt");
        $fileHandler->saveStudent($student);

        $alert = "success";
    } else {
        $alert = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Form</title>
</head>
<body>

<h2>Student Form</h2>

<form method="POST">
    <label>Student ID:</label><br>
    <input type="text" name="id"><br><br>

    <label>Student Name:</label><br>
    <input type="text" name="name"><br><br>

    <button type="submit">Save Student</button>
</form>

<?php
// Alert system
if ($alert == "success") {
    echo "<script>alert('Student data saved successfully.');</script>";
} elseif ($alert == "error") {
    echo "<script>alert('Please fill all fields!');</script>";
}
?>

</body>
</html>