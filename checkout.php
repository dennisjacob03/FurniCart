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
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Product.php';
require_once __DIR__ . '/config/razorpay_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
	header('Location: /FurniCart/login.php');
	exit;
}

$userId = $_SESSION['user_id'];

// Initialize models
$cartModel = new Cart($pdo);
$userModel = new User($pdo);
$productModel = new Product($pdo);

// Check if user has complete profile (address, pincode, city, state)
if (!$userModel->hasCompleteProfile($userId)) {
	// Redirect to profile page with message
	$_SESSION['checkout_redirect'] = true;
	$_SESSION['profile_message'] = 'Please complete your profile details (Address, Pincode, City, State) before proceeding to checkout.';
	header('Location: /FurniCart/profile.php?edit=true');
	exit;
}

// Get cart items
$cartItems = $cartModel->getCartItems($userId);
$cartTotal = $cartModel->getCartTotal($userId);
$cartItemCount = $cartModel->getCartItemCount($userId);

// Check if cart is empty
if (empty($cartItems)) {
	header('Location: /FurniCart/cart.php');
	exit;
}

// Get user details
$user = $userModel->getUserById($userId);

// Calculate totals
$platformFee = $cartTotal * 0.05;
$grandTotal = $cartTotal + $platformFee;

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
	<title>FurniCart - Checkout</title>
	<link rel="stylesheet" href="/FurniCart/assets/css/style.css">
	<style>
		.container {
			max-width: 1200px;
			margin: 0 auto;
			padding: 20px;
		}

		.checkout-header {
			text-align: center;
			margin-bottom: 30px;
		}

		.checkout-header h1 {
			color: #333;
			font-size: 2rem;
			margin-bottom: 10px;
		}

		.checkout-content {
			display: grid;
			grid-template-columns: 1fr 400px;
			gap: 30px;
			margin-bottom: 40px;
		}

		.checkout-section {
			background: white;
			padding: 25px;
			border-radius: 10px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
		}

		.section-title {
			font-size: 1.5rem;
			color: #333;
			margin-bottom: 20px;
			padding-bottom: 10px;
			border-bottom: 2px solid #007bff;
		}

		.address-info {
			background: #f8f9fa;
			padding: 15px;
			border-radius: 5px;
			margin-bottom: 15px;
		}

		.address-info p {
			margin: 8px 0;
			color: #555;
		}

		.address-info strong {
			color: #333;
			margin-right: 5px;
		}

		.edit-address-btn {
			display: inline-block;
			padding: 8px 15px;
			background: #007bff;
			color: white;
			text-decoration: none;
			border-radius: 5px;
			font-size: 0.9rem;
			transition: background 0.3s ease;
		}

		.edit-address-btn:hover {
			background: #0056b3;
		}

		.order-item {
			display: flex;
			gap: 15px;
			padding: 15px;
			border-bottom: 1px solid #eee;
		}

		.order-item:last-child {
			border-bottom: none;
		}

		.order-item-image {
			width: 80px;
			height: 80px;
			object-fit: cover;
			border-radius: 5px;
		}

		.order-item-details {
			flex: 1;
		}

		.order-item-details h4 {
			margin: 0 0 5px 0;
			color: #333;
			font-size: 1rem;
		}

		.order-item-details p {
			margin: 3px 0;
			color: #666;
			font-size: 0.9rem;
		}

		.order-summary {
			background: white;
			padding: 25px;
			border-radius: 10px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
			position: sticky;
			top: 20px;
		}

		.summary-row {
			display: flex;
			justify-content: space-between;
			padding: 10px 0;
			color: #555;
		}

		.summary-total {
			display: flex;
			justify-content: space-between;
			padding: 15px 0;
			margin-top: 10px;
			border-top: 2px solid #333;
			font-size: 1.3rem;
			font-weight: bold;
			color: #333;
		}

		.payment-btn {
			width: 100%;
			padding: 15px;
			background: #28a745;
			color: white;
			border: none;
			border-radius: 5px;
			font-size: 1.1rem;
			font-weight: bold;
			cursor: pointer;
			margin-top: 20px;
			transition: background 0.3s ease;
		}

		.payment-btn:hover {
			background: #218838;
		}

		.back-to-cart {
			display: inline-block;
			margin-top: 15px;
			color: #007bff;
			text-decoration: none;
			font-size: 0.9rem;
		}

		.back-to-cart:hover {
			text-decoration: underline;
		}

		@media (max-width: 768px) {
			.checkout-content {
				grid-template-columns: 1fr;
			}

			.order-summary {
				position: static;
			}
		}

		.loading {
			display: none;
			text-align: center;
			margin-top: 10px;
			color: #666;
		}

		.payment-btn:disabled {
			background: #6c757d;
			cursor: not-allowed;
		}
	</style>
	<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>

