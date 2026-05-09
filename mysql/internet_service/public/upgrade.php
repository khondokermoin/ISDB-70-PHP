<?php
session_start();

// 🔥 সিকিউরিটি চেক: লগইন করা না থাকলে লগইন পেজে পাঠাবে
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';
require_once '../src/Models/Package.php';

$db = (new Database())->getConnection();
$packageModel = new Package($db);
$user_id = $_SESSION['user_id'];

$success_msg = "";
$error_msg = "";

// ==========================================
// 🔥 PACKAGE UPGRADE LOGIC WITH INVOICE & NOTIFICATION
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upgrade_package_id'])) {
    $new_pkg_id = (int)$_POST['upgrade_package_id'];
    $new_pkg = $packageModel->getById($new_pkg_id);

    if ($new_pkg) {
        // চেক করা হচ্ছে কাস্টমারের আগে থেকেই কোনো আপগ্রেড রিকোয়েস্ট পেন্ডিং আছে কিনা
        $checkTicket = $db->prepare("SELECT ticket_id FROM tickets WHERE user_id = ? AND category = 'Package Upgrade' AND status != 'resolved'");
        $checkTicket->execute([$user_id]);

        if ($checkTicket->rowCount() > 0) {
            $error_msg = "You already have a pending upgrade request. Our team will contact you soon.";
        } else {
            try {
                $db->beginTransaction();

                // ১. সাপোর্ট টিকিট তৈরি
                $subject = "Request to upgrade package to: " . $new_pkg['name'];
                $message = "Customer requested an upgrade to " . $new_pkg['name'] . " (" . $new_pkg['speed_mbps'] . " Mbps). Invoice generated for ৳" . $new_pkg['price'] . ". Please review and activate the new package.";

                $db->prepare("INSERT INTO tickets (user_id, subject, category, message, status) VALUES (?, ?, 'Package Upgrade', ?, 'open')")->execute([$user_id, $subject, $message]);

                // ২. নতুন ইনভয়েস তৈরি (Unpaid)
                $inv_no = "UPG-" . strtoupper(uniqid());
                $db->prepare("INSERT INTO invoices (user_id, subscription_id, invoice_number, amount, due_date, status) 
                              SELECT ?, subscription_id, ?, ?, DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'unpaid' 
                              FROM subscriptions WHERE user_id = ? ORDER BY subscription_id DESC LIMIT 1")
                    ->execute([$user_id, $inv_no, $new_pkg['price'], $user_id]);

                // ৩. অ্যাডমিনকে নোটিফিকেশন পাঠানো
                $adminQuery = $db->query("SELECT user_id FROM users WHERE role = 'admin' LIMIT 1")->fetch();
                if ($adminQuery) {
                    $notif_msg = "🚀 Upgrade & Invoice: " . $_SESSION['user_name'] . " requested to upgrade to " . $new_pkg['name'] . ". New invoice generated.";
                    $db->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")->execute([$adminQuery['user_id'], $notif_msg]);
                }

                $db->commit();
                $success_msg = "Your upgrade request for '" . $new_pkg['name'] . "' has been submitted! An invoice has been generated. Please pay to activate the new speed.";
            } catch (Exception $e) {
                $db->rollBack();
                $error_msg = "Something went wrong while processing your request. Please try again.";
            }
        }
    } else {
        $error_msg = "Invalid package selected.";
    }
}

// প্যাকেজ লিস্ট আনা
$packages = $packageModel->getAllActive();

// কাস্টমার বর্তমানে কোন প্যাকেজ ব্যবহার করছে তা বের করা
$currentSub = $db->prepare("SELECT p.package_id FROM subscriptions s JOIN packages p ON s.package_id = p.package_id WHERE s.user_id = ? ORDER BY s.subscription_id DESC LIMIT 1");
$currentSub->execute([$user_id]);
$current_pkg = $currentSub->fetch(PDO::FETCH_ASSOC);
$current_pkg_id = $current_pkg ? $current_pkg['package_id'] : 0;

include '../views/layouts/header.php';
?>

<div class="bg-gray-50 min-h-screen py-10">
    <div class="container mx-auto max-w-6xl px-4">

        <div class="mb-6 flex justify-between items-center">
            <a href="user_dashboard.php" class="text-blue-600 hover:underline font-semibold"><i class="fa fa-arrow-left mr-1"></i> Back to Dashboard</a>
        </div>

        <div class="text-center mb-10">
            <h2 class="text-4xl font-extrabold text-gray-800">Upgrade Your Plan</h2>
            <p class="text-gray-500 mt-2">Choose a higher speed package that fits your needs.</p>
            <div class="w-16 h-1 bg-red-600 mx-auto mt-4"></div>
        </div>

        <?php if ($success_msg): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-8 rounded shadow-sm text-center font-bold">
                <i class="fa fa-check-circle mr-2"></i> <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-8 rounded shadow-sm text-center font-bold">
                <i class="fa fa-exclamation-triangle mr-2"></i> <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php while ($row = $packages->fetch()):
                $is_current = ($row['package_id'] == $current_pkg_id);
            ?>
                <div class="bg-white p-8 rounded-2xl shadow-lg border <?php echo $is_current ? 'border-red-500 ring-2 ring-red-200 relative overflow-hidden' : 'border-gray-200 hover:shadow-xl transition transform hover:-translate-y-1'; ?> text-center flex flex-col justify-between">

                    <?php if ($is_current): ?>
                        <div class="absolute top-0 right-0 bg-red-600 text-white font-bold px-4 py-1 rounded-bl-lg text-xs">CURRENT PLAN</div>
                    <?php endif; ?>

                    <div>
                        <h4 class="font-extrabold text-2xl uppercase text-gray-800"><?php echo htmlspecialchars($row['name']); ?></h4>
                        <div class="my-6">
                            <span class="text-4xl font-black text-red-600">৳<?php echo number_format($row['price']); ?></span>
                            <span class="text-gray-500">/<?php echo $row['duration_days']; ?> Days</span>
                        </div>

                        <div class="space-y-3 text-gray-600 text-sm font-medium mb-8">
                            <p class="flex items-center justify-center"><i class="fa fa-tachometer-alt text-green-500 mr-2"></i> Speed: <?php echo $row['speed_mbps']; ?> Mbps</p>
                            <p class="flex items-center justify-center"><i class="fa fa-database text-blue-500 mr-2"></i> Quota: <?php echo is_null($row['quota_gb']) ? 'Unlimited' : $row['quota_gb'] . ' GB'; ?></p>
                            <?php if (!empty($row['features'])): ?>
                                <p class="text-xs text-gray-400 mt-2 line-clamp-2"><?php echo htmlspecialchars($row['features']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <?php if ($is_current): ?>
                            <button disabled class="w-full bg-gray-200 text-gray-500 font-bold py-3 rounded-lg cursor-not-allowed">Active Plan</button>
                        <?php else: ?>
                            <form action="" method="POST" onsubmit="return confirm('Are you sure you want to request an upgrade to this package?');">
                                <input type="hidden" name="upgrade_package_id" value="<?php echo $row['package_id']; ?>">
                                <button type="submit" class="w-full bg-gray-800 hover:bg-black text-white font-bold py-3 rounded-lg shadow-md transition">Request Upgrade</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>