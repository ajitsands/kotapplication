<?php
// Configuration File
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Detect environment (Server vs Local)
$dbHost = '127.0.0.1';
$dbPort = '3306';
$dbUser = 'root';
$dbPass = 'S@nds1@b';
$dbName = 'kot_billing';

$scriptDir = dirname(__FILE__);
$httpHost = $_SERVER['HTTP_HOST'] ?? '';

// Check b1.restoflow.us and restoflow domains/paths
if (strpos($httpHost, 'b1.restoflow.us') !== false || strpos($scriptDir, 'b1burger') !== false || strpos($scriptDir, 'restoflow') !== false || strpos($httpHost, 'restoflow') !== false) {
    $dbHost = 'localhost';
    $dbUser = 'restoflow_b1burger_user';
    $dbPass = 'S@nds1@b';
    $dbName = 'restoflow_b1burger_db';
} elseif (strpos($scriptDir, '/home/sandsl23/') !== false || strpos($httpHost, 'sandslab.com') !== false) {
    $dbHost = 'localhost';
    $dbUser = 'sandsl23_kot_user';
    $dbPass = 'S@nds1@b';
    $dbName = 'sandsl23_kot_db';
} elseif (php_sapi_name() === 'cli' && (strpos($scriptDir, 'kotapplication') === false || strpos($scriptDir, 'sandslab') !== false)) {
    $dbHost = 'localhost';
    $dbUser = 'sandsl23_kot_user';
    $dbPass = 'S@nds1@b';
    $dbName = 'sandsl23_kot_db';
}

define('DB_HOST', $dbHost);
define('DB_PORT', $dbPort);
define('DB_USER', $dbUser);
define('DB_PASS', $dbPass);
define('DB_NAME', $dbName);

// Configure local session directory to bypass broken cPanel session save paths
if (session_status() === PHP_SESSION_NONE) {
    $sessionDir = dirname(__FILE__) . '/sessions';
    if (!file_exists($sessionDir)) {
        mkdir($sessionDir, 0777, true);
        // Secure sessions folder with .htaccess
        file_put_contents($sessionDir . '/.htaccess', "Deny from all\n");
    }
    session_save_path($sessionDir);
    session_start();
}

// Global settings helper
function getSettings()
{
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
