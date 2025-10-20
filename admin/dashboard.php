<?php
require_once '../includes/db.php';
require_once '../includes/admin_auth.php';
require_once '../classes/User.php';
require_once '../classes/Product.php';
require_once '../classes/Order.php';

$userModel = new User($pdo);
$productModel = new Product($pdo);
$orderModel = new Order($pdo);

// Fetch stats
$totalUsers = $userModel->countUsers();
$totalProducts = $productModel->countProducts();
$totalOrders = $orderModel->countOrders();
$pendingOrders = $orderModel->countOrders('pending');
$paidOrders = $orderModel->countOrders('paid');
?>

<?php include 'admin_header.php'; ?>

<main class="admin-main">
	<div class="admin-header">
		<h1>Admin Dashboard</h1>
		<p>Welcome back! Here's your administration overview.</p>
	</div>

	<div class="dashboard-grid">
		<div class="stat-card users">
			<div class="stat-icon">👥</div>
			<div class="stat-details">
				<h3>Users</h3>
				<p class="stat-number"><?= $totalUsers ?></p>
				<a href="manage_users.php" class="view-details">View Details →</a>
			</div>
		</div>

		<div class="stat-card products">
			<div class="stat-icon">🛋</div>
			<div class="stat-details">
				<h3>Products</h3>
				<p class="stat-number"><?= $totalProducts ?></p>
				<a href="manage_products.php" class="view-details">View Details →</a>
			</div>
		</div>

		<div class="stat-card orders">
			<div class="stat-icon">📦</div>
			<div class="stat-details">
				<h3>Orders</h3>
				<p class="stat-number"><?= $totalOrders ?></p>
				<a href="manage_orders.php" class="view-details">View Details →</a>
			</div>
		</div>
	</div>

	<div class="order-status">
		<div class="status-card pending">
			<h4>Pending Orders</h4>
			<p class="number"><?= $pendingOrders ?></p>
		</div>
		<div class="status-card completed">
			<h4>Completed Orders</h4>
			<p class="number"><?= $paidOrders ?></p>
		</div>
	</div>
</main>

</div>
</body>

</html>