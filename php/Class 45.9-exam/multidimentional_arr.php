<?php
$arr = [
    ["A", "B", "C", "D", "E"],
    ["B", "T", "S", "F", "M"],
    [3, 6, 4, 7, 5]
];
// use normal for loop

/* for ($i = 0; $i < count($arr); $i++) {
    for ($j = 0; $j < count($arr[$i]); $j++) {
        echo $arr[$i][$j] . " ";
    }
    echo "<br>";
} */


// using foreach loop
/* foreach ($arr as $row) {
    foreach ($row as $item) {
        echo $item . " ";
    }
    echo "<br>";
} */


foreach ($arr as $rowIndex => $row) {
    echo "<p>Row number $rowIndex</p>";
    echo "<ul>";
    foreach ($row as $item) {
        echo "<li>$item</li>";
    }
    echo "</ul>";
}
