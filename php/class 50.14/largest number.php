<!DOCTYPE html>
<html>
<head>
    <title>Largest Number Using Prompt</title>
</head>
<body>

<h2>FIND OUT THE LARGEST NUMBER.</h2>

<?php
if (isset($_POST['num1'])) {
    // Superglobal $_POST থেকে input নেওয়া
    $num1 = $_POST['num1'];
    $num2 = $_POST['num2'];
    $num3 = $_POST['num3'];

    // Largest number calculation
    $largest = $num1;
    if ($num2 > $largest) $largest = $num2;
    if ($num3 > $largest) $largest = $num3;

    echo "<h3>Largest number: $largest</h3>";
}
?>

<script>
    // শুধু প্রথমবার show করার জন্য check
    if (!<?php echo isset($_POST['num1']) ? 'true' : 'false'; ?>) {
        // Prompt দিয়ে input নেওয়া
        var num1 = prompt("Number 1 লিখুন:");
        var num2 = prompt("Number 2 লিখুন:");
        var num3 = prompt("Number 3 লিখুন:");

        // PHP তে POST করার জন্য form তৈরি
        var form = document.createElement("form");
        form.method = "POST";
        form.style.display = "none";

        var n1 = document.createElement("input");
        n1.name = "num1"; n1.value = num1; form.appendChild(n1);

        var n2 = document.createElement("input");
        n2.name = "num2"; n2.value = num2; form.appendChild(n2);

        var n3 = document.createElement("input");
        n3.name = "num3"; n3.value = num3; form.appendChild(n3);

        document.body.appendChild(form);
        form.submit();
    }
</script>

</body>
</html>