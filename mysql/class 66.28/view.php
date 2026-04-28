<?php
include 'db.php';


$message = "";
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'updated') {
        $message = "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4 text-center'>Data Updated Successfully!</div>";
    } elseif ($_GET['msg'] == 'deleted') {
        $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4 text-center'>Data Deleted Successfully!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Students</title>
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-5 md:p-10">

    <div class="max-w-6xl mx-auto bg-white p-8 rounded-lg shadow-lg">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Student Database</h2>
            <a href="insert.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Add Student</a>
        </div>


        <?php echo $message; ?>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 border text-left">Id</th>
                        <th class="p-3 border text-left">Name</th>
                        <th class="p-3 border text-left">Email</th>
                        <th class="p-3 border text-left">Contact</th>
                        <th class="p-3 border text-left">Address</th>
                        <th class="p-3 border text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM students ORDER BY id ASC");
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 border"><?php echo htmlspecialchars($row['id']); ?></td>
                                <td class="p-3 border"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td class="p-3 border"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td class="p-3 border"><?php echo htmlspecialchars($row['contact']); ?></td>
                                <td class="p-3 border"><?php echo htmlspecialchars($row['address']); ?></td>
                                <td class="p-3 border text-center">
                                    <div class="flex justify-center gap-2">

                                        <a href="update.php?id=<?php echo $row['id']; ?>" 
                                           class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 text-sm">
                                           Edit
                                        </a>


                                        <a href="delete.php?id=<?php echo $row['id']; ?>" 
                                           onclick="return confirm('Are you sure you want to delete this?')"
                                           class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-sm">
                                           Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='5' class='p-5 text-center text-gray-500'>No data found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</body>
</html>
