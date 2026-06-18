<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_GET['invoice_id'])) {
    header("Location: user_dashboard.php");
    exit;
}

require_once '../config/database.php';
require_once '../config/ssl_config.php';

$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];
$invoice_id = (int)$_GET['invoice_id'];

// ইনভয়েস এবং ইউজারের ডাটা আনা
$stmt = $db->prepare("
    SELECT i.*, u.full_name, u.email, u.phone, u.address 
    FROM invoices i 
    JOIN users u ON i.user_id = u.user_id 
    WHERE i.invoice_id = ? AND i.user_id = ?
");
$stmt->execute([$invoice_id, $user_id]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invoice || $invoice['status'] == 'paid') {
    header("Location: user_dashboard.php");
    exit;
}

// SSLCommerz API Request Data
$post_data = array();
$post_data['store_id'] = SSL_STORE_ID;
$post_data['store_passwd'] = SSL_STORE_PASSWORD;
$post_data['total_amount'] = number_format((float)$invoice['amount'], 2, '.', '');
$post_data['currency'] = "BDT";
$post_data['tran_id'] = "SSL_" . uniqid() . "_" . $invoice_id; // Unique Transaction ID
$post_data['success_url'] = "http://localhost/ISDB-70-PHP/mysql/internet_service/public/ssl_callback.php?status=success&inv_id=" . $invoice_id;
$post_data['fail_url'] = "http://localhost/ISDB-70-PHP/mysql/internet_service/public/ssl_callback.php?status=fail";
$post_data['cancel_url'] = "http://localhost/ISDB-70-PHP/mysql/internet_service/public/ssl_callback.php?status=cancel";

// Customer Information
$post_data['cus_name'] = $invoice['full_name'];
$post_data['cus_email'] = $invoice['email'];
$post_data['cus_add1'] = $invoice['address'];
$post_data['cus_city'] = "Dhaka";
$post_data['cus_country'] = "Bangladesh";
$post_data['cus_phone'] = $invoice['phone'];

// Initiate API Call (FIXED: Using v4 API)
$handle = curl_init();
curl_setopt($handle, CURLOPT_URL, SSL_BASE_URL . "/gwprocess/v4/api.php");
curl_setopt($handle, CURLOPT_TIMEOUT, 30);
curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($handle, CURLOPT_POST, 1);
curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE);

$content = curl_exec($handle);
$code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
curl_close($handle);

if ($code == 200 && !(curl_errno($handle))) {
    $sslcommerzResponse = json_decode($content, true);
    
    // FIX 1: যদি API থেকে JSON না এসে HTML/Text আসে, তবে null এরর ঠেকানো
    if ($sslcommerzResponse === null) {
        echo "<div style='color:red; padding:20px; font-family:sans-serif;'>";
        echo "<h3>SSLCommerz API Error</h3>";
        echo "<p>Invalid response received from SSLCommerz. It seems the API endpoint or Store ID is incorrect.</p>";
        echo "<p><b>Raw Response:</b> " . htmlspecialchars($content) . "</p>";
        echo "</div>";
        exit;
    }

    if (isset($sslcommerzResponse['GatewayPageURL']) && $sslcommerzResponse['GatewayPageURL'] != "") {
        // Redirect to SSLCommerz Gateway
        echo "<script>window.location.href = '" . $sslcommerzResponse['GatewayPageURL'] . "';</script>";
        exit;
    } else {
        // FIX 2: failedreason না থাকলে যাতে null offset error না দেয়
        $reason = isset($sslcommerzResponse['failedreason']) ? $sslcommerzResponse['failedreason'] : 'Unknown Error (Check Store ID/Password)';
        echo "SSLCommerz API Error: " . $reason;
    }
} else {
    echo "Failed to connect with SSLCommerz API. HTTP Status Code: " . $code;
}
?>