<?php 
/** @var array $packageData */ 

// Check if package is Enterprise (Speed and Price are 0)
$isEnterprise = ($packageData['speed_mbps'] == 0 && $packageData['price'] == 0);
// Check if Quota is Unlimited
$isUnlimited = is_null($packageData['quota_gb']);
?>
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-sm border border-gray-200 p-8 mt-6">
    <div class="flex justify-between items-center mb-6 border-b pb-4">
        <h2 class="text-2xl font-bold text-gray-800">Edit Package</h2>
        <a href="admin.php?page=packages" class="text-gray-500 hover:text-gray-800"><i class="fa fa-arrow-left"></i> Back</a>
    </div>

    <form action="admin.php?action=update_package" method="POST">
        <input type="hidden" name="package_id" value="<?php echo $packageData['package_id']; ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            
            <div class="col-span-2 md:col-span-1">
                <label class="block text-gray-700 font-medium mb-2">Package Type <span class="text-red-500">*</span></label>
                <select name="type" id="package_type" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="home" <?php echo ($packageData['type'] == 'home') ? 'selected' : ''; ?>>Home Internet</option>
                    <option value="corporate" <?php echo ($packageData['type'] == 'corporate') ? 'selected' : ''; ?>>Corporate Internet</option>
                </select>
            </div>

            <div id="corporate_features_div" class="col-span-2 md:col-span-1 <?php echo ($packageData['type'] == 'home') ? 'hidden' : ''; ?>">
                <label class="block text-gray-700 font-medium mb-2">Corporate Features</label>
                <select name="features" id="features" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Select Feature --</option>
                    <option value="Static IP, Priority Support" <?php echo ($packageData['features'] == 'Static IP, Priority Support') ? 'selected' : ''; ?>>Static IP, Priority Support</option>
                    <option value="1 Static IP, SLA" <?php echo ($packageData['features'] == '1 Static IP, SLA') ? 'selected' : ''; ?>>1 Static IP, SLA</option>
                    <option value="Dedicated Support" <?php echo ($packageData['features'] == 'Dedicated Support') ? 'selected' : ''; ?>>Dedicated Support</option>
                    <option value="2 Static IPs" <?php echo ($packageData['features'] == '2 Static IPs') ? 'selected' : ''; ?>>2 Static IPs</option>
                    <option value="Dedicated bandwidth" <?php echo ($packageData['features'] == 'Dedicated bandwidth') ? 'selected' : ''; ?>>Dedicated bandwidth</option>
                    <option value="SLA + Dedicated line" <?php echo ($packageData['features'] == 'SLA + Dedicated line') ? 'selected' : ''; ?>>SLA + Dedicated line (Enterprise)</option>
                </select>
            </div>

            <div class="col-span-2">
                <label class="block text-gray-700 font-medium mb-2">Package Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($packageData['name']); ?>" required class="w-full px-4 py-2 border rounded-lg">
            </div>

            <div class="col-span-2 bg-blue-50 p-3 rounded border border-blue-100">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" id="is_enterprise" class="mr-2 rounded text-blue-600 focus:ring-blue-500 w-5 h-5" <?php echo $isEnterprise ? 'checked' : ''; ?>>
                    <span class="text-gray-800 font-semibold">Enterprise Package (Custom Speed & Negotiable Price)</span>
                </label>
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Speed (Mbps) <span class="text-red-500">*</span></label>
                <input type="number" step="0.1" id="speed_input" name="speed_mbps" value="<?php echo $packageData['speed_mbps']; ?>" required class="w-full px-4 py-2 border rounded-lg <?php echo $isEnterprise ? 'bg-gray-100 text-gray-500' : ''; ?>" <?php echo $isEnterprise ? 'readonly' : ''; ?>>
                <small class="text-gray-500 <?php echo $isEnterprise ? '' : 'hidden'; ?>" id="speed_help">Speed set to Custom</small>
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Price (BDT) <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" id="price_input" name="price" value="<?php echo $packageData['price']; ?>" required class="w-full px-4 py-2 border rounded-lg <?php echo $isEnterprise ? 'bg-gray-100 text-gray-500' : ''; ?>" <?php echo $isEnterprise ? 'readonly' : ''; ?>>
                <small class="text-gray-500 <?php echo $isEnterprise ? '' : 'hidden'; ?>" id="price_help">Price set to Negotiable</small>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-2">Quota (Data Limit)</label>
                <select id="quota_type" name="quota_type" class="w-full px-4 py-2 border rounded-lg mb-2">
                    <option value="unlimited" <?php echo $isUnlimited ? 'selected' : ''; ?>>Unlimited Data</option>
                    <option value="limited" <?php echo !$isUnlimited ? 'selected' : ''; ?>>Limited Data</option>
                </select>
                <input type="number" id="quota_input" name="quota_gb" value="<?php echo $packageData['quota_gb']; ?>" placeholder="Enter GB" class="w-full px-4 py-2 border rounded-lg <?php echo $isUnlimited ? 'hidden' : ''; ?>">
            </div>

            <div class="mb-6">
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
            speedInput.value = '0';
            speedInput.readOnly = true;
            speedInput.classList.add('bg-gray-100', 'text-gray-500');
            speedHelp.classList.remove('hidden');

            priceInput.value = '0';
            priceInput.readOnly = true;
            priceInput.classList.add('bg-gray-100', 'text-gray-500');
            priceHelp.classList.remove('hidden');
        } else {
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