<?php
/**
 * Clear PHP OPcache and Configuration Cache
 */

echo "<h2>Cache Clearing Script</h2>";
echo "<hr>";

// Clear OPcache
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "✅ OPcache cleared successfully<br>";
    } else {
        echo "❌ Failed to clear OPcache<br>";
    }
} else {
    echo "ℹ️ OPcache is not enabled<br>";
}

// Clear realpath cache
clearstatcache(true);
echo "✅ Realpath cache cleared<br>";

echo "<hr>";
echo "<p><strong>Cache cleared!</strong></p>";
echo "<p>Now restart Apache and try again.</p>";
echo "<p><a href='test_razorpay.php'>Run Razorpay Test Again</a></p>";
?>
