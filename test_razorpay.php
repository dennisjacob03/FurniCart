<?php
/**
 * Razorpay Connection Test Script
 * This script tests if Razorpay API is accessible and credentials are valid
 */

require_once __DIR__ . '/config/razorpay_config.php';

echo "<h2>Razorpay Integration Test</h2>";
echo "<hr>";

// Test 1: Check if cURL is enabled
echo "<h3>1. cURL Extension Check</h3>";
if (function_exists('curl_init')) {
    echo "✅ <strong style='color: green;'>cURL is enabled</strong><br>";
    $curlVersion = curl_version();
    echo "Version: " . $curlVersion['version'] . "<br>";
    echo "SSL Version: " . $curlVersion['ssl_version'] . "<br>";
} else {
    echo "❌ <strong style='color: red;'>cURL is NOT enabled</strong><br>";
    echo "Please enable cURL in php.ini<br>";
    exit;
}

echo "<hr>";

// Test 2: Check Razorpay Configuration
echo "<h3>2. Razorpay Configuration</h3>";
echo "Key ID: " . RAZORPAY_KEY_ID . "<br>";
echo "Key Secret: " . str_repeat('*', strlen(RAZORPAY_KEY_SECRET) - 4) . substr(RAZORPAY_KEY_SECRET, -4) . "<br>";
echo "Environment: " . RAZORPAY_ENV . "<br>";
echo "Currency: " . RAZORPAY_CURRENCY . "<br>";

echo "<hr>";

// Test 3: Test Razorpay API Connection
echo "<h3>3. Razorpay API Connection Test</h3>";

$url = 'https://api.razorpay.com/v1/orders';

$data = [
    'amount' => 100, // ₹1.00 in paise
    'currency' => RAZORPAY_CURRENCY,
    'receipt' => 'test_' . time(),
    'notes' => [
        'test' => 'connection_test'
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$curlError = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status Code: <strong>" . $httpCode . "</strong><br>";

if ($response === false) {
    echo "❌ <strong style='color: red;'>cURL Error:</strong> " . $curlError . "<br>";
} else {
    $responseData = json_decode($response, true);
    
    if ($httpCode === 200) {
        echo "✅ <strong style='color: green;'>Connection Successful!</strong><br>";
        echo "Order ID: " . $responseData['id'] . "<br>";
        echo "Amount: ₹" . ($responseData['amount'] / 100) . "<br>";
        echo "Status: " . $responseData['status'] . "<br>";
        echo "<br><strong>Your Razorpay integration is working correctly!</strong><br>";
    } else {
        echo "❌ <strong style='color: red;'>API Error:</strong><br>";
        echo "<pre>" . print_r($responseData, true) . "</pre>";
        
        if (isset($responseData['error'])) {
            echo "<strong>Error Description:</strong> " . $responseData['error']['description'] . "<br>";
            
            if ($httpCode === 401) {
                echo "<br><strong style='color: red;'>Authentication Failed!</strong><br>";
                echo "Please check your API keys in config/razorpay_config.php<br>";
            }
        }
    }
}

echo "<hr>";

// Test 4: Check PHP Settings
echo "<h3>4. PHP Configuration</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "allow_url_fopen: " . (ini_get('allow_url_fopen') ? 'Enabled' : 'Disabled') . "<br>";
echo "max_execution_time: " . ini_get('max_execution_time') . " seconds<br>";

echo "<hr>";
echo "<p><strong>Test completed!</strong></p>";
echo "<p>If you see errors above, please fix them before proceeding with payments.</p>";
?>
