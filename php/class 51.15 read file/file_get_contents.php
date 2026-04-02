<?php
/* $content = file_get_contents("data.txt");
echo $content; */


// Line by Line Read (fgets())


/* $file = fopen("data.txt", "r") or die;

while (!feof($file)) {
    echo fgets($file) . "<br>";
}

fclose($file); */


// file() — Line array

/* $lines = file("data.txt");

foreach ($lines as $line) {
    echo $line . "<br>";
} */


// readfile() (direct output)
readfile("data.txt");

?>
