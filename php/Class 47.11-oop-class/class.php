b<?php
/* class Car {
    // Properties (variables)
    public $brand;
    public $model;

    // Methods (functions)
    public function setBrand($brand) {
        $this->brand = $brand;
    }

    public function displayInfo() {
        echo "Car: $this->brand $this->model";
    }
} */



class Rocket {
    public $model = "420";
    public $color = "Red"; 

    public function start() {
        echo "Rocket $this->model model and $this->color color is launching process ready.</br>";
    }

    public function fly() {
        echo "Rocket $this->model model and $this->color color is launch.\n";
    }

    public function back() {
        echo "Rocket $this->model model and $this->color color is return.\n";
    }
}


$myRocket = new Rocket();


$myRocket->start(); 
$myRocket->fly();   
$myRocket->back();  







?>
