<?php
class StudentRepository
{
    private $file;

    public function __construct($file = "data.txt")
    {
        $this->file = $file;
    }

    public function save(Student $student)
    {
        file_put_contents($this->file, $student->toCSV(), FILE_APPEND);
    }

    public function getAll()
    {
        $students = [];

        if (!file_exists($this->file)) return $students;

        $lines = file($this->file, FILE_IGNORE_NEW_LINES);

        foreach ($lines as $line) {
            $students[] = Student::fromCSV($line);
        }

        return $students;
    }
}