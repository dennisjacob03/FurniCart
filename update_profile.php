<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/classes/User.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'errors' => []];

try {
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$address = trim($_POST['address'] ?? '');
		$pincode = trim($_POST['pincode'] ?? '');
		$city = trim($_POST['city'] ?? '');
		$state = trim($_POST['state'] ?? '');

		if (empty($address)) {
			$response['errors']['address'] = 'Address is required';
		}
		if (!preg_match('/^[0-9]{6}$/', $pincode)) {
			$response['errors']['pincode'] = 'Invalid pincode format';
		}

		if (empty($response['errors'])) {
			$userModel = new User($pdo);
			if ($userModel->updateAddress($_SESSION['user_id'], $address, $pincode, $city, $state)) {
				$response['success'] = true;
				$response['message'] = 'Profile updated successfully!';
			} else {
				$response['message'] = 'Failed to update profile';
			}
		} else {
			$response['message'] = 'Please fix the errors below';
		}
	}
} catch (Exception $e) {
	$response['message'] = 'An error occurred: ' . $e->getMessage();
}

echo json_encode($response);
?>