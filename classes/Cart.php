<?php
class Cart
{
	private $pdo;

	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	public function addToCart($userId, $productId, $quantity = 1)
	{
		// Check if user is logged in
		if (!$userId) {
			return ['success' => false, 'message' => 'Please login to add items to cart'];
		}

		// Check if product exists and is in stock
		$product = $this->getProduct($productId);
		if (!$product) {
			return ['success' => false, 'message' => 'Product not found'];
		}

		if ($product['stock'] < $quantity) {
			return ['success' => false, 'message' => 'Insufficient stock available'];
		}

		// Check if item already exists in cart
		$existingItem = $this->getCartItem($userId, $productId);

		if ($existingItem) {
			// Update quantity
			$newQuantity = $existingItem['quantity'] + $quantity;
			if ($newQuantity > $product['stock']) {
				return ['success' => false, 'message' => 'Cannot add more items. Stock limit reached'];
			}

			$sql = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
			$stmt = $this->pdo->prepare($sql);
			$result = $stmt->execute([$newQuantity, $userId, $productId]);

			// Update stock: subtract the additional quantity
			if ($result) {
				$this->updateProductStock($productId, -$quantity);
			}
		} else {
			// Add new item
			$sql = "INSERT INTO cart (user_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())";
			$stmt = $this->pdo->prepare($sql);
			$result = $stmt->execute([$userId, $productId, $quantity]);

			// Update stock: subtract the quantity
			if ($result) {
				$this->updateProductStock($productId, -$quantity);
			}
		}

		if ($result) {
			return ['success' => true, 'message' => 'Item added to cart successfully'];
		} else {
			return ['success' => false, 'message' => 'Failed to add item to cart'];
		}
	}

	public function getCartItems($userId)
	{
		if (!$userId) {
			return [];
		}

		$sql = "SELECT c.*, p.name, p.price, p.image, p.stock, p.category 
                FROM cart c 
                JOIN products p ON c.product_id = p.product_id 
                WHERE c.user_id = ? 
                ORDER BY c.created_at DESC";

		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([$userId]);
		return $stmt->fetchAll();
	}

	public function updateCartItem($userId, $productId, $quantity)
	{
		if (!$userId) {
			return ['success' => false, 'message' => 'Please login'];
		}

		if ($quantity <= 0) {
			return $this->removeFromCart($userId, $productId);
		}

		// Get current cart item to calculate stock difference
		$currentItem = $this->getCartItem($userId, $productId);
		if (!$currentItem) {
			return ['success' => false, 'message' => 'Item not found in cart'];
		}

		// Check stock availability
		$product = $this->getProduct($productId);
		if (!$product) {
			return ['success' => false, 'message' => 'Product not found'];
		}

		// Calculate stock difference
		$quantityDifference = $quantity - $currentItem['quantity'];

		// Check if we have enough stock for the increase
		if ($quantityDifference > 0 && $product['stock'] < $quantityDifference) {
			return ['success' => false, 'message' => 'Insufficient stock available'];
		}

		$sql = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
		$stmt = $this->pdo->prepare($sql);
		$result = $stmt->execute([$quantity, $userId, $productId]);

		if ($result) {
			// Update stock: subtract the difference (positive for increase, negative for decrease)
			$this->updateProductStock($productId, -$quantityDifference);
			return ['success' => true, 'message' => 'Cart updated successfully'];
		} else {
			return ['success' => false, 'message' => 'Failed to update cart'];
		}
	}

	public function removeFromCart($userId, $productId)
	{
		if (!$userId) {
			return ['success' => false, 'message' => 'Please login'];
		}

		// Get current cart item to restore stock
		$currentItem = $this->getCartItem($userId, $productId);
		if (!$currentItem) {
			return ['success' => false, 'message' => 'Item not found in cart'];
		}

		$sql = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
		$stmt = $this->pdo->prepare($sql);
		$result = $stmt->execute([$userId, $productId]);

		if ($result) {
			// Restore stock: add back the quantity that was in cart
			$this->updateProductStock($productId, $currentItem['quantity']);
			return ['success' => true, 'message' => 'Item removed from cart'];
		} else {
			return ['success' => false, 'message' => 'Failed to remove item from cart'];
		}
	}

	public function clearCart($userId)
	{
		if (!$userId) {
			return ['success' => false, 'message' => 'Please login'];
		}

		// Get all cart items to restore stock
		$cartItems = $this->getCartItems($userId);

		$sql = "DELETE FROM cart WHERE user_id = ?";
		$stmt = $this->pdo->prepare($sql);
		$result = $stmt->execute([$userId]);

		if ($result) {
			// Restore stock for all items
			foreach ($cartItems as $item) {
				$this->updateProductStock($item['product_id'], $item['quantity']);
			}
			return ['success' => true, 'message' => 'Cart cleared successfully'];
		} else {
			return ['success' => false, 'message' => 'Failed to clear cart'];
		}
	}

	public function getCartItemCount($userId)
	{
		if (!$userId) {
			return 0;
		}

		$sql = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([$userId]);
		$result = $stmt->fetch();

		return $result['total'] ?? 0;
	}

	public function getCartTotal($userId)
	{
		if (!$userId) {
			return 0;
		}

		$sql = "SELECT SUM(c.quantity * p.price) as total 
                FROM cart c 
                JOIN products p ON c.product_id = p.product_id 
                WHERE c.user_id = ?";

		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([$userId]);
		$result = $stmt->fetch();

		return $result['total'] ?? 0;
	}

	private function getProduct($productId)
	{
		$sql = "SELECT * FROM products WHERE product_id = ?";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([$productId]);
		return $stmt->fetch();
	}

	private function getCartItem($userId, $productId)
	{
		$sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([$userId, $productId]);
		return $stmt->fetch();
	}

	private function updateProductStock($productId, $quantityChange)
	{
		$sql = "UPDATE products SET stock = stock + ? WHERE product_id = ?";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute([$quantityChange, $productId]);
	}
}
