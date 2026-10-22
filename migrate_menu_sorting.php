<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/Database.php';

$db = Database::getInstance()->getConnection();

echo "Running migrations for Menu Category & Product Display Sorting...\n";

// 1. Add sort_order to categories
try {
    $cols = $db->query("SHOW COLUMNS FROM categories LIKE 'sort_order'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE categories ADD COLUMN sort_order INT NOT NULL DEFAULT 0 AFTER name");
        echo "✓ Added column 'sort_order' to categories\n";

        // Assign initial sequential sort_order (1, 2, 3...) based on existing IDs
        $cats = $db->query("SELECT id FROM categories ORDER BY id ASC")->fetchAll(PDO::FETCH_COLUMN);
        $order = 1;
        $stmtUpdate = $db->prepare("UPDATE categories SET sort_order = ? WHERE id = ?");
        foreach ($cats as $catId) {
            $stmtUpdate->execute([$order, $catId]);
            $order++;
        }
        echo "✓ Initialized sequential sort_order (1.." . count($cats) . ") for existing categories\n";
    } else {
        echo "• Column 'sort_order' already exists in categories\n";
    }
} catch (Exception $e) {
    echo "✗ Error adding sort_order to categories: " . $e->getMessage() . "\n";
}

// 2. Add sort_order to products
try {
    $cols = $db->query("SHOW COLUMNS FROM products LIKE 'sort_order'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE products ADD COLUMN sort_order INT NOT NULL DEFAULT 0 AFTER price");
        echo "✓ Added column 'sort_order' to products\n";

        // Assign initial sequential sort_order per category
        $prods = $db->query("SELECT id, category_id FROM products ORDER BY category_id ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
        $currentCat = null;
        $prodOrder = 1;
        $stmtProdUpdate = $db->prepare("UPDATE products SET sort_order = ? WHERE id = ?");
        foreach ($prods as $p) {
            if ($p['category_id'] !== $currentCat) {
                $currentCat = $p['category_id'];
                $prodOrder = 1;
            }
            $stmtProdUpdate->execute([$prodOrder, $p['id']]);
            $prodOrder++;
        }
        echo "✓ Initialized sequential sort_order for existing products\n";
    } else {
        echo "• Column 'sort_order' already exists in products\n";
    }
} catch (Exception $e) {
    echo "✗ Error adding sort_order to products: " . $e->getMessage() . "\n";
}

echo "Migration finished.\n";
