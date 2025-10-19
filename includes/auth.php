<?php
session_start();
if (!isset($_SESSION['user_id'])) {
	header("Location: login.php");
	exit;
}

// Optional: restrict admin-only page
if ($_SESSION['role'] !== 'admin') {
	header("Location: index.php");
	exit;
}

?>