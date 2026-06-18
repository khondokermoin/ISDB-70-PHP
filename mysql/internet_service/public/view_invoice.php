<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';

$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];
$invoice_id = isset($_GET['invoice_id']) ? (int)$_GET['invoice_id'] : 0;

if ($invoice_id === 0) {
    header("Location: user_dashboard.php");
    exit;
}

// ইনভয়েস, প্যাকেজ এবং ইউজারের সম্পূর্ণ ডাটা আনা
$stmt = $db->prepare("
    SELECT i.*, p.name as package_name, p.speed_mbps, u.full_name, u.email, u.phone, u.address 
    FROM invoices i
    LEFT JOIN subscriptions s ON i.subscription_id = s.subscription_id
    LEFT JOIN packages p ON s.package_id = p.package_id
    JOIN users u ON i.user_id = u.user_id
    WHERE i.invoice_id = ? AND i.user_id = ?
");
$stmt->execute([$invoice_id, $user_id]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invoice) {
    header("Location: user_dashboard.php?msg=invoice_not_found");
    exit;
}

// ইনভয়েসের ধরন নির্ধারণ (Upgrade নাকি Regular)
$is_upgrade = (strpos($invoice['invoice_number'], 'UPG-') === 0);
$invoice_title = $is_upgrade ? "Package Upgrade Invoice" : "Internet Subscription Invoice";

// 🔥 FIX: যদি এটি আপগ্রেড ইনভয়েস হয়, তবে নতুন প্যাকেজের নাম এবং স্পিড ডাটাবেস থেকে আনতে হবে
if ($is_upgrade) {
    $parts = explode('-', $invoice['invoice_number']);
    if (isset($parts[1]) && is_numeric($parts[1])) {
        $target_pkg_id = (int)$parts[1];

        $pkgStmt = $db->prepare("SELECT name, speed_mbps FROM packages WHERE package_id = ?");
        $pkgStmt->execute([$target_pkg_id]);
        $newPkg = $pkgStmt->fetch(PDO::FETCH_ASSOC);

        if ($newPkg) {
            $invoice['package_name'] = $newPkg['name'];
            $invoice['speed_mbps']   = $newPkg['speed_mbps'];
        }
    }
}

include '../views/layouts/header.php';
?>

<div class="bg-gray-100 min-h-screen py-8">
    <div class="container mx-auto max-w-3xl px-4">

        <div class="flex justify-between items-center mb-4 print:hidden">
            <a href="user_dashboard.php" class="text-gray-600 hover:text-orange-600 font-bold transition flex items-center">
                <i class="fa fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
            <button onclick="window.print()" class="bg-gray-800 hover:bg-gray-900 text-white px-5 py-2 rounded-lg text-sm font-bold shadow transition flex items-center">
                <i class="fa fa-print mr-2"></i> Print / PDF
            </button>
        </div>

        <div class="bg-white p-8 md:p-12 rounded-xl shadow-lg border border-gray-200">

            <div class="flex justify-between items-start border-b-2 border-gray-100 pb-8 mb-8">
                <div>
                    <a href="index.php" class="text-3xl font-extrabold text-red-500 tracking-tight">
                        AMAR <span class="text-gray-800">IT</span>
                    </a>
                    <p class="text-gray-500 text-sm mt-1">Reliable Internet Service Provider</p>
                    <p class="text-gray-400 text-xs mt-1">Jigatola, Dhaka, Bangladesh</p>
                </div>
                <div class="text-right">
                    <h1 class="text-2xl font-bold text-gray-800 uppercase tracking-widest">INVOICE</h1>
                    <p class="text-gray-500 font-bold mt-1">#<?php echo htmlspecialchars($invoice['invoice_number'], ENT_QUOTES, 'UTF-8'); ?></p>

                    <div class="mt-3">
                        <?php if ($invoice['status'] == 'paid'): ?>
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded text-xs font-black uppercase tracking-wider border border-green-200">PAID</span>
                        <?php elseif ($invoice['status'] == 'cancelled'): ?>
                            <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded text-xs font-black uppercase tracking-wider border border-gray-200">CANCELLED</span>
                        <?php else: ?>
                            <span class="bg-red-100 text-red-600 px-3 py-1 rounded text-xs font-black uppercase tracking-wider border border-red-200">UNPAID</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-8 mb-10">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Billed To:</p>
                    <h3 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($invoice['full_name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p class="text-gray-600 text-sm mt-1"><i class="fa fa-phone mr-1 w-4"></i> <?php echo htmlspecialchars($invoice['phone'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="text-gray-600 text-sm"><i class="fa fa-envelope mr-1 w-4"></i> <?php echo htmlspecialchars($invoice['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="text-gray-600 text-sm mt-1"><i class="fa fa-map-marker-alt mr-1 w-4"></i> <?php echo htmlspecialchars($invoice['address'], ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Invoice Details:</p>
                    <p class="text-gray-800 text-sm font-bold mt-1">Date: <span class="text-gray-600 font-normal"><?php echo date("d M Y", strtotime($invoice['created_at'])); ?></span></p>
                    <p class="text-gray-800 text-sm font-bold mt-1">Due Date: <span class="text-red-500 font-bold"><?php echo !empty($invoice['due_date']) ? date("d M Y", strtotime($invoice['due_date'])) : 'N/A'; ?></span></p>
                    <p class="text-gray-800 text-sm font-bold mt-1">Type: <span class="text-gray-600 font-normal"><?php echo $invoice_title; ?></span></p>
                </div>
            </div>

            <table class="w-full text-left border-collapse mb-8">
                <thead>
                    <tr class="bg-gray-50 border-y border-gray-200">
                        <th class="py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4">
                            <p class="font-bold text-gray-800"><?php echo htmlspecialchars($invoice['package_name'] ?? 'Custom Package', ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php if (!empty($invoice['speed_mbps'])): ?>
                                <p class="text-xs text-gray-500 mt-1">Bandwidth: <?php echo htmlspecialchars($invoice['speed_mbps'], ENT_QUOTES, 'UTF-8'); ?> Mbps</p>
                            <?php endif; ?>
                            <?php if (!$is_upgrade): ?>
                                <p class="text-xs text-gray-500">Billing Cycle: 1 Month</p>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 px-4 text-right font-bold text-gray-800">
                            ৳<?php echo number_format($invoice['amount'], 2); ?>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="flex justify-end">
                <div class="w-1/2">
                    <div class="flex justify-between py-2 text-sm text-gray-600 border-b border-gray-100">
                        <span>Subtotal:</span>
                        <span>৳<?php echo number_format($invoice['amount'], 2); ?></span>
                    </div>
                    <div class="flex justify-between py-2 text-sm text-gray-600 border-b border-gray-100">
                        <span>VAT/Tax (0%):</span>
                        <span>৳0.00</span>
                    </div>
                    <div class="flex justify-between py-3 text-lg font-black text-gray-900 border-b-2 border-gray-800">
                        <span>Total:</span>
                        <span>৳<?php echo number_format($invoice['amount'], 2); ?></span>
                    </div>
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-gray-100 flex justify-between items-center print:hidden">
                <p class="text-xs text-gray-400 italic">If you have any questions concerning this invoice, contact our support team.</p>

                <?php if (in_array($invoice['status'], ['unpaid', 'pending'])): ?>
                    <a href="pay_invoice.php?invoice_id=<?php echo $invoice_id; ?>" class="bg-orange-600 hover:bg-orange-700 text-white px-8 py-3 rounded-lg font-bold shadow-lg transition transform hover:scale-105">
                        <i class="fa fa-credit-card mr-2"></i> Proceed to Pay
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<style>
    /* Print Styles */
    @media print {
        body {
            background-color: white !important;
        }

        .print\:hidden {
            display: none !important;
        }

        .container {
            max-width: 100% !important;
            padding: 0 !important;
        }

        .shadow-lg {
            box-shadow: none !important;
        }

        .border {
            border-color: #ddd !important;
        }
    }
</style>

<?php include '../views/layouts/footer.php'; ?>