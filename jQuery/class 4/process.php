<?php

if(isset($_POST['username'])){

    $name = $_POST['username'];

    echo "Welcome, " . htmlspecialchars($name);

}
?>