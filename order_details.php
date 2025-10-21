<?php
// Start session first, before any output
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/classes/Order.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
	header('Location: /FurniCart/login.php');
	exit;
}

$userId = $_SESSION['user_id'];
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$orderId) {
	header('Location: /FurniCart/my_orders.php');
	exit;
}

// Initialize Order model
$orderModel = new Order($pdo);

// Get order details
$order = $orderModel->getOrderDetails($orderId);

// Check if order exists and belongs to user
if (!$order || $order['user_id'] != $userId) {
	header('Location: /FurniCart/my_orders.php');
	exit;
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

function getStatusBadge($status)
{
	$badges = [
		'pending' => '<span class="status-badge status-pending">Pending</span>',
		'paid' => '<span class="status-badge status-paid">Paid</span>',
		'failed' => '<span class="status-badge status-failed">Failed</span>'
	];
	return $badges[$status] ?? '<span class="status-badge">' . ucfirst($status) . '</span>';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>FurniCart - Order Details</title>
	<link rel="stylesheet" href="/FurniCart/assets/css/style.css">
	<style>
		.container {
			max-width: 1200px;
			margin: 0 auto;
			padding: 20px;
		}

		.page-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 30px;
			padding-bottom: 20px;
			border-bottom: 2px solid #e0e0e0;
			margin-top: 20px;
		}

		.page-header h1 {
			font-size: 2rem;
			color: #333;
			margin: 0;
		}

		.back-link {
			color: #007bff;
			text-decoration: none;
			font-weight: 500;
		}

		.back-link:hover {
			text-decoration: underline;
		}

		.order-details-grid {
			display: grid;
			grid-template-columns: 2fr 1fr;
			gap: 20px;
			margin-bottom: 30px;
		}

		.detail-card {
			background: white;
			padding: 25px;
			border-radius: 10px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
		}

		.card-title {
			font-size: 1.3rem;
			color: #333;
			margin-bottom: 20px;
			padding-bottom: 10px;
			border-bottom: 2px solid #007bff;
		}

		.info-row {
			display: flex;
			justify-content: space-between;
			padding: 12px 0;
			border-bottom: 1px solid #e0e0e0;
		}

		.info-row:last-child {
			border-bottom: none;
		}

		.info-label {
			color: #666;
			font-weight: 500;
		}

		.info-value {
			color: #333;
			font-weight: 600;
		}

		.order-items {
			background: white;
			padding: 25px;
			border-radius: 10px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
		}

		.item-card {
			display: grid;
			grid-template-columns: 100px 1fr auto;
			gap: 20px;
			padding: 15px;
			background: #f8f9fa;
			border-radius: 8px;
			margin-bottom: 15px;
			align-items: center;
		}

		.item-card:last-child {
			margin-bottom: 0;
		}

		.item-image {
			width: 100px;
			height: 100px;
			object-fit: cover;
			border-radius: 8px;
			background: white;
			padding: 8px;
		}

		.item-info h3 {
			margin: 0 0 10px 0;
			color: #333;
			font-size: 1.1rem;
		}

		.item-info p {
			margin: 5px 0;
			color: #666;
			font-size: 0.9rem;
		}

		.item-price {
			text-align: right;
		}

		.item-price .price {
			font-size: 1.2rem;
			font-weight: bold;
			color: #007bff;
		}

		.item-price .unit-price {
			font-size: 0.85rem;
			color: #666;
			margin-top: 5px;
		}

		.order-summary {
			background: #f8f9fa;
			padding: 20px;
			border-radius: 8px;
			margin-top: 20px;
		}

		.summary-row {
			display: flex;
			justify-content: space-between;
			padding: 10px 0;
			color: #666;
		}

		.summary-total {
			display: flex;
			justify-content: space-between;
			padding: 15px 0;
			border-top: 2px solid #007bff;
			margin-top: 10px;
			font-size: 1.3rem;
			font-weight: bold;
			color: #333;
		}

		.status-badge {
			padding: 6px 15px;
			border-radius: 20px;
			font-size: 0.9rem;
			font-weight: 600;
			text-transform: uppercase;
			display: inline-block;
		}

		.status-pending {
			background: #fff3cd;
			color: #856404;
		}

		.status-paid {
			background: #d4edda;
			color: #155724;
		}

		.status-failed {
			background: #f8d7da;
			color: #721c24;
		}

		.address-box {
			background: #f8f9fa;
			padding: 15px;
			border-radius: 5px;
			line-height: 1.8;
			color: #555;
		}

		.payment-info {
			background: #e7f3ff;
			padding: 15px;
			border-radius: 5px;
			margin-top: 15px;
		}

		.payment-info p {
			margin: 5px 0;
			color: #0066cc;
			font-size: 0.9rem;
		}

		@media (max-width: 768px) {
			.order-details-grid {
				grid-template-columns: 1fr;
			}

			.item-card {
				grid-template-columns: 1fr;
				text-align: center;
			}

			.item-image {
				margin: 0 auto;
			}

			.item-price {
				text-align: center;
			}
		}
	</style>
</head>

<body>
	<?php include 'includes/header.php'; ?>

	<div class="container">
		<div class="page-header">
			<h1>Order Details - #<?php echo $order['order_id']; ?></h1>
			<a href="/FurniCart/my_orders.php" class="back-link">← Back to Orders</a>
		</div>

		<div class="order-details-grid">
			<!-- Order Information -->
			<div class="detail-card">
				<h2 class="card-title">Order Information</h2>
				<div class="info-row">
					<span class="info-label">Order ID:</span>
					<span class="info-value">#<?php echo $order['order_id']; ?></span>
				</div>
				<div class="info-row">
					<span class="info-label">Order Date:</span>
					<span class="info-value">
						<?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?>
					</span>
				</div>
				<div class="info-row">
					<span class="info-label">Payment Status:</span>
					<span><?php echo getStatusBadge($order['payment_status']); ?></span>
				</div>
				<div class="info-row">
					<span class="info-label">Total Amount:</span>
					<span class="info-value">₹<?php echo number_format($order['total_amount'], 2); ?></span>
				</div>

				<?php if (!empty($order['razorpay_order_id'])): ?>
					<div class="payment-info">
						<p><strong>Payment Details:</strong></p>
						<?php if (!empty($order['razorpay_payment_id'])): ?>
							<p>Payment ID: <?php echo htmlspecialchars($order['razorpay_payment_id']); ?></p>
						<?php endif; ?>
						<?php if (!empty($order['payment_method'])): ?>
							<p>Method: <?php echo ucfirst($order['payment_method']); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<!-- Delivery Address -->
			<div class="detail-card">
				<h2 class="card-title">Delivery Address</h2>
				<div class="address-box">
					<strong><?php echo htmlspecialchars($order['name']); ?></strong><br>
					<?php echo htmlspecialchars($order['address']); ?><br>
					<?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['state']); ?><br>
					PIN: <?php echo htmlspecialchars($order['pincode']); ?><br>
					<br>
					<strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?><br>
					<strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?>
				</div>
			</div>
		</div>

		<!-- Order Items -->
		<div class="order-items">
			<h2 class="card-title">Order Items (<?php echo count($order['items']); ?>)</h2>

			<?php foreach ($order['items'] as $item): ?>
				<div class="item-card">
					<img src="<?php echo htmlspecialchars(getImageSrc($item['image'])); ?>"
						alt="<?php echo htmlspecialchars($item['name']); ?>"
						class="item-image">

					<div class="item-info">
						<h3><?php echo htmlspecialchars($item['name']); ?></h3>
						<p>Quantity: <strong><?php echo $item['quantity']; ?></strong></p>
						<p>Price per item: ₹<?php echo number_format($item['price'], 2); ?></p>
					</div>

					<div class="item-price">
						<div class="price">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
						<div class="unit-price">
							<?php echo $item['quantity']; ?> × ₹<?php echo number_format($item['price'], 2); ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>

			<!-- Order Summary -->
			<div class="order-summary">
				<?php
				$subtotal = 0;
				foreach ($order['items'] as $item) {
					$subtotal += $item['price'] * $item['quantity'];
				}
				$platformFee = $subtotal * 0.05;
				?>

				<div class="summary-row">
					<span>Subtotal (<?php echo count($order['items']); ?> items):</span>
					<span>₹<?php echo number_format($subtotal, 2); ?></span>
				</div>
				<div class="summary-row">
					<span>Platform Fee (5%):</span>
					<span>₹<?php echo number_format($platformFee, 2); ?></span>
				</div>
				<div class="summary-total">
					<span>Total Amount:</span>
					<span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
				</div>
			</div>
		</div>
	</div>

	<?php include 'includes/footer.php'; ?>
</body>

</html>
