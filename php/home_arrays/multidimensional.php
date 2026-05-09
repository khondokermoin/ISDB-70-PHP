<?php

$emp = [
    [1, "Moin", "Manage", 5000],
    [2, "Mirad", "Salesman", 9000],
    [3, "Monju", "Engineer", 4000],
    [4, "Maysha", "Receptionist", 3000],
    [5, "Meghla", "Driver", 7000]

];

/* for ($row = 0; $row < 4; $row++) {
    for ($col = 0; $col < 4; $col++) {
        echo $emp[$row][$col] . " ";
    }
    echo "<br>";
} */

echo "<table border ='1px' cellpadding ='8px' cellspacing='0px'>";
echo "<tr>
        <th>id</th>
        <th>name</th>
        <th>post</th>
        <th>salery</th>
    </tr>";
foreach ($emp as $v1) {
    echo "<tr>";
    foreach ($v1 as $v2) {
        echo "<td>$v2</td>";
    }
    echo "</tr>";
}
echo "</table>";

echo "<pre>";
print_r($emp);
echo "</pre>";
