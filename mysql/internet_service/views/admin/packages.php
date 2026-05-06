<?php /** @var PDOStatement $packages */ ?>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
    <div class="flex justify-between items-center mb-6 border-b pb-4">
        <h2 class="text-2xl font-bold text-gray-800">Manage Packages</h2>
        <a href="admin.php?page=create_package" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition">
            <i class="fa fa-plus mr-2"></i> Add New
        </a>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php 
                if($_GET['msg'] == 'deleted') echo "Package successfully deleted!";
                if($_GET['msg'] == 'updated') echo "Package successfully updated!";
            ?>
        </div>
    <?php endif; ?>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 text-left">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-4 border-b">ID</th>
                    <th class="py-3 px-4 border-b">Package Name</th>
                    <th class="py-3 px-4 border-b">Speed</th>
                    <th class="py-3 px-4 border-b">Price</th>
                    <th class="py-3 px-4 border-b">Status</th>
                    <th class="py-3 px-4 border-b text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($packages->rowCount() > 0) {
                    while ($row = $packages->fetch()) {
                        $statusClass = $row['status'] == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 border-b"><?php echo $row['package_id']; ?></td>
                            <td class="py-2 px-4 border-b font-semibold"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo $row['speed_mbps']; ?> Mbps</td>
                            <td class="py-2 px-4 border-b">৳<?php echo $row['price']; ?></td>
                            <td class="py-2 px-4 border-b">
                                <span class="px-2 py-1 text-xs font-bold rounded-full <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td class="py-2 px-4 border-b text-center">
                                <!-- Edit Button -->
                                <a href="admin.php?page=edit_package&id=<?php echo $row['package_id']; ?>" class="text-blue-500 hover:text-blue-700 mx-1">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <!-- Delete Button (with JS Confirmation) -->
                                <a href="admin.php?action=delete_package&id=<?php echo $row['package_id']; ?>" onclick="return confirm('Are you sure you want to delete this package?');" class="text-red-500 hover:text-red-700 mx-1">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center py-4 text-gray-500'>No packages found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>