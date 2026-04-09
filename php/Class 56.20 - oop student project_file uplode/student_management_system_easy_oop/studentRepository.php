<?php
require_once __DIR__ . '/student.php';

// Repository class = file handling আলাদা রাখার জন্য
class StudentRepository
{
    // property = file path রাখবে
    private string $file;

    // constructor = file path set করবে
    public function __construct(string $file = null)
    {
        $this->file = $file ?? (__DIR__ . '/data.txt');
    }

    // method = Student object file এ save করবে
    public function save(Student $student): void
    {
        // object কে CSV বানিয়ে file এর শেষে add করছে
        file_put_contents($this->file, $student->toCSV(), FILE_APPEND | LOCK_EX);
    }

    // method = সব student data read করবে
    public function getAll(): array
    {
        $students = [];

        // file না থাকলে empty array return
        if (!file_exists($this->file)) {
            return $students;
        }

        // file line by line পড়ছে
        $lines = file($this->file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // প্রতিটি line থেকে Student object বানাচ্ছে
        foreach ($lines as $line) {
            $student = Student::fromCSV($line);

            if ($student !== null) {
                $students[] = $student;
            }
        }

        return $students;
    }
}
