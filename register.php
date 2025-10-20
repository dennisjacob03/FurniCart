<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/classes/User.php';

$userModel = new User($pdo);
$msg = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Validate name
	$name = trim($_POST['name']);
	if (strlen($name) < 2) {
		$errors['name'] = "Name must contain at least 2 characters";
	}
	if (!preg_match("/^[a-zA-Z ]*$/", $name)) {
		$errors['name'] = "Name should only contain letters and spaces";
	}

	// Validate email
	$email = trim($_POST['email']);
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$errors['email'] = "Please enter a valid email address";
	}

	// Validate phone
	$phone = trim($_POST['phone']);
	if (!preg_match("/^[0-9]{10}$/", $phone)) {
		$errors['phone'] = "Please enter a valid 10-digit phone number";
	}

	// Validate password
	$password = trim($_POST['password']);
	$confirm_password = trim($_POST['confirm_password']);

	// Password validation using a single regex check
	if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>])[A-Za-z\d!@#$%^&*(),.?":{}|<>]{6,}$/', $password)) {
		$errors['password'] = "Password must contain: atleast 6 characters, one capital letter, one small letter, one number, one special character";
	}

	if ($password !== $confirm_password) {
		$errors['confirm_password'] = "Passwords do not match";
	}

	// If no errors, proceed with registration
	if (empty($errors)) {
		if ($userModel->register($name, $email, $phone, $password)) {
			header("Location: login.php?msg=" . urlencode("✅ Registration successful! Please login."));
			exit;
		} else {
			$errors['email'] = "Email already exists. Try another one.";
		}
	}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Register - FurniCart</title>
	<link rel="stylesheet" href="/FurniCart/assets/css/style.css">
	<style>
		body{
			padding-top: 0;
		}
		.logo {
			margin: 20px;
			display: block;
		}

		.logo img {
			max-width: 200px;
			height: auto;
		}

		.error {
			color: red;
			font-size: 0.9em;
			margin-top: 5px;
		}

		.form-group {
			margin-bottom: 15px;
		}
	</style>
</head>

<body>
	<a href="index.php" class="logo">
		<img src="/FurniCart/assets/img/logo.png" alt="FurniCart Logo">
	</a>
	<div class="auth-container">
		<h2>Sign Up</h2>

		<form method="POST" novalidate>
			<div class="form-group">
				<label>Full Name</label>
				<input type="text" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required>
				<?php if (isset($errors['name'])): ?>
					<div class="error"><?= $errors['name'] ?></div>
				<?php endif; ?>
			</div>

			<div class="form-group">
				<label>Email</label>
				<input type="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
				<?php if (isset($errors['email'])): ?>
					<div class="error"><?= $errors['email'] ?></div>
				<?php endif; ?>
			</div>

			<div class="form-group">
				<label>Phone</label>
				<input type="tel" name="phone" value="<?= htmlspecialchars($phone ?? '') ?>" required>
				<?php if (isset($errors['phone'])): ?>
					<div class="error"><?= $errors['phone'] ?></div>
				<?php endif; ?>
			</div>

			<div class="form-group">
				<label>Password</label>
				<input type="password" name="password" required>
				<?php if (isset($errors['password'])): ?>
					<div class="error"><?= $errors['password'] ?></div>
				<?php endif; ?>
			</div>

			<div class="form-group">
				<label>Confirm Password</label>
				<input type="password" name="confirm_password" required>
				<?php if (isset($errors['confirm_password'])): ?>
					<div class="error"><?= $errors['confirm_password'] ?></div>
				<?php endif; ?>
			</div>

			<button type="submit">Create Account</button>
		</form>

		<div class="redirect-link">
			Already have an account? <a href="login.php">Login</a>
		</div>
	</div>
</body>

</html>