<?php
class Rocket {
    public $model;
    public $color;

    // Magic method: constructor
    public function __construct($model, $color) {
        $this->model = $model;
        $this->color = $color;
        echo "🚀 Rocket $this->model with $this->color color is ready for launch.\n";
    }

    public function fly() {
        echo "🚀 Rocket $this->model is launching!\n";
    }

    public function back() {
        echo "🚀 Rocket $this->model is returning!\n";
    }

    // Magic method: destructor
    public function __destruct() {
        echo "💥 Rocket $this->model mission complete, object destroyed.\n";
    }
}

// Create object (calls __construct)
$myRocket = new Rocket("420", "Red");

// Call methods
$myRocket->fly();
$myRocket->back();

// Object destroyed automatically at the end (calls __destruct)
?>