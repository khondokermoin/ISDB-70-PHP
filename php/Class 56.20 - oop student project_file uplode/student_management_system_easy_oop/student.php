<?php
require_once __DIR__ . '/person.php';

// Student class = class concept
// extends Person = inheritance concept
class Student extends Person
{
    // private property = encapsulation
    private string $id;
    private string $course;

    // constructor = object তৈরি হলে data set হবে
    public function __construct(string $id, string $name, string $course, string $phone)
    {
        // parent constructor call = parent class এর code use করা
        parent::__construct($name, $phone);

        $this->id = trim($id);
        $this->course = trim($course);
    }

    // getter method
    public function getId(): string
    {
        return $this->id;
    }

    public function getCourse(): string
    {
        return $this->course;
    }

    // parent class এর abstract method implement করা হয়েছে
    public function toCSV(): string
    {
        return $this->id . ',' . $this->name . ',' . $this->course . ',' . $this->phone . PHP_EOL;
    }

    // static method = object ছাড়াই call করা যায়
    public static function fromCSV(string $line): ?self
    {
        // CSV line কে array বানাচ্ছে
        $data = str_getcsv(trim($line));

        // data incomplete হলে null return করবে
        if (count($data) < 4) {
            return null;
        }

        // new self = নতুন Student object তৈরি হচ্ছে
        return new self($data[0], $data[1], $data[2], $data[3]);
    }
}
