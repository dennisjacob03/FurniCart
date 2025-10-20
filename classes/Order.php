<?php
class Order
{
	private $pdo;
	public function __construct($pdo)
	{
		$this->pdo = $pdo;
	}

	public function countOrders($status = null)
	{
		if ($status) {
			$stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE payment_status = ?");
			$stmt->execute([$status]);
		} else {
			$stmt = $this->pdo->query("SELECT COUNT(*) as total FROM orders");
		}
		return $stmt->fetch()['total'];
	}

	/**
	 * Create a new order
	 */
	public function createOrder($userId, $totalAmount, $razorpayOrderId)
	{
		try {
			$sql = "INSERT INTO orders (user_id, total_amount, razorpay_order_id, payment_status) 
			        VALUES (?, ?, ?, 'pending')";
			$stmt = $this->pdo->prepare($sql);
			$stmt->execute([$userId, $totalAmount, $razorpayOrderId]);
			return $this->pdo->lastInsertId();
		} catch (PDOException $e) {
			error_log("Order creation failed: " . $e->getMessage());
			return false;
		}
	}

	/**
	 * Add items to an order
	 */
	public function addOrderItems($orderId, $cartItems)
	{
		try {
			$sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
			$stmt = $this->pdo->prepare($sql);
			
			foreach ($cartItems as $item) {
				$stmt->execute([
					$orderId,
					$item['product_id'],
					$item['quantity'],
					$item['price']
				]);
			}
			return true;
		} catch (PDOException $e) {
			error_log("Order items creation failed: " . $e->getMessage());
			return false;
		}
	}

	/**
	 * Update order payment status
	 */
	public function updatePaymentStatus($orderId, $paymentId, $signature, $status, $paymentMethod = null)
	{
		try {
			$sql = "UPDATE orders 
			        SET razorpay_payment_id = ?, 
			            razorpay_signature = ?, 
			            payment_status = ?,
			            payment_method = ?
			        WHERE order_id = ?";
			$stmt = $this->pdo->prepare($sql);
			return $stmt->execute([$paymentId, $signature, $status, $paymentMethod, $orderId]);
		} catch (PDOException $e) {
			error_log("Payment status update failed: " . $e->getMessage());
			return false;
		}
	}

	/**
	 * Get order by Razorpay order ID
	 */
	public function getOrderByRazorpayId($razorpayOrderId)
	{
		try {
			$stmt = $this->pdo->prepare("SELECT * FROM orders WHERE razorpay_order_id = ?");
			$stmt->execute([$razorpayOrderId]);
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			error_log("Get order failed: " . $e->getMessage());
			return false;
		}
	}

	/**
	 * Get order details with items
	 */
	public function getOrderDetails($orderId)
	{
		try {
			// Get order info
			$orderStmt = $this->pdo->prepare("
				SELECT o.*, u.name, u.email, u.phone, u.address, u.city, u.state, u.pincode
				FROM orders o
				JOIN users u ON o.user_id = u.user_id
				WHERE o.order_id = ?
			");
			$orderStmt->execute([$orderId]);
			$order = $orderStmt->fetch(PDO::FETCH_ASSOC);
			
			if (!$order) {
				return false;
			}
			
			// Get order items
			$itemsStmt = $this->pdo->prepare("
				SELECT oi.*, p.name, p.image
				FROM order_items oi
				JOIN products p ON oi.product_id = p.product_id
				WHERE oi.order_id = ?
			");
			$itemsStmt->execute([$orderId]);
			$order['items'] = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
			
			return $order;
		} catch (PDOException $e) {
			error_log("Get order details failed: " . $e->getMessage());
			return false;
		}
	}

	/**
	 * Get user orders
	 */
	public function getUserOrders($userId)
	{
		try {
			$stmt = $this->pdo->prepare("
				SELECT o.*, COUNT(oi.order_item_id) as item_count
				FROM orders o
				LEFT JOIN order_items oi ON o.order_id = oi.order_id
				WHERE o.user_id = ?
				GROUP BY o.order_id
				ORDER BY o.created_at DESC
			");
			$stmt->execute([$userId]);
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			error_log("Get user orders failed: " . $e->getMessage());
			return [];
		}
	}
}
?>