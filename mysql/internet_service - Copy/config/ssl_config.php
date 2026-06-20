<?php
// config/ssl_config.php
define('SSL_STORE_ID', 'testbox'); // Sandbox Store ID
define('SSL_STORE_PASSWORD', 'qwerty'); // Sandbox Store Password
define('SSL_SANDBOX', true); // স্যান্ডবক্স মোড অন (Production এ গেলে false করে দেবেন)

define('SSL_BASE_URL', SSL_SANDBOX ? 'https://sandbox.sslcommerz.com' : 'https://securepay.sslcommerz.com');
?>