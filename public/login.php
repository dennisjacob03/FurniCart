<?php
session_start();
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../includes/db.php';

$error = ''; // Initialize error variable

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = $_POST['email'];
	$password = $_POST['password'];

	try {
		$userModel = new User($pdo);
		$user = $userModel->login($email, $password);
	} catch (Exception $e) {
		$error = "Database error occurred. Please try again.";
	}

	if (isset($user) && $user) {
		// Store user info in session
		$_SESSION['user_id'] = $user['user_id'];
		$_SESSION['role'] = $user['role'];

		// Fix redirect paths
		if ($user['role'] === 'admin') {
			header("Location: /FurniCart/admin/dashboard.php");
		} else {
			header("Location: /FurniCart/public/index.php");
		}
		exit;
	} else {
		$error = "Invalid email or password.";
	}
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Login - FurniCart</title>
	<link rel="stylesheet" href="/FurniCart/assets/css/style.css">
	<style>
		.logo {
			margin: 20px;
			display: block;
		}

		.logo img {
			max-width: 200px;
			height: auto;
		}
	</style>
</head>

<body>
	<a href="/FurniCart/public/index.php" class="logo">
		<img src="/FurniCart/assets/img/logo.png" alt="FurniCart Logo">
	</a>
	<div class="auth-container">
		<h2>Sign In</h2>

		<?php if (isset($_GET['msg'])): ?>
			<div class="success"><?= htmlspecialchars($_GET['msg']) ?></div>
		<?php endif; ?>

		<form method="POST">
			<div class="form-group">
				<label>Email</label>
				<input type="email" name="email" required>
			</div>

			<div class="form-group">
				<label>Password</label>
				<input type="password" name="password" required>
			</div>

			<?php if ($error): ?>
				<div class="error"><?= $error ?></div>
			<?php endif; ?>

			<button type="submit">Login</button>
		</form>

		<div class="redirect-link">
			Don't have an account? <a href="register.php">Sign up</a>
		</div>
	</div>
</body>

</html>