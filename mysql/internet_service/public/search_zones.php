<?php

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Search Value
$search = trim($_GET['search'] ?? '');

// Query
$sql = "
    SELECT * FROM coverage_zones
    WHERE district LIKE :search
    OR upazila LIKE :search
    OR description LIKE :search
    ORDER BY status ASC, district ASC
";

$stmt = $db->prepare($sql);

$stmt->execute([
    ':search' => "%{$search}%"
]);

$zones = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($zones) > 0):

    foreach ($zones as $z):

?>

    <!-- CARD -->
    <div class="zone-card bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 group overflow-hidden <?= $z['status'] == 'upcoming' ? 'opacity-75' : ''; ?>">

        <div class="p-6 flex items-start gap-5">

            <!-- ICON -->
            <div class="<?= $z['status'] == 'active'
                                ? 'bg-red-50 group-hover:bg-amberRed'
                                : 'bg-gray-100'; ?>
                    dark:bg-gray-700 transition-colors duration-300 p-4 rounded-xl flex-shrink-0">

                <i class="fa <?= $z['status'] == 'active'
                                    ? 'fa-map-marker-alt text-amberRed group-hover:text-white'
                                    : 'fa-tools text-gray-400'; ?>
                        text-3xl transition-colors duration-300"></i>

            </div>

            <!-- CONTENT -->
            <div>

                <h2 class="text-gray-900 dark:text-gray-100 text-2xl font-bold mb-1">

                    <?= htmlspecialchars($z['upazila']) ?>

                </h2>

                <p class="text-xs font-bold text-gray-500 uppercase mb-2">

                    <i class="fa fa-map mr-1"></i>

                    <?= htmlspecialchars($z['district']) ?>

                </p>

                <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">

                    <?= htmlspecialchars($z['description']) ?>

                </p>

                <!-- STATUS -->
                <?php if ($z['status'] == 'active'): ?>

                    <span class="inline-block mt-3 px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full border border-green-200">

                        <i class="fa fa-check-circle mr-1"></i>

                        Active Zone

                    </span>

                <?php else: ?>

                    <span class="inline-block mt-3 px-3 py-1 bg-orange-100 text-orange-700 text-xs font-bold rounded-full border border-orange-200">

                        <i class="fa fa-clock mr-1"></i>

                        Coming Soon

                    </span>

                <?php endif; ?>

            </div>

        </div>

    </div>

<?php

    endforeach;

else:

?>

    <!-- NO RESULTS -->
    <div class="col-span-full text-center py-16">

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-10 shadow-sm">

            <i class="fa fa-map-marker-alt text-5xl text-gray-300 mb-4"></i>

            <h3 class="text-2xl font-bold text-gray-700 dark:text-gray-200 mb-2">

                No Coverage Found

            </h3>

            <p class="text-gray-500 dark:text-gray-400">

                Sorry, we couldn't find your area coverage.

            </p>

        </div>

    </div>

<?php endif; ?>