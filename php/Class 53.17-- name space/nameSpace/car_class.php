<?php
namespace Carname;

class Car{
    public $name = "BMW";
    public $Modele = "5466";

    public  function carInfo(){
        echo "This is ". $this->name . "<br>";
        echo "This is ". $this->Modele . "<br>";
    }
}

?>