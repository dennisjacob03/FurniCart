<?php
require_once '../includes/db.php';
require_once '../includes/admin_auth.php';
require_once '../classes/User.php';

$userModel = new User($pdo);
$users = $userModel->getAllUsers();

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['action'])) {
	$userId = $_POST['user_id'];
	$status = $_POST['action'] === 'activate' ? 'active' : 'inactive';

	if ($userModel->updateStatus($userId, $status)) {
		header("Location: manage_users.php?msg=Status updated successfully");
		exit;
	}
}

include 'admin_header.php';
?>

<main class="admin-main">
	<div class="admin-header">
		<h1>Manage Users</h1>
		<p>View and manage user accounts</p>
	</div>

	<?php if (isset($_GET['msg'])): ?>
		<div class="alert alert-success">
			<?php echo htmlspecialchars($_GET['msg']); ?>
		</div>
	<?php endif; ?>

	<div class="table-container">
		<table class="admin-table">
			<thead>
				<tr>
					<th>SI No</th>
					<th>Name</th>
					<th>Email</th>
					<th>Phone</th>
					<th>Role</th>
					<th>Status</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($users as $index => $user): ?>
					<tr>
						<td><?php echo $index + 1; ?></td>
						<td><?php echo htmlspecialchars($user['name']); ?></td>
						<td><?php echo htmlspecialchars($user['email']); ?></td>
						<td><?php echo htmlspecialchars($user['phone']); ?></td>
						<td>
							<span class="badge <?php echo $user['role'] === 'admin' ? 'badge-admin' : 'badge-user'; ?>">
								<?php echo ucfirst(htmlspecialchars($user['role'])); ?>
							</span>
						</td>
						<td>
							<span class="badge <?php echo $user['status'] === 'active' ? 'badge-active' : 'badge-inactive'; ?>">
								<?php echo ucfirst(htmlspecialchars($user['status'])); ?>
							</span>
						</td>
						<td class="actions">
							<?php if ($user['role'] === 'admin'): ?>
								<span class="not-allowed">Not Allowed</span>
							<?php else: ?>
								<form method="POST" class="status-form" onsubmit="return confirm('Are you sure?');">
									<input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
									<?php if ($user['status'] === 'active'): ?>
										<button type="submit" name="action" value="deactivate" class="btn-danger">
											Deactivate
										</button>
									<?php else: ?>
										<button type="submit" name="action" value="activate" class="btn-success">
											Activate
										</button>
									<?php endif; ?>
								</form>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</main>
</div>
</body>

</html>