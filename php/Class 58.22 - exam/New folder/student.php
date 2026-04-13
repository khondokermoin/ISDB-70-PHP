<?php
require_once __DIR__ . '/person.php';

class Student extends Person
{
    private $batch;

    public function __construct($id, $name, $batch)
    {
        parent::__construct($id, $name);
        $this->batch = trim($batch);
    }

    public function getBatch()
    {
        return $this->batch;
    }

    public function getRole()
    {
        return 'Student';
    }

    public function toCSV()
    {
        return $this->id . ',' . $this->name . ',' . $this->batch . PHP_EOL;
    }

    // id দিয়ে file থেকে student data খুঁজে print করবে
    public function result($searchId)
    {
        $studentFile = __DIR__ . '/data.txt';
        $studentFound = false;
        $studentName = '';
        $studentBatch = '';
        $studentId = trim($searchId);

        if (file_exists($studentFile)) {
            $studentLines = file($studentFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($studentLines as $line) {
                $data = explode(',', $line);

                if (count($data) >= 3 && trim($data[0]) == $studentId) {
                    $studentFound = true;
                    $studentName = $data[1];
                    $studentBatch = $data[2];
                    break;
                }
            }
        }

        if (!$studentFound) {
            echo 'Student ID not found.';
            return;
        }

        echo 'ID: ' . htmlspecialchars($studentId) . '<br>';
        echo 'Name: ' . htmlspecialchars($studentName) . '<br>';
        echo 'Batch: ' . htmlspecialchars($studentBatch);
    }
}
