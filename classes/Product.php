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

	public function addProduct($name, $description, $price, $category, $image, $stock)
	{
		$sql = "INSERT INTO products (name, description, price, category, image, stock) 
				VALUES (?, ?, ?, ?, ?, ?)";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute([$name, $description, $price, $category, $image, $stock]);
	}

	public function getProductsByCategory($category)
	{
		$stmt = $this->pdo->prepare("SELECT * FROM products WHERE category = ? ORDER BY created_at DESC");
		$stmt->execute([$category]);
		return $stmt->fetchAll();
	}

	public function getCategories()
	{
		$stmt = $this->pdo->query("SELECT DISTINCT category FROM products ORDER BY category");
		return $stmt->fetchAll(PDO::FETCH_COLUMN);
	}

	public function getImageUrl($image)
	{
		if (filter_var($image, FILTER_VALIDATE_URL)) {
			return $image;
		}
		return "/FurniCart/uploads/products/" . $image;
	}
}
