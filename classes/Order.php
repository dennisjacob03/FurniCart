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
}
?>