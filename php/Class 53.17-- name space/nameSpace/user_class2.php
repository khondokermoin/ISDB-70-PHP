<?php
namespace UserTwo;

class User{
    public $name = "Khondoker";
    public $Modele = "9999999";

    public  function show(){
        echo "This is ". $this->name . "<br>";
        echo "This is ". $this->Modele . "<br>";
    }
}

?>