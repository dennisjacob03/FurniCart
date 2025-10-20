<?php
// Start session first, before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set content type to JSON
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to manage your cart']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing product ID']);
    exit;
}

$productId = (int)$input['product_id'];
$userId = $_SESSION['user_id'];

try {
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../classes/Cart.php';

    $cartModel = new Cart($pdo);
    $result = $cartModel->removeFromCart($userId, $productId);

    if ($result['success']) {
        // Get updated cart data
        $cartTotal = $cartModel->getCartTotal($userId);
        $cartItemCount = $cartModel->getCartItemCount($userId);
        
        $result['cart_data'] = [
            'cart_count' => $cartItemCount,
            'cart_total' => $cartTotal
        ];
    }

    echo json_encode($result);

} catch (Exception $e) {
    error_log("Cart remove error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while removing item from cart']);
}
?>
