<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/classes/Cart.php';
require_once __DIR__ . '/classes/Order.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/config/razorpay_config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];

// Initialize models
$cartModel = new Cart($pdo);
$orderModel = new Order($pdo);
$userModel = new User($pdo);

// Check if user has complete profile
if (!$userModel->hasCompleteProfile($userId)) {
    echo json_encode(['success' => false, 'message' => 'Please complete your profile first']);
    exit;
}

// Get cart details
$cartItems = $cartModel->getCartItems($userId);
$cartTotal = $cartModel->getCartTotal($userId);

if (empty($cartItems)) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit;
}

// Calculate total with platform fee
$platformFee = $cartTotal * 0.05;
$grandTotal = $cartTotal + $platformFee;

// Convert to paise (Razorpay accepts amount in smallest currency unit)
$amountInPaise = (int)($grandTotal * 100);

try {
    // Create Razorpay order using cURL
    $url = 'https://api.razorpay.com/v1/orders';
    
    $data = [
        'amount' => $amountInPaise,
        'currency' => RAZORPAY_CURRENCY,
        'receipt' => 'order_' . time(),
        'notes' => [
            'user_id' => $userId,
            'cart_items' => count($cartItems)
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
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception('Failed to create Razorpay order');
    }
    
    $razorpayOrder = json_decode($response, true);
    
    if (!isset($razorpayOrder['id'])) {
        throw new Exception('Invalid response from Razorpay');
    }
    
    // Create order in database
    $orderId = $orderModel->createOrder($userId, $grandTotal, $razorpayOrder['id']);
    
    if (!$orderId) {
        throw new Exception('Failed to create order in database');
    }
    
    // Add order items
    $orderModel->addOrderItems($orderId, $cartItems);
    
    // Get user details
    $user = $userModel->getUserById($userId);
    
    // Return success with order details
    echo json_encode([
        'success' => true,
        'order_id' => $razorpayOrder['id'],
        'amount' => $amountInPaise,
        'currency' => RAZORPAY_CURRENCY,
        'key' => RAZORPAY_KEY_ID,
        'name' => RAZORPAY_COMPANY_NAME,
        'description' => 'Order for ' . count($cartItems) . ' items',
        'prefill' => [
            'name' => $user['name'],
            'email' => $user['email'],
            'contact' => $user['phone']
        ],
        'db_order_id' => $orderId
    ]);
    
} catch (Exception $e) {
    error_log('Razorpay order creation error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create order. Please try again.'
    ]);
}
?>
