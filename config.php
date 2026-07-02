<?php
// Configuration File
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', 'S@nds1@b');
define('DB_NAME', 'kot_billing');

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Global settings helper
function getSettings() {
    static $settings = null;
    if ($settings === null) {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            $stmt = $pdo->query("SELECT * FROM settings ORDER BY id DESC LIMIT 1");
            $settings = $stmt->fetch();
        } catch (PDOException $e) {
            $settings = [
                'restaurant_name' => 'Gourmet Express',
                'currency_code' => 'BHD',
                'time_zone' => 'Asia/Bahrain',
                'tax_type' => 'VAT',
                'vat_percent' => 10.00,
                'cgst_percent' => 2.50,
                'sgst_percent' => 2.50,
                'printer_size' => 80,
                'logo_path' => null
            ];
        }
    }
    return $settings;
}

// Set global Timezone based on DB settings
$settings = getSettings();
date_default_timezone_set($settings['time_zone'] ?? 'Asia/Bahrain');
