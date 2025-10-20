<?php
// Start session first, before any output
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/classes/Cart.php';
require_once __DIR__ . '/classes/Product.php';

$cartModel = new Cart($pdo);
$productModel = new Product($pdo);

// Check if user is logged in
$userId = $_SESSION['user_id'] ?? null;
$cartItems = [];
$cartTotal = 0;
$cartItemCount = 0;

if ($userId) {
	$cartItems = $cartModel->getCartItems($userId);
	$cartTotal = $cartModel->getCartTotal($userId);
	$cartItemCount = $cartModel->getCartItemCount($userId);
}

// Handle cart actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!$userId) {
		$message = 'Please login to manage your cart';
		$messageType = 'error';
	} else {
		$action = $_POST['action'] ?? '';
		$productId = (int) ($_POST['product_id'] ?? 0);

		switch ($action) {
			case 'update':
				$quantity = (int) ($_POST['quantity'] ?? 0);
				$result = $cartModel->updateCartItem($userId, $productId, $quantity);
				$message = $result['message'];
				$messageType = $result['success'] ? 'success' : 'error';
				break;

			case 'remove':
				$result = $cartModel->removeFromCart($userId, $productId);
				$message = $result['message'];
				$messageType = $result['success'] ? 'success' : 'error';
				break;

			case 'clear':
				$result = $cartModel->clearCart($userId);
				$message = $result['message'];
				$messageType = $result['success'] ? 'success' : 'error';
				break;
		}

		// Refresh cart data after action
		if ($userId) {
			$cartItems = $cartModel->getCartItems($userId);
			$cartTotal = $cartModel->getCartTotal($userId);
			$cartItemCount = $cartModel->getCartItemCount($userId);
		}
	}
}

