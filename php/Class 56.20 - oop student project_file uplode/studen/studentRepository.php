<?php
require_once __DIR__ . '/student.php';

// Repository class = file save/read
class StudentRepository
{
    private $file;

    public function __construct()
    {
        $this->file = __DIR__ . '/data.txt';
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

        $lines = file($this->file, FILE_IGNORE_NEW_LINES);

        foreach ($lines as $line) {
            if ($line == '') {
                continue;
            }

            $data = explode(',', $line);

            if (count($data) == 4) {
                $students[] = new Student($data[0], $data[1], $data[2], $data[3]);
            }
        }

        return $students;
    }
}
