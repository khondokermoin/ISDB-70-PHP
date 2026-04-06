<?php
// Student class → একজন student এর data object আকারে represent করবে
class Student
{
    // Private properties → Encapsulation (data বাইরে থেকে direct access করা যাবে না)
    private string $id;
    private string $name;
    private string $course;
    private string $phone;

    // Constructor → object তৈরি হলে automatically run হবে
    // এখানে student এর data initialize করা হচ্ছে
    public function __construct(string $id, string $name, string $course, string $phone)
    {
        // trim() → extra space remove করে clean data save করা হচ্ছে
        $this->id = trim($id);
        $this->name = trim($name);
        $this->course = trim($course);
        $this->phone = trim($phone);
    }

    // Getter method → id return করবে
    public function getId(): string
    {
        return $this->id;
    }

    // Getter method → name return করবে
    public function getName(): string
    {
        return $this->name;
    }

    // Getter method → course return করবে
    public function getCourse(): string
    {
        return $this->course;
    }

    // Getter method → phone return করবে
    public function getPhone(): string
    {
        return $this->phone;
    }

    // toCSV() method → Student object data কে CSV string বানায়
    // File এ save করার জন্য এই format ব্যবহার করা হয়
    public function toCSV(): string
    {
        // implode() → array কে comma দিয়ে join করে string বানায়
        return implode(',', [
            $this->id,
            $this->name,
            $this->course,
            $this->phone,
        ]) . PHP_EOL; // PHP_EOL → new line
    }

    // Static method → CSV line থেকে Student object বানায়
    // ?self → Student object অথবা null return করতে পারে
    public static function fromCSV(string $line): ?self
    {
        // trim() → line clean
        // str_getcsv() → CSV string কে array বানায়
        $data = str_getcsv(trim($line));

        // যদি CSV data incomplete হয় (4টা field না থাকে)
        if (count($data) < 4) {
            return null; // invalid data হলে null return
        }

        // নতুন Student object তৈরি করে return
        return new self($data[0], $data[1], $data[2], $data[3]);
    }
}