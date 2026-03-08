<?php
echo '<script>
        setInterval(function() {
            location.reload();
        }, 1000);
      </script>';
$name = "Rahim"; // global variable

function showName()
{
    global $name;
    echo $name;
}

showName();


echo $_SERVER['SCRIPT_NAME'];
echo "<br>";
echo $_SERVER['SERVER_ADDR'];
echo "<br>";
echo $_SERVER['SERVER_PORT'];
echo "<br>";
echo $_SERVER['SERVER_SIGNATURE'];
echo "<br>";
echo $_SERVER['REQUEST_METHOD'];
echo "<br>";
echo $_SERVER['DOCUMENT_ROOT'];
echo "<br>";
echo $_SERVER['SERVER_ADMIN'];
echo "<br>";
