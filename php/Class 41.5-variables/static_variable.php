<?php
function counter()
{
    static $count = 0;
    $count++;
    echo $count . "<br>";
}

counter();
counter();
counter();
