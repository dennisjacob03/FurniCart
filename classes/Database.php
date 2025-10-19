<?php
class Database
{
	private $pdo;
	private static $instance = null;

	private function __construct($config)
	{
		try {
			$dsn = "mysql:host={$config['host']};dbname={$config['db']};charset={$config['charset']}";
			$options = [
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES => false,
			];
			$this->pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
		} catch (PDOException $e) {
			throw new Exception("Connection failed: " . $e->getMessage());
		}
	}

	public static function getInstance($config = null)
	{
		if (self::$instance === null) {
			if ($config === null)
				throw new Exception("Database config required on first connect.");
			self::$instance = new Database($config);
		}
		return self::$instance;
	}

	public function getConnection()
	{
		return $this->pdo;
	}
}
