<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
	header("Location: /FurniCart/login.php");
	exit;
}

// Only check for admin role if accessing admin pages
if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false && $_SESSION['role'] !== 'admin') {
	header("Location: /FurniCart/index.php");
	exit;
}
?>