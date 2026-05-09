<?php
session_start();

// সিকিউরিটি চেক
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';
$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];

if (!isset($_GET['invoice_id'])) {
    header("Location: user_dashboard.php");
    exit;
}

$invoice_id = (int)$_GET['invoice_id'];

// ইনভয়েস চেক করা হচ্ছে
$stmt = $db->prepare("SELECT * FROM invoices WHERE invoice_id = ? AND user_id = ?");
$stmt->execute([$invoice_id, $user_id]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

// ইনভয়েস না পেলে বা ইতিমধ্যে পেইড হলে ড্যাশবোর্ডে ফেরত পাঠাবে
if (!$invoice || $invoice['status'] == 'paid') {
    header("Location: user_dashboard.php");
    exit;
}

$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bkash_number = trim($_POST['bkash_number']);
    $trx_id = trim($_POST['trx_id']);

    if (empty($bkash_number) || empty($trx_id)) {
        $error_msg = "Please provide both bKash number and Transaction ID.";
    } else {
        try {
            $db->beginTransaction();

            // ১. ইনভয়েস স্ট্যাটাস 'pending' (Verifying) করে দেওয়া
            $db->prepare("UPDATE invoices SET status = 'pending' WHERE invoice_id = ?")->execute([$invoice_id]);

            // ২. বিলিং ম্যানেজারের ভেরিফিকেশনের জন্য অটোমেটিক টিকিট তৈরি
            $subject = "Payment Verification for " . $invoice['invoice_number'];
            $message = "I have paid ৳" . $invoice['amount'] . " via bKash.\n\nMy bKash Number: $bkash_number\nTrxID: $trx_id\n\nPlease verify and approve my payment.";
            $db->prepare("INSERT INTO tickets (user_id, subject, category, message, status) VALUES (?, ?, 'Billing Issue', ?, 'open')")->execute([$user_id, $subject, $message]);

            // ৩. অ্যাডমিনকে নোটিফিকেশন পাঠানো
            $adminQuery = $db->query("SELECT user_id FROM users WHERE role = 'admin' LIMIT 1")->fetch();
            if ($adminQuery) {
                $notif_msg = "💰 Payment Submitted: TrxID $trx_id received for {$invoice['invoice_number']}. Check Support Tickets to verify.";
                $db->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")->execute([$adminQuery['user_id'], $notif_msg]);
            }

            $db->commit();
            header("Location: user_dashboard.php?msg=payment_submitted");
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            $error_msg = "Something went wrong. Please try again.";
        }
    }
}
include '../views/layouts/header.php';
?>

<div class="bg-gray-50 min-h-screen py-12 flex items-center justify-center">
    <div class="container mx-auto max-w-lg px-4">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">

            <div class="bg-gray-900 text-white p-6 text-center relative">
                <a href="user_dashboard.php" class="absolute left-4 top-6 text-gray-400 hover:text-white transition"><i class="fa fa-arrow-left text-xl"></i></a>
                <h2 class="text-2xl font-bold">Secure Payment</h2>
                <p class="text-gray-400 text-sm mt-1">Invoice: <?php echo htmlspecialchars($invoice['invoice_number']); ?></p>
            </div>

            <div class="p-8">
                <div class="bg-pink-50 border border-pink-200 rounded-xl p-5 mb-6 text-center">
                    <img src="https://freelogopng.com/images/all_img/1656234782bkash-app-logo.png" alt="bKash" class="h-10 mx-auto mb-3">
                    <p class="text-gray-700 text-sm font-medium mb-1">Please Send Money or Make Payment to:</p>
                    <h3 class="text-3xl font-black text-pink-600 tracking-wider">01711-000000</h3>
                    <p class="text-gray-500 text-xs mt-1">(Personal / Merchant Account)</p>
                </div>

                <div class="flex justify-between items-center bg-gray-100 p-5 rounded-xl mb-8 border border-gray-200">
                    <span class="text-gray-600 font-bold text-lg">Total Payable:</span>
                    <span class="text-3xl font-black text-gray-900">৳<?php echo number_format($invoice['amount']); ?></span>
                </div>

                <?php if ($error_msg): ?>
                    <div class="bg-red-100 text-red-600 p-3 rounded mb-4 text-sm font-bold text-center border border-red-200"><i class="fa fa-exclamation-triangle mr-1"></i> <?php echo $error_msg; ?></div>
                <?php endif; ?>

                <form action="" method="POST" class="space-y-5">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2 text-sm">Your bKash Account Number</label>
                        <input type="text" name="bkash_number" required placeholder="e.g., 01XXXXXXXXX" class="w-full border-gray-300 border px-4 py-3 rounded-lg focus:ring-2 focus:ring-pink-500 outline-none font-semibold text-gray-800">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2 text-sm">Transaction ID (TrxID)</label>
                        <input type="text" name="trx_id" required placeholder="e.g., 9F8A7B6C5D" class="w-full border-gray-300 border px-4 py-3 rounded-lg focus:ring-2 focus:ring-pink-500 outline-none font-semibold text-gray-800 uppercase">
                    </div>

                    <button type="submit" class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-4 rounded-xl shadow-lg transition mt-4 text-lg">
                        <i class="fa fa-paper-plane mr-2"></i> Submit Payment Info
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>