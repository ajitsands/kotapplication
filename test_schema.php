<?php
require 'config.php';
require 'core/Database.php';
$stmt = Database::getInstance()->getConnection()->query("SHOW COLUMNS FROM orders WHERE Field = 'status'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
