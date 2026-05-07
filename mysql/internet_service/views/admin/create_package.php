<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-sm border border-gray-200 p-8 mt-6">
    <div class="mb-6 border-b pb-4">
        <h2 class="text-2xl font-bold text-gray-800">Create New Package</h2>
        <p class="text-gray-500 text-sm">Add a new internet package offering to the database.</p>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <form action="admin.php?page=create_package" method="POST">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

            <div class="col-span-2 md:col-span-1">
                <label class="block text-gray-700 font-medium mb-2">Package Type <span class="text-red-500">*</span></label>
                <select name="type" id="package_type" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="home">Home Internet</option>
                    <option value="corporate">Corporate Internet</option>
                </select>
            </div>

            <div id="corporate_features_div" class="col-span-2 md:col-span-1 hidden">
                <label class="block text-gray-700 font-medium mb-2">Corporate Features</label>
                <select name="features" id="features" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">-- Select Feature --</option>
                    <option value="Static IP, Priority Support">Static IP, Priority Support</option>
                    <option value="1 Static IP, SLA">1 Static IP, SLA</option>
                    <option value="Dedicated Support">Dedicated Support</option>
                    <option value="2 Static IPs">2 Static IPs</option>
                    <option value="Dedicated bandwidth">Dedicated bandwidth</option>
                    <option value="SLA + Dedicated line">SLA + Dedicated line (Enterprise)</option>
                </select>
            </div>

            <div class="col-span-2">
                <label class="block text-gray-700 font-medium mb-2">Package Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="e.g., Ultra Speed Pro" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>

            <div class="col-span-2 bg-blue-50 p-3 rounded border border-blue-100">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" id="is_enterprise" class="mr-2 rounded text-blue-600 focus:ring-blue-500 w-5 h-5">
                    <span class="text-gray-800 font-semibold">Enterprise Package (Custom Speed & Negotiable Price)</span>
                </label>
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Speed (Mbps) <span class="text-red-500">*</span></label>
                <input type="number" step="0.1" id="speed_input" name="speed_mbps" required placeholder="e.g., 100" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                <small class="text-gray-500 hidden" id="speed_help">Speed set to Custom</small>
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Price (BDT) <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" id="price_input" name="price" required placeholder="e.g., 1500" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                <small class="text-gray-500 hidden" id="price_help">Price set to Negotiable</small>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-2">Quota (Data Limit)</label>
                <select id="quota_type" name="quota_type" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 mb-2 transition">
                    <option value="unlimited" selected>Unlimited Data</option>
                    <option value="limited">Limited Data</option>
                </select>
                <input type="number" id="quota_input" name="quota_gb" placeholder="Enter GB (e.g., 500)" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 hidden transition">
                <small class="text-gray-500">Select Unlimited or specify a quota in GB.</small>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-2">Duration (Days)</label>
                <input type="number" name="duration_days" value="30" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>

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

<script>
    // 1. Quota Type Toggle
    document.getElementById('quota_type').addEventListener('change', function() {
        const quotaInput = document.getElementById('quota_input');
        if (this.value === 'limited') {
            quotaInput.classList.remove('hidden');
            quotaInput.required = true;
        } else {
            quotaInput.classList.add('hidden');
            quotaInput.required = false;
            quotaInput.value = '';
        }
    });

    // 2. Corporate Features Toggle
    document.getElementById('package_type').addEventListener('change', function() {
        const featureDiv = document.getElementById('corporate_features_div');
        if (this.value === 'corporate') {
            featureDiv.classList.remove('hidden');
        } else {
            featureDiv.classList.add('hidden');
            document.getElementById('features').value = ''; // Reset selection
        }
    });

    // 3. Enterprise Checkbox Logic
    document.getElementById('is_enterprise').addEventListener('change', function() {
        const speedInput = document.getElementById('speed_input');
        const priceInput = document.getElementById('price_input');
        const speedHelp = document.getElementById('speed_help');
        const priceHelp = document.getElementById('price_help');

        if (this.checked) {
            // Disable and set to 0 for Enterprise
            speedInput.value = '0';
            speedInput.readOnly = true;
            speedInput.classList.add('bg-gray-100', 'text-gray-500');
            speedHelp.classList.remove('hidden');

            priceInput.value = '0';
            priceInput.readOnly = true;
            priceInput.classList.add('bg-gray-100', 'text-gray-500');
            priceHelp.classList.remove('hidden');
        } else {
            // Re-enable for normal packages
            speedInput.value = '';
            speedInput.readOnly = false;
            speedInput.classList.remove('bg-gray-100', 'text-gray-500');
            speedHelp.classList.add('hidden');

            priceInput.value = '';
            priceInput.readOnly = false;
            priceInput.classList.remove('bg-gray-100', 'text-gray-500');
            priceHelp.classList.add('hidden');
        }
    });
</script>