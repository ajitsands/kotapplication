<?php
require 'config.php';
require 'core/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    $queries = [
        "CREATE TABLE IF NOT EXISTS `suppliers` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `contact_person` VARCHAR(100) DEFAULT NULL,
            `phone` VARCHAR(20) DEFAULT NULL,
            `email` VARCHAR(100) DEFAULT NULL,
            `address` TEXT DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;",

        "CREATE TABLE IF NOT EXISTS `inventory_items` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL UNIQUE,
            `unit` VARCHAR(50) NOT NULL DEFAULT 'Nos',
            `current_stock` DECIMAL(10,3) NOT NULL DEFAULT 0.000,
            `min_stock_level` DECIMAL(10,3) NOT NULL DEFAULT 0.000,
            `buying_price_per_unit` DECIMAL(10,3) NOT NULL DEFAULT 0.000,
            `selling_price` DECIMAL(10,3) NOT NULL DEFAULT 0.000,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;",

        "CREATE TABLE IF NOT EXISTS `product_recipes` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `product_id` INT NOT NULL,
            `inventory_item_id` INT NOT NULL,
            `quantity_required` DECIMAL(10,3) NOT NULL,
            FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB;",

        "CREATE TABLE IF NOT EXISTS `inventory_transactions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `inventory_item_id` INT NOT NULL,
            `transaction_type` ENUM('add_stock', 'consume_kot', 'adjustment', 'damage') NOT NULL,
            `quantity` DECIMAL(10,3) NOT NULL,
            `unit_price` DECIMAL(10,3) DEFAULT NULL,
            `supplier_id` INT DEFAULT NULL,
            `chef_id` INT DEFAULT NULL,
            `reference_id` VARCHAR(50) DEFAULT NULL,
            `notes` TEXT DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE SET NULL,
            FOREIGN KEY (`chef_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB;"
    ];

    foreach ($queries as $query) {
        $db->exec($query);
    }
    
    // Add missing column if it doesn't exist
    try {
        $db->exec("ALTER TABLE `inventory_items` ADD COLUMN `min_stock_level` DECIMAL(10,3) NOT NULL DEFAULT 0.000 AFTER `current_stock`");
    } catch (Exception $e) {
        // Ignore error if column already exists
    }

    echo "<h1>Migration completed successfully.</h1>";
    echo "<p>The V3 inventory tables have been created.</p>";
    echo "<a href='/admin/inventory'>Return to Inventory</a>";

} catch (Exception $e) {
    echo "Error during migration: " . $e->getMessage();
}
