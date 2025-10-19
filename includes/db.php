<?php
require_once __DIR__ . '/../classes/Database.php';
$config = require __DIR__ . '/config.php';

$dbInstance = Database::getInstance($config['db']);
$pdo = $dbInstance->getConnection();
