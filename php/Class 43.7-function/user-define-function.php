<?php

function info($name)
{
    echo "my name is $name";
}
info("moin");

echo "<br>";

function sum($a, $b)
{
    echo $a + $b;
}
echo sum(10, 30);

echo "<br>";
// anonymous function

$add = function ($v) {
    echo "hello $v";
};
$add("world");

echo "<br>";
// arrow function -- syntex-> fn(parameters) => expression;

$name = fn() => "moin";
echo $name();

echo "<br>";

$sums = fn($a, $b) => $a + $b;
echo $sums(5, 6);

echo "<br>";

$numbers = [1, 2, 3, 4];
$square = array_map(fn($n) => $n * $n, $numbers);
print_r($square);

echo "<br>";

// isset() Function

$name = "Moin";

if (isset($name)) {
    echo "Variable is set";
}

echo "<br>";

// empty() Function
$name = "";

if (empty($name)) {
    echo "Variable is empty";
}


echo "<br>";
