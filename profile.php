<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/classes/User.php';

// Initialize models
$userModel = new User($pdo);

// Get user data
$user = $userModel->getUserById($_SESSION['user_id']);

$isEditing = isset($_GET['edit']) && $_GET['edit'] === 'true';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isEditing) {
    $address = trim($_POST['address']);
    $pincode = trim($_POST['pincode']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);

    if ($userModel->updateAddress($_SESSION['user_id'], $address, $pincode, $city, $state)) {
        header("Location: profile.php?msg=Address updated successfully");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Profile - FurniCart</title>
	<link rel="stylesheet" href="/FurniCart/assets/css/style.css">
	<style>
		.profile-container {
			max-width: 800px;
			margin: 2rem auto;
			padding: 0 1rem;
		}

		.profile-info {
			background: white;
			padding: 2rem;
			border-radius: 10px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
			margin-bottom: 2rem;
		}

		.address-form {
			background: white;
			padding: 2rem;
			border-radius: 10px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
			margin-bottom: 2rem;
		}

		.address-list {
			background: white;
			padding: 2rem;
			border-radius: 10px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
		}

		.address-item {
			padding: 1rem;
			border: 1px solid #eee;
			margin-bottom: 1rem;
			border-radius: 5px;
		}

		.form-group {
			margin-bottom: 1rem;
		}

		.form-group input {
			width: 100%;
			padding: 0.5rem;
			border: 1px solid #ddd;
			border-radius: 5px;
			outline: none;
		}

		.success-msg {
			background: #d4edda;
			color: #155724;
			padding: 1rem;
			border-radius: 5px;
			margin-bottom: 1rem;
		}

		.profile-header {
			display: flex;
			align-items: center;
			margin-bottom: 2rem;
		}

		.profile-avatar {
			margin-right: 1.5rem;
		}

		.profile-avatar img {
			width: 80px;
			height: 80px;
			border-radius: 50%;
			object-fit: cover;
		}

		.profile-title h1 {
			margin: 0;
			font-size: 1.5rem;
		}

		.edit-profile-btn {
			background: #007bff;
			color: white;
			border: none;
			padding: 0.5rem 1rem;
			border-radius: 5px;
			cursor: pointer;
			text-decoration: none;
		}

		.profile-sections {
			background: white;
			padding: 2rem;
			border-radius: 10px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
		}

		.account-details,
		.additional-details,
		.address-details {
			margin-bottom: 2rem;
		}

		.address-card {
			background: #f9f9f9;
			padding: 1rem;
			border-radius: 5px;
			margin-bottom: 1rem;
			border: 1px solid #ddd;
		}

		.add-address-btn {
			background: #28a745;
			color: white;
			border: none;
			padding: 0.5rem 1rem;
			border-radius: 5px;
			cursor: pointer;
		}

		.readonly-form input {
			background-color: #f5f5f5;
			cursor: not-allowed;
		}

		.save-btn {
			background: #28a745;
			color: white;
			border: none;
			padding: 0.5rem 1rem;
			border-radius: 5px;
			cursor: pointer;
			margin-right: 1rem;
		}

		.cancel-btn {
			background: #dc3545;
			color: white;
			text-decoration: none;
			padding: 0.5rem 1rem;
			border-radius: 5px;
			display: inline-block;
		}

		.editing-status {
			color: #007bff;
			font-size: 1rem;
			margin-top: 0.5rem;
			display: block;
		}

		.field-error {
			color: #dc3545;
			font-size: 0.875rem;
			margin-top: 0.25rem;
		}
	</style>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="/FurniCart/assets/js/app.js"></script>
</head>

<body>
	<?php include 'includes/header.php'; ?>

	<div class="profile-container">
		<div class="profile-header">
			<div class="profile-avatar">
				<img src="/FurniCart/assets/img/avatar_default.png" alt="Profile Avatar">
			</div>
			<div class="profile-title">
				<h1>Hi, <?php echo htmlspecialchars($user['name']); ?></h1>
				<?php if ($isEditing): ?>
					<span class="editing-status">Editing Profile</span>
				<?php else: ?>
					<a href="?edit=true" class="edit-profile-btn">Edit Profile</a>
				<?php endif; ?>
			</div>
		</div>

		<div class="profile-sections">
			<section class="account-details">
				<h2>Account Details:</h2>
				<div class="form-group">
					<label>Full Name: *</label>
					<input type="text" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
				</div>
				<div class="form-group">
					<label>Email: *</label>
					<input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
				</div>
				<div class="form-group">
					<label>Phone: *</label>
					<input type="tel" value="<?php echo htmlspecialchars($user['phone']); ?>" readonly>
				</div>
			</section>

			<section class="address-details">
				<h2>Additional Details:</h2>
				<div id="message-container"></div>

				<form method="POST" id="addressForm" class="<?php echo $isEditing ? '' : 'readonly-form'; ?>">
					<div class="form-group">
						<label>Address: *</label>
						<input type="text" name="address" id="address" 
							value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" 
							<?php echo $isEditing ? '' : 'readonly'; ?>>
						<span id="address-error" class="field-error"></span>
					</div>
					<div class="form-group">
						<label>Pincode: *</label>
						<input type="number" name="pincode" id="pincode" 
							pattern="[0-9]{6}" 
							value="<?php echo htmlspecialchars($user['pincode'] ?? ''); ?>" 
							<?php echo $isEditing ? '' : 'readonly'; ?> 
							onchange="lookupPincode(this.value)">
						<span id="pincode-error" class="field-error"></span>
					</div>
					<div class="form-group">
						<label>City: </label>
						<input type="text" name="city" id="city" 
							value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" readonly>
					</div>
					<div class="form-group">
						<label>State: </label>
						<input type="text" name="state" id="state" 
							value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>" readonly>
					</div>
					<?php if ($isEditing): ?>
						<button type="submit" class="save-btn">Save Changes</button>
						<a href="profile.php" class="cancel-btn">Cancel</a>
					<?php endif; ?>
				</form>
			</section>
		</div>
	</div>

	<?php include 'includes/footer.php'; ?>
</body>

</html>