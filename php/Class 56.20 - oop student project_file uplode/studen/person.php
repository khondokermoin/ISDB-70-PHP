<?php
// Abstract class = abstraction
abstract class Person
{
    // properties
    protected $id;
    protected $name;
    protected $phone;

    // constructor
    public function __construct($id, $name, $phone)
    {
        $this->id = trim($id);
        $this->name = trim($name);
        $this->phone = trim($phone);
    }

    // methods
    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    // abstract method
    abstract public function getRole();
}
