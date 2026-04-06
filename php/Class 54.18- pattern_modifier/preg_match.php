<?php

$str = "this is a caw redion vi bolod";
$pattern = "/i/i";

echo preg_match_all($pattern, $str);
echo preg_match($pattern, $str);
echo "<br>";

$data = "we have a plan";
$p = "/plan/i";

echo preg_replace($p, "idea", $data)
?>