<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expensive Products</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased p-8">

    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Premium Products</h2>
            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-3 py-1 rounded-full">Price > 5000</span>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 border-b border-gray-200 text-gray-600 text-sm uppercase tracking-wider">
                            <th class="px-6 py-4 font-semibold">ID</th>
                            <th class="px-6 py-4 font-semibold">Product Name</th>
                            <th class="px-6 py-4 font-semibold text-right">Price (৳)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        
                        <?php
                        $db = new mysqli("localhost", "root", "", "your_database");
                        $result = $db->query("SELECT * FROM expensive_products");

                        if($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()){
                                echo "<tr class='hover:bg-gray-50 transition-colors'>
                                        <td class='px-6 py-4 text-sm text-gray-500'>#{$row['id']}</td>
                                        <td class='px-6 py-4 text-sm font-medium text-gray-900'>{$row['name']}</td>
                                        <td class='px-6 py-4 text-sm text-gray-700 text-right font-mono'>" . number_format($row['price']) . "</td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3' class='px-6 py-8 text-center text-gray-500'>No products found over 5000.</td></tr>";
                        }
                        ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>