<?php
require_once '../includes/db.php';
require_once '../includes/admin_auth.php';
require_once '../classes/User.php';
require_once '../classes/Product.php';
require_once '../classes/Order.php';

header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

$userModel = new User($pdo);
$productModel = new Product($pdo);
$orderModel = new Order($pdo);

// Fetch stats
$totalUsers = $userModel->countUsers();
$totalProducts = $productModel->countProducts();
$totalOrders = $orderModel->countOrders();
$pendingOrders = $orderModel->countOrders('pending');
$paidOrders = $orderModel->countOrders('paid');
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Admin Dashboard</title>
	<link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
	<?php include '../includes/header.php'; ?>

	<h1>Welcome, Admin!</h1>

	<div class="stats">
		<div>Total Users: <?= $totalUsers ?></div>
		<div>Total Products: <?= $totalProducts ?></div>
		<div>Total Orders: <?= $totalOrders ?></div>
		<div>Pending Orders: <?= $pendingOrders ?></div>
		<div>Paid Orders: <?= $paidOrders ?></div>
	</div>

	<nav>
		<a href="manage_users.php">Manage Users</a> |
		<a href="manage_products.php">Manage Products</a> |
		<a href="../public/logout.php">Logout</a>
	</nav>

	<?php include '../includes/footer.php'; ?>
</body>

</html>