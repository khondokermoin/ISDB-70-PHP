<?php
trait Message {
    public function sayHello() {
        echo "Hello!";
    }
}

class User {
    use Message;
}

class Admin {
    use Message;
}

$user = new User();
$user->sayHello(); 