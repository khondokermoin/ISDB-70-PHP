<?php
session_start();

$_SESSION["user"] = "Moin";

echo $_SESSION["user"];
?>