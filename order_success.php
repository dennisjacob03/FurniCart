<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/classes/Order.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
	header('Location: /FurniCart/login.php');
	exit;
}

$userId = $_SESSION['user_id'];
$orderId = $_GET['order_id'] ?? null;

if (!$orderId) {
	header('Location: /FurniCart/index.php');
	exit;
}

// Initialize Order model
$orderModel = new Order($pdo);

// Get order details
$order = $orderModel->getOrderDetails($orderId);

// Verify order exists and belongs to user
if (!$order || $order['user_id'] != $userId) {
	header('Location: /FurniCart/index.php');
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Order Success - FurniCart</title>
	<link rel="stylesheet" href="/FurniCart/assets/css/style.css">
	<style>
		.container {
			max-width: 900px;
			margin: 0 auto;
			padding: 20px;
		}

		.success-header {
			text-align: center;
			padding: 40px 20px;
			background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
			color: white;
			border-radius: 10px;
			margin-bottom: 30px;
		}

		.success-icon {
			font-size: 4rem;
			margin-bottom: 20px;
		}

		.success-header h1 {
			margin: 0 0 10px 0;
			font-size: 2rem;
		}

		.success-header p {
			margin: 0;
			font-size: 1.1rem;
			opacity: 0.9;
		}

		.order-details {
			background: white;
			padding: 30px;
			border-radius: 10px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
			margin-bottom: 20px;
		}

		.detail-row {
			display: flex;
			justify-content: space-between;
			padding: 12px 0;
			border-bottom: 1px solid #eee;
		}

		.detail-row:last-child {
			border-bottom: none;
		}

		.detail-label {
			font-weight: 600;
			color: #555;
		}

		.detail-value {
			color: #333;
		}

		.status-badge {
			display: inline-block;
			padding: 5px 15px;
			border-radius: 20px;
			font-size: 0.9rem;
			font-weight: 600;
		}

		.status-paid {
			background: #d4edda;
			color: #155724;
		}

		.status-pending {
			background: #fff3cd;
			color: #856404;
		}

		.status-failed {
			background: #f8d7da;
			color: #721c24;
		}

		.section-title {
			font-size: 1.3rem;
			color: #333;
			margin-bottom: 20px;
			padding-bottom: 10px;
			border-bottom: 2px solid #007bff;
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

		.item-image {
			width: 80px;
			height: 80px;
			object-fit: cover;
			border-radius: 5px;
		}

		.item-details {
			flex: 1;
		}

		.item-details h4 {
			margin: 0 0 5px 0;
			color: #333;
		}

		.item-details p {
			margin: 3px 0;
			color: #666;
			font-size: 0.9rem;
		}

		.address-box {
			background: #f8f9fa;
			padding: 15px;
			border-radius: 5px;
			margin-top: 15px;
		}

		.address-box p {
			margin: 5px 0;
			color: #555;
		}

		.action-buttons {
			display: flex;
			gap: 15px;
			justify-content: center;
			margin-top: 30px;
		}

		.btn {
			display: inline-block;
			padding: 12px 30px;
			border-radius: 5px;
			text-decoration: none;
			font-weight: 600;
			transition: all 0.3s ease;
		}

		.btn-primary {
			background: #007bff;
			color: white;
		}

		.btn-primary:hover {
			background: #0056b3;
		}

		.btn-secondary {
			background: #6c757d;
			color: white;
		}

		.btn-secondary:hover {
			background: #545b62;
		}

		@media (max-width: 768px) {
			.action-buttons {
				flex-direction: column;
			}

			.btn {
				width: 100%;
				text-align: center;
			}
		}
	</style>
</head>

<body>
	<?php include 'includes/header.php'; ?>

	<div class="container">
		<div class="success-header">
			<div class="success-icon">✓</div>
			<h1>Order Placed Successfully!</h1>
			<p>Thank you for your purchase. Your order has been confirmed.</p>
		</div>

		<div class="order-details">
			<h2 class="section-title">Order Information</h2>
			
			<div class="detail-row">
				<span class="detail-label">Order ID:</span>
				<span class="detail-value">#<?php echo $order['order_id']; ?></span>
			</div>

			<div class="detail-row">
				<span class="detail-label">Order Date:</span>
				<span class="detail-value"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></span>
			</div>

			<div class="detail-row">
				<span class="detail-label">Payment Status:</span>
				<span class="detail-value">
					<span class="status-badge status-<?php echo $order['payment_status']; ?>">
						<?php echo ucfirst($order['payment_status']); ?>
					</span>
				</span>
			</div>

			<?php if ($order['razorpay_payment_id']): ?>
			<div class="detail-row">
				<span class="detail-label">Payment ID:</span>
				<span class="detail-value"><?php echo htmlspecialchars($order['razorpay_payment_id']); ?></span>
			</div>
			<?php endif; ?>

			<div class="detail-row">
				<span class="detail-label">Total Amount:</span>
				<span class="detail-value"><strong>₹<?php echo number_format($order['total_amount'], 2); ?></strong></span>
			</div>
		</div>

		<div class="order-details">
			<h2 class="section-title">Delivery Address</h2>
			<div class="address-box">
				<p><strong><?php echo htmlspecialchars($order['name']); ?></strong></p>
				<p><?php echo htmlspecialchars($order['address']); ?></p>
				<p><?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['state']); ?> - <?php echo htmlspecialchars($order['pincode']); ?></p>
				<p>Phone: <?php echo htmlspecialchars($order['phone']); ?></p>
				<p>Email: <?php echo htmlspecialchars($order['email']); ?></p>
			</div>
		</div>

		<div class="order-details">
			<h2 class="section-title">Order Items (<?php echo count($order['items']); ?>)</h2>
			<?php foreach ($order['items'] as $item): ?>
				<div class="order-item">
					<img src="<?php echo htmlspecialchars(getImageSrc($item['image'])); ?>"
						alt="<?php echo htmlspecialchars($item['name']); ?>"
						class="item-image">
					<div class="item-details">
						<h4><?php echo htmlspecialchars($item['name']); ?></h4>
						<p>Quantity: <?php echo $item['quantity']; ?></p>
						<p><strong>₹<?php echo number_format($item['price'], 2); ?> × <?php echo $item['quantity']; ?> = ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="action-buttons">
			<a href="/FurniCart/my_orders.php" class="btn btn-primary">View My Orders</a>
			<a href="/FurniCart/product.php" class="btn btn-secondary">Continue Shopping</a>
		</div>
	</div>

	<?php include 'includes/footer.php'; ?>
</body>

</html>
