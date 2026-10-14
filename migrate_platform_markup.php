<?php
require_once 'config.php';
require_once 'core/Database.php';

$db = Database::getInstance()->getConnection();

echo "Running migrations for Online Platform Markup...\n";

// 1. Add markup_percent to online_platforms
try {
    $cols = $db->query("SHOW COLUMNS FROM online_platforms LIKE 'markup_percent'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE online_platforms ADD COLUMN markup_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER name");
        echo "✓ Added column 'markup_percent' to online_platforms\n";
    } else {
        echo "• Column 'markup_percent' already exists in online_platforms\n";
    }
} catch (Exception $e) {
    echo "✗ Error adding markup_percent to online_platforms: " . $e->getMessage() . "\n";
}

// 2. Add price to kot_items
try {
    $cols = $db->query("SHOW COLUMNS FROM kot_items LIKE 'price'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE kot_items ADD COLUMN price DECIMAL(10,3) NULL DEFAULT NULL AFTER quantity");
        echo "✓ Added column 'price' to kot_items\n";
    } else {
        echo "• Column 'price' already exists in kot_items\n";
    }
} catch (Exception $e) {
    echo "✗ Error adding price to kot_items: " . $e->getMessage() . "\n";
}

echo "Migration finished.\n";
