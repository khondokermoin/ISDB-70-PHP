<?php
$lines = file("data.txt"); 

echo "
<table border='1' cellpadding='10' cellspacing='0'>
<tr>
    <th>Name</th>
    <th>Age</th>
    <th>Email</th>
    <th>Contact</th>
</tr>
";

foreach ($lines as $line) {
    $data = explode(",", trim($line)); 

    [$name, $age, $email, $contact] = $data;

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
?>