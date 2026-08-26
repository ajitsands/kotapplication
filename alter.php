<?php
require 'config.php';
require 'core/Database.php';
$db = Database::getInstance()->getConnection();
try {
    $db->exec("ALTER TABLE inventory_items ADD COLUMN min_stock_level DECIMAL(10,3) NOT NULL DEFAULT 0.000 AFTER current_stock");
    echo "Success\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
