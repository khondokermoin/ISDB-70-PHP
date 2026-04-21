<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Manufacturer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased min-h-screen flex items-center justify-center p-4">

    <div class="bg-white w-full max-w-md rounded-xl shadow-lg border border-gray-100 p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Add Manufacturer</h2>

        <?php
        if(isset($_POST['submit'])){
            $db = new mysqli("localhost", "root", "", "your_database");

            $name = $_POST['name'];
            $address = $_POST['address'];
            $contact = $_POST['contact'];

            $stmt = $db->prepare("CALL add_manufacturer(?, ?, ?)");
            $stmt->bind_param("sss", $name, $address, $contact);
            
            if($stmt->execute()){
                // Success Alert UI
                echo '<div class="mb-6 p-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200" role="alert">
                        <span class="font-medium">Success!</span> Manufacturer added successfully.
                      </div>';
            }
        }
        ?>

        <form method="post" class="space-y-5">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
                <input type="text" id="name" name="name" required 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors" 
                       placeholder="Enter manufacturer name">
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <input type="text" id="address" name="address" required 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors" 
                       placeholder="123 Business Rd.">
            </div>

            <div>
                <label for="contact" class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                <input type="text" id="contact" name="contact" required 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors" 
                       placeholder="+880 1XXX-XXXXXX">
            </div>

            <button type="submit" name="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-lg shadow-sm transition-colors focus:ring-4 focus:ring-blue-200">
                Save Manufacturer
            </button>
        </form>
    </div>

</body>
</html>