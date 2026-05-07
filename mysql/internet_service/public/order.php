<?php
session_start();
require_once '../config/database.php';
require_once '../src/Models/Package.php';

$database = new Database();
$db = $database->getConnection();

// ১. Sanitize Package ID (Security Update)
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("<div class='p-10 text-center text-red-500 font-bold text-2xl'>Invalid Package Request!</div>");
}

$package_id = (int) $_GET['id'];
$packageModel = new Package($db);
$package = $packageModel->getById($package_id);

if (!$package) {
    die("<div class='p-10 text-center text-red-500 font-bold text-2xl'>Package Not Found!</div>");
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Trim User Inputs (Security Update)
        $full_name = trim($_POST['full_name']);
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email']);
        $address = trim($_POST['address']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate Email Properly (Security Update)
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address.");
        }

        // Phone Number Validation (Security Update)
        if (!preg_match('/^01[0-9]{9}$/', $phone)) {
            throw new Exception("Invalid mobile number format.");
        }

        // Password Length Validation (Security Update)
        if (strlen($password) < 6) {
            throw new Exception("Password must be at least 6 characters.");
        }

        // Confirm Password Matching (Security Update)
        if ($password !== $confirm_password) {
            throw new Exception("Passwords do not match.");
        }

        // Email Duplicate Check (Security Update)
        $emailCheck = $db->prepare("SELECT user_id FROM users WHERE email = :email LIMIT 1");
        $emailCheck->execute([':email' => $email]);
        if ($emailCheck->rowCount() > 0) {
            throw new Exception("Email already exists. Please login or use a different email.");
        }

        // --- Database Transaction Start ---
        $db->beginTransaction();

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $userQuery = "INSERT INTO users (full_name, email, password_hash, phone, address, status) VALUES (:name, :email, :pass, :phone, :address, 'active')";
        $stmtUser = $db->prepare($userQuery);
        $stmtUser->execute([
            ':name' => $full_name,
            ':email' => $email,
            ':pass' => $password_hash,
            ':phone' => $phone,
            ':address' => $address
        ]);
        $user_id = $db->lastInsertId();

        $subQuery = "INSERT INTO subscriptions (user_id, package_id, start_date, end_date, status) VALUES (:uid, :pid, NULL, NULL, 'pending')";
        $stmtSub = $db->prepare($subQuery);
        $stmtSub->execute([
            ':uid' => $user_id,
            ':pid' => $package_id
        ]);
        $subscription_id = $db->lastInsertId();

        $invoice_no = "INV-" . strtoupper(uniqid()); 
        $invQuery = "INSERT INTO invoices (user_id, subscription_id, invoice_number, period_start, period_end, amount, due_date, status) VALUES (:uid, :sid, :inv_no, CURDATE(), DATE_ADD(CURDATE(), INTERVAL :days DAY), :amount, DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'unpaid')";
        $stmtInv = $db->prepare($invQuery);
        $stmtInv->execute([
            ':uid' => $user_id,
            ':sid' => $subscription_id,
            ':inv_no' => $invoice_no,
            ':days' => $package['duration_days'],
            ':amount' => $package['price']
        ]);

        $db->commit();
        
        // Session Security (Security Update)
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $full_name;
        
        header("Location: user_dashboard.php");
        exit;

    } catch (PDOException $e) {
        // Hide Raw Database Errors (Security Update)
        if ($db->inTransaction()) {
            $db->rollBack(); 
        }
        error_log($e->getMessage()); // লগে সেভ হবে কিন্তু ইউজার দেখবে না
        $error_message = "Something went wrong. Please try again.";

    } catch (Exception $e) {
        // Validation Errors (Custom Messages)
        $error_message = $e->getMessage();
    }
}

include '../views/layouts/header.php';
?>

<section class="py-12 bg-gray-50 min-h-screen">
    <div class="container mx-auto max-w-5xl px-4">
        
        <div class="text-center mb-10">
            <h2 class="text-3xl font-bold text-gray-800">Complete Your Order</h2>
            <div class="w-16 h-1 bg-amberRed mx-auto mt-4"></div>
        </div>

        <div class="flex flex-col md:flex-row gap-8">
            
            <div class="w-full md:w-1/3">
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden sticky top-24">
                    <div class="bg-gray-800 text-white p-6 text-center">
                        <h4 class="text-lg font-bold text-gray-300 uppercase tracking-wide">Order Summary</h4>
                        <h2 class="text-2xl font-extrabold mt-2 text-amberRed"><?php echo htmlspecialchars($package['name']); ?></h2>
                    </div>
                    <div class="p-6 bg-gray-50">
                        <div class="flex justify-between border-b pb-3 mb-3">
                            <span class="text-gray-600">Speed</span>
                            <span class="font-bold text-gray-800"><?php echo ($package['speed_mbps'] == 0) ? 'Custom' : $package['speed_mbps'] . ' Mbps'; ?></span>
                        </div>
                        <div class="flex justify-between border-b pb-3 mb-3">
                            <span class="text-gray-600">Quota</span>
                            <span class="font-bold text-gray-800"><?php echo is_null($package['quota_gb']) ? 'Unlimited' : $package['quota_gb'] . ' GB'; ?></span>
                        </div>
                        <div class="flex justify-between border-b pb-3 mb-3">
                            <span class="text-gray-600">Duration</span>
                            <span class="font-bold text-gray-800"><?php echo $package['duration_days']; ?> Days</span>
                        </div>
                        
                        <div class="flex justify-between mt-6 pt-4 border-t-2 border-gray-300">
                            <span class="text-lg font-bold text-gray-800">Total Payable</span>
                            <span class="text-2xl font-extrabold text-amberRed">৳<?php echo number_format($package['price']); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full md:w-2/3">
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-6 border-b pb-2">Customer Details</h3>
                    
                    <?php if($error_message): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                            <i class="fa fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <form action="order.php?id=<?php echo $package_id; ?>" method="POST">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-gray-700 font-medium mb-2">Full Name <span class="text-red-500">*</span></label>
                                <input type="text" name="full_name" required placeholder="John Doe" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-amberRed outline-none">
                            </div>
                            
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-gray-700 font-medium mb-2">Mobile Number <span class="text-red-500">*</span></label>
                                <input type="text" name="phone" required placeholder="01XXXXXXXXX" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-amberRed outline-none">
                            </div>

                            <div class="col-span-2">
                                <label class="block text-gray-700 font-medium mb-2">Email Address <span class="text-red-500">*</span></label>
                                <input type="email" name="email" required placeholder="john@example.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-amberRed outline-none">
                            </div>

                            <div class="col-span-2">
                                <label class="block text-gray-700 font-medium mb-2">Connection Address <span class="text-red-500">*</span></label>
                                <textarea name="address" rows="3" required placeholder="House, Road, Area..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-amberRed outline-none"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                            </div>

                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-gray-700 font-medium mb-2">Password <span class="text-red-500">*</span></label>
                                <input type="password" name="password" required placeholder="Min. 6 characters" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-amberRed outline-none">
                            </div>

                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-gray-700 font-medium mb-2">Confirm Password <span class="text-red-500">*</span></label>
                                <input type="password" name="confirm_password" required placeholder="Re-type password" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-amberRed outline-none">
                            </div>
                        </div>

                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <button type="submit" class="w-full bg-amberRed hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg shadow-lg transition transform hover:-translate-y-1 text-lg">
                                Confirm Order
                            </button>
                            <p class="text-center text-sm text-gray-500 mt-4">By confirming, you agree to our Terms of Service.</p>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</section>

<?php include '../views/layouts/footer.php'; ?>