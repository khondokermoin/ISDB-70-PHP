<?php
require_once __DIR__ . '/student.php';

class StudentRepository
{
    private string $file;

    public function __construct(string $file = null)
    {
        $this->file = $file ?? (__DIR__ . '/data.txt');
    }

    public function save(Student $student): void
    {
        file_put_contents($this->file, $student->toCSV(), FILE_APPEND | LOCK_EX);
    }

    public function getAll(): array
    {
        $students = [];

        if (!file_exists($this->file)) {
            return $students;
        }

        $lines = file($this->file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $student = Student::fromCSV($line);
            if ($student !== null) {
                $students[] = $student;
            }
        }

        return $students;
    }
}
