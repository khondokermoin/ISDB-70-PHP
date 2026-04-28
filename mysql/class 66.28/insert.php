<?php
include 'db.php';

$message = "";


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];

  
    $sql = "INSERT INTO students (name, email, contact, address) 
            VALUES ('$name', '$email', '$contact', '$address')";

    if ($conn->query($sql) === TRUE) {
        
        $message = "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4' role='alert'>
                        <span class='block sm:inline'>Data inserted successfully!</span>
                    </div>";
    } else {
        
        $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>
                        <span class='block sm:inline'>Error: " . $conn->error . "</span>
                    </div>";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <!-- Tailwind CSS CDN -->
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <?php echo $message; ?>
        <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">Student Information Form</h2>
        
        <form action="insert.php" method="post" class="space-y-4">
            <!-- Name Input -->
            <div>
                <label class="block text-gray-700 font-medium">Name</label>
                <input type="text" name="name" placeholder="Enter full name" required
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Email Input -->
            <div>
                <label class="block text-gray-700 font-medium">Email</label>
                <input type="email" name="email" placeholder="example@mail.com" required
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Contact Input -->
            <div>
                <label class="block text-gray-700 font-medium">Contact</label>
                <input type="text" name="contact" placeholder="017XXXXXXXX" required
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Address Input -->
            <div>
                <label class="block text-gray-700 font-medium">Address</label>
                <textarea name="address" rows="3" placeholder="Enter your address" required
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-md hover:bg-blue-700 transition duration-300">
                Submit Data
            </button>
            <div class="mt-4 text-center">
    <a href="view.php" 
        class="inline-block w-full bg-gray-500 text-white font-bold py-2 px-4 rounded-md hover:bg-gray-600 transition duration-300">
        View All Students
    </a>
</div>
        </form>
    </div>
<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</body>
</html>
