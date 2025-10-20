<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Furnicart</title>
	<link rel="stylesheet" href="/FurniCart/assets/css/style.css">
	<style>
		.avatar {
			width: 32px;
			height: 32px;
			border-radius: 50%;
			vertical-align: middle;
			margin-right: 8px;
		}
	</style>
</head>

<body>
	<header>
		<div class="container">
			<nav>
				<div class="logo">
					<a href="/FurniCart/index.php">
						<img src="/FurniCart/assets/img/logo.png" alt="FurniCart Logo">
					</a>
				</div>

				<div class="nav-center">
					<a href="/FurniCart/index.php">Home</a>
					<a href="/FurniCart/products.php">Products</a>
					<a href="/FurniCart/cart.php">Cart</a>
				</div>

				<div class="nav-right">
					<?php if (isset($_SESSION['user_id'])): ?>
						<div class="user-menu">
							<div class="user-dropdown">
								<div class="user-trigger">
									<img src="/FurniCart/assets/img/avatar_default.png" alt="Avatar" class="avatar">
									<span class="user-name">Hi, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
								</div>
								<div class="dropdown-content">
									<a href="/FurniCart/profile.php">My Profile</a>
									<a href="/FurniCart/logout.php">Log Out</a>
								</div>
							</div>
						</div>
					<?php else: ?>
						<a href="/FurniCart/login.php">Sign In</a>
					<?php endif; ?>
				</div>
			</nav>
		</div>
	</header>