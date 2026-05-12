<?php
session_start();
require_once '../config/database.php';
require_once '../src/Models/Package.php';

$database = new Database();
$db = $database->getConnection();

include '../views/layouts/header.php';
?>

<section class="bg-gradient-to-r from-red-600 to-amber-600 text-white py-24 px-4 text-center">
    <div class="container mx-auto max-w-4xl">
        <h4 class="text-white font-bold tracking-widest uppercase mb-2">For Your Family</h4>
        <h1 class="text-5xl font-extrabold mb-6 leading-tight">Superfast Home Internet</h1>
        <p class="text-xl text-red-50">Unlimited streaming, lag-free gaming, and bufferless browsing for your entire home.</p>
    </div>
</section>

<section class="py-16 bg-gray-50">
    <div class="container mx-auto max-w-6xl px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-8">
            <?php
            $packageModel = new Package($db);

            // 🔥 এখানে শুধু "home" টাইপের প্যাকেজগুলো কল করা হচ্ছে (আপনার ডাটাবেসে টাইপের নাম 'home' বা 'standard' হতে পারে)
            $packages = $packageModel->getActiveByType('home');

            if ($packages->rowCount() > 0) {
                while ($row = $packages->fetch()) {
                    $speedDisplay = ($row['speed_mbps'] == 0) ? 'Custom' : htmlspecialchars($row['speed_mbps']) . ' Mbps';
            ?>
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden transform transition duration-300 hover:scale-105 hover:border-amberRed">
                        <div class="p-6 text-center border-b border-gray-100">
                            <i class="fa fa-home text-5xl text-gray-300 mb-4"></i>
                            <h4 class="text-xl font-bold text-gray-800 uppercase tracking-wide"><?php echo htmlspecialchars($row['name']); ?></h4>
                            <div class="my-4">
                                <span class="text-sm font-semibold text-gray-500 align-top">BDT</span>
                                <span class="text-4xl font-extrabold text-amberRed"><?php echo number_format($row['price']); ?></span>
                                <span class="text-gray-500">/mo</span>
                            </div>
                        </div>
                        <div class="p-6 bg-gray-50">
                            <ul class="space-y-3 text-gray-600 text-center mb-6">
                                <li>Speed: <span class="font-bold text-gray-800"><?php echo $speedDisplay; ?></span></li>
                                <li>Bufferless YouTube & Facebook</li>
                                <li>BDIX Connected</li>
                                <li>Free Installation</li>
                                <li>24/7 Phone Support</li>
                            </ul>
                            <a href="order.php?id=<?php echo $row['package_id']; ?>" class="block w-full text-center bg-gray-800 hover:bg-amberRed text-white font-semibold py-3 rounded transition duration-300">Order Now <i class="fa fa-arrow-right ml-2"></i></a>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo "<div class='col-span-full text-center text-gray-500 py-10'>No home packages are currently available. Please check back later.</div>";
            }
            ?>
        </div>
    </div>
</section>

<?php include '../views/layouts/footer.php'; ?>