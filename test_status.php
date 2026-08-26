<?php
require 'config.php';
require 'core/Database.php';

$db = Database::getInstance()->getConnection();
$kots = $db->query("SELECT id, order_id, status FROM kots ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
print_r($kots);

$kotItems = $db->query("SELECT id, kot_id, status FROM kot_items ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
print_r($kotItems);
