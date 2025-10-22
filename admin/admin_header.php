<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Admin - FurniCart</title>
	<link rel="stylesheet" href="/FurniCart/assets/css/admin.css">
	<script src="/FurniCart/assets/js/admin.js"></script>
</head>

<body>
	<div class="admin-layout">
		<aside class="sidebar">
			<div class="logo">
				<a href="/FurniCart/admin/dashboard.php">
					<img src="/FurniCart/assets/img/logo.png" alt="FurniCart Logo">
				</a>
			</div>
			<nav class="admin-nav">
				<div class="admin-user">
					<img src="/FurniCart/assets/img/avatar_default.png" alt="Admin" class="admin-avatar">
					<span>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
				</div>
				<ul>
					<li><a href="/FurniCart/admin/dashboard.php">Dashboard</a></li>
					<li><a href="/FurniCart/admin/manage_users.php">Users</a></li>
					<li><a href="/FurniCart/admin/manage_products.php">Products</a></li>
					<li><a href="/FurniCart/admin/view_orders.php">Orders</a></li>
					<li><a href="/FurniCart/admin/manage_categories.php">Categories</a></li>
					<li><a href="/FurniCart/logout.php">Logout</a></li>
				</ul>
			</nav>
		</aside>