<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/classes/Product.php';

$productModel = new Product($pdo);
$products = $productModel->getAllProducts();
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>FurniCart - Home</title>
	<link rel="stylesheet" href="/FurniCart/assets/css/style.css">
	<style>
		.product-grid {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
			gap: 20px;
			padding: 20px;
		}

		.card {
			border: 1px solid #ccc;
			border-radius: 10px;
			padding: 15px;
			text-align: center;
		}

		.card img {
			width: 100%;
			height: 180px;
			object-fit: cover;
			border-radius: 10px;
		}
	</style>
</head>

<body>
	<?php include 'includes/header.php'; ?>

	<!-- Hero Section -->
	<div class="hero">
		<div class="hero-overlay"></div>
		<div class="hero-content">
			<h1>Unique & Stylish<br>Furniture</h1>
			<p>Find Your Perfect Piece, Effortlessly.</p>
			<a href="#products" class="btn-primary">Shop Now</a>
		</div>
	</div>

	<!-- Categories Section -->
	<section class="categories">
		<h2 class="section-title">Choose Your Category</h2>
		<div class="category-grid">
			<div class="category-card">
				<img src="/FurniCart/assets/img/sofa_default.png" alt="Sofas">
				<h3>Sofas</h3>
				<p>Luxury & Comfort</p>
			</div>
			<div class="category-card">
				<img src="/FurniCart/assets/img/table_default.png" alt="Tables">
				<h3>Tables</h3>
				<p>Elegant Designs</p>
			</div>
			<div class="category-card">
				<img src="/FurniCart/assets/img/chair_default.png" alt="Chairs">
				<h3>Chairs</h3>
				<p>Stylish Seating</p>
			</div>
			<div class="category-card">
				<img src="/FurniCart/assets/img/bed_default.png" alt="Beds">
				<h3>Beds</h3>
				<p>Peaceful Sleep</p>
			</div>
			<div class="category-card">
				<img src="/FurniCart/assets/img/almirah_default.png" alt="Almirah">
				<h3>Almirah</h3>
				<p>Storage Solutions</p>
			</div>
		</div>
	</section>

	<!-- Products Section -->
	<section id="products" class="products">
		<h2 class="section-title">Our Products</h2>
		<div class="product-grid">
			<?php foreach ($products as $p): ?>
				<div class="product-card">
					<div class="product-image">
						<img src="/FurniCart/uploads/<?php echo htmlspecialchars($p['image'] ?: 'placeholder.jpg'); ?>"
							alt="<?php echo htmlspecialchars($p['name']); ?>">
					</div>
					<div class="product-info">
						<h3><?php echo htmlspecialchars($p['name']); ?></h3>
						<div class="product-price">₹<?php echo number_format($p['price'], 2); ?></div>
						<div class="product-rating">
							<span class="stars">★★★★★</span>
							<span class="rating">(5.0)</span>
						</div>
						<a href="product.php?id=<?php echo $p['product_id']; ?>" class="btn-view">View Details</a>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<?php include 'includes/footer.php'; ?>
</body>

</html>