<?php
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Bill.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/CounterSession.php';
require_once __DIR__ . '/../models/OnlinePlatform.php';

class AdminAppController extends Controller {

    public function index() {
        $settingsModel = new Setting();
        $settings = $settingsModel->getSettings();
        
        $isLoggedIn = isset($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') === 'admin';
        $adminUser = $isLoggedIn ? [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'name' => $_SESSION['user_name'] ?? $_SESSION['username'],
            'role' => $_SESSION['user_role']
        ] : null;

        $this->render('admin_app', [
            'settings' => $settings,
            'isLoggedIn' => $isLoggedIn,
            'adminUser' => $adminUser
        ]);
    }

    public function login() {
        $data = $this->getJsonInput();
        if (empty($data)) {
            $data = $_POST;
        }

        $username = trim($data['username'] ?? '');
        $password = trim($data['password'] ?? '');

        if (empty($username) || empty($password)) {
            $this->json(['success' => false, 'error' => 'Please enter username and password.'], 400);
            return;
        }

        $userModel = new User();
        $user = $userModel->authenticate($username, $password);

        if ($user === 'deactivated') {
            $this->json(['success' => false, 'error' => 'Your account is deactivated.'], 403);
            return;
        }

        if (!$user) {
            $this->json(['success' => false, 'error' => 'Invalid username or password.'], 401);
            return;
        }

        // Strictly verify that the user has admin role
        if ($user['role'] !== 'admin') {
            $this->json([
                'success' => false, 
                'error' => 'Access Denied: This app is restricted strictly to Administrators.'
            ], 403);
            return;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];

        $this->json([
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'name' => $user['name'],
                'role' => $user['role']
            ]
        ]);
    }

