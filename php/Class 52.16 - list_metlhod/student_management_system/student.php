<?php
class Student
{
    private string $id;
    private string $name;
    private string $course;
    private string $phone;

    public function __construct(string $id, string $name, string $course, string $phone)
    {
        $this->id = trim($id);
        $this->name = trim($name);
        $this->course = trim($course);
        $this->phone = trim($phone);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCourse(): string
    {
        return $this->course;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function toCSV(): string
    {
        return implode(',', [
            $this->id,
            $this->name,
            $this->course,
            $this->phone,
        ]) . PHP_EOL;
    }

    public static function fromCSV(string $line): ?self
    {
        $data = str_getcsv(trim($line));

        if (count($data) < 4) {
            return null;
        }

        return new self($data[0], $data[1], $data[2], $data[3]);
    }
}
