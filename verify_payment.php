<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/classes/Order.php';
require_once __DIR__ . '/classes/Cart.php';
require_once __DIR__ . '/config/razorpay_config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];

// Get POST data
$razorpayOrderId = $_POST['razorpay_order_id'] ?? '';
$razorpayPaymentId = $_POST['razorpay_payment_id'] ?? '';
$razorpaySignature = $_POST['razorpay_signature'] ?? '';
$paymentMethod = $_POST['payment_method'] ?? 'razorpay';

if (empty($razorpayOrderId) || empty($razorpayPaymentId) || empty($razorpaySignature)) {
    echo json_encode(['success' => false, 'message' => 'Missing payment details']);
    exit;
}

try {
    // Verify signature
    $generatedSignature = hash_hmac(
        'sha256',
        $razorpayOrderId . '|' . $razorpayPaymentId,
        RAZORPAY_KEY_SECRET
    );
    
    if ($generatedSignature !== $razorpaySignature) {
        throw new Exception('Invalid payment signature');
    }
    
    // Initialize models
    $orderModel = new Order($pdo);
    $cartModel = new Cart($pdo);
    
    // Get order from database
    $order = $orderModel->getOrderByRazorpayId($razorpayOrderId);
    
    if (!$order) {
        throw new Exception('Order not found');
    }
    
    // Verify user owns this order
    if ($order['user_id'] != $userId) {
        throw new Exception('Unauthorized access');
    }
    
    // Update payment status
    $updated = $orderModel->updatePaymentStatus(
        $order['order_id'],
        $razorpayPaymentId,
        $razorpaySignature,
        'paid',
        $paymentMethod
    );
    
    if (!$updated) {
        throw new Exception('Failed to update payment status');
    }
    
    // Clear user's cart after successful payment
    $cartModel->clearCart($userId);
    
    // Return success
    echo json_encode([
        'success' => true,
        'message' => 'Payment verified successfully',
        'order_id' => $order['order_id']
    ]);
    
} catch (Exception $e) {
    error_log('Payment verification error: ' . $e->getMessage());
    
    // Update order status to failed if order exists
    if (isset($order) && $order) {
        $orderModel->updatePaymentStatus(
            $order['order_id'],
            $razorpayPaymentId,
            '',
            'failed',
            $paymentMethod
        );
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Payment verification failed: ' . $e->getMessage()
    ]);
}
?>
