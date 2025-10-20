# 💳 Razorpay Payment Integration - Complete Setup Guide

## 🎯 Overview
This guide will help you integrate Razorpay payment gateway into FurniCart, supporting:
- ✅ UPI (Google Pay, PhonePe, Paytm, BHIM)
- ✅ Credit/Debit Cards (Visa, Mastercard, RuPay)
- ✅ Wallets (Paytm, PhonePe, Mobikwik)
- ✅ Net Banking

---

## 📁 File Structure

```
FurniCart/
├── config/
│   └── razorpay_config.php          # API keys & configuration
├── classes/
│   └── Order.php                     # Order management methods
├── checkout.php                      # Checkout page with Razorpay button
├── create_razorpay_order.php        # Creates Razorpay order
├── verify_payment.php               # Verifies payment signature
├── order_success.php                # Success page after payment
└── sql/
    └── furniture.sql                # Database schema with orders table
```

---

## 🚀 Step-by-Step Setup

### **STEP 1: Get Razorpay API Keys**

1. **Sign up at Razorpay:**
   - Go to: https://razorpay.com/
   - Click "Sign Up" and create account
   - Verify your email

2. **Get Test Keys:**
   - Login to Razorpay Dashboard
   - Go to **Settings** → **API Keys**
   - Click **"Generate Test Key"**
   - Copy both:
     - **Key ID** (starts with `rzp_test_`)
     - **Key Secret** (keep confidential)

---

### **STEP 2: Configure Razorpay Keys**

1. Open: `config/razorpay_config.php`

2. Replace with your actual keys:
```php
define('RAZORPAY_KEY_ID', 'rzp_test_YOUR_ACTUAL_KEY_ID');
define('RAZORPAY_KEY_SECRET', 'YOUR_ACTUAL_KEY_SECRET');
```

3. **Your keys are already configured:**
   - Key ID: `rzp_test_RVpDHv4yr2OquD`
   - Key Secret: `t8219ksgoJa7l04vJ6Tg5BrD`

---

### **STEP 3: Update Database Schema**

**Option A: Run SQL in phpMyAdmin**

1. Open: http://localhost/phpmyadmin
2. Select `furnicart` database
3. Click "SQL" tab
4. Run this query:

```sql
-- Add Razorpay fields to orders table
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS razorpay_order_id VARCHAR(100) AFTER payment_status,
ADD COLUMN IF NOT EXISTS razorpay_payment_id VARCHAR(100) AFTER razorpay_order_id,
ADD COLUMN IF NOT EXISTS razorpay_signature VARCHAR(255) AFTER razorpay_payment_id,
ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) AFTER razorpay_signature,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Add index for faster lookups
ALTER TABLE orders ADD INDEX IF NOT EXISTS idx_razorpay_order (razorpay_order_id);
```

**Option B: Use the updated SQL file**

Drop and recreate tables using `sql/furniture.sql` (already includes Razorpay fields)

---

### **STEP 4: Verify File Structure**

Make sure these files exist:

✅ **Configuration:**
- `config/razorpay_config.php` - API keys

✅ **Classes:**
- `classes/Order.php` - Order management (restored)
- `classes/Cart.php` - Cart management
- `classes/User.php` - User management

✅ **Payment Flow:**
- `checkout.php` - Checkout page with Razorpay
- `create_razorpay_order.php` - Creates order
- `verify_payment.php` - Verifies payment
- `order_success.php` - Success page

---

### **STEP 5: Enable cURL (If Not Already)**

1. Open XAMPP Control Panel
2. Click **Config** → **PHP (php.ini)**
3. Find: `;extension=curl`
4. Remove semicolon: `extension=curl`
5. Save and **Restart Apache**

---

### **STEP 6: Test the Integration**

#### **A. Add Items to Cart**
1. Browse products: http://localhost/FurniCart/product.php
2. Add items to cart
3. Make sure you're logged in

#### **B. Complete Profile**
1. Go to: http://localhost/FurniCart/profile.php
2. Fill in required fields:
   - Address
   - Pincode (will auto-fill city/state)
   - City
   - State

#### **C. Go to Checkout**
1. Visit cart: http://localhost/FurniCart/cart.php
2. Click **"Proceed to Checkout"**
3. Review order details

#### **D. Make Test Payment**
1. Click **"Proceed to Payment"**
2. Razorpay popup will open
3. Choose payment method:

**Test UPI:**
- UPI ID: `success@razorpay`
- Will simulate successful payment

**Test Card:**
- Card Number: `4111 1111 1111 1111`
- CVV: `123` (any 3 digits)
- Expiry: `12/25` (any future date)
- Name: Any name

