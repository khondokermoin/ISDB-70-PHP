<?php 
$conn = mysqli_connect("localhost", "root", "", "office");


if (!$conn){
    die ("connection faild..");
}else{
    echo "connection success...";
}
?>