<?php
class Animal
{
    public $name;
    public function __construct($n)
    {
        $this->name = $n;
    }
    public function eat()
    {
        echo $this->name . "is eating...<br>";
    }
}
class Dog extends Animal
{
    public function bark()
    {
        echo $this->name . "is barking woof..";
    }
}

$myDog = new Dog("Buddy");
$myDog->eat();
$myDog->bark();
