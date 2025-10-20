<?php
// Session should be started in the main file before including this header
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

		.cart-link {
			position: relative;
		}

		.cart-count {
			position: absolute;
			top: -8px;
			right: -8px;
			background: #dc3545;
			color: white;
			border-radius: 50%;
			width: 20px;
			height: 20px;
			font-size: 12px;
			font-weight: bold;
			display: flex;
			align-items: center;
			justify-content: center;
			line-height: 1;
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
					<a href="/FurniCart/product.php">Products</a>
					<?php if (isset($_SESSION['user_id'])): ?>
						<a href="/FurniCart/my_orders.php">My Orders</a>
					<?php endif; ?>
					<a href="/FurniCart/cart.php" class="cart-link">
						Cart
						<?php if (isset($_SESSION['user_id'])): ?>
							<?php
							require_once __DIR__ . '/../classes/Cart.php';
							require_once __DIR__ . '/db.php';
							$cartModel = new Cart($pdo);
							$cartCount = $cartModel->getCartItemCount($_SESSION['user_id']);
							?>
							<?php if ($cartCount > 0): ?>
								<span class="cart-count"><?php echo $cartCount; ?></span>
							<?php endif; ?>
						<?php endif; ?>
					</a>
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
									<a href="/FurniCart/my_orders.php">My Orders</a>
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