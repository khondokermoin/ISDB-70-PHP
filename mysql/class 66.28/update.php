<?php
include 'db.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];


    $sql = "UPDATE students SET 
            name = '$name', 
            email = '$email', 
            contact = '$contact', 
            address = '$address' 
            WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        header("Location: view.php?msg=updated");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}


if(isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']); 
    $result = $conn->query("SELECT * FROM students WHERE id=$id");

    if($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        header("Location: view.php?error=notfound");
        exit();
    }
} else {
    header("Location: view.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Student</title>
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center text-blue-600">Update Student</h2>
        

        <form action="" method="post" class="space-y-4">
            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
            
            <div>
                <label class="block text-gray-700 font-medium">Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-blue-400 outline-none">
            </div>
            
            <div>
                <label class="block text-gray-700 font-medium">Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-blue-400 outline-none">
            </div>
            
            <div>
                <label class="block text-gray-700 font-medium">Contact</label>
                <input type="text" name="contact" value="<?php echo htmlspecialchars($row['contact']); ?>" required class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-blue-400 outline-none">
            </div>
            
            <div>
                <label class="block text-gray-700 font-medium">Address</label>
                <input type="text" name="address" value="<?php echo htmlspecialchars($row['address']); ?>" required class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-blue-400 outline-none">
            </div>
            
            <div class="flex gap-2">
                <a href="view.php" class="w-1/2 bg-gray-500 text-white text-center py-2 rounded-md hover:bg-gray-600 transition">Cancel</a>
                <button type="submit" class="w-1/2 bg-green-600 text-white py-2 rounded-md hover:bg-green-700 transition">Update Now</button>
            </div>
        </form>
    </div>
</body>
</html>
