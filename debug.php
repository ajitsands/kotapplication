<?php
require_once 'config.php';
require_once 'core/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "Connected to DB successfully.\n";
    
    $stmt = $db->query("SELECT * FROM suppliers ORDER BY id DESC");
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "SUCCESS! Fetched " . count($suppliers) . " suppliers.\n";
    print_r($suppliers);
} catch (Exception $e) {
    echo "ERROR:\n" . $e->getMessage() . "\n";
}
