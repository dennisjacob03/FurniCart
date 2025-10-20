<?php
class User
{
	private $pdo;

	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	// Register new user
	public function register($name, $email, $phone, $password)
	{
		// Check for duplicate email
		$check = $this->pdo->prepare("SELECT user_id FROM users WHERE email = ?");
		$check->execute([$email]);
		if ($check->fetch())
			return false;

		$hashed = password_hash($password, PASSWORD_DEFAULT);
		$sql = "INSERT INTO users (name, email, phone, password) VALUES (?,?,?,?)";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute([$name, $email, $phone, $hashed]);
	}

	// Login user or admin
	public function login($email, $password)
	{
		$stmt = $this->pdo->prepare("SELECT user_id, name, email, password, role FROM users WHERE email = ?");
		$stmt->execute([$email]);
		$user = $stmt->fetch();

		if ($user && password_verify($password, $user['password'])) {
			$_SESSION['user_id'] = $user['user_id'];
			$_SESSION['name'] = $user['name'];
			$_SESSION['role'] = $user['role'];
			return $user;
		}
		return false;
	}

	// ✅ Get user details by ID (needed for profile page)
	public function getUserById($id)
	{
		$stmt = $this->pdo->prepare("SELECT * FROM users WHERE user_id = ?");
		$stmt->execute([$id]);
		return $stmt->fetch();
	}

	// Count all users
	public function countUsers()
	{
		$stmt = $this->pdo->query("SELECT COUNT(*) as total FROM users");
		return $stmt->fetch()['total'];
	}

	public function updateAddress($user_id, $address, $pincode, $city, $state)
	{
		$sql = "UPDATE users SET address = ?, pincode = ?, city = ?, state = ? WHERE user_id = ?";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute([$address, $pincode, $city, $state, $user_id]);
	}

	public function getAllUsers()
	{
		$stmt = $this->pdo->query("SELECT user_id, name, email, phone, role, status FROM users ORDER BY user_id DESC");
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function updateStatus($userId, $status)
	{
		$stmt = $this->pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
		return $stmt->execute([$status, $userId]);
	}
}
