<?php
require_once __DIR__ . '/person.php';

// Student class inherits Person class = inheritance
class Student extends Person
{
    // Encapsulation: private properties
    private $id;
    private $course;

    public function __construct($id, $name, $course, $phone)
    {
        parent::__construct($name, $phone);
        $this->id = trim($id);
        $this->course = trim($course);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCourse()
    {
        return $this->course;
    }

    public function toCSV()
    {
        return $this->id . ',' . $this->name . ',' . $this->course . ',' . $this->phone . PHP_EOL;
    }

    public static function fromCSV($line)
    {
        $data = str_getcsv(trim($line));

        if (count($data) < 4) {
            return null;
        }

        return new Student($data[0], $data[1], $data[2], $data[3]);
    }
}
