<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../classes/Address.php';
require_once __DIR__ . '/../classes/User.php';

$addressModel = new Address($pdo);
$userModel = new User($pdo);
$user = $userModel->getUserById($_SESSION['user_id']);
$msg = "";

// Add new address
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$address = trim($_POST['address']);
	$city = trim($_POST['city']);
	$state = trim($_POST['state']);
	$country = trim($_POST['country']);
	$pincode = trim($_POST['pincode']);

	if ($addressModel->addAddress($user['user_id'], $address, $city, $state, $country, $pincode)) {
		$msg = "✅ Address added successfully!";
	} else {
		$msg = "❌ Failed to add address.";
	}
}

$addresses = $addressModel->getUserAddresses($user['user_id']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Profile - FurniCart</title>
	<link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
	<?php include '../includes/header.php'; ?>

	<h2>Welcome, <?php echo htmlspecialchars($user['name']); ?> 👋</h2>
	<p><b>Email:</b> <?php echo htmlspecialchars($user['email']); ?></p>
	<p><b>Phone:</b> <?php echo htmlspecialchars($user['phone']); ?></p>

	<h3>Add New Address</h3>
	<form method="POST">
		<input type="text" name="address" placeholder="Address Line" required><br>
		<input type="text" name="city" placeholder="City" required><br>
		<input type="text" name="state" placeholder="State" required><br>
		<input type="text" name="country" placeholder="Country" required><br>
		<input type="text" name="pincode" placeholder="Pincode" required pattern="[0-9]{6}"><br>
		<button type="submit">Save Address</button>
	</form>

	<p style="color:green;"><?php echo $msg; ?></p>

	<h3>Your Saved Addresses</h3>
	<ul>
		<?php foreach ($addresses as $a): ?>
			<li>
				<?php echo htmlspecialchars($a['address_line']); ?>,
				<?php echo htmlspecialchars($a['city']); ?>,
				<?php echo htmlspecialchars($a['state']); ?>,
				<?php echo htmlspecialchars($a['country']); ?> -
				<?php echo htmlspecialchars($a['pincode']); ?>
			</li>
		<?php endforeach; ?>
	</ul>

	<?php include '../includes/footer.php'; ?>
</body>

</html>