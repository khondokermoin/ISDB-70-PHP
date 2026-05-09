<?php

/* associative array */

$age = array("moin" => 22, "mirad" => 18, "mim" => 16, "mahmuda" => 35, "maya" => 19);

echo "<pre>";
print_r($age);
echo "</pre>";

echo "<br>";
echo "<pre>";
var_dump($age); // check data type
echo "</pre>";

echo "<br>";
echo $age["moin"];

$age["maya"] = 20;
echo "<pre>";
print_r($age);
echo "</pre>";


/* use foreach loop */

foreach ($age as $key => $value) {
    echo "$key _ $value <br>";
}
