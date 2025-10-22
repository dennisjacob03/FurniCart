<?php
class Category
{
	private $pdo;

	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	public function addCategory($name, $image)
	{
		$sql = "INSERT INTO categories (name, image) VALUES (?, ?)";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute([$name, $image]);
	}

	public function getAllCategories()
	{
		$stmt = $this->pdo->query("SELECT * FROM categories ORDER BY name");
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	public function countCategories()
	{
		$stmt = $this->pdo->query("SELECT COUNT(*) as total FROM categories");
		return $stmt->fetch()['total'];
	}
}