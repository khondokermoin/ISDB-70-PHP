<?php
$password = "1354646asdfaser84$#@^&*()GFDGH";

echo hash('SHA512',$password); 
echo hash('SHA512',$password); 
echo "<br>";
echo "<br>";
echo hash('SHA224',$password); 
echo "<br>";
echo "<br>";
echo hash('SHA256',$password); 
echo "<br>";
echo "<br>";
echo hash('SHA384',$password); 
echo "<br>";
echo "<br>";
echo hash('md5',$password); 
echo "<br>";
echo "<br>";
echo password_hash($password, PASSWORD_DEFAULT); 

$r = password_hash($password, PASSWORD_DEFAULT); 
echo "<br>";
echo "<br>";
if(password_verify($password, $r)){
echo "valid";
}else{
    echo "invlid";
}
echo "<br>";
echo "<br>";
echo $r;
echo "<br>";
echo "<br>";

$stor = "123";
echo base64_encode($stor);
echo "<br>";
echo "<br>";
echo base64_decode('MTIz');


echo "<br>";
echo "<br>";

$pas = '123';
$key = 'sdf55';
$method = "AES-128-CTR";

$iv = openssl_random_pseudo_bytes(16);
$encrypted = openssl_encrypt($pas, $method, $key, 0, $iv);
$decrypted = openssl_decrypt($encrypted,$method,$pas, 0,$iv);


echo ("Orignal: ".$pas);
echo "<br>";
echo ("Encrypted: ".$encrypted);
echo "<br>";
echo ("Decrypted: ".$decrypted);

echo "<br>";
echo "<br>";

$pas = '123';
$key = 'sdf55';
$method = "AES-128-CTR";

$iv = openssl_random_pseudo_bytes(16);
$encrypted = openssl_encrypt($pas, $method, $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
$decrypted = openssl_decrypt($encrypted,$method,$pas, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,$iv);


echo ("Orignal: ".$pas);
echo "<br>";
echo ("Encrypted: ".$encrypted);
echo "<br>";
echo ("Decrypted: ".$decrypted);

echo "<br>";
echo "<br>";

$pas = '123';
$key = 'sdf55';
$method = "AES-128-CTR";

$iv = openssl_random_pseudo_bytes(16);
$encrypted = openssl_encrypt($pas, $method, $key, OPENSSL_RAW_DATA, $iv);
$decrypted = openssl_decrypt($encrypted,$method,$pas, OPENSSL_RAW_DATA,$iv);


echo ("Orignal: ".$pas);
echo "<br>";
echo ("Encrypted: ".$encrypted);
echo "<br>";
echo ("Decrypted: ".$decrypted);

echo "<br>";
echo "<br>";

$pas = '123';
$key = 'sdf55';
$method = "AES-128-CTR";

$iv = openssl_random_pseudo_bytes(16);
$encrypted = openssl_encrypt($pas, $method, $key, OPENSSL_ZERO_PADDING, $iv);
$decrypted = openssl_decrypt($encrypted,$method,$pas, OPENSSL_ZERO_PADDING,$iv);


echo ("Orignal: ".$pas);
echo "<br>";
echo ("Encrypted: ".$encrypted);
echo "<br>";
echo ("Decrypted: ".$decrypted);

echo "<br>";
echo "<br>";

$pas = '123';
$key = 'sdf55';
$method = "AES-128-GCM";

$iv = openssl_random_pseudo_bytes(16);
$tag = "";

$encrypted = openssl_encrypt($pas, $method, $key, 0, $iv, $tag);
$decrypted = openssl_decrypt($encrypted, $method, $key, 0, $iv, $tag);

echo "Original: " . $pas . "<br>";
echo "Encrypted: " . $encrypted . "<br>";
echo "Decrypted: " . $decrypted;

?>