<?php
// Abstract class = abstraction
abstract class Person
{
    // Properties
    protected $name;
    protected $phone;

    public function __construct($name, $phone)
    {
        $this->name = trim($name);
        $this->phone = trim($phone);
    }

    // Methods
    public function getName()
    {
        return $this->name;
    }

    public function getPhone()
    {
        return $this->phone;
    }
}
