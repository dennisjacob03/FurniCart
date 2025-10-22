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

// Initialize Order model
$orderModel = new Order($pdo);

// Get user's orders
$orders = $orderModel->getUserOrders($userId);

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
		'failed' => '<span class="status-badge status-failed">Failed</span>',
		'processing' => '<span class="status-badge status-processing">Processing</span>',
		'shipped' => '<span class="status-badge status-shipped">Shipped</span>',
		'delivered' => '<span class="status-badge status-delivered">Delivered</span>',
		'cancelled' => '<span class="status-badge status-cancelled">Cancelled</span>'
	];
	return $badges[$status] ?? '<span class="status-badge">' . ucfirst($status) . '</span>';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>FurniCart - My Orders</title>
	<link rel="stylesheet" href="/FurniCart/assets/css/style.css">
	<style>
		.container {
			max-width: 1200px;
			margin: 0 auto;
			padding: 20px;
		}

		.page-header {
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

		.orders-container {
			display: flex;
			flex-direction: column;
			gap: 20px;
		}

		.order-card {
			background: white;
			border-radius: 10px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
			overflow: hidden;
			transition: transform 0.2s ease;
		}

		.order-card:hover {
			transform: translateY(-2px);
			box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
		}

		.order-header {
			background: #f8f9fa;
			padding: 15px 20px;
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
			gap: 15px;
			border-bottom: 1px solid #e0e0e0;
		}

		.order-info-item {
			display: flex;
			flex-direction: column;
		}

		.order-info-label {
			font-size: 0.85rem;
			color: #666;
			margin-bottom: 5px;
		}

		.order-info-value {
			font-weight: bold;
			color: #333;
		}

		.order-body {
			padding: 20px;
		}

		.order-items {
			display: flex;
			flex-direction: column;
			gap: 15px;
		}

		.order-item {
			display: grid;
			grid-template-columns: 80px 1fr auto;
			gap: 15px;
			align-items: center;
			padding: 10px;
			background: #f8f9fa;
			border-radius: 5px;
		}

		.order-item-image {
			width: 80px;
			height: 80px;
			object-fit: cover;
			border-radius: 5px;
			background: white;
			padding: 5px;
		}

		.order-item-info h4 {
			margin: 0 0 5px 0;
			color: #333;
			font-size: 1rem;
		}

		.order-item-info p {
			margin: 3px 0;
			color: #666;
			font-size: 0.9rem;
		}

		.order-item-price {
			font-weight: bold;
			color: #007bff;
			font-size: 1.1rem;
			text-align: right;
		}

		.order-footer {
			padding: 15px 20px;
			background: #f8f9fa;
			border-top: 1px solid #e0e0e0;
			display: flex;
			justify-content: space-between;
			align-items: center;
		}

		.order-total {
			font-size: 1.2rem;
			font-weight: bold;
			color: #333;
		}

		.order-actions {
			display: flex;
			gap: 10px;
		}

		.btn {
			padding: 8px 20px;
			border-radius: 5px;
			text-decoration: none;
			font-weight: 500;
			transition: all 0.3s ease;
			border: none;
			cursor: pointer;
			font-size: 0.9rem;
		}

		.btn-primary {
			background: #007bff;
			color: white;
		}

		.btn-primary:hover {
			background: #0056b3;
			color: white;
		}

		.btn-secondary {
			background: #6c757d;
			color: white;
		}

		.btn-secondary:hover {
			background: #545b62;
			color: white;
		}

		.status-badge {
			padding: 5px 12px;
			border-radius: 20px;
			font-size: 0.85rem;
			font-weight: 600;
			text-transform: uppercase;
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

		.status-processing {
			background: #d1ecf1;
			color: #0c5460;
		}

		.status-shipped {
			background: #cce5ff;
			color: #004085;
		}

		.status-delivered {
			background: #d4edda;
			color: #155724;
		}

		.status-cancelled {
			background: #f8d7da;
			color: #721c24;
		}

		.empty-orders {
			text-align: center;
			padding: 120px 20px;
			background: white;
			border-radius: 10px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
		}

		.empty-orders h2 {
			color: #333;
			margin-bottom: 15px;
		}

		.empty-orders p {
			color: #666;
			margin-bottom: 30px;
			font-size: 1.1rem;
		}

		.payment-method {
			display: inline-block;
			padding: 3px 10px;
			background: #e7f3ff;
			color: #0066cc;
			border-radius: 3px;
			font-size: 0.85rem;
			margin-top: 5px;
		}

		@media (max-width: 768px) {
			.order-header {
				grid-template-columns: 1fr;
			}

			.order-item {
				grid-template-columns: 1fr;
				text-align: center;
			}

			.order-item-image {
				margin: 0 auto;
			}

			.order-footer {
				flex-direction: column;
				gap: 15px;
				align-items: stretch;
			}

			.order-actions {
				flex-direction: column;
			}
		}
	</style>
</head>

<body>
	<?php include 'includes/header.php'; ?>

	<div class="container">
		<div class="page-header">
			<h1>My Orders</h1>
		</div>

		<?php if (empty($orders)): ?>
			<!-- No Orders -->
			<div class="empty-orders">
				<h2>No Orders Yet</h2>
				<p>You haven't placed any orders yet. Start shopping to see your orders here!</p>
				<a href="/FurniCart/product.php" class="btn btn-primary">Browse Products</a>
			</div>

		<?php else: ?>
			<!-- Orders List -->
			<div class="orders-container">
				<?php foreach ($orders as $order): ?>
					<?php
					// Get order details with items
					$orderDetails = $orderModel->getOrderDetails($order['order_id']);
					?>
					<div class="order-card">
						<div class="order-header">
							<div class="order-info-item">
								<span class="order-info-label">Order ID</span>
								<span class="order-info-value">#<?php echo $order['order_id']; ?></span>
							</div>
							<div class="order-info-item">
								<span class="order-info-label">Order Date</span>
								<span class="order-info-value">
									<?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?>
								</span>
							</div>
							<div class="order-info-item">
								<span class="order-info-label">Status</span>
								<div><?php echo getStatusBadge($order['payment_status']); ?></div>
							</div>
							<div class="order-info-item">
								<span class="order-info-label">Items</span>
								<span class="order-info-value"><?php echo $order['item_count']; ?> item(s)</span>
							</div>
						</div>

						<div class="order-body">
							<?php if ($orderDetails && !empty($orderDetails['items'])): ?>
								<div class="order-items">
									<?php foreach ($orderDetails['items'] as $item): ?>
										<div class="order-item">
											<img src="<?php echo htmlspecialchars(getImageSrc($item['image'])); ?>"
												alt="<?php echo htmlspecialchars($item['name']); ?>"
												class="order-item-image">

											<div class="order-item-info">
												<h4><?php echo htmlspecialchars($item['name']); ?></h4>
												<p>Quantity: <?php echo $item['quantity']; ?></p>
												<p>Price: ₹<?php echo number_format($item['price'], 2); ?> each</p>
											</div>

											<div class="order-item-price">
												₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<?php if (!empty($order['payment_method'])): ?>
								<div style="margin-top: 15px;">
									<span class="payment-method">
										Payment: <?php echo ucfirst($order['payment_method']); ?>
									</span>
								</div>
							<?php endif; ?>
						</div>

						<div class="order-footer">
							<div class="order-total">
								Total: ₹<?php echo number_format($order['total_amount'], 2); ?>
							</div>
							<div class="order-actions">
								<a href="/FurniCart/order_details.php?id=<?php echo $order['order_id']; ?>"
									class="btn btn-primary">
									View Details
								</a>
								<?php if ($order['payment_status'] === 'paid'): ?>
									<button class="btn btn-secondary" onclick="alert('Invoice download coming soon!')">
										Download Invoice
									</button>
								<?php endif; ?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php include 'includes/footer.php'; ?>
</body>

</html>
