<?php
require 'config.php';
require 'core/Database.php';

$db = Database::getInstance()->getConnection();

$prodId = $db->query("SELECT id FROM products LIMIT 1")->fetchColumn();

$order = $db->query("INSERT INTO orders (order_type, customer_name, status) VALUES ('take_away', 'Test', 'active')");
$orderId = $db->lastInsertId();

$kotIdUnique = uniqid('KOT-');
$kot = $db->query("INSERT INTO kots (order_id, kot_number, status) VALUES ($orderId, '$kotIdUnique', 'pending')");
$kotId = $db->lastInsertId();

$item = $db->query("INSERT INTO kot_items (kot_id, product_id, quantity, status) VALUES ($kotId, $prodId, 1, 'pending')");
$itemId = $db->lastInsertId();

echo "Created Order $orderId, KOT $kotId, Item $itemId\n";

require 'core/Model.php';
require 'models/Kot.php';

$kotModel = new Kot();
$res1 = $kotModel->deleteKotItem($itemId);
echo "deleteKotItem result: " . var_export($res1, true) . "\n";
