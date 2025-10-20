<?php
/**
 * Razorpay Configuration
 * 
 * To get your API keys:
 * 1. Sign up at https://razorpay.com/
 * 2. Go to Settings > API Keys
 * 3. Generate Test/Live keys
 * 4. Replace the keys below
 */

// Razorpay API Credentials
define('RAZORPAY_KEY_ID', 'rzp_test_RVpDHv4yr2OquD');  // Replace with your Key ID
define('RAZORPAY_KEY_SECRET', 't8219ksgoJa7l04vJ6Tg5BrD');    // Replace with your Key Secret

// Environment (test or live)
define('RAZORPAY_ENV', 'test'); // Change to 'live' for production

// Currency
define('RAZORPAY_CURRENCY', 'INR');

// Company Details (shown in payment popup)
define('RAZORPAY_COMPANY_NAME', 'FurniCart');
define('RAZORPAY_COMPANY_LOGO', 'https://yourwebsite.com/logo.png'); // Optional

// Webhook Secret (for payment verification)
define('RAZORPAY_WEBHOOK_SECRET', 'YOUR_WEBHOOK_SECRET'); // Optional, for webhooks

/**
 * Note: Keep this file secure and never commit actual credentials to version control
 * For production, use environment variables or a secure configuration management system
 */
?>
