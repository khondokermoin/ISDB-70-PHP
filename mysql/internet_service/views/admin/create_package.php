<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-sm border border-gray-200 p-8 mt-6">
    <div class="mb-6 border-b pb-4">
        <h2 class="text-2xl font-bold text-gray-800">Create New Package</h2>
        <p class="text-gray-500 text-sm">Add a new internet package offering to the database.</p>
    </div>

    <?php if(isset($success_message)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <form action="admin.php?page=create_package" method="POST">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Package Name -->
            <div class="col-span-2">
                <label class="block text-gray-700 font-medium mb-2">Package Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="e.g., Ultra Speed Pro" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>

            <!-- Speed -->
            <div>
                <label class="block text-gray-700 font-medium mb-2">Speed (Mbps) <span class="text-red-500">*</span></label>
                <input type="number" step="0.1" name="speed_mbps" required placeholder="e.g., 100" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>

            <!-- Price -->
            <div>
                <label class="block text-gray-700 font-medium mb-2">Price (BDT) <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" name="price" required placeholder="e.g., 1500" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>

            <!-- Quota -->
            <div>
                <label class="block text-gray-700 font-medium mb-2">Quota (GB)</label>
                <input type="number" name="quota_gb" value="9999" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                <small class="text-gray-500">Use 9999 for Unlimited.</small>
            </div>

            <!-- Duration -->
            <div>
                <label class="block text-gray-700 font-medium mb-2">Duration (Days)</label>
                <input type="number" name="duration_days" value="30" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>

            <!-- Status -->
            <div class="col-span-2">
                <label class="block text-gray-700 font-medium mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="active">Active (Visible to users)</option>
                    <option value="inactive">Inactive (Hidden)</option>
                </select>
            </div>
        </div>

        <div class="flex justify-end mt-8 border-t pt-4">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg transition shadow-md">
                Save Package
            </button>
        </div>
    </form>
</div>