<?php
require_once '../includes/db.php';
require_once '../includes/admin_auth.php';
require_once '../classes/Product.php';

$productModel = new Product($pdo);
$products = $productModel->getAllProducts();
$categories = $productModel->getCategories();

// Handle product addition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = trim($_POST['name']);
	$description = trim($_POST['description']);
	$price = floatval($_POST['price']);
	$category = trim($_POST['category']);
	$imageUrl = trim($_POST['image_url'] ?? '');

	// Handle either file upload or URL
	$image = '';
	if (!empty($imageUrl)) {
		// Use the URL directly
		$image = $imageUrl;
	} elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
		$uploadDir = '../uploads/products/';
		if (!file_exists($uploadDir)) {
			mkdir($uploadDir, 0777, true);
		}

		$fileName = uniqid() . '_' . basename($_FILES['image']['name']);
		$targetPath = $uploadDir . $fileName;

		if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
			$image = $fileName;
		}
	}

	$stock = intval($_POST['stock']);

	if ($productModel->addProduct($name, $description, $price, $category, $image, $stock)) {
		header("Location: manage_products.php?msg=Product added successfully");
		exit;
	}
}

include 'admin_header.php';
?>

<main class="admin-main">
	<div class="admin-header">
		<h1>Manage Products</h1>
		<p>Add and manage products</p>
	</div>

	<?php if (isset($_GET['msg'])): ?>
		<div class="alert alert-success">
			<?php echo htmlspecialchars($_GET['msg']); ?>
		</div>
	<?php endif; ?>

	<!-- Add Product Form -->
	<div class="form-container">
		<h2>Add New Product</h2>
		<form method="POST" enctype="multipart/form-data" class="admin-form">
			<div class="form-group">
				<label for="name">Product Name *</label>
				<input type="text" id="name" name="name" required>
			</div>

			<div class="form-group">
				<label for="description">Description *</label>
				<textarea id="description" name="description" required rows="4"></textarea>
			</div>

			<div class="form-row">
				<div class="form-group">
					<label for="price">Price *</label>
					<input type="number" id="price" name="price" step="0.01" required>
				</div>

				<div class="form-group">
					<label for="category">Category *</label>
					<select id="category" name="category" required>
						<option value="">Select Category</option>
						<option value="Sofas">Sofas</option>
						<option value="Tables">Tables</option>
						<option value="Chairs">Chairs</option>
						<option value="Beds">Beds</option>
						<option value="Almirah">Almirah</option>
					</select>
				</div>
			</div>

			<div class="form-row">
				<div class="form-group">
					<label for="stock">Stock *</label>
					<input type="number" id="stock" name="stock" min="0" required>
				</div>
			</div>

			<div class="form-group">
				<label>Product Image *</label>
				<div class="image-input-toggle">
					<label>
						<input type="radio" name="image_type" value="file" checked> Upload File
					</label>
					<label>
						<input type="radio" name="image_type" value="url"> Image URL
					</label>
				</div>
				<div id="file-input" class="image-input">
					<input type="file" id="image" name="image" accept="image/*">
				</div>
				<div id="url-input" class="image-input" style="display: none;">
					<input type="url" id="image_url" name="image_url" placeholder="Enter image URL">
				</div>
			</div>

			<button type="submit" class="btn-primary">Add Product</button>
		</form>
	</div>

	<!-- Products List -->
	<div class="table-container">
		<h2>Current Products</h2>
		<table class="admin-table">
			<thead>
				<tr>
					<th>SI No</th>
					<th>Image</th>
					<th>Name</th>
					<th>Category</th>
					<th>Price</th>
					<th>Stock</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($products as $index => $product): ?>
					<tr>
						<td><?php echo $index + 1; ?></td>
						<td>
							<?php
							$imageSrc = filter_var($product['image'], FILTER_VALIDATE_URL)
								? $product['image']
								: "/FurniCart/uploads/products/" . htmlspecialchars($product['image']);
							?>
							<img src="<?php echo $imageSrc; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>"
								class="product-thumbnail">
						</td>
						<td><?php echo htmlspecialchars($product['name']); ?></td>
						<td><?php echo htmlspecialchars($product['category']); ?></td>
						<td>₹<?php echo number_format($product['price'], 2); ?></td>
						<td>
							<span class="stock-badge <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
								<?php echo $product['stock'] > 0 ? $product['stock'] : 'Out of Stock'; ?>
							</span>
						</td>
						<td class="actions">
							<button class="btn-primary btn-edit">Edit</button>
							<button class="btn-danger btn-delete">Delete</button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</main>
</div>
</body>

</html>