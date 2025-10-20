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
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
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

    $cartModel = new Cart($pdo);
    $result = $cartModel->addToCart($userId, $productId, $quantity);

    // Get updated cart count
    $cartItemCount = $cartModel->getCartItemCount($userId);
    $result['cart_count'] = $cartItemCount;

    echo json_encode($result);

} catch (Exception $e) {
    error_log("Cart error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while adding item to cart']);
}
?>
