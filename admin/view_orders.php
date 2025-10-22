<?php
require_once '../includes/db.php';
require_once '../includes/admin_auth.php';
require_once '../classes/Order.php';
require_once '../classes/Product.php';

// Initialize models
$orderModel = new Order($pdo);
$productModel = new Product($pdo);

// Get filter parameters
$statusFilter = $_GET['status'] ?? 'all';
$categoryFilter = $_GET['category'] ?? 'all';
$searchTerm = $_GET['search'] ?? '';

// Get orders based on filters
if ($searchTerm) {
	$orders = $orderModel->searchOrders($searchTerm);
} elseif ($categoryFilter !== 'all') {
	$orders = $orderModel->getOrdersByCategory($categoryFilter, $statusFilter !== 'all' ? $statusFilter : null);
} else {
	$orders = $orderModel->getAllOrders($statusFilter !== 'all' ? $statusFilter : null);
}

// Get statistics
$stats = $orderModel->getOrderStats();

// Get all categories for filter
$categories = $productModel->getCategories();

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

<?php include 'admin_header.php'; ?>

<main class="admin-main">
	<style>

		.stats-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
			gap: 20px;
			margin-bottom: 30px;
		}

		.stat-card {
			background: white;
			padding: 25px;
			border-radius: 10px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
			transition: transform 0.2s ease;
		}

		.stat-card:hover {
			transform: translateY(-5px);
		}

		.stat-card.total {
			border-left: 4px solid #007bff;
		}

		.stat-card.pending {
			border-left: 4px solid #ffc107;
		}

		.stat-card.paid {
			border-left: 4px solid #28a745;
		}

		.stat-card.failed {
			border-left: 4px solid #dc3545;
		}

		.stat-card.revenue {
			border-left: 4px solid #17a2b8;
		}

		.stat-label {
			font-size: 0.9rem;
			color: #666;
			margin-bottom: 10px;
			text-transform: uppercase;
			letter-spacing: 0.5px;
		}

		.stat-value {
			font-size: 2rem;
			font-weight: bold;
			color: #333;
		}

		.filters-section {
			background: white;
			padding: 20px;
			border-radius: 10px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
			margin-bottom: 30px;
		}

		.filters-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
			gap: 15px;
			align-items: end;
		}

		.filter-group {
			display: flex;
			flex-direction: column;
		}

		.filter-group label {
			margin-bottom: 8px;
			color: #555;
			font-weight: 500;
		}

		.filter-group select,
		.filter-group input {
			padding: 10px;
			border: 1px solid #ddd;
			border-radius: 5px;
			font-size: 1rem;
		}

		.filter-group select:focus,
		.filter-group input:focus {
			outline: none;
			border-color: #007bff;
		}

		.filter-buttons {
			display: flex;
			gap: 10px;
		}

		.btn {
			padding: 10px 20px;
			border: none;
			border-radius: 5px;
			font-weight: 500;
			cursor: pointer;
			transition: all 0.3s ease;
			text-decoration: none;
			display: inline-block;
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

		.orders-section {
			background: white;
			padding: 20px;
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

		.orders-table {
			width: 100%;
			border-collapse: collapse;
			overflow-x: auto;
		}

		.orders-table thead {
			background: #f8f9fa;
		}

		.orders-table th {
			padding: 15px;
			text-align: left;
			font-weight: 600;
			color: #555;
			border-bottom: 2px solid #dee2e6;
		}

		.orders-table td {
			padding: 15px;
			border-bottom: 1px solid #dee2e6;
			color: #333;
		}

		.orders-table tbody tr:hover {
			background: #f8f9fa;
		}

		.status-badge {
			padding: 5px 12px;
			border-radius: 20px;
			font-size: 0.85rem;
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

		.action-links {
			display: flex;
			gap: 10px;
		}

		.action-link {
			color: #007bff;
			text-decoration: none;
			font-weight: 500;
			padding: 5px 10px;
			border-radius: 3px;
			transition: all 0.3s ease;
		}

		.action-link:hover {
			background: #007bff;
			color: white;
		}

		.empty-state {
			text-align: center;
			padding: 60px 20px;
			color: #666;
		}

		.empty-state h3 {
			margin-bottom: 10px;
			color: #333;
		}

		.results-count {
			color: #666;
			margin-bottom: 15px;
			font-size: 0.95rem;
		}

		@media (max-width: 768px) {
			.stats-grid {
				grid-template-columns: 1fr;
			}

			.filters-grid {
				grid-template-columns: 1fr;
			}

			.orders-table {
				display: block;
				overflow-x: auto;
			}

			.page-header {
				flex-direction: column;
				gap: 15px;
			}
		}
	</style>

	<div class="admin-header">
		<h1>📦 Orders Management</h1>
		<p>View all customer orders</p>
	</div>

		<!-- Statistics Cards -->
		<div class="stats-grid">
			<div class="stat-card total">
				<div class="stat-label">Total Orders</div>
				<div class="stat-value"><?php echo $stats['total']; ?></div>
			</div>
			<div class="stat-card pending">
				<div class="stat-label">Pending</div>
				<div class="stat-value"><?php echo $stats['pending']; ?></div>
			</div>
			<div class="stat-card paid">
				<div class="stat-label">Paid</div>
				<div class="stat-value"><?php echo $stats['paid']; ?></div>
			</div>
			<div class="stat-card failed">
				<div class="stat-label">Failed</div>
				<div class="stat-value"><?php echo $stats['failed']; ?></div>
			</div>
			<div class="stat-card revenue">
				<div class="stat-label">Total Revenue</div>
				<div class="stat-value">₹<?php echo number_format($stats['total_revenue'], 2); ?></div>
			</div>
		</div>

		<!-- Filters Section -->
		<div class="filters-section">
			<form method="GET" action="">
				<div class="filters-grid">
					<div class="filter-group">
						<label for="status">Payment Status</label>
						<select name="status" id="status">
							<option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
							<option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
							<option value="paid" <?php echo $statusFilter === 'paid' ? 'selected' : ''; ?>>Paid</option>
							<option value="failed" <?php echo $statusFilter === 'failed' ? 'selected' : ''; ?>>Failed</option>
						</select>
					</div>

					<div class="filter-group">
						<label for="category">Product Category</label>
						<select name="category" id="category">
							<option value="all" <?php echo $categoryFilter === 'all' ? 'selected' : ''; ?>>All Categories</option>
							<?php foreach ($categories as $category): ?>
								<option value="<?php echo htmlspecialchars($category); ?>" 
									<?php echo $categoryFilter === $category ? 'selected' : ''; ?>>
									<?php echo htmlspecialchars($category); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="filter-group">
						<label for="search">Search Orders</label>
						<input type="text" name="search" id="search" 
							placeholder="Order ID, Customer Name, Email..."
							value="<?php echo htmlspecialchars($searchTerm); ?>">
					</div>

					<div class="filter-group">
						<label>&nbsp;</label>
						<div class="filter-buttons">
							<button type="submit" class="btn btn-primary">Apply Filters</button>
							<a href="/FurniCart/admin/view_orders.php" class="btn btn-secondary">Clear</a>
						</div>
					</div>
				</div>
			</form>
		</div>

		<!-- Orders Table -->
		<div class="orders-section">
			<h2 class="section-title">Orders List</h2>
			
			<?php if (!empty($orders)): ?>
				<div class="results-count">
					Showing <?php echo count($orders); ?> order(s)
				</div>

				<div style="overflow-x: auto;">
					<table class="orders-table">
						<thead>
							<tr>
								<th>SI No</th>
								<th>Order ID</th>
								<th>Customer</th>
								<th>Email</th>
								<th>Phone</th>
								<th>Items</th>
								<th>Total Amount</th>
								<th>Status</th>
								<th>Order Date</th>
							</tr>
						</thead>
						<tbody>
							<?php 
							$siNo = 1;
							foreach ($orders as $order): 
							?>
								<tr>
									<td><?php echo $siNo++; ?></td>
									<td><strong>#<?php echo $order['order_id']; ?></strong></td>
									<td><?php echo htmlspecialchars($order['customer_name']); ?></td>
									<td><?php echo htmlspecialchars($order['email']); ?></td>
									<td><?php echo htmlspecialchars($order['phone']); ?></td>
									<td><?php echo $order['item_count']; ?></td>
									<td><strong>₹<?php echo number_format($order['total_amount'], 2); ?></strong></td>
									<td><?php echo getStatusBadge($order['payment_status']); ?></td>
									<td><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php else: ?>
				<div class="empty-state">
					<h3>No Orders Found</h3>
					<p>No orders match your current filters. Try adjusting the filters or clearing them.</p>
				</div>
			<?php endif; ?>
		</div>
	</div>

</main>

</div>
</body>

</html>