<body>
	<?php include 'includes/header.php'; ?>

	<div class="container">
		<div class="checkout-header">
			<h1>Checkout</h1>
			<p>Review your order and complete the payment</p>
		</div>

		<div class="checkout-content">
			<div>
				<!-- Delivery Address Section -->
				<div class="checkout-section">
					<h2 class="section-title">Delivery Address</h2>
					<div class="address-info">
						<p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
						<p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
						<p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
						<p><strong>City:</strong> <?php echo htmlspecialchars($user['city']); ?></p>
						<p><strong>State:</strong> <?php echo htmlspecialchars($user['state']); ?></p>
						<p><strong>Pincode:</strong> <?php echo htmlspecialchars($user['pincode']); ?></p>
					</div>
					<a href="/FurniCart/profile.php?edit=true" class="edit-address-btn">Edit Address</a>
				</div>

				<!-- Order Items Section -->
				<div class="checkout-section" style="margin-top: 20px;">
					<h2 class="section-title">Order Items (<?php echo $cartItemCount; ?>)</h2>
					<?php foreach ($cartItems as $item): ?>
						<div class="order-item">
							<img src="<?php echo htmlspecialchars(getImageSrc($item['image'])); ?>"
								alt="<?php echo htmlspecialchars($item['name']); ?>"
								class="order-item-image">
							<div class="order-item-details">
								<h4><?php echo htmlspecialchars($item['name']); ?></h4>
								<p>Category: <?php echo htmlspecialchars($item['category']); ?></p>
								<p>Quantity: <?php echo $item['quantity']; ?></p>
								<p><strong>₹<?php echo number_format($item['price'], 2); ?> × <?php echo $item['quantity']; ?> = ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Order Summary Section -->
			<div class="order-summary">
				<h3 class="section-title">Order Summary</h3>

				<div class="summary-row">
					<span>Subtotal (<?php echo $cartItemCount; ?> items):</span>
					<span>₹<?php echo number_format($cartTotal, 2); ?></span>
				</div>

				<div class="summary-row">
					<span>Platform Fee (5%):</span>
					<span>₹<?php echo number_format($platformFee, 2); ?></span>
				</div>

				<div class="summary-total">
					<span>Total Amount:</span>
					<span>₹<?php echo number_format($grandTotal, 2); ?></span>
				</div>

				<button class="payment-btn" id="payBtn" onclick="initiatePayment()">
					Proceed to Payment
				</button>
				<div class="loading" id="loading">Processing...</div>

				<a href="/FurniCart/cart.php" class="back-to-cart">← Back to Cart</a>
			</div>
		</div>
	</div>

	<?php include 'includes/footer.php'; ?>

	<script>
		function initiatePayment() {
			const payBtn = document.getElementById('payBtn');
			const loading = document.getElementById('loading');
			
			// Disable button and show loading
			payBtn.disabled = true;
			loading.style.display = 'block';
			
			// Create Razorpay order
			fetch('/FurniCart/create_razorpay_order.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				}
			})
			.then(response => response.json())
			.then(data => {
				if (!data.success) {
					throw new Error(data.message || 'Failed to create order');
				}
				
				// Open Razorpay checkout
				const options = {
					key: data.key,
					amount: data.amount,
					currency: data.currency,
					name: data.name,
					description: data.description,
					order_id: data.order_id,
					prefill: data.prefill,
					theme: {
						color: '#007bff'
					},
					handler: function(response) {
						// Payment successful, verify on server
						verifyPayment(response);
					},
					modal: {
						ondismiss: function() {
							// User closed the payment modal
							payBtn.disabled = false;
							loading.style.display = 'none';
						}
					}
				};
				
				const rzp = new Razorpay(options);
				rzp.open();
				
				// Re-enable button after modal opens
				payBtn.disabled = false;
				loading.style.display = 'none';
			})
			.catch(error => {
				console.error('Error:', error);
				alert('Failed to initiate payment: ' + error.message);
				payBtn.disabled = false;
				loading.style.display = 'none';
			});
		}
		
		function verifyPayment(response) {
			const payBtn = document.getElementById('payBtn');
			const loading = document.getElementById('loading');
			
			payBtn.disabled = true;
			loading.style.display = 'block';
			loading.textContent = 'Verifying payment...';
			
			// Send payment details to server for verification
			const formData = new FormData();
			formData.append('razorpay_order_id', response.razorpay_order_id);
			formData.append('razorpay_payment_id', response.razorpay_payment_id);
			formData.append('razorpay_signature', response.razorpay_signature);
			
			fetch('/FurniCart/verify_payment.php', {
				method: 'POST',
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					// Redirect to success page
					window.location.href = '/FurniCart/order_success.php?order_id=' + data.order_id;
				} else {
					throw new Error(data.message || 'Payment verification failed');
				}
			})
			.catch(error => {
				console.error('Error:', error);
				alert('Payment verification failed: ' + error.message);
				payBtn.disabled = false;
				loading.style.display = 'none';
				loading.textContent = 'Processing...';
			});
		}
	</script>
</body>

</html>