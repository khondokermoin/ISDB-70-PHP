<?php
$students = [
    ["Moin", 22, "moin@gmail.com", "01647615608"],
    ["Rahim", 23, "rahim@gmail.com", "01711111111"],
    ["Karim", 24, "karim@gmail.com", "01822222222"],
    ["Sabbir", 25, "sabbir@gmail.com", "01933333333"]
];

echo "
<table border='1' cellpadding='10' cellspacing='0'>
    <tr>
        <th>Name</th>
        <th>Age</th>
        <th>Email</th>
        <th>Contact</th>
    </tr>
";

foreach ($students as [$name, $age, $email, $contact]) {
    echo "
    <tr>
        <td>$name</td>
        <td>$age</td>
        <td>$email</td>
        <td>$contact</td>
    </tr>
    ";
}

echo "</table>";

/* $students = [
    "Moin", 22, "moin@gmail.com", "01647615608", 
    "Rahim", 23, "rahim@gmail.com", "01711111111", 
    "Karim", 24, "karim@gmail.com", "01822222222", 
    "Sabbir", 25, "sabbir@gmail.com", "01933333333" 
];

$students = array_chunk($students, 4);

foreach ($students as [$name, $age, $email, $contact]) {
    echo "$name - $age - $email - $contact <br>";
} */
?>