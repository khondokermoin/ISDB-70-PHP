<?php
class Student
{
    private $id;
    private $name;
    private $course;
    private $phone;

    public function __construct($id, $name, $course, $phone)
    {
        $this->id = trim($id);
        $this->name = trim($name);
        $this->course = trim($course);
        $this->phone = trim($phone);
    }

    // Getter methods
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getCourse() { return $this->course; }
    public function getPhone() { return $this->phone; }

    // Convert to CSV
    public function toCSV()
    {
        return implode(",", [
            $this->id,
            $this->name,
            $this->course,
            $this->phone
        ]) . PHP_EOL;
    }

    // Static factory method
    public static function fromCSV($line)
    {
        $data = str_getcsv(trim($line));
        return new self($data[0], $data[1], $data[2], $data[3]);
    }
}