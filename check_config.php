<?php
require_once __DIR__ . '/config/razorpay_config.php';

echo "<h2>Configuration Check</h2>";
echo "<hr>";
echo "RAZORPAY_KEY_ID: " . RAZORPAY_KEY_ID . "<br>";
echo "RAZORPAY_KEY_SECRET: " . RAZORPAY_KEY_SECRET . "<br>";
echo "<hr>";

if (RAZORPAY_KEY_ID === 'rzp_test_YOUR_KEY_ID') {
    echo "❌ <strong style='color: red;'>Still using placeholder keys!</strong><br>";
} else {
    echo "✅ <strong style='color: green;'>Keys are loaded correctly!</strong><br>";
}
?>
