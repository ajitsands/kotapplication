<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/Database.php';

$db = Database::getInstance()->getConnection();

echo "Running migration for cancellation audit fields...\n";

// 1. Add cancellation_reason to orders
try {
    $cols = $db->query("SHOW COLUMNS FROM orders LIKE 'cancellation_reason'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE orders ADD COLUMN cancellation_reason VARCHAR(255) NULL DEFAULT NULL AFTER token_number");
        echo "✓ Added column 'cancellation_reason' to orders\n";
    } else {
        echo "• Column 'cancellation_reason' already exists in orders\n";
    }
} catch (Exception $e) {
    echo "✗ Error adding cancellation_reason: " . $e->getMessage() . "\n";
}

// 2. Add cancelled_by to orders
try {
    $cols = $db->query("SHOW COLUMNS FROM orders LIKE 'cancelled_by'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE orders ADD COLUMN cancelled_by VARCHAR(100) NULL DEFAULT NULL AFTER cancellation_reason");
        echo "✓ Added column 'cancelled_by' to orders\n";
    } else {
        echo "• Column 'cancelled_by' already exists in orders\n";
    }
} catch (Exception $e) {
    echo "✗ Error adding cancelled_by: " . $e->getMessage() . "\n";
}

// 3. Add cancelled_at to orders
try {
    $cols = $db->query("SHOW COLUMNS FROM orders LIKE 'cancelled_at'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE orders ADD COLUMN cancelled_at TIMESTAMP NULL DEFAULT NULL AFTER cancelled_by");
        echo "✓ Added column 'cancelled_at' to orders\n";
    } else {
        echo "• Column 'cancelled_at' already exists in orders\n";
    }
} catch (Exception $e) {
    echo "✗ Error adding cancelled_at: " . $e->getMessage() . "\n";
}

// 4. Update bills status enum to include 'cancelled'
try {
    $db->exec("ALTER TABLE bills MODIFY status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending'");
    echo "✓ Updated bills status enum to include 'cancelled'\n";
} catch (Exception $e) {
    echo "✗ Error updating bills enum: " . $e->getMessage() . "\n";
}

echo "Migration finished.\n";