    private function ensureAdminAuth() {
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
            $this->json(['success' => false, 'error' => 'Unauthorized. Admin session required.'], 401);
            exit;
        }
    }

    public function getDashboardData() {
        $this->ensureAdminAuth();
        $db = Database::getInstance()->getConnection();
        $today = date('Y-m-d');

        // 1. Engaged Tables
        $orderModel = new Order();
        $engagedTables = $orderModel->getEngagedTables();
        $engagedTablesCount = count($engagedTables);
        
        $totalTablesCount = (int)$db->query("SELECT COUNT(*) FROM dining_tables")->fetchColumn();

        // 2. Online Orders
        $sqlOnline = "SELECT o.id, o.token_number, o.status, o.platform_order_number, o.created_at, p.name as platform_name,
                             (SELECT COUNT(*) FROM kot_items ki JOIN kots k ON ki.kot_id = k.id WHERE k.order_id = o.id) as item_count,
                             (SELECT SUM(p2.price * ki2.quantity) FROM kot_items ki2 JOIN kots k2 ON ki2.kot_id = k2.id JOIN products p2 ON ki2.product_id = p2.id WHERE k2.order_id = o.id) as total_amount
                      FROM orders o
                      LEFT JOIN online_platforms p ON o.platform_id = p.id
                      WHERE o.order_type = 'online' AND o.status IN ('active', 'closed')
                      ORDER BY o.created_at DESC";
        $stmtOnline = $db->query($sqlOnline);
        $onlineOrders = $stmtOnline->fetchAll();
        $onlineOrdersCount = count($onlineOrders);

        // 3. Takeaway Orders Queue
        $takeawayOrders = $orderModel->getReadyTakeawayOrders();
        $takeawayOrdersCount = count($takeawayOrders);

        // Also get active takeaways that might not be closed yet
        $sqlActiveTakeaways = "SELECT o.id, o.token_number, o.customer_name, o.customer_mobile, o.status, o.created_at,
                                      (SELECT COUNT(*) FROM kot_items ki JOIN kots k ON ki.kot_id = k.id WHERE k.order_id = o.id) as item_count
                               FROM orders o
                               WHERE o.order_type = 'take_away' AND o.status = 'active'
                               ORDER BY o.created_at DESC";
        $activeTakeaways = $db->query($sqlActiveTakeaways)->fetchAll();
        $totalActiveTakeawaysCount = $takeawayOrdersCount + count($activeTakeaways);

        // 4. Collection Summary for Today
        $billModel = new Bill();
        $todayCollection = $billModel->getCollectionSummary($today, $today, null);
        $todayRefunds = $billModel->getRefundTotal($today, $today);
        $todayOnline = $billModel->getOnlineOrdersBreakdown($today, $today);
        $cashiersBreakdown = $billModel->getCashiersBreakdown($today, $today);

        $todayCollection['refund_total'] = (float)$todayRefunds;
        $todayCollection['online_total'] = (float)$todayOnline['total'];
        $todayCollection['online_breakdown'] = $todayOnline['breakdown'];
        $todayCollection['actual_total'] = max(0, (float)$todayCollection['grand_total'] - (float)$todayRefunds + (float)$todayOnline['total']);

        // Today's order counts
        $sqlStats = "SELECT 
                        COUNT(DISTINCT id) as total_bills,
                        COUNT(DISTINCT CASE WHEN payment_method = 'cash' THEN id END) as cash_bills,
                        COUNT(DISTINCT CASE WHEN payment_method = 'card' THEN id END) as card_bills,
                        COUNT(DISTINCT CASE WHEN payment_method = 'qr_pay' THEN id END) as qr_bills,
                        COALESCE(SUM(tax_amount), 0) as total_tax,
                        COALESCE(SUM(discount_amount), 0) as total_discount
                     FROM bills 
                     WHERE status = 'paid' AND DATE(created_at) = ?";
        $stmtStats = $db->prepare($sqlStats);
        $stmtStats->execute([$today]);
        $billStats = $stmtStats->fetch();

        // 5. Cashier Closures
        $csModel = new CounterSession();
        $pendingClosures = $csModel->getPendingClosures();
        $closureHistory = $csModel->getClosedSessions(15);

        // Enrich pending closures with variance calculation
        foreach ($pendingClosures as &$pc) {
            $expectedTotal = (float)$pc['system_total'];
            $collectedTotal = (float)$pc['collected_total'];
            $diff = $collectedTotal - $expectedTotal;
            $pc['variance'] = $diff;
            $pc['variance_type'] = ($diff == 0) ? 'matched' : (($diff > 0) ? 'excess' : 'shortage');
        }

        $this->json([
            'success' => true,
            'server_time' => date('Y-m-d H:i:s'),
            'operations' => [
                'tables_engaged_count' => $engagedTablesCount,
                'total_tables_count' => $totalTablesCount,
                'engaged_tables' => $engagedTables,
                'online_orders_count' => $onlineOrdersCount,
                'online_orders' => $onlineOrders,
                'takeaways_count' => $totalActiveTakeawaysCount,
                'takeaway_queue_count' => $takeawayOrdersCount,
                'takeaway_queue' => $takeawayOrders,
                'active_takeaways' => $activeTakeaways
            ],
            'collection' => [
                'date' => $today,
                'summary' => $todayCollection,
                'stats' => $billStats,
                'cashiers' => $cashiersBreakdown
            ],
            'closures' => [
                'pending_count' => count($pendingClosures),
                'pending' => $pendingClosures,
                'history' => $closureHistory
            ]
        ]);
    }

    public function getCollectionSummary() {
        $this->ensureAdminAuth();
        $startDate = $_GET['start_date'] ?? date('Y-m-d');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $cashierId = !empty($_GET['cashier_id']) ? (int)$_GET['cashier_id'] : null;

        $billModel = new Bill();
        $summary = $billModel->getCollectionSummary($startDate, $endDate, $cashierId);
        $refundTotal = $billModel->getRefundTotal($startDate, $endDate);
        $onlineData = $billModel->getOnlineOrdersBreakdown($startDate, $endDate);
        $cashiersBreakdown = $billModel->getCashiersBreakdown($startDate, $endDate);

        $summary['refund_total'] = (float)$refundTotal;
        $summary['online_total'] = (float)$onlineData['total'];
        $summary['online_breakdown'] = $onlineData['breakdown'];
        $summary['actual_total'] = max(0, (float)$summary['grand_total'] - (float)$refundTotal + (float)$onlineData['total']);

        $db = Database::getInstance()->getConnection();
        $sqlStats = "SELECT 
                        COUNT(DISTINCT id) as total_bills,
                        COUNT(DISTINCT CASE WHEN payment_method = 'cash' THEN id END) as cash_bills,
                        COUNT(DISTINCT CASE WHEN payment_method = 'card' THEN id END) as card_bills,
                        COUNT(DISTINCT CASE WHEN payment_method = 'qr_pay' THEN id END) as qr_bills,
                        COALESCE(SUM(tax_amount), 0) as total_tax,
                        COALESCE(SUM(discount_amount), 0) as total_discount
                     FROM bills 
                     WHERE status = 'paid' AND DATE(created_at) BETWEEN ? AND ?";
        $params = [$startDate, $endDate];
        if ($cashierId !== null) {
            $sqlStats .= " AND cashier_id = ?";
            $params[] = $cashierId;
        }
        $stmtStats = $db->prepare($sqlStats);
        $stmtStats->execute($params);
        $stats = $stmtStats->fetch();

        // Hourly breakdown for chart
        $sqlHourly = "SELECT HOUR(created_at) as hour, SUM(grand_total) as amount, COUNT(id) as bills_count
                      FROM bills
                      WHERE status = 'paid' AND DATE(created_at) BETWEEN ? AND ?
                      GROUP BY HOUR(created_at)
                      ORDER BY HOUR(created_at) ASC";
        $stmtHourly = $db->prepare($sqlHourly);
        $stmtHourly->execute([$startDate, $endDate]);
        $hourly = $stmtHourly->fetchAll();

        $this->json([
            'success' => true,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'summary' => $summary,
            'stats' => $stats,
            'cashiers' => $cashiersBreakdown,
            'hourly' => $hourly
        ]);
    }

    public function getEngagedTables() {
        $this->ensureAdminAuth();
        $orderModel = new Order();
        $tables = $orderModel->getEngagedTables();
        $this->json(['success' => true, 'tables' => $tables]);
    }

    public function getOnlineOrders() {
        $this->ensureAdminAuth();
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT o.id, o.token_number, o.status, o.platform_order_number, o.customer_name, o.customer_mobile, o.created_at,
                       p.name as platform_name,
                       (SELECT COUNT(*) FROM kot_items ki JOIN kots k ON ki.kot_id = k.id WHERE k.order_id = o.id) as item_count,
                       (SELECT SUM(p2.price * ki2.quantity) FROM kot_items ki2 JOIN kots k2 ON ki2.kot_id = k2.id JOIN products p2 ON ki2.product_id = p2.id WHERE k2.order_id = o.id) as total_amount
                FROM orders o
                LEFT JOIN online_platforms p ON o.platform_id = p.id
                WHERE o.order_type = 'online' AND o.status IN ('active', 'closed')
                ORDER BY o.created_at DESC";
        $orders = $db->query($sql)->fetchAll();
        $this->json(['success' => true, 'orders' => $orders]);
    }

    public function getTakeaways() {
        $this->ensureAdminAuth();
        $orderModel = new Order();
        $ready = $orderModel->getReadyTakeawayOrders();
        
        $db = Database::getInstance()->getConnection();
        $sqlActive = "SELECT o.id, o.token_number, o.customer_name, o.customer_mobile, o.status, o.created_at,
                             (SELECT COUNT(*) FROM kot_items ki JOIN kots k ON ki.kot_id = k.id WHERE k.order_id = o.id) as item_count,
                             (SELECT SUM(p.price * ki.quantity) FROM kot_items ki JOIN kots k ON ki.kot_id = k.id JOIN products p ON ki.product_id = p.id WHERE k.order_id = o.id) as subtotal
                      FROM orders o
                      WHERE o.order_type = 'take_away' AND o.status = 'active'
                      ORDER BY o.created_at DESC";
        $active = $db->query($sqlActive)->fetchAll();
        
        $this->json([
            'success' => true, 
            'ready_for_pickup' => $ready,
            'active_in_prep' => $active
        ]);
    }

    public function getClosures() {
        $this->ensureAdminAuth();
        $csModel = new CounterSession();
        $pending = $csModel->getPendingClosures();
        $history = $csModel->getClosedSessions(30);

        foreach ($pending as &$pc) {
            $expectedTotal = (float)$pc['system_total'];
            $collectedTotal = (float)$pc['collected_total'];
            $diff = $collectedTotal - $expectedTotal;
            $pc['variance'] = $diff;
            $pc['variance_type'] = ($diff == 0) ? 'matched' : (($diff > 0) ? 'excess' : 'shortage');
        }

        $this->json([
            'success' => true,
            'pending' => $pending,
            'history' => $history
        ]);
    }

    public function approveClosure($params) {
        $this->ensureAdminAuth();
        $sessionId = (int)($params['id'] ?? 0);
        $adminId = $_SESSION['user_id'];
        
        if ($sessionId <= 0) {
            $this->json(['success' => false, 'error' => 'Invalid session ID'], 400);
            return;
        }

        $csModel = new CounterSession();
        $success = $csModel->approveClose($sessionId, $adminId);

        if ($success) {
            $this->json([
                'success' => true, 
                'message' => 'Cashier shift closing has been verified and approved successfully. The cashier can now log in to begin a new shift.'
            ]);
        } else {
            $this->json(['success' => false, 'error' => 'Failed to approve closing. It may already be closed.'], 500);
        }
    }

    public function rejectClosure($params) {
        $this->ensureAdminAuth();
        $sessionId = (int)($params['id'] ?? 0);
        
        if ($sessionId <= 0) {
            $this->json(['success' => false, 'error' => 'Invalid session ID'], 400);
            return;
        }

        $csModel = new CounterSession();
        $success = $csModel->rejectClose($sessionId);

        if ($success) {
            $this->json([
                'success' => true, 
                'message' => 'Cashier closing request has been rejected. Shift session is re-opened for cashier recount.'
            ]);
        } else {
            $this->json(['success' => false, 'error' => 'Failed to reject closing.'], 500);
        }
    }

    public function orderDetails($params) {
        $this->ensureAdminAuth();
        $orderId = (int)($params['id'] ?? 0);
        $orderModel = new Order();
        $order = $orderModel->getOrderDetails($orderId);
        
        if ($order) {
            $this->json(['success' => true, 'order' => $order]);
        } else {
            $this->json(['success' => false, 'error' => 'Order not found'], 404);
        }
    }
}
