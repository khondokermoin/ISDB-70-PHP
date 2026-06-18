<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';
require_once '../config/bkash_config.php';

$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];

if (!isset($_GET['invoice_id'])) {
    header("Location: user_dashboard.php");
    exit;
}

$invoice_id = (int)$_GET['invoice_id'];

// ইনভয়েস চেক
$stmt = $db->prepare("SELECT * FROM invoices WHERE invoice_id = ? AND user_id = ?");
$stmt->execute([$invoice_id, $user_id]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invoice || $invoice['status'] == 'paid') {
    header("Location: user_dashboard.php");
    exit;
}

$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // 🔥 FIX: CSRF Token Verification (সিকিউরিটি চেক)
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_msg = "Invalid Security Token! Please refresh the page and try again.";
    } else {
        // টোকেন সঠিক হলে তবেই bKash API Call হবে
        // ১. Grant Token API Call
        $post_token = json_encode([
            'app_key' => BKASH_APP_KEY,
            'app_secret' => BKASH_APP_SECRET
        ]);

        $url = BKASH_BASE_URL . '/tokenized/checkout/token/grant';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'username: ' . BKASH_USERNAME,
            'password: ' . BKASH_PASSWORD
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_token);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $token_response = curl_exec($ch);
        curl_close($ch);

        $token_data = json_decode($token_response, true);

        if (isset($token_data['id_token'])) {
            $id_token = $token_data['id_token'];
            $_SESSION['bkash_token'] = $id_token; // টোকেনটি সেশনে সেভ করে রাখছি

            // ২. Create Payment API Call
            $callback_url = BASE_URL . 'bkash_callback.php?invoice_id=' . $invoice_id;

            // ফিক্স ১: ইনভয়েস নম্বরটিকে প্রতিবার ইউনিক করার জন্য শেষে টাইমস্ট্যাম্প যোগ করা
            $unique_invoice_number = $invoice['invoice_number'] . '_' . time();

            // ফিক্স ২: অ্যামাউন্টকে দশমিকের পর দুই ঘর (যেমন: 500.00) করা
            $formatted_amount = number_format((float)$invoice['amount'], 2, '.', '');

            $post_create = json_encode([
                'mode' => '0011',
                'payerReference' => '01711111111',
                'callbackURL' => $callback_url,
                'amount' => $formatted_amount,
                'currency' => 'BDT',
                'intent' => 'sale',
                'merchantInvoiceNumber' => $unique_invoice_number
            ]);

            $url = BKASH_BASE_URL . '/tokenized/checkout/create';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: ' . $id_token,
                'X-APP-Key: ' . BKASH_APP_KEY
            ]);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_create);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $create_response = curl_exec($ch);
            curl_close($ch);

            $create_data = json_decode($create_response, true);

            if (isset($create_data['bkashURL'])) {
                // ইউজারকে বিকাশের গেটওয়ে পেজে রিডাইরেক্ট করা হচ্ছে
                header("Location: " . $create_data['bkashURL']);
                exit;
            } else {
                $error_msg = "bKash Payment Creation Failed. Reason: " . ($create_data['errorMessage'] ?? 'Unknown Error');
            }
        } else {
            $error_msg = "Failed to authenticate with bKash API.";
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
                <h2 class="text-2xl font-bold">Secure Checkout</h2>
                <p class="text-gray-400 text-sm mt-1">Invoice: <?php echo htmlspecialchars($invoice['invoice_number'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>

            <div class="p-8">
                <h3 class="text-center text-gray-500 font-bold mb-5 uppercase tracking-wider text-sm">Select Payment Method</h3>

                <div class="flex justify-between items-center bg-gray-100 p-5 rounded-xl mb-8 border border-gray-200">
                    <span class="text-gray-600 font-bold text-lg">Total Payable:</span>
                    <span class="text-3xl font-black text-gray-900">৳<?php echo number_format($invoice['amount']); ?></span>
                </div>

                <?php if (!empty($error_msg)): ?>
                    <div class="bg-red-100 text-red-600 p-3 rounded mb-4 text-sm font-bold text-center border border-red-200">
                        <?php echo htmlspecialchars($error_msg, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div class="border border-pink-200 bg-pink-50 rounded-xl p-6 text-center flex flex-col justify-between hover:shadow-md transition">
                        <img src="https://freelogopng.com/images/all_img/1656234782bkash-app-logo.png" alt="bKash" class="h-10 mx-auto mb-3">
                        <p class="text-xs text-gray-600 mb-5">Pay securely directly from your bKash wallet.</p>

                        <form action="pay_invoice.php?invoice_id=<?php echo (int)$invoice_id; ?>" method="POST">
                            <?php if (isset($_SESSION['csrf_token'])): ?>
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?php endif; ?>

                            <button type="submit" class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 rounded-lg shadow transition text-sm flex items-center justify-center gap-2">
                                <i class="fa fa-paper-plane"></i> Pay with bKash
                            </button>
                        </form>
                    </div>

                    <div class="border border-blue-200 bg-blue-50 rounded-xl p-6 text-center flex flex-col justify-between hover:shadow-md transition">
                        <div class="flex justify-center gap-3 mb-3 text-blue-600">
                            <i class="fa fa-credit-card text-3xl"></i>
                            <i class="fa fa-university text-3xl"></i>
                        </div>
                        <p class="text-xs text-gray-600 mb-5">Cards, Mobile Banking, or Net Banking.</p>

                        <a href="ssl_pay.php?invoice_id=<?php echo (int)$invoice_id; ?>" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg shadow transition text-sm flex items-center justify-center gap-2">
                            <i class="fa fa-lock"></i> SSLCommerz
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
<?php include '../views/layouts/footer.php'; ?>