<?php
session_start();
require_once '../config/database.php';

// ডাটাবেস কানেকশন চালু করা হলো
$database = new Database();
$db = $database->getConnection();

// Include header
include '../views/layouts/header.php';
?>

<section class="bg-gradient-to-r from-gray-900 to-gray-800 text-white py-20 px-4 text-center border-b-4 border-amberRed">
    <div class="container mx-auto max-w-4xl">
        <h4 class="text-amberRed font-bold tracking-widest uppercase mb-2">Network Availability</h4>
        <h1 class="text-4xl md:text-5xl font-extrabold mb-4 leading-tight">Our Coverage Zones</h1>
        <p class="text-lg text-gray-300">Find out if Amar IT's super-fast broadband network is available in your city or neighborhood.</p>
    </div>
</section>

<section class="py-16 bg-gray-50 dark:bg-gray-900 min-h-[50vh]">
    <div class="container mx-auto max-w-7xl px-5">

        <div class="max-w-xl mx-auto mb-12 relative">
            <input type="text" id="searchInput" placeholder="Search your area (e.g. Munshiganj, Dhaka)..." class="w-full pl-5 pr-12 py-4 rounded-full shadow-md border border-gray-200 focus:outline-none focus:ring-2 focus:ring-amberRed text-gray-700 font-medium transition">
            <button class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-amberRed">
                <i class="fa fa-search text-xl"></i>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="zoneContainer">
            <?php
            // ডাটাবেস থেকে ডায়নামিক জোনগুলো কল করা হচ্ছে
            $zones = $db->query("SELECT * FROM coverage_zones ORDER BY status ASC, district ASC")->fetchAll(PDO::FETCH_ASSOC);

            if (count($zones) > 0) {
                foreach ($zones as $z):
            ?>
                    <div class="zone-card bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 group overflow-hidden <?php echo $z['status'] == 'upcoming' ? 'opacity-75' : ''; ?>">
                        <div class="p-6 flex items-start gap-5">
                            <div class="<?php echo $z['status'] == 'active' ? 'bg-red-50 group-hover:bg-amberRed' : 'bg-gray-100'; ?> dark:bg-gray-700 transition-colors duration-300 p-4 rounded-xl flex-shrink-0">
                                <i class="fa <?php echo $z['status'] == 'active' ? 'fa-map-marker-alt text-amberRed group-hover:text-white' : 'fa-tools text-gray-400'; ?> text-3xl transition-colors duration-300"></i>
                            </div>
                            <div>
                                <h2 class="text-gray-900 dark:text-gray-100 text-2xl font-bold mb-1"><?php echo htmlspecialchars($z['upazila']); ?></h2>
                                <p class="text-xs font-bold text-gray-500 uppercase mb-2"><i class="fa fa-map mr-1"></i> <?php echo htmlspecialchars($z['district']); ?></p>
                                <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed"><?php echo htmlspecialchars($z['description']); ?></p>

                                <?php if ($z['status'] == 'active'): ?>
                                    <span class="inline-block mt-3 px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full border border-green-200"><i class="fa fa-check-circle mr-1"></i> Active Zone</span>
                                <?php else: ?>
                                    <span class="inline-block mt-3 px-3 py-1 bg-orange-100 text-orange-700 text-xs font-bold rounded-full border border-orange-200"><i class="fa fa-clock mr-1"></i> Coming Soon</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
            <?php
                endforeach;
            } else {
                echo "<div class='col-span-full text-center text-gray-500 py-10'><i class='fa fa-info-circle text-2xl mb-2 block'></i> No coverage areas have been added yet. Please check back later!</div>";
            }
            ?>
        </div>

        <div class="mt-16 text-center bg-white border border-gray-200 rounded-2xl p-8 max-w-3xl mx-auto shadow-sm">
            <h3 class="text-xl font-bold text-gray-800 mb-2">Don't see your area?</h3>
            <p class="text-gray-600 mb-6">Contact our support team to request a connection survey in your neighborhood.</p>
            <a href="support.php" class="bg-gray-800 hover:bg-black text-white font-bold py-3 px-8 rounded-lg shadow-md transition"><i class="fa fa-headset mr-2"></i> Request Connection</a>
        </div>

    </div>
</section>

<script>
    document.getElementById('searchInput').addEventListener('input', function(e) {
        let searchTerm = e.target.value.toLowerCase();
        let cards = document.querySelectorAll('.zone-card');

        cards.forEach(card => {
            let cardText = card.innerText.toLowerCase();
            if (cardText.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
</script>

<?php include '../views/layouts/footer.php'; ?>