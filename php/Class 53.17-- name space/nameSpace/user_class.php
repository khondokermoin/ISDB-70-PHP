<?php
namespace UserOne;

class User{
    public $name = "Myt";
    public $deg = "ceo";
    
    public function userInfo(){
        echo "This is ". $this->name . "<br>";
        echo "This is ". $this->deg . "<br>";
    } 


}


?>