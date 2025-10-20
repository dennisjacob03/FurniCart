<?php
require_once '../includes/db.php';
require_once '../includes/admin_auth.php';
require_once '../classes/Category.php';

$categoryModel = new Category($pdo);
$categories = $categoryModel->getAllCategories();

// Handle category addition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $imageUrl = trim($_POST['image_url'] ?? '');
    
    // Handle either file upload or URL
    $image = '';
    if (!empty($imageUrl)) {
        // Use the URL directly
        $image = $imageUrl;
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/categories/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image = $fileName;
        }
    }
    
    if ($categoryModel->addCategory($name, $image)) {
        header("Location: manage_categories.php?msg=Category added successfully");
        exit;
    }
}

include 'admin_header.php';
?>

<main class="admin-main">
    <div class="admin-header">
        <h1>Manage Categories</h1>
        <p>Add and manage product categories</p>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_GET['msg']); ?>
        </div>
    <?php endif; ?>

    <!-- Add Category Form -->
    <div class="form-container">
        <h2>Add New Category</h2>
        <form method="POST" enctype="multipart/form-data" class="admin-form">
            <div class="form-group">
                <label for="name">Category Name *</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label>Category Image *</label>
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

            <button type="submit" class="btn-primary">Add Category</button>
        </form>
    </div>

    <!-- Categories List -->
    <div class="table-container">
        <h2>Current Categories</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>SI No</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $index => $category): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td>
                            <?php 
                            $imageSrc = filter_var($category['image'], FILTER_VALIDATE_URL) 
                                ? $category['image'] 
                                : "/FurniCart/uploads/categories/" . htmlspecialchars($category['image']);
                            ?>
                            <img src="<?php echo $imageSrc; ?>" 
                                 alt="<?php echo htmlspecialchars($category['name']); ?>"
                                 class="product-thumbnail">
                        </td>
                        <td><?php echo htmlspecialchars($category['name']); ?></td>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const radioButtons = document.querySelectorAll('input[name="image_type"]');
    const fileInput = document.getElementById('file-input');
    const urlInput = document.getElementById('url-input');

    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'file') {
                fileInput.style.display = 'block';
                urlInput.style.display = 'none';
                document.getElementById('image').required = true;
                document.getElementById('image_url').required = false;
            } else {
                fileInput.style.display = 'none';
                urlInput.style.display = 'block';
                document.getElementById('image').required = false;
                document.getElementById('image_url').required = true;
            }
        });
    });
});
</script>