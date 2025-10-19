<?php
class Address
{
	private $pdo;

	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	// Add new address
	public function addAddress($user_id, $address, $city, $state, $country, $pincode)
	{
		$sql = "INSERT INTO addresses (user_id, address_line, city, state, country, pincode)
                VALUES (?,?,?,?,?,?)";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute([$user_id, $address, $city, $state, $country, $pincode]);
	}

	// Get all addresses for a user
	public function getUserAddresses($user_id)
	{
		$stmt = $this->pdo->prepare("SELECT * FROM addresses WHERE user_id = ?");
		$stmt->execute([$user_id]);
		return $stmt->fetchAll();
	}

	// Get one address
	public function getAddressById($address_id)
	{
		$stmt = $this->pdo->prepare("SELECT * FROM addresses WHERE address_id = ?");
		$stmt->execute([$address_id]);
		return $stmt->fetch();
	}
}
