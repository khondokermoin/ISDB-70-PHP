<?php
session_start();
require_once '../config/database.php';
require_once '../src/Models/Package.php';
$db = (new Database())->getConnection();
$packageModel = new Package($db);
$packages = $packageModel->getAllActive();

include '../views/layouts/header.php';
?>
<div class="container mx-auto py-10 px-4">
    <h2 class="text-3xl font-bold text-center mb-10">Upgrade Your Plan</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php while ($row = $packages->fetch()): ?>
            <div class="bg-white p-6 rounded-xl shadow border text-center">
                <h4 class="font-bold text-xl uppercase"><?php echo $row['name']; ?></h4>
                <p class="text-2xl font-bold text-amberRed my-3">৳<?php echo number_format($row['price']); ?></p>
                <button onclick="alert('Our team will contact you for upgrading to this plan.')" class="bg-gray-800 text-white px-6 py-2 rounded">Request Upgrade</button>
            </div>
        <?php endwhile; ?>
    </div>
</div>