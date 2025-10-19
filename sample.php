<?php
session_start();
require_once 'classes/User.php';
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = $_POST['email'];
	$password = $_POST['password'];

	$userModel = new User($pdo);
	$user = $userModel->login($email, $password);

	if ($user) {
		// Store user info in session
		$_SESSION['user_id'] = $user['user_id'];
		$_SESSION['role'] = $user['role'];

		// Role-based redirect
		if ($user['role'] === 'admin') {
			header("Location: admin/dashboard.php");
		} else {
			header("Location: index.php");
		}
		exit;
	} else {
		$error = "Invalid email or password.";
	}
}
?>