<?php
require 'config.php';
require 'core/Database.php';

$db = Database::getInstance()->getConnection();
$kotId = 34;

$stmtCheck = $db->prepare("SELECT status FROM kots WHERE id = ?");
$stmtCheck->execute([$kotId]);
$status = $stmtCheck->fetchColumn();

echo "KOT Status: " . var_export($status, true) . "\n";

$stmtOrderType = $db->prepare("SELECT o.order_type FROM kots k JOIN orders o ON k.order_id = o.id WHERE k.id = ?");
$stmtOrderType->execute([$kotId]);
$orderType = $stmtOrderType->fetchColumn();
echo "Order Type: " . var_export($orderType, true) . "\n";