function getImageSrc($image, $type = 'products', $default = 'placeholder.jpg')
{
	if (empty($image)) {
		return "/FurniCart/assets/img/{$default}";
	}

	if (filter_var($image, FILTER_VALIDATE_URL)) {
		return $image;
	}

	return "/FurniCart/uploads/{$type}/" . $image;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>FurniCart - Shopping Cart</title>
	<link rel="stylesheet" href="/FurniCart/assets/css/style.css">
	<style>
		.container {
			max-width: 1200px;
			margin: 0 auto;
			padding: 20px;
		}

		.cart-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 30px;
			padding-bottom: 20px;
			border-bottom: 2px solid #e0e0e0;
			margin-top: 20px;
		}

		.cart-title {
			font-size: 2rem;
			color: #333;
			margin: 0;
		}

		.cart-item-count {
			background: #007bff;
			color: white;
			padding: 5px 15px;
			border-radius: 20px;
			font-size: 0.9rem;
		}

		.message {
			padding: 15px;
			border-radius: 5px;
			margin-bottom: 20px;
		}

		.message.success {
			background: #d4edda;
			color: #155724;
			border: 1px solid #c3e6cb;
		}

		.message.error {
			background: #f8d7da;
			color: #721c24;
			border: 1px solid #f5c6cb;
		}

		.cart-content {
			display: grid;
			grid-template-columns: 1fr 300px;
			gap: 30px;
		}

		.cart-items {
			background: white;
			border-radius: 10px;
			padding: 20px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
		}

		.cart-item {
			display: grid;
			grid-template-columns: 120px 1fr auto auto;
			gap: 20px;
			align-items: center;
			padding: 20px 0;
			border-bottom: 1px solid #e0e0e0;
		}

		.cart-item:last-child {
			border-bottom: none;
		}

		.cart-item-image {
			width: 120px;
			height: 120px;
			object-fit: cover;
			border-radius: 10px;
			background: #f8f9fa;
			padding: 10px;
		}

		.cart-item-info h3 {
			margin: 0 0 10px 0;
			color: #333;
			font-size: 1.2rem;
		}

		.cart-item-info p {
			margin: 5px 0;
			color: #666;
			font-size: 0.9rem;
		}

		.cart-item-price {
			font-size: 1.1rem;
			font-weight: bold;
			color: #007bff;
		}

		.quantity-controls {
			display: flex;
			align-items: center;
			gap: 10px;
		}

		.quantity-display {
			display: inline-block;
			padding: 8px 12px;
			background: #f8f9fa;
			border: 1px solid #ddd;
			border-radius: 5px;
			font-weight: bold;
			min-width: 40px;
			text-align: center;
		}

		.quantity-input {
			width: 60px;
			padding: 8px;
			text-align: center;
			border: 1px solid #ddd;
			border-radius: 5px;
		}

		.quantity-btn {
			background: #007bff;
			color: white;
			border: none;
			width: 30px;
			height: 30px;
			border-radius: 50%;
			cursor: pointer;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.quantity-btn:hover {
			background: #0056b3;
		}

		.remove-btn {
			background: #dc3545;
			color: white;
			border: none;
			padding: 8px 12px;
			border-radius: 5px;
			cursor: pointer;
			font-size: 0.9rem;
		}

		.remove-btn:hover {
			background: #c82333;
		}

		.cart-summary {
			background: white;
			border-radius: 10px;
			padding: 20px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
			height: fit-content;
		}

		.summary-title {
			font-size: 1.3rem;
			color: #333;
			margin-bottom: 20px;
			padding-bottom: 10px;
			border-bottom: 1px solid #e0e0e0;
		}

		.summary-row {
			display: flex;
			justify-content: space-between;
			margin-bottom: 15px;
			color: #666;
		}

		.summary-total {
			display: flex;
			justify-content: space-between;
			font-size: 1.2rem;
			font-weight: bold;
			color: #333;
			padding-top: 15px;
			border-top: 2px solid #e0e0e0;
		}

		.checkout-btn {
			width: 100%;
			background: #28a745;
			color: white;
			border: none;
			padding: 15px;
			border-radius: 5px;
			font-size: 1.1rem;
			font-weight: bold;
			cursor: pointer;
			margin-top: 20px;
			transition: background 0.3s ease;
		}

		.checkout-btn:hover {
			background: #218838;
		}

		.checkout-btn:disabled {
			background: #6c757d;
			cursor: not-allowed;
		}

		.clear-cart-btn {
			background: #dc3545;
			color: white;
			border: none;
			padding: 10px 20px;
			border-radius: 5px;
			cursor: pointer;
			margin-top: 10px;
			width: 100%;
		}

		.clear-cart-btn:hover {
			background: #c82333;
		}

		.empty-cart {
			text-align: center;
			padding: 60px 20px;
			color: #666;
			margin-bottom: 140px;
		}

		.empty-cart h2 {
			margin-bottom: 20px;
			color: #333;
		}

		.empty-cart p {
			margin-bottom: 30px;
			font-size: 1.1rem;
		}

		.btn-primary {
			display: inline-block;
			padding: 12px 30px;
			background: #007bff;
			color: white;
			text-decoration: none;
			border-radius: 5px;
			font-weight: bold;
			transition: background 0.3s ease;
		}

		.btn-primary:hover {
			background: #0056b3;
			color: white;
			text-decoration: none;
		}

		.login-prompt {
			text-align: center;
			padding: 60px 20px;
			background: white;
			border-radius: 10px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
		}

		.login-prompt h2 {
			margin-bottom: 20px;
			color: #333;
		}

		.login-prompt p {
			margin-bottom: 30px;
			color: #666;
			font-size: 1.1rem;
		}

		@media (max-width: 768px) {
			.cart-content {
				grid-template-columns: 1fr;
				gap: 20px;
			}

			.cart-item {
				grid-template-columns: 1fr;
				gap: 15px;
				text-align: center;
			}

			.cart-item-image {
				width: 100%;
				height: 200px;
			}

			.quantity-controls {
				justify-content: center;
			}
		}
	</style>
</head>

<body>
	<?php include 'includes/header.php'; ?>

	<div class="container">
		<div class="cart-header">
			<h1 class="cart-title">Shopping Cart</h1>
			<?php if ($userId): ?>
				<div class="cart-item-count">
					<?php echo $cartItemCount; ?> item(s)
				</div>
			<?php endif; ?>
		</div>

		<?php if ($message): ?>
			<div class="message <?php echo $messageType; ?>">
				<?php echo htmlspecialchars($message); ?>
			</div>
		<?php endif; ?>

		<?php if (!$userId): ?>
			<!-- Login Prompt -->
			<div class="login-prompt">
				<h2>Please Login</h2>
				<p>You need to be logged in to view your shopping cart.</p>
				<a href="/FurniCart/login.php" class="btn-primary">Login</a>
			</div>

		<?php elseif (empty($cartItems)): ?>
			<!-- Empty Cart -->
			<div class="empty-cart">
				<h2>Your cart is empty</h2>
				<p>Looks like you haven't added any items to your cart yet.</p>
				<a href="/FurniCart/product.php" class="btn-primary">Continue Shopping</a>
			</div>

		<?php else: ?>
			<!-- Cart Content -->
			<div class="cart-content">
				<div class="cart-items">
					<h2>Cart Items</h2>
					<?php foreach ($cartItems as $item): ?>
						<div class="cart-item">
							<img src="<?php echo htmlspecialchars(getImageSrc($item['image'])); ?>"
								alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-item-image">

							<div class="cart-item-info">
								<h3><?php echo htmlspecialchars($item['name']); ?></h3>
								<p><strong>Category:</strong> <?php echo htmlspecialchars($item['category']); ?></p>
								<p><strong>Stock:</strong> <?php echo $item['stock']; ?> available</p>
								<div class="cart-item-price">
									₹<?php echo number_format($item['price'], 2); ?> each
								</div>
							</div>

							<div class="quantity-controls">
								<!-- Minus Button -->
								<form method="POST" style="display: inline;">
									<input type="hidden" name="action" value="update">
									<input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
									<input type="hidden" name="quantity" value="<?php echo max(1, $item['quantity'] - 1); ?>">
									<button type="submit" class="quantity-btn" <?php echo $item['quantity'] <= 1 ? 'disabled' : ''; ?>>-</button>
								</form>

								<!-- Quantity Display -->
								<span class="quantity-display"><?php echo $item['quantity']; ?></span>

								<!-- Plus Button -->
								<form method="POST" style="display: inline;">
									<input type="hidden" name="action" value="update">
									<input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
									<input type="hidden" name="quantity" value="<?php echo min($item['stock'], $item['quantity'] + 1); ?>">
									<button type="submit" class="quantity-btn" <?php echo $item['quantity'] >= $item['stock'] ? 'disabled' : ''; ?>>+</button>
								</form>
							</div>

							<div>
								<div class="cart-item-price">
									₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
								</div>
								<form method="POST" style="margin-top: 10px;">
									<input type="hidden" name="action" value="remove">
									<input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
									<button type="submit" class="remove-btn"
										onclick="return confirm('Are you sure you want to remove this item?')">
										Remove
									</button>
								</form>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="cart-summary">
					<h3 class="summary-title">Order Summary</h3>

					<div class="summary-row">
						<span>Items (<?php echo $cartItemCount; ?>):</span>
						<span>₹<?php echo number_format($cartTotal, 2); ?></span>
					</div>

					<div class="summary-row">
						<span>Platform Fee(5%):</span>
						<span>₹<?php echo number_format($cartTotal * 0.05, 2); ?></span>
					</div>

					<div class="summary-total">
						<span>Total:</span>
						<span>₹<?php echo number_format($cartTotal + $cartTotal * 0.05, 2); ?></span>
					</div>

					<a href="/FurniCart/checkout.php" class="checkout-btn" style="display: block; text-align: center; text-decoration: none;">
						Proceed to Checkout
					</a>

					<form method="POST">
						<input type="hidden" name="action" value="clear">
						<button type="submit" class="clear-cart-btn"
							onclick="return confirm('Are you sure you want to clear your entire cart?')">
							Clear Cart
						</button>
					</form>
				</div>
			</div>
		<?php endif; ?>
	</div>

	<?php include 'includes/footer.php'; ?>
</body>

</html>