<?php
require_once __DIR__ . '/student.php';

class StudentRepository
{
    private $file;

    public function __construct($file = null)
    {
        $this->file = $file ? $file : (__DIR__ . '/data.txt');
    }

    public function save($student)
    {
        file_put_contents($this->file, $student->toCSV(), FILE_APPEND);
    }

    public function getAll()
    {
        $students = [];

        if (!file_exists($this->file)) {
            return $students;
        }

        $lines = file($this->file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $student = Student::fromCSV($line);
            if ($student != null) {
                $students[] = $student;
            }
        }

        return $students;
    }
}
