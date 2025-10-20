<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/classes/Product.php';
require_once __DIR__ . '/classes/Category.php';

$productModel = new Product($pdo);
$categoryModel = new Category($pdo);

$products = $productModel->getAllProducts();
$categories = $categoryModel->getAllCategories();

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
			<?php foreach ($categories as $category): ?>
				<div class="category-card">
					<?php
					$imageSrc = $category['image'] ?
						(filter_var($category['image'], FILTER_VALIDATE_URL) ?
							$category['image'] :
							"/FurniCart/uploads/categories/" . $category['image']
						) :
						"/FurniCart/assets/img/{$category['name']}_default.png";
					?>
					<img src="<?php echo htmlspecialchars($imageSrc); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
					<h3><?php echo htmlspecialchars($category['name']); ?></h3>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- Products Section -->
	<section id="products" class="products">
		<h2 class="section-title">Our Products</h2>
		<div class="product-grid">
			<?php foreach ($products as $p): ?>
				<div class="product-card">
					<div class="product-image">
						<?php
						$imageSrc = $p['image'] ?
							(filter_var($p['image'], FILTER_VALIDATE_URL) ?
								$p['image'] :
								"/FurniCart/uploads/products/" . $p['image']
							) :
							'/FurniCart/assets/img/placeholder.jpg';
						?>
						<img src="<?php echo htmlspecialchars($imageSrc); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
					</div>
					<div class="product-info">
						<h3><?php echo htmlspecialchars($p['name']); ?></h3>
						<div class="product-price">₹<?php echo number_format($p['price'], 2); ?></div>
						<a href="product.php?id=<?php echo $p['product_id']; ?>" class="btn-view">View Details</a>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<?php include 'includes/footer.php'; ?>
</body>

</html>