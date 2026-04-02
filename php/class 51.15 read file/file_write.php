<?php
// fwrite()

/* $file = fopen("data.txt", "w");
fwrite($file, "Hello World");
fclose($file); */

//w (Write) 

/* $file = fopen("data.txt", "w");
fwrite($file, "New Data");
fclose($file); */

// a (Append)

/* $file = fopen("data.txt", "a");
fwrite($file, "New Line\n");
fclose($file); */

// a+ (Append + Read)

$file = fopen("data.txt", "a+");
fwrite($file, "Add Data\n");

rewind($file);
echo fread($file, filesize("data.txt"));

fclose($file);






































?>