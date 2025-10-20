<?php
// Start session first, before any output
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/classes/Product.php';

$productModel = new Product($pdo);

// Get all products to show current stock levels
$products = $productModel->getAllProducts();
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Stock Test - FurniCart</title>
	<style>
		body {
			font-family: Arial, sans-serif;
			margin: 20px;
		}

		table {
			border-collapse: collapse;
			width: 100%;
		}

		th,
		td {
			border: 1px solid #ddd;
			padding: 8px;
			text-align: left;
		}

		th {
			background-color: #f2f2f2;
		}

		.stock-low {
			color: red;
			font-weight: bold;
		}

		.stock-ok {
			color: green;
		}
	</style>
</head>

<body>
	<h1>Product Stock Levels Test</h1>
	<p>This page shows the current stock levels for all products. Stock should decrease when items are added to cart and
		increase when removed.</p>

	<table>
		<tr>
			<th>Product ID</th>
			<th>Product Name</th>
			<th>Current Stock</th>
			<th>Price</th>
			<th>Category</th>
		</tr>
		<?php foreach ($products as $product): ?>
			<tr>
				<td><?php echo $product['product_id']; ?></td>
				<td><?php echo htmlspecialchars($product['name']); ?></td>
				<td class="<?php echo $product['stock'] <= 5 ? 'stock-low' : 'stock-ok'; ?>">
					<?php echo $product['stock']; ?>
				</td>
				<td>₹<?php echo number_format($product['price'], 2); ?></td>
				<td><?php echo htmlspecialchars($product['category']); ?></td>
			</tr>
		<?php endforeach; ?>
	</table>

	<h2>Test Instructions:</h2>
	<ol>
		<li>Note the current stock levels above</li>
		<li>Go to <a href="/FurniCart/product.php">Products page</a> and add some items to cart</li>
		<li>Refresh this page to see if stock decreased</li>
		<li>Go to <a href="/FurniCart/cart.php">Cart page</a> and remove items</li>
		<li>Refresh this page to see if stock increased back</li>
	</ol>

	<p><a href="/FurniCart/index.php">← Back to Home</a></p>
</body>

</html>