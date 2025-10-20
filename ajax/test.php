<?php
// Start session first, before any output
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Set content type to JSON
header('Content-Type: application/json');

echo json_encode([
	'success' => true,
	'message' => 'Test endpoint working',
	'session_user_id' => $_SESSION['user_id'] ?? 'not set',
	'request_method' => $_SERVER['REQUEST_METHOD']
]);
?>