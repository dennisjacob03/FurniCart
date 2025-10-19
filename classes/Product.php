<?php
class Product
{
	private $pdo;
	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	public function countProducts()
	{
		$stmt = $this->pdo->query("SELECT COUNT(*) as total FROM products");
		return $stmt->fetch()['total'];
	}
	
	public function getAllProducts()
	{
		$stmt = $this->pdo->query("SELECT * FROM products ORDER BY created_at DESC");
		return $stmt->fetchAll();
	}

	public function getProductById($id)
	{
		$stmt = $this->pdo->prepare("SELECT * FROM products WHERE product_id = ?");
		$stmt->execute([$id]);
		return $stmt->fetch();
	}

	
}
