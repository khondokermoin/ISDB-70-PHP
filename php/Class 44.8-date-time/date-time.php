<?php
echo "<script>setTimeout(function() {
  location.reload();
}, 1000);</script>";
// Assuming today is March 10th, 2001, 5:16:18 pm, and that we are in the
// Mountain Standard Time (MST) Time Zone
// date_default_timezone_set("America/Phoenix");

date_default_timezone_set('Asia/Dhaka');
echo date_default_timezone_get();
echo date("F j, Y, g:i a") . "\n";
echo "<br>";               // March 10, 2001, 5:16 pm
echo date("m.d.y") . "\n";
echo "<br>";                         // 03.10.01
echo date("j, n, Y") . "\n";
echo "<br>";                      // 10, 3, 2001
echo date("Ymd") . "\n";
echo "<br>";                          // 20010310
echo date('h-i-s, j-m-y, it is w Day') . "\n";
echo "<br>";    // 05-16-18, 10-03-01, 1631 1618 6 Satpm01
echo date('\i\t \i\s \t\h\e jS \d\a\y.') . "\n";
echo "<br>";   // it is the 10th day.
echo date("D M j G:i:s T Y") . "\n";
echo "<br>";              // Sat Mar 10 17:16:18 MST 2001
echo date('h:i:s \m \i\s\ \m\o\n\t\h') . "\n";
echo "<br>";     // 17:03:18 m is month
echo date("h:i:s") . "\n";
echo "<br>";                        // 17:16:18
echo date("Y-m-d h:i:s") . "\n";
echo "<br>";

echo date("h:i:s A");
echo "<br>";
echo "<br>";
echo "<br>";

$default_birthdate = "2005-01-09";

$today = new DateTime();
$birth = new DateTime($default_birthdate);
$age = $today->diff($birth);

echo "Age: " . $age->y . " Years, " . $age->m . " Months, " . $age->d . " Days";
// মোট দিন
$total_days = $age->format("%a");

// মোট সেকেন্ড
$total_seconds = $total_days * 24 * 60 * 60;

// মোট মিনিট
$total_minutes = $total_days * 24 * 60;

// মোট মাস (প্রায়ন) - বছর*12 + মাস
$total_months = $age->format("%y") * 12 + $age->format("%m");

// আউটপুট
echo "Total Days: $total_days\n";
echo "<br>";
echo "Total Months: $total_months\n";
echo "<br>";
echo "Total Minutes: $total_minutes\n";
echo "<br>";
echo "Total Seconds: $total_seconds\n";
echo "<br>";
