<?php /** @var array $packageData */ ?>
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-sm border border-gray-200 p-8 mt-6">
    <div class="flex justify-between items-center mb-6 border-b pb-4">
        <h2 class="text-2xl font-bold text-gray-800">Edit Package</h2>
        <a href="admin.php?page=packages" class="text-gray-500 hover:text-gray-800"><i class="fa fa-arrow-left"></i> Back</a>
    </div>

    <form action="admin.php?action=update_package" method="POST">
        <!-- Hidden ID field -->
        <input type="hidden" name="package_id" value="<?php echo $packageData['package_id']; ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="col-span-2">
                <label class="block text-gray-700 font-medium mb-2">Package Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($packageData['name']); ?>" required class="w-full px-4 py-2 border rounded-lg">
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Speed (Mbps)</label>
                <input type="number" step="0.1" name="speed_mbps" value="<?php echo $packageData['speed_mbps']; ?>" required class="w-full px-4 py-2 border rounded-lg">
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Price (BDT)</label>
                <input type="number" step="0.01" name="price" value="<?php echo $packageData['price']; ?>" required class="w-full px-4 py-2 border rounded-lg">
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Quota (GB)</label>
                <input type="number" name="quota_gb" value="<?php echo $packageData['quota_gb']; ?>" class="w-full px-4 py-2 border rounded-lg">
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Duration (Days)</label>
                <input type="number" name="duration_days" value="<?php echo $packageData['duration_days']; ?>" class="w-full px-4 py-2 border rounded-lg">
            </div>

            <div class="col-span-2">
                <label class="block text-gray-700 font-medium mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 border rounded-lg">
                    <option value="active" <?php echo ($packageData['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ($packageData['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
        </div>

        <div class="flex justify-end mt-8 border-t pt-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-md">
                Update Package
            </button>
        </div>
    </form>
</div>