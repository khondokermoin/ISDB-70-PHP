<?php
$repo = new StudentRepository();
$students = $repo->getAll();

foreach ($students as $s) {
    echo $s->getId() . " | " .
         $s->getName() . " | " .
         $s->getCourse() . " | " .
         $s->getPhone() . "<br>";
}