**Test Wallet:**
- Select any wallet
- Will auto-complete in test mode

4. Complete payment
5. You'll be redirected to success page

---

## 🔍 How It Works

### **Payment Flow:**

```
1. User clicks "Proceed to Payment" on checkout page
   ↓
2. JavaScript calls create_razorpay_order.php
   ↓
3. Server creates order in Razorpay API
   ↓
4. Server saves order in database (status: pending)
   ↓
5. Razorpay checkout popup opens
   ↓
6. User selects payment method (UPI/Card/Wallet)
   ↓
7. User completes payment
   ↓
8. Razorpay sends payment details to JavaScript
   ↓
9. JavaScript calls verify_payment.php
   ↓
10. Server verifies payment signature (security)
    ↓
11. Server updates order status to "paid"
    ↓
12. Server clears user's cart
    ↓
13. User redirected to order_success.php
```

---

## 🔒 Security Features

✅ **Payment Signature Verification** - Prevents tampering  
✅ **Server-side Validation** - All checks on server  
✅ **Secure API Keys** - Never exposed to client  
✅ **Order Ownership Check** - Users can only access their orders  
✅ **HTTPS Recommended** - For production use  

---

## 🧪 Testing Checklist

Before going live, test these scenarios:

- [ ] Successful UPI payment
- [ ] Successful card payment
- [ ] Successful wallet payment
- [ ] Failed payment (card: `4000 0000 0000 0002`)
- [ ] User closes payment popup (should allow retry)
- [ ] Cart clears after successful payment
- [ ] Order appears in database with correct status
- [ ] Order success page shows correct details

---

## 🐛 Troubleshooting

### **Issue: "Failed to create order"**
**Solutions:**
1. Check if cURL is enabled
2. Verify API keys are correct
3. Check internet connection
4. Restart Apache

### **Issue: "Payment verification failed"**
**Solutions:**
1. Check Razorpay Key Secret is correct
2. Verify database connection
3. Check PHP error logs

### **Issue: Database connection error**
**Solutions:**
1. Make sure MySQL is running in XAMPP
2. Run this in phpMyAdmin:
```sql
GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;
```
3. Restart MySQL

### **Issue: Razorpay popup not opening**
**Solutions:**
1. Check browser console for errors (F12)
2. Verify Razorpay SDK is loaded
3. Check if JavaScript is enabled

---

## 📊 Database Schema

### **orders table:**
```sql
- order_id (INT, PRIMARY KEY, AUTO_INCREMENT)
- user_id (INT, FOREIGN KEY)
- total_amount (DECIMAL)
- payment_status (ENUM: 'pending', 'paid', 'failed')
- razorpay_order_id (VARCHAR)
- razorpay_payment_id (VARCHAR)
- razorpay_signature (VARCHAR)
- payment_method (VARCHAR)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### **order_items table:**
```sql
- order_item_id (INT, PRIMARY KEY, AUTO_INCREMENT)
- order_id (INT, FOREIGN KEY)
- product_id (INT, FOREIGN KEY)
- quantity (INT)
- price (DECIMAL)
```

---

## 🌐 Going Live

When ready for production:

1. **Complete KYC** in Razorpay Dashboard
2. **Generate Live Keys** (starts with `rzp_live_`)
3. **Update config:**
```php
define('RAZORPAY_KEY_ID', 'rzp_live_YOUR_LIVE_KEY');
define('RAZORPAY_KEY_SECRET', 'YOUR_LIVE_SECRET');
define('RAZORPAY_ENV', 'live');
```
4. **Enable HTTPS** on your website (required)
5. **Test with small real transactions**
6. **Set up webhooks** for additional security

---

## 💰 Razorpay Pricing

- **Transaction Fee:** 2% + GST
- **Settlement:** T+3 days (3 working days)
- **Minimum Amount:** ₹1
- **No setup fee** or monthly charges

---

## 📞 Support

- **Razorpay Docs:** https://razorpay.com/docs/
- **Test Cards:** https://razorpay.com/docs/payments/payments/test-card-details/
- **Support:** https://razorpay.com/support/

---

## ✅ Quick Start Summary

1. ✅ Get Razorpay test keys (already done)
2. ✅ Configure keys in `config/razorpay_config.php` (already done)
3. ⬜ Update database schema (run SQL)
4. ⬜ Enable cURL in PHP
5. ⬜ Restart Apache
6. ⬜ Test payment flow

**Your integration is 90% complete! Just need to:**
1. Fix database connection (MySQL permissions)
2. Run the SQL to add Razorpay fields
3. Test the payment flow

---

**Happy Selling! 🚀**
