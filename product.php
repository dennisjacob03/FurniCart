<?php
// Start session first, before any output
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/classes/Product.php';
require_once __DIR__ . '/classes/Category.php';

$productModel = new Product($pdo);
$categoryModel = new Category($pdo);

// Get all categories for navigation
$categories = $categoryModel->getAllCategories();
$productCategories = $productModel->getCategories();

// Handle different views
$view = 'list'; // default view
$selectedCategory = '';
$product = null;

// Check if viewing a specific product
if (isset($_GET['id']) && !empty($_GET['id'])) {
	$productId = (int) $_GET['id'];
	$product = $productModel->getProductById($productId);
	if ($product) {
		$view = 'detail';
	}
}

// Check if filtering by category
if (isset($_GET['category']) && !empty($_GET['category'])) {
	$selectedCategory = $_GET['category'];
	$view = 'category';
}

// Get products based on view
$products = [];
if ($view === 'category') {
	$products = $productModel->getProductsByCategory($selectedCategory);
} elseif ($view === 'list') {
	$products = $productModel->getAllProducts();
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
	<title>FurniCart - <?php echo $view === 'detail' ? htmlspecialchars($product['name']) : 'Products'; ?></title>
	<link rel="stylesheet" href="/FurniCart/assets/css/style.css">
	<style>
		.container {
			max-width: 1200px;
			margin: 0 auto;
			padding: 0 30px;
		}

		.breadcrumb {
			margin-top: 30px;
			margin-bottom: 20px;
			font-size: 14px;
		}

		.breadcrumb a {
			color: #007bff;
			text-decoration: none;
		}

		.breadcrumb a:hover {
			text-decoration: underline;
		}

		.category-filter {
			background: #f8f9fa;
			padding: 20px;
			border-radius: 10px;
			margin-bottom: 30px;
		}

		.category-filter h3 {
			margin-bottom: 15px;
			color: #333;
		}

		.category-buttons {
			display: flex;
			flex-wrap: wrap;
			gap: 10px;
		}

		.category-btn {
			padding: 8px 16px;
			background: #fff;
			border: 2px solid #ddd;
			border-radius: 20px;
			text-decoration: none;
			color: #333;
			transition: all 0.3s ease;
		}

		.category-btn:hover,
		.category-btn.active {
			background: #007bff;
			color: white;
			border-color: #007bff;
		}

		.product-grid {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
			gap: 25px;
			margin-top: 20px;
		}

		.product-card {
			border: 1px solid #e0e0e0;
			border-radius: 15px;
			overflow: hidden;
			transition: transform 0.3s ease, box-shadow 0.3s ease;
			background: white;
		}

		.product-card:hover {
			transform: translateY(-5px);
			box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
		}

		.product-image {
			position: relative;
			overflow: hidden;
		}

		.product-card img {
			width: 100%;
			height: 220px;
			object-fit: cover;
			transition: transform 0.3s ease;
		}

		.product-card:hover img {
			transform: scale(1.05);
		}

		.product-info {
			padding: 20px;
		}

		.product-info h3 {
			margin: 0 0 10px 0;
			font-size: 18px;
			color: #333;
		}

		.product-price {
			font-size: 20px;
			font-weight: bold;
			color: #007bff;
			margin-bottom: 15px;
		}

		.product-description {
			color: #666;
			font-size: 14px;
			margin-bottom: 15px;
			line-height: 1.4;
		}

		.btn-view {
			display: inline-block;
			padding: 10px 20px;
			background: #007bff;
			color: white;
			text-decoration: none;
			border-radius: 5px;
			transition: background 0.3s ease;
		}

		.btn-view:hover {
			background: #0056b3;
		}

		/* Product Detail Styles */
		.product-detail {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 40px;
			margin: 60px 0;
		}

		.product-detail-image {
			position: relative;
		}

		.product-detail-image img {
			width: 100%;
			height: 400px;
			object-fit: cover;
			border-radius: 15px;
		}

		.product-detail-info h1 {
			margin: 0 0 20px 0;
			color: #333;
			font-size: 28px;
		}

		.product-detail-price {
			font-size: 32px;
			font-weight: bold;
			color: #007bff;
			margin-bottom: 20px;
		}

		.product-detail-description {
			color: #666;
			line-height: 1.6;
			margin-bottom: 20px;
			font-size: 16px;
		}

		.product-meta {
			background: #f8f9fa;
			padding: 20px;
			border-radius: 10px;
			margin-bottom: 20px;
		}

		.product-meta p {
			margin: 5px 0;
			color: #666;
		}

		.product-meta strong {
			color: #333;
		}

		.btn-add-cart {
			display: inline-block;
			padding: 15px 30px;
			background: #28a745;
			color: white;
			text-decoration: none;
			border-radius: 5px;
			font-size: 16px;
			font-weight: bold;
			transition: background 0.3s ease;
		}

		.btn-add-cart:hover {
			background: #218838;
		}

		.no-products {
			text-align: center;
			padding: 40px;
			color: #666;
		}

		.section-title {
			text-align: center;
			margin-bottom: 30px;
			color: #333;
		}

		@media (max-width: 768px) {
			.product-detail {
				grid-template-columns: 1fr;
				gap: 20px;
			}

			.category-buttons {
				justify-content: center;
			}

			.product-grid {
				grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
				gap: 20px;
			}
		}
	</style>
</head>

<body>
	<?php include 'includes/header.php'; ?>

	<div class="container">
		<!-- Breadcrumb -->
		<div class="breadcrumb">
			<a href="/FurniCart/index.php">Home</a>
			<?php if ($view === 'detail'): ?>
				> <a href="/FurniCart/product.php">Products</a> > <?php echo htmlspecialchars($product['name']); ?>
			<?php elseif ($view === 'category'): ?>
				> <a href="/FurniCart/product.php">Products</a> > <?php echo htmlspecialchars($selectedCategory); ?>
			<?php else: ?>
				> Products
			<?php endif; ?>
		</div>

		<?php if ($view === 'detail'): ?>
			<!-- Product Detail View -->
			<?php if ($product): ?>
				<div class="product-detail">
					<div class="product-detail-image">
						<?php
						$imageSrc = getImageSrc($product['image']);
						?>
						<img src="<?php echo htmlspecialchars($imageSrc); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
					</div>
					<div class="product-detail-info">
						<h1><?php echo htmlspecialchars($product['name']); ?></h1>
						<div class="product-detail-price">₹<?php echo number_format($product['price'], 2); ?></div>
						<div class="product-detail-description">
							<?php echo nl2br(htmlspecialchars($product['description'])); ?>
						</div>
						<div class="product-meta">
							<p><strong>Category:</strong> <?php echo htmlspecialchars($product['category']); ?></p>
							<p><strong>Stock:</strong>
								<?php echo $product['stock'] > 0 ? $product['stock'] . ' available' : 'Out of stock'; ?></p>
						</div>
						<?php if ($product['stock'] > 0): ?>
							<a href="#" class="btn-add-cart" onclick="addToCart(<?php echo $product['product_id']; ?>)">Add to Cart</a>
						<?php else: ?>
							<button class="btn-add-cart" disabled>Out of Stock</button>
						<?php endif; ?>
					</div>
				</div>
			<?php else: ?>
				<div class="no-products">
					<h2>Product Not Found</h2>
					<p>The product you're looking for doesn't exist.</p>
					<a href="/FurniCart/product.php" class="btn-view">Back to Products</a>
				</div>
			<?php endif; ?>

		<?php else: ?>
			<!-- Product List View -->
			<div class="category-filter">
				<h3>Filter by Category</h3>
				<div class="category-buttons">
					<a href="/FurniCart/product.php" class="category-btn <?php echo empty($selectedCategory) ? 'active' : ''; ?>">
						All Products
					</a>
					<?php foreach ($productCategories as $category): ?>
						<a href="/FurniCart/product.php?category=<?php echo urlencode($category); ?>"
							class="category-btn <?php echo $selectedCategory === $category ? 'active' : ''; ?>">
							<?php echo htmlspecialchars($category); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>

			<h2 class="section-title">
				<?php if ($view === 'category'): ?>
					<?php echo htmlspecialchars($selectedCategory); ?>
				<?php else: ?>
					All Products
				<?php endif; ?>
			</h2>

			<?php if (!empty($products)): ?>
				<div class="product-grid">
					<?php foreach ($products as $p): ?>
						<div class="product-card">
							<div class="product-image">
								<?php
								$imageSrc = getImageSrc($p['image']);
								?>
								<img src="<?php echo htmlspecialchars($imageSrc); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
							</div>
							<div class="product-info">
								<h3><?php echo htmlspecialchars($p['name']); ?></h3>
								<div class="product-price">₹<?php echo number_format($p['price'], 2); ?></div>
								<div class="product-description">
									<?php echo htmlspecialchars(substr($p['description'], 0, 100)) . (strlen($p['description']) > 100 ? '...' : ''); ?>
								</div>
								<a href="/FurniCart/product.php?id=<?php echo $p['product_id']; ?>" class="btn-view">View Details</a>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else: ?>
				<div class="no-products">
					<h2>No Products Found</h2>
					<?php if ($view === 'category'): ?>
						<p>No products found in the "<?php echo htmlspecialchars($selectedCategory); ?>" category.</p>
					<?php else: ?>
						<p>No products available at the moment.</p>
					<?php endif; ?>
					<a href="/FurniCart/product.php" class="btn-view">View All Products</a>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>

	<?php include 'includes/footer.php'; ?>

	<script>
		function addToCart(productId) {
			// Check if user is logged in
			<?php if (!isset($_SESSION['user_id'])): ?>
				alert('Please login to add items to cart');
				window.location.href = '/FurniCart/login.php';
				return;
			<?php endif; ?>

			// Show loading state
			const button = event.target;
			const originalText = button.textContent;
			button.textContent = 'Adding...';
			button.disabled = true;

			// Make AJAX call to add product to cart
			fetch('/FurniCart/ajax/add_to_cart.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify({
					product_id: productId,
					quantity: 1
				})
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					// Show success message
					showMessage('Product added to cart successfully!', 'success');
					
					// Update cart count in header if it exists
					const cartCount = document.querySelector('.cart-count');
					if (cartCount && data.cart_count !== undefined) {
						cartCount.textContent = data.cart_count;
					}
				} else {
					showMessage(data.message || 'Error adding product to cart', 'error');
				}
			})
			.catch(error => {
				console.error('Error:', error);
				showMessage('An error occurred while adding item to cart', 'error');
			})
			.finally(() => {
				// Restore button state
				button.textContent = originalText;
				button.disabled = false;
			});
		}

		function showMessage(message, type) {
			// Create message element
			const messageDiv = document.createElement('div');
			messageDiv.className = `message ${type}`;
			messageDiv.textContent = message;
			messageDiv.style.cssText = `
				position: fixed;
				top: 100px;
				right: 20px;
				padding: 15px 20px;
				border-radius: 5px;
				color: white;
				font-weight: bold;
				z-index: 1000;
				opacity: 0;
				transition: opacity 0.3s ease;
			`;

			if (type === 'success') {
				messageDiv.style.backgroundColor = '#28a745';
			} else {
				messageDiv.style.backgroundColor = '#dc3545';
			}

			// Add to page
			document.body.appendChild(messageDiv);

			// Animate in
			setTimeout(() => {
				messageDiv.style.opacity = '1';
			}, 100);

			// Remove after 3 seconds
			setTimeout(() => {
				messageDiv.style.opacity = '0';
				setTimeout(() => {
					document.body.removeChild(messageDiv);
				}, 300);
			}, 3000);
		}
	</script>
</body>

</html>