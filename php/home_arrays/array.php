<?php
$color = ["red", 20, "blue", 60, "yellow", 50];
echo "<pre>";
print_r($color);
echo "</pre>";
echo "<br>";

echo "<ul>";
for ($i = 0; $i  < 5; $i++) {
    echo "<li>" . $color[$i] . "</li>";
}
echo "</ul>";
