<?php
require_once '../config/database.php';
require_once '../src/Models/Package.php';

$database = new Database();
$db = $database->getConnection();

include '../views/layouts/header.php';
?>

<section class="bg-gradient-to-r from-gray-800 to-gray-700 text-white py-24 px-4 text-center">
    <div class="container mx-auto max-w-4xl">
        <h4 class="text-blue-400 font-bold tracking-widest uppercase mb-2">Business Solutions</h4>
        <h1 class="text-5xl font-extrabold mb-6 leading-tight">Corporate Internet Packages</h1>
        <p class="text-xl text-gray-300">Dedicated bandwidth, maximum uptime, and premium support for your growing business needs.</p>
    </div>
</section>
<section class="py-16 bg-gray-50">
    <div class="container mx-auto max-w-6xl px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-8">
            <?php
            $packageModel = new Package($db);
            // এখানে শুধু "corporate" টাইপের প্যাকেজগুলো কল করা হচ্ছে
            $packages = $packageModel->getActiveByType('corporate');

            if ($packages->rowCount() > 0) {
                while ($row = $packages->fetch()) {
                    ?>
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden transform transition duration-300 hover:shadow-xl hover:border-blue-500">
                        <div class="bg-gray-800 text-white p-6 text-center border-b border-gray-700">
                            <i class="fa fa-building text-4xl mb-4 text-blue-400"></i>
                            <h4 class="text-xl font-bold uppercase tracking-wide"><?php echo htmlspecialchars($row['name']); ?></h4>
                            <div class="my-4">
                                <span class="text-sm font-semibold text-gray-400 align-top">BDT</span>
                                <span class="text-4xl font-extrabold text-white"><?php echo htmlspecialchars($row['price']); ?></span>
                                <span class="text-gray-400">/mo</span>
                            </div>
                        </div>
                        <div class="p-6 bg-white">
                            <ul class="space-y-3 text-gray-600 text-left mx-auto max-w-xs mb-6">
                                <li><i class="fa fa-check text-blue-500 mr-2"></i> <strong><?php echo htmlspecialchars($row['speed_mbps']); ?> Mbps</strong> Dedicated Speed</li>
                                <li><i class="fa fa-check text-blue-500 mr-2"></i> Real IP Included</li>
                                <li><i class="fa fa-check text-blue-500 mr-2"></i> Fiber Optic Connection</li>
                                <li><i class="fa fa-check text-blue-500 mr-2"></i> 99.9% Uptime Guarantee</li>
                                <li><i class="fa fa-check text-blue-500 mr-2"></i> 24/7 Priority Support</li>
                            </ul>
                            <a href="order.php?id=<?php echo $row['package_id']; ?>" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded transition duration-300">Request Quote</a>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<div class='col-span-full text-center text-gray-500 py-10'>No corporate packages are currently available. Please contact support.</div>";
            }
            ?>
        </div>
    </div>
</section>
<?php include '../views/layouts/footer.php'; ?>