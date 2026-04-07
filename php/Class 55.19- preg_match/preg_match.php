<?php
$data = "aldskjf656asd12adasf";
$data1 = "+8801647615608";
// $p = "/^[a-z]{2,4}";
$q = "/^(\\+8801|8801|01)[3-9][0-9]{8}$/";
// $r = "/^[a-z]{2,4}";
echo preg_match($q, $data1);
echo "<br>";

$email = "tes265-Tfa+t@gMail984.coVAqaeeweradsf";
$pattern = "/^[a-zA-Z0-9._+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
echo preg_match($pattern, $email);


if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Valid Email";
} else {
    echo "Invalid Email";
}
?>