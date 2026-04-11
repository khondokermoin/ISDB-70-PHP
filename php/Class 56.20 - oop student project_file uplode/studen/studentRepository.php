<?php
require_once __DIR__ . '/student.php';

// Repository class = file save/read
class StudentRepository
{
    // static property → সব object একই file share করবে
    private static $file = __DIR__ . '/data.txt';

    // save method → student data file এ save করবে
    public function save($student)
    {
        // static property access → self::$file
        // FILE_APPEND → file এর শেষে data add করবে
        file_put_contents(self::$file, $student->toCSV(), FILE_APPEND);
    }

    // getAll method → file থেকে সব data read করবে
    public function getAll()
    {
        $students = [];

        // file না থাকলে empty array return
        if (!file_exists(self::$file)) {
            return $students;
        }

        // file line by line read
        $lines = file(self::$file, FILE_IGNORE_NEW_LINES);

        foreach ($lines as $line) {

            // empty line skip
            if ($line == '') {
                continue;
            }

            // CSV string কে array বানানো
            $data = explode(',', $line);

            // যদি 4টা value থাকে
            if (count($data) == 4) {

                // Student object তৈরি
                $students[] = new Student(
                    $data[0],
                    $data[1],
                    $data[2],
                    $data[3]
                );
            }
        }

        // সব student object return
        return $students;
    }
}