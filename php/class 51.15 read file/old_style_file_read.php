<?php
$file = fopen("data.txt", "r"); // open file

$content = fread($file, filesize("data.txt")); // read file

echo $content;

fclose($file); // close file
?>