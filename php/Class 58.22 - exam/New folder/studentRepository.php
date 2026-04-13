<?php
require_once __DIR__ . '/student.php';

class StudentRepository
{
    private static $file = __DIR__ . '/data.txt';

    public function save($student)
    {
        file_put_contents(self::$file, $student->toCSV(), FILE_APPEND);
    }

    public function getAll()
    {
        $students = [];

        if (!file_exists(self::$file)) {
            return $students;
        }

        $lines = file(self::$file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $data = explode(',', $line);

            if (count($data) >= 3) {
                $students[] = new Student($data[0], $data[1], $data[2]);
            }
        }

        return $students;
    }
}
