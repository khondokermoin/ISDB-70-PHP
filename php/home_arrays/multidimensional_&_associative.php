<?php

$marks = [
    "Moin" => [
        "Match" => 80,
        "Physics" => 70,
        "Chemistry" => 60,
    ],
    "Mirad" => [
        "Match" => 88,
        "Physics" => 77,
        "Chemistry" => 67,
    ],
    "Mahmuda" => [
        "Match" => 60,
        "Physics" => 78,
        "Chemistry" => 75,
    ],
    "Mariya" => [
        "Match" => 95,
        "Physics" => 78,
        "Chemistry" => 65,
    ],

];

echo "<table border=solid 1px cellspacing =0px>";
echo "<tr>
<th>Name</th>
<th>Match</th>
<th>Physics</th>
<th>Chemistry</th>
</tr>";
foreach ($marks as $key => $v1) {
    echo "<tr>
        <td>$key</td>";
    foreach ($v1 as $v2) {
        echo "<td>$v2</td>";
    }
    echo "</tr>";
}
echo "</table>";

echo "<pre>";
print_r($marks);
echo "</pre>";
