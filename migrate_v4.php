<?php
require 'config.php';
require 'core/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    // 1. Create online_platforms table
    $db->exec("CREATE TABLE IF NOT EXISTS `online_platforms` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(100) NOT NULL,
      `status` enum('active','inactive') DEFAULT 'active',
      `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 2. Modify orders table order_type to include 'online'
    try {
        $db->exec("ALTER TABLE `orders` MODIFY `order_type` enum('dine_in','take_away','online') NOT NULL DEFAULT 'dine_in';");
    } catch (Exception $e) {}

    // 3. Add platform_id and platform_order_number to orders
    try {
        $db->exec("ALTER TABLE `orders` ADD COLUMN `platform_id` int(11) DEFAULT NULL;");
    } catch (Exception $e) {}

    try {
        $db->exec("ALTER TABLE `orders` ADD COLUMN `platform_order_number` varchar(100) DEFAULT NULL;");
    } catch (Exception $e) {}

    // 4. Default dummy data for platforms if empty
    $stmt = $db->query("SELECT COUNT(*) FROM online_platforms");
    if ($stmt->fetchColumn() == 0) {
        $db->exec("INSERT INTO `online_platforms` (`name`) VALUES ('UberEats'), ('Talabat'), ('Zomato')");
    }

    echo "<h1>Migration V4 completed successfully.</h1>";
    echo "<p>The database is ready for Online Orders.</p>";
    echo "<a href='/counter'>Return to Counter</a>";

} catch (Exception $e) {
    echo "Error during migration: " . $e->getMessage();
}
