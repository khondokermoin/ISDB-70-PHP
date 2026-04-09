<?php
require_once __DIR__ . '/person.php';

// Student class = class
// extends Person = inheritance
class Student extends Person
{
    // encapsulation = private property
    private $course;

    public function __construct($id, $name, $course, $phone)
    {
        parent::__construct($id, $name, $phone);
        $this->course = trim($course);
    }

    public function getCourse()
    {
        return $this->course;
    }

    public function getRole()
    {
        return 'Student';
    }

    // method = object data to csv
    public function toCSV()
    {
        return $this->id . ',' . $this->name . ',' . $this->course . ',' . $this->phone . PHP_EOL;
    }
}
