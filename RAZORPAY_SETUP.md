# Razorpay Payment Gateway Integration Guide

## Overview
This guide will help you set up Razorpay payment gateway for FurniCart, supporting UPI, Cards, Wallets, and Net Banking.

---

## Step 1: Create Razorpay Account

1. Visit [https://razorpay.com/](https://razorpay.com/)
2. Click on **Sign Up** and create an account
3. Complete the registration process
4. Verify your email address

---

## Step 2: Get API Keys

### For Testing (Test Mode):
1. Log in to your Razorpay Dashboard
2. Go to **Settings** → **API Keys**
3. Click on **Generate Test Key**
4. You'll get:
   - **Key ID** (starts with `rzp_test_`)
   - **Key Secret** (keep this confidential)

### For Production (Live Mode):
1. Complete KYC verification in Razorpay Dashboard
2. Activate your account
3. Go to **Settings** → **API Keys**
4. Click on **Generate Live Key**
5. You'll get:
   - **Key ID** (starts with `rzp_live_`)
   - **Key Secret** (keep this confidential)

---

## Step 3: Configure FurniCart

1. Open the file: `config/razorpay_config.php`

2. Replace the placeholder values with your actual keys:

```php
// For Test Mode
define('RAZORPAY_KEY_ID', 'rzp_test_YOUR_ACTUAL_KEY_ID');
define('RAZORPAY_KEY_SECRET', 'YOUR_ACTUAL_KEY_SECRET');
define('RAZORPAY_ENV', 'test');

// For Live Mode (after testing)
define('RAZORPAY_KEY_ID', 'rzp_live_YOUR_ACTUAL_KEY_ID');
define('RAZORPAY_KEY_SECRET', 'YOUR_ACTUAL_KEY_SECRET');
define('RAZORPAY_ENV', 'live');
```

3. Update company details (optional):
```php
define('RAZORPAY_COMPANY_NAME', 'FurniCart');
define('RAZORPAY_COMPANY_LOGO', 'https://yourwebsite.com/logo.png');
```

---

## Step 4: Update Database

Run the updated SQL schema to add Razorpay fields to the orders table:

```sql
-- Run this in phpMyAdmin or MySQL command line
ALTER TABLE orders 
ADD COLUMN razorpay_order_id VARCHAR(100) AFTER payment_status,
ADD COLUMN razorpay_payment_id VARCHAR(100) AFTER razorpay_order_id,
ADD COLUMN razorpay_signature VARCHAR(255) AFTER razorpay_payment_id,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at,
ADD INDEX (razorpay_order_id);

-- If you have address_id foreign key, you may need to drop it
-- ALTER TABLE orders DROP FOREIGN KEY orders_ibfk_2;
-- ALTER TABLE orders DROP COLUMN address_id;
```

Or drop and recreate the orders table using the updated `sql/furniture.sql` file.

---

## Step 5: Test Payment Integration

### Test Cards (Test Mode Only):

**Successful Payment:**
- Card Number: `4111 1111 1111 1111`
- CVV: Any 3 digits (e.g., `123`)
- Expiry: Any future date (e.g., `12/25`)
- Name: Any name

**Failed Payment:**
- Card Number: `4000 0000 0000 0002`
- CVV: Any 3 digits
- Expiry: Any future date

### Test UPI:
- UPI ID: `success@razorpay`
- This will simulate a successful payment

### Test Wallets:
- Select any wallet (Paytm, PhonePe, etc.)
- In test mode, it will auto-complete

---

## Step 6: Testing Workflow

1. **Add items to cart** as a logged-in user
2. **Complete profile** with address details
3. **Go to checkout** page
4. Click **"Proceed to Payment"**
5. Razorpay popup will open with payment options:
   - **Cards** (Credit/Debit)
   - **UPI** (Google Pay, PhonePe, Paytm, etc.)
   - **Wallets** (Paytm, PhonePe, Mobikwik, etc.)
   - **Net Banking**
6. Complete payment using test credentials
7. You'll be redirected to **Order Success** page
8. Cart will be automatically cleared

---

## Step 7: Enable PHP cURL Extension

Razorpay integration requires cURL. Ensure it's enabled:

### For XAMPP:
1. Open `php.ini` file (in XAMPP Control Panel → Apache → Config → php.ini)
2. Find the line: `;extension=curl`
3. Remove the semicolon: `extension=curl`
4. Save and restart Apache

### Verify cURL:
Create a test file `test_curl.php`:
```php
<?php
if (function_exists('curl_version')) {
    echo "cURL is enabled";
    print_r(curl_version());
} else {
    echo "cURL is NOT enabled";
}
?>
```

---

## Step 8: Security Best Practices

1. **Never commit API keys** to version control
   - Add `config/razorpay_config.php` to `.gitignore`

2. **Use environment variables** in production:
```php
define('RAZORPAY_KEY_ID', getenv('RAZORPAY_KEY_ID'));
define('RAZORPAY_KEY_SECRET', getenv('RAZORPAY_KEY_SECRET'));
```

3. **Enable HTTPS** for production (required by Razorpay)

4. **Verify payment signatures** (already implemented in `verify_payment.php`)

5. **Set up webhooks** for additional security:
   - Go to Razorpay Dashboard → Settings → Webhooks
   - Add webhook URL: `https://yourdomain.com/FurniCart/webhook.php`
   - Select events: `payment.authorized`, `payment.failed`

---

## Step 9: Go Live Checklist

Before switching to live mode:

- [ ] Complete KYC verification in Razorpay
- [ ] Test all payment methods thoroughly
- [ ] Enable HTTPS on your website
- [ ] Replace test keys with live keys
- [ ] Change `RAZORPAY_ENV` to `'live'`
- [ ] Test with small real transactions
- [ ] Set up webhook for payment notifications
- [ ] Configure settlement account in Razorpay

---

## Supported Payment Methods

✅ **Credit/Debit Cards** (Visa, Mastercard, RuPay, Amex)  
✅ **UPI** (Google Pay, PhonePe, Paytm, BHIM, etc.)  
✅ **Wallets** (Paytm, PhonePe, Mobikwik, Freecharge, etc.)  
✅ **Net Banking** (All major banks)  
✅ **EMI** (Available for eligible cards)  
✅ **Cardless EMI** (ZestMoney, ePayLater, etc.)

---

## Troubleshooting

### Issue: "cURL error" or "Failed to create order"
**Solution:** Enable cURL extension in PHP (see Step 7)

### Issue: "Invalid API Key"
**Solution:** Verify your API keys in `config/razorpay_config.php`

### Issue: Payment successful but order not updating
**Solution:** Check `verify_payment.php` logs and ensure signature verification is working

### Issue: Razorpay popup not opening
**Solution:** Check browser console for JavaScript errors. Ensure Razorpay SDK is loaded.

---

## File Structure

```
FurniCart/
├── config/
│   └── razorpay_config.php          # API keys configuration
├── classes/
│   └── Order.php                     # Order management
├── checkout.php                      # Checkout page with Razorpay
├── create_razorpay_order.php        # Create Razorpay order
├── verify_payment.php               # Verify payment signature
├── order_success.php                # Success page
└── sql/
    └── furniture.sql                # Updated database schema
```

---

## Support

- **Razorpay Documentation:** [https://razorpay.com/docs/](https://razorpay.com/docs/)
- **Razorpay Support:** [https://razorpay.com/support/](https://razorpay.com/support/)
- **Test Cards:** [https://razorpay.com/docs/payments/payments/test-card-details/](https://razorpay.com/docs/payments/payments/test-card-details/)

---

## Notes

- Razorpay charges **2% + GST** on successful transactions
- Settlements are typically done in **T+3 days** (3 working days)
- Minimum transaction amount: **₹1**
- Maximum transaction amount: Depends on payment method and customer limits
- International cards require additional activation in Razorpay Dashboard

---

**Happy Selling! 🚀**
