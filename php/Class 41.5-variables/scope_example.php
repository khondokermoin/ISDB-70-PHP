<?php
$x = 5; // global

function demo()
{
    $y = 10; // local
    global $x;

    echo $x + $y;
}

demo();
