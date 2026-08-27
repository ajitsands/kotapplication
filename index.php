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
require_once 'core/helpers.php';
require_once 'models/OnlinePlatform.php';

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
$router->add('POST', '/admin/categories', 'AdminController@saveCategory');
$router->add('POST', '/admin/categories/delete/:id', 'AdminController@deleteCategory');
$router->add('POST', '/admin/products', 'AdminController@saveProduct');
$router->add('POST', '/admin/products/delete/:id', 'AdminController@deleteProduct');
$router->add('POST', '/admin/tables', 'AdminController@addTable');
$router->add('POST', '/admin/tables/delete/:id', 'AdminController@deleteTable');
$router->add('POST', '/admin/users', 'AdminController@addUser');
$router->add('POST', '/admin/users/delete/:id', 'AdminController@deleteUser');
$router->add('POST', '/admin/users/status/:id', 'AdminController@toggleUserStatus');
$router->add('POST', '/admin/users/reset-password/:id', 'AdminController@resetUserPassword');
$router->add('POST', '/admin/platforms', 'AdminController@savePlatform');
$router->add('POST', '/admin/platforms/delete/:id', 'AdminController@deletePlatform');
$router->add('POST', '/admin/platforms/status/:id', 'AdminController@togglePlatformStatus');
$router->add('POST', '/user/change-password', 'HomeController@changePassword');
$router->add('GET', '/admin/tax-report/json', 'AdminController@taxReportJson');
$router->add('GET', '/admin/analytics/json', 'AdminController@analyticsJson');
$router->add('GET', '/admin/waiter-performance/json', 'AdminController@waiterPerformanceJson');

// Inventory & Supplier Routes
$router->add('GET', '/admin/suppliers/list', 'SupplierController@suppliersListJson');
$router->add('POST', '/admin/suppliers', 'SupplierController@saveSupplier');
$router->add('POST', '/admin/suppliers/delete/:id', 'SupplierController@deleteSupplier');

$router->add('GET', '/admin/inventory/items/list', 'InventoryController@itemsListJson');
$router->add('POST', '/admin/inventory/items', 'InventoryController@saveItem');
$router->add('POST', '/admin/inventory/items/delete/:id', 'InventoryController@deleteItem');
$router->add('GET', '/admin/inventory/items/template', 'InventoryController@downloadItemsTemplate');
$router->add('POST', '/admin/inventory/items/import', 'InventoryController@importItems');
$router->add('GET', '/admin/inventory/transactions/:item_id', 'InventoryController@transactionsJson');
$router->add('GET', '/admin/inventory/exp-history/:item_id', 'InventoryController@expHistoryJson');
$router->add('POST', '/admin/inventory/damage', 'InventoryController@markDamage');
$router->add('GET', '/admin/inventory/damage/report', 'InventoryController@damageReportJson');

$router->add('POST', '/admin/inventory/stock/add', 'InventoryController@addStock');
$router->add('GET', '/admin/inventory/stock/template', 'InventoryController@downloadStockTemplate');
$router->add('POST', '/admin/inventory/stock/import', 'InventoryController@importStock');

$router->add('GET', '/admin/inventory/recipes/:product_id', 'InventoryController@getRecipe');
$router->add('POST', '/admin/inventory/recipes', 'InventoryController@saveRecipe');

$router->add('GET', '/admin/reports/kpi/chef', 'ReportsController@chefKpiJson');
$router->add('GET', '/admin/reports/kpi/chef/details/:id', 'ReportsController@chefDetailsJson');
$router->add('GET', '/admin/reports/kpi/supplier', 'ReportsController@supplierKpiJson');
$router->add('GET', '/admin/reports/profitability', 'ReportsController@profitabilityJson');
$router->add('GET', '/admin/reports/kpi/product-sales', 'ReportsController@productSalesKpiJson');
$router->add('GET', '/admin/reports/item-insights', 'ReportsController@itemInsightsJson');


// KOT Operations Routes
$router->add('GET', '/kot', 'KotController@index');
$router->add('GET', '/kot/items', 'KotController@itemsList');
$router->add('GET', '/kot/completed', 'KotController@completedList');
$router->add('POST', '/kot/items/ready/:id', 'KotController@markItemReady');
$router->add('POST', '/kot/ready/:id', 'KotController@markKotReady');
$router->add('GET', '/kot/print/:id', 'KotController@printKot');
$router->add('POST', '/kot/items/delete/:id', 'KotController@deleteItem');
$router->add('POST', '/kot/delete/:id', 'KotController@deleteKot');
$router->add('GET', '/kot/products/list', 'KotController@productsListJson');
$router->add('POST', '/kot/products/toggle/:id', 'KotController@toggleProductAvailability');

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
$router->add('GET', '/counter/engaged-tables', 'CounterController@engagedTablesList');
$router->add('GET', '/counter/order/:id', 'CounterController@orderDetails');
$router->add('POST', '/counter/order/online/confirm/:id', 'CounterController@confirmOnlineOrder');
$router->add('POST', '/counter/order/cancel/:id', 'CounterController@cancelOrder');
$router->add('POST', '/counter/order/close/:id', 'CounterController@closeActiveOrder');
$router->add('GET', '/counter/products/counter-items', 'CounterController@getCounterItems');
$router->add('GET', '/counter/products/all-available', 'CounterController@getAllAvailableProducts');
$router->add('POST', '/counter/order/add-counter-items/:id', 'CounterController@addCounterItems');
$router->add('POST', '/counter/order/remove-item/:id', 'CounterController@removeOrderItem');
$router->add('GET', '/counter/delivery-queue', 'CounterController@getDeliveryQueue');
$router->add('GET', '/counter/completed-takeaways', 'CounterController@getCompletedTakeaways');
$router->add('GET', '/counter/online-orders/active', 'CounterController@getActiveOnlineOrders');
$router->add('POST', '/counter/order/online/complete/:id', 'CounterController@completeOnlineOrder');
$router->add('POST', '/counter/order/online/create', 'CounterController@createOnlineOrder');
$router->add('POST', '/counter/delivery-queue/deliver/:id', 'CounterController@markDelivered');
$router->add('GET', '/counter/refunds/pending', 'CounterController@pendingRefunds');
$router->add('GET', '/counter/refunds/completed', 'CounterController@completedRefunds');
$router->add('POST', '/counter/refunds/pay/:id', 'CounterController@processRefund');
$router->add('POST', '/counter/refunds/pay-order/:id', 'CounterController@processOrderRefund');
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
$router->add('GET', '/api/orders/status/:id', 'ApiController@getOrderStatus');
$router->add('GET', '/api/orders/mobile/:mobile', 'ApiController@getActiveOrderByMobile');
$router->add('POST', '/api/orders/received/:id', 'ApiController@customerItemReceived');
// Catch-all Customer Web Menu for scanned QR code (e.g. /customer/5)
$router->add('GET', '/customer/:table', 'HomeController@customerView');
$router->add('GET', '/takeaway', 'HomeController@takeawayView');

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
