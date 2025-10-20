<?php
class Address
{
	private $pdo;

	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	public function addAddress($user_id, $address, $city, $state, $pincode)
	{
		$sql = "INSERT INTO addresses (user_id, address, city, state, pincode) 
                VALUES (?, ?, ?, ?, ?)";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute([$user_id, $address, $city, $state, $pincode]);
	}

	public function getUserAddresses($user_id)
	{
		$sql = "SELECT * FROM addresses WHERE user_id = ? ORDER BY address_id DESC";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([$user_id]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getAddressById($address_id)
	{
		$stmt = $this->pdo->prepare("SELECT * FROM addresses WHERE address_id = ?");
		$stmt->execute([$address_id]);
		return $stmt->fetch();
	}
}
