<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Add custom_units to settings table if it doesn't exist
    $sql1 = "ALTER TABLE `settings` ADD COLUMN `custom_units` VARCHAR(255) DEFAULT 'Nos, Box, Packet, Gram, KG, Litre, ML' AFTER `time_zone`";
    try {
        $db->exec($sql1);
        echo "Successfully added custom_units column to settings.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false || strpos($e->getMessage(), '42S21') !== false) {
            echo "Column custom_units already exists.\n";
        } else {
            throw $e;
        }
    }

    // 2. Modify inventory_items unit column from ENUM to VARCHAR(50)
    $sql2 = "ALTER TABLE `inventory_items` MODIFY COLUMN `unit` VARCHAR(50) NOT NULL DEFAULT 'Nos'";
    $db->exec($sql2);
    echo "Successfully modified unit column in inventory_items to VARCHAR(50).\n";

} catch (Exception $e) {
    echo "Error executing migration: " . $e->getMessage() . "\n";
}
