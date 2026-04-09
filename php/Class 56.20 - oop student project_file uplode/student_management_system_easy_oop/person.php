<?php
// Abstract class = abstraction
// এই class সরাসরি object বানানোর জন্য না
// শুধু common property ও method রাখার জন্য
abstract class Person
{
    // protected property = child class (Student) access করতে পারবে
    protected string $name;
    protected string $phone;

    // constructor = common data set করবে
    public function __construct(string $name, string $phone)
    {
        $this->name = trim($name);
        $this->phone = trim($phone);
    }

    // common getter method
    public function getName(): string
    {
        return $this->name;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    // abstract method = child class এ অবশ্যই define করতে হবে
    abstract public function toCSV(): string;
}
