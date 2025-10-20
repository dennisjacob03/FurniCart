<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
	header("Location: /FurniCart/login.php");
	exit;
}
?>