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
if (!isset($input['product_id']) || !isset($input['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$productId = (int)$input['product_id'];
$quantity = (int)$input['quantity'];
$userId = $_SESSION['user_id'];

// Validate quantity
if ($quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
    exit;
}

try {
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../classes/Cart.php';

    // Debug: Log the request
    error_log("Update cart request: productId=$productId, quantity=$quantity, userId=$userId");

    $cartModel = new Cart($pdo);
    $result = $cartModel->updateCartItem($userId, $productId, $quantity);
    
    // Debug: Log the result
    error_log("Update cart result: " . json_encode($result));

    if ($result['success']) {
        // Get updated cart data
        $cartItems = $cartModel->getCartItems($userId);
        $cartTotal = $cartModel->getCartTotal($userId);
        $cartItemCount = $cartModel->getCartItemCount($userId);
        
        // Find the updated item
        $updatedItem = null;
        foreach ($cartItems as $item) {
            if ($item['product_id'] == $productId) {
                $updatedItem = $item;
                break;
            }
        }

        $result['cart_data'] = [
            'cart_count' => $cartItemCount,
            'cart_total' => $cartTotal,
            'item_total' => $updatedItem ? $updatedItem['price'] * $updatedItem['quantity'] : 0,
            'item_quantity' => $updatedItem ? $updatedItem['quantity'] : 0
        ];
    }

    echo json_encode($result);

} catch (Exception $e) {
    error_log("Cart update error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode(['success' => false, 'message' => 'An error occurred while updating cart: ' . $e->getMessage()]);
}
?>
