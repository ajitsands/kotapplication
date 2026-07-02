<?php
/**
 * setup.php - Automated Database Installer
 * 
 * Access this file on your server to install/re-install the database:
 * http://kot.sandslab.com/setup.php?key=install123
 */

require_once 'config.php';

// Secure access key to prevent unauthorized database execution
$accessKey = 'install123';
if (($_GET['key'] ?? '') !== $accessKey) {
    header('HTTP/1.1 403 Forbidden');
    echo "<h1>Access Denied</h1><p>Please provide the correct setup key in the URL: <code>setup.php?key=install123</code></p>";
    exit;
}

try {
    $dbName = DB_NAME;
    
    // Connect to MySQL server (without specifying DB first, to handle database auto-creation if local)
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbName`");

    echo "<h2 style='font-family:sans-serif;'>Connected to database <code>$dbName</code> successfully.</h2>";

    // Auto-migrate: Check if 'is_active' column is missing in 'users' table
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
    if ($tableCheck) {
        $columnCheck = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'is_active'")->fetch();
        if (!$columnCheck) {
            $pdo->exec("ALTER TABLE `users` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `role`");
            echo "<p style='color:green; font-family:sans-serif; font-weight:600;'>✓ Migrated: Added missing 'is_active' column to existing 'users' table.</p>";
        }
    }

    // Auto-migrate: Check and add missing columns/foreign keys to existing 'bills' table
    $billsTableCheck = $pdo->query("SHOW TABLES LIKE 'bills'")->fetch();
    if ($billsTableCheck) {
        // Check discount_percent
        $colCheck = $pdo->query("SHOW COLUMNS FROM `bills` LIKE 'discount_percent'")->fetch();
        if (!$colCheck) {
            $pdo->exec("ALTER TABLE `bills` ADD COLUMN `discount_percent` DECIMAL(5,2) DEFAULT 0.00 AFTER `status`");
            echo "<p style='color:green; font-family:sans-serif;'>✓ Migrated: Added 'discount_percent' to 'bills' table.</p>";
        }

        // Check discount_amount
        $colCheck = $pdo->query("SHOW COLUMNS FROM `bills` LIKE 'discount_amount'")->fetch();
        if (!$colCheck) {
            $pdo->exec("ALTER TABLE `bills` ADD COLUMN `discount_amount` DECIMAL(10,3) DEFAULT 0.000 AFTER `discount_percent`");
            echo "<p style='color:green; font-family:sans-serif;'>✓ Migrated: Added 'discount_amount' to 'bills' table.</p>";
        }

        // Check cashier_id
        $colCheck = $pdo->query("SHOW COLUMNS FROM `bills` LIKE 'cashier_id'")->fetch();
        if (!$colCheck) {
            // First add column
            $pdo->exec("ALTER TABLE `bills` ADD COLUMN `cashier_id` INT DEFAULT NULL AFTER `discount_amount`");
            // Add foreign key constraint
            try {
                $pdo->exec("ALTER TABLE `bills` ADD CONSTRAINT `fk_bills_cashier` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`) ON DELETE SET NULL");
            } catch (Exception $ex) {
                // Ignore constraint if it already exists or fails
            }
            echo "<p style='color:green; font-family:sans-serif;'>✓ Migrated: Added 'cashier_id' column and constraint to 'bills' table.</p>";
        }

        // Check customer_id
        $colCheck = $pdo->query("SHOW COLUMNS FROM `bills` LIKE 'customer_id'")->fetch();
        if (!$colCheck) {
            // Make sure customers table exists first by parsing/executing its creation early if needed
            $pdo->exec("CREATE TABLE IF NOT EXISTS `customers` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `mobile` VARCHAR(20) NOT NULL UNIQUE,
                `name` VARCHAR(100) NOT NULL,
                `gender` VARCHAR(20) DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB");
            
            // Add column
            $pdo->exec("ALTER TABLE `bills` ADD COLUMN `customer_id` INT DEFAULT NULL AFTER `cashier_id`");
            // Add foreign key constraint
            try {
                $pdo->exec("ALTER TABLE `bills` ADD CONSTRAINT `bills_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL");
            } catch (Exception $ex) {
                // Ignore constraint if it already exists or fails
            }
            echo "<p style='color:green; font-family:sans-serif;'>✓ Migrated: Added 'customer_id' column and constraint to 'bills' table.</p>";
        }
    }

    // Read schema.sql
    if (!file_exists('schema.sql')) {
        throw new Exception("schema.sql file not found at the root of the project.");
    }
    
    $sql = file_get_contents('schema.sql');

    // Remove database creation and "USE" statements from schema so we don't trigger permission issues on shared hosting
    $sql = preg_replace('/CREATE DATABASE IF NOT EXISTS.*?;/is', '', $sql);
    $sql = preg_replace('/USE `?.*?`?;/is', '', $sql);

    // Split query statements by semicolon followed by newline
    $queries = preg_split('/;(?:\s*[\r\n]+)/', $sql);

    $successCount = 0;
    $errorCount = 0;

    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) continue;

        try {
            $pdo->exec($query);
            $successCount++;
        } catch (PDOException $e) {
            echo "<p style='color:red; font-family:monospace;'>Error executing query: " . htmlspecialchars(substr($query, 0, 100)) . "... <br><b>Reason:</b> " . $e->getMessage() . "</p>";
            $errorCount++;
        }
    }

    echo "<hr>";
    echo "<h3 style='font-family:sans-serif;'>Database Sync Result:</h3>";
    echo "<p style='font-family:sans-serif;'>Successfully executed queries: <b>$successCount</b></p>";
    if ($errorCount > 0) {
        echo "<p style='color:red; font-family:sans-serif;'>Queries with errors: <b>$errorCount</b></p>";
    } else {
        echo "<p style='color:green; font-family:sans-serif; font-size:16px;'><b>✓ All database tables, users, settings, and indices have been created successfully!</b></p>";
    }

} catch (Exception $e) {
    echo "<h2 style='font-family:sans-serif; color:red;'>Installation Failed</h2>";
    echo "<p style='color:red; font-family:monospace;'>" . $e->getMessage() . "</p>";
}
