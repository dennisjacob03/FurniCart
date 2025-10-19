<?php
session_start(); // ensure session is started
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Furnicart</title>
	<link rel="stylesheet" href="/FurniCart/assets/css/style.css">
</head>

<body>
	<header>
		<div class="container">
			<nav>
				<div class="logo">
					<a href="/FurniCart/public/index.php">
						<img src="/FurniCart/assets/img/logo.png" alt="FurniCart Logo">
					</a>
				</div>
				<div class="nav-links">
					<a href="/FurniCart/public/index.php">Home</a>
					<a href="/FurniCart/public/products.php">Products</a>
					<a href="/FurniCart/public/cart.php">Cart</a>
					<?php if (isset($_SESSION['user_id'])): ?>
						<a href="/FurniCart/public/profile.php">Profile</a>
						<a href="/FurniCart/public/logout.php">Logout</a>
					<?php else: ?>
						<a href="/FurniCart/public/login.php">Login</a>
					<?php endif; ?>
				</div>
			</nav>
		</div>
	</header>