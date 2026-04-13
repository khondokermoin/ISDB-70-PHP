<?php
// Abstract class = abstraction
abstract class Person
{
    // properties
    protected $id;
    protected $name;

    // constructor
    public function __construct($id, $name)
    {
        $this->id = trim($id);
        $this->name = trim($name);
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


    // abstract method
    abstract public function getRole();
}

// person.php → base structure দেয়
// student.php → সেই structure inherit করে Student-specific কাজ করে
