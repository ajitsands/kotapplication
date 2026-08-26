<?php
require 'config.php';
require 'core/Database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query('SHOW CREATE TABLE kot_items');
print_r($stmt->fetch());
