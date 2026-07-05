<?php
// Support local PHP built-in web server static files bypass
if (php_sapi_name() === 'cli-server') {
    $filePath = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($filePath)) {
        return false;
    }
}

// Bootstrap the application
require_once 'config.php';
require_once 'core/Database.php';
require_once 'core/Router.php';
require_once 'core/Controller.php';
require_once 'core/Model.php';

// Create directories for uploads if not exists
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// Instantiate router
$router = new Router();

// Views / Dashboard Routes
$router->add('GET', '/', 'HomeController@index');
$router->add('GET', '/login', 'HomeController@loginView');
$router->add('POST', '/login', 'HomeController@loginSubmit');
$router->add('GET', '/logout', 'HomeController@logout');

// Admin Panel Routes
$router->add('GET', '/admin', 'AdminController@index');
$router->add('GET', '/admin/products/list', 'AdminController@productsListJson');
$router->add('POST', '/admin/settings', 'AdminController@saveSettings');
$router->add('POST', '/admin/categories', 'AdminController@addCategory');
$router->add('POST', '/admin/categories/delete/:id', 'AdminController@deleteCategory');
$router->add('POST', '/admin/products', 'AdminController@saveProduct');
$router->add('POST', '/admin/products/delete/:id', 'AdminController@deleteProduct');
$router->add('POST', '/admin/tables', 'AdminController@addTable');
$router->add('POST', '/admin/tables/delete/:id', 'AdminController@deleteTable');
$router->add('POST', '/admin/users', 'AdminController@addUser');
$router->add('POST', '/admin/users/delete/:id', 'AdminController@deleteUser');
$router->add('POST', '/admin/users/status/:id', 'AdminController@toggleUserStatus');
$router->add('POST', '/admin/users/reset-password/:id', 'AdminController@resetUserPassword');
$router->add('POST', '/user/change-password', 'HomeController@changePassword');
$router->add('GET', '/admin/tax-report/json', 'AdminController@taxReportJson');
$router->add('GET', '/admin/analytics/json', 'AdminController@analyticsJson');
$router->add('GET', '/admin/waiter-performance/json', 'AdminController@waiterPerformanceJson');

// KOT Operations Routes
$router->add('GET', '/kot', 'KotController@index');
$router->add('GET', '/kot/items', 'KotController@itemsList');
$router->add('GET', '/kot/completed', 'KotController@completedList');
$router->add('POST', '/kot/items/ready/:id', 'KotController@markItemReady');
$router->add('POST', '/kot/ready/:id', 'KotController@markKotReady');
$router->add('GET', '/kot/print/:id', 'KotController@printKot');
$router->add('POST', '/kot/items/delete/:id', 'KotController@deleteItem');
$router->add('POST', '/kot/delete/:id', 'KotController@deleteKot');

// Counter & Billing Routes
$router->add('GET', '/counter', 'CounterController@index');
$router->add('GET', '/counter/bills', 'CounterController@billsList');
$router->add('GET', '/counter/summary', 'CounterController@summary');
$router->add('GET', '/counter/bill/:id', 'CounterController@billDetails');
$router->add('GET', '/counter/customer/lookup', 'CounterController@lookupCustomer');
$router->add('GET', '/counter/customers', 'CounterController@customersList');
$router->add('GET', '/counter/print/:id', 'CounterController@printBill');
$router->add('POST', '/counter/pay/:id', 'CounterController@payBill');
$router->add('POST', '/counter/bills/delete/:id', 'CounterController@deleteBill');
$router->add('POST', '/counter/bills/merge/:table', 'CounterController@mergeBills');
$router->add('GET', '/counter/session', 'CounterController@sessionInfo');
$router->add('POST', '/counter/session/close', 'CounterController@requestClose');
$router->add('GET', '/counter/session/pending', 'CounterController@pendingClosures');
$router->add('POST', '/counter/session/approve/:id', 'CounterController@approveClose');
$router->add('POST', '/counter/session/reject/:id', 'CounterController@rejectClose');

// Waiter App & Customer API Routes
$router->add('POST', '/api/login', 'ApiController@login');
$router->add('GET', '/api/user', 'ApiController@user');
$router->add('GET', '/api/settings', 'ApiController@settings');
$router->add('GET', '/api/categories', 'ApiController@categories');
$router->add('GET', '/api/products', 'ApiController@products');
$router->add('GET', '/api/tables', 'ApiController@tables');
$router->add('GET', '/api/orders/active/:table', 'ApiController@getActiveOrder');
$router->add('POST', '/api/orders', 'ApiController@createOrder');
$router->add('POST', '/api/orders/close/:id', 'ApiController@closeOrder');
$router->add('POST', '/api/orders/cancel/:id', 'ApiController@cancelOrder');
$router->add('GET', '/api/notifications', 'ApiController@getWaiterNotifications');
$router->add('POST', '/api/notifications/dispatch', 'ApiController@dispatchKotItem');
$router->add('POST', '/api/notifications/dispatch/:id', 'ApiController@dispatchKotItem');

// Catch-all Customer Web Menu for scanned QR code (e.g. /customer/5)
$router->add('GET', '/customer/:table', 'HomeController@customerView');

// Check for license expiry
try {
    require_once 'models/Setting.php';
    $settingModel = new Setting();
    $settings = $settingModel->getSettings();
    $expiryDate = $settings['software_expiry_date'] ?? null;
    
    if ($expiryDate) {
        $today = date('Y-m-d');
        if ($today > $expiryDate) {
            $isSuperAdmin = isset($_SESSION['username']) && $_SESSION['username'] === 'superadmin';
            
            // Get clean URI path
            $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
            $prefix = ($scriptDir === '/') ? '' : $scriptDir;
            $cleanUri = '/' . rtrim(ltrim(preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $requestUri), '/'), '/');
            
            // Allow login, logout, and login submission
            $allowedRoutes = ['/login', '/logout', '/api/login'];
            if (!$isSuperAdmin && !in_array($cleanUri, $allowedRoutes)) {
                if (strpos($cleanUri, '/api/') === 0) {
                    header('Content-Type: application/json');
                    http_response_code(403);
                    echo json_encode(['error' => 'Software license has expired. Please contact your vendor at 97335078079.']);
                    exit;
                }
                require_once 'views/license_expired.php';
                exit;
            }
        }
    }
} catch (Exception $e) {
    // Database or column doesn't exist yet (e.g. during initial install)
}

// Dispatch current request
$router->handle($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
