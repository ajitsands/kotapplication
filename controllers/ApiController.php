<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Kot.php';

class ApiController extends Controller {
    // API Authentication
    public function login() {
        $data = $this->getJsonInput();
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        $userModel = new User();
        $user = $userModel->authenticate($username, $password);

        if ($user === 'deactivated') {
            $this->json(['error' => 'Account is deactivated. Please contact the administrator.'], 403);
        } elseif ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            
            // Clean waiter prefix from user name
            $name = $user['name'];
            $name = preg_replace('/^waiter\s+/i', '', $name);
            $_SESSION['user_name'] = $name;
            $user['name'] = $name;

            $this->json(['success' => true, 'user' => $user]);
        } else {
            $this->json(['error' => 'Invalid credentials'], 401);
        }
    }

    public function user() {
        if (!isset($_SESSION['user_id'])) {
            $this->json(['logged_in' => false]);
        }
        $this->json([
            'logged_in' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['user_role'],
                'name' => $_SESSION['user_name']
            ]
        ]);
    }

    public function settings() {
        $settingsModel = new Setting();
        $this->json($settingsModel->getSettings());
    }

    public function categories() {
        $categoryModel = new Category();
        $this->json($categoryModel->getAll());
    }

    public function products() {
        $productModel = new Product();
        $this->json($productModel->getAll());
    }

    public function tables() {
        $orderModel = new Order();
        $this->json($orderModel->getTablesState());
    }

    // Get table's active order detail and its KOT items
    public function getActiveOrder($params) {
        $table = (int)($params['table'] ?? 0);
        $orderModel = new Order();
        $order = $orderModel->getActiveOrderByTable($table);

        if (!$order) {
            $this->json(['active' => false]);
        }

        // Fetch item details
        $items = $orderModel->getOrderItemsSummary($order['id']);
        
        // Fetch all individual KOTs for this table
        $db = Database::getInstance()->getConnection();
        $stmtKots = $db->prepare("SELECT id, kot_number, status, created_at FROM kots WHERE order_id = ? ORDER BY created_at DESC");
        $stmtKots->execute([$order['id']]);
        $kots = $stmtKots->fetchAll();

        foreach ($kots as &$k) {
            $stmtKi = $db->prepare("SELECT ki.*, p.name as product_name, p.price 
                                    FROM kot_items ki 
                                    JOIN products p ON ki.product_id = p.id 
                                    WHERE ki.kot_id = ?");
            $stmtKi->execute([$k['id']]);
            $k['items'] = $stmtKi->fetchAll();
        }

        $this->json([
            'active' => true,
            'order' => $order,
            'items' => $items,
            'kots' => $kots
        ]);
    }

    // Place an order (waiter or customer)
    public function createOrder() {
        $data = $this->getJsonInput();
        $tableNumber = (int)($data['table_number'] ?? 0);
        $orderType = $data['order_type'] ?? 'dine_in';
        $customerName = $data['customer_name'] ?? null;
        $customerMobile = $data['customer_mobile'] ?? null;
        $items = $data['items'] ?? []; // Array of ['product_id' => X, 'quantity' => Y, 'notes' => Z]

        if (($orderType === 'dine_in' && $tableNumber <= 0) || empty($items)) {
            $this->json(['error' => 'Invalid parameters'], 400);
        }

        // Detect waiter session if logged in
        $waiterId = isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'waiter' ? $_SESSION['user_id'] : null;

        $orderModel = new Order();
        $orderId = $orderModel->createOrder($tableNumber, $waiterId, $orderType, $customerName, $customerMobile);

        if ($orderId) {
            $kotModel = new Kot();
            $kotId = $kotModel->createKot($orderId, $waiterId, $items);
            if ($kotId) {
                // If it's a takeaway, we want to immediately generate the bill
                if ($orderType === 'take_away') {
                    $orderModel->closeOrder($orderId);
                }

                // Return token number if it's take_away
                $orderInfo = $orderModel->getOrderDetails($orderId);
                
                $this->json([
                    'success' => true,
                    'order_id' => $orderId,
                    'kot_id' => $kotId,
                    'token_number' => $orderInfo['token_number'] ?? null,
                    'message' => 'Order placed successfully'
                ]);
            } else {
                $this->json(['error' => 'Failed to create KOT ticket'], 500);
            }
        } else {
            $this->json(['error' => 'Failed to initialize order'], 500);
        }
    }

    public function getOrderStatus($params) {
        $orderId = (int)($params['id'] ?? 0);
        if ($orderId <= 0) {
            $this->json(['error' => 'Invalid order ID'], 400);
        }

        $orderModel = new Order();
        $order = $orderModel->getOrderDetails($orderId);
        
        if (!$order) {
            $this->json(['error' => 'Order not found'], 404);
        }

        $this->json([
            'order_id' => $order['id'],
            'status' => $order['status'], // 'active', 'closed', 'completed'
            'token_number' => $order['token_number']
        ]);
    }

    public function getActiveOrderByMobile($params) {
        $mobile = $params['mobile'] ?? '';
        if (empty($mobile)) {
            $this->json(['active' => false]);
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id FROM orders WHERE order_type = 'take_away' AND customer_mobile = ? AND status IN ('active', 'closed') ORDER BY id DESC LIMIT 1");
        $stmt->execute([$mobile]);
        $orderId = $stmt->fetchColumn();

        if ($orderId) {
            $orderModel = new Order();
            $orderDetails = $orderModel->getOrderDetails($orderId);
            $this->json([
                'active' => true,
                'order' => $orderDetails['order'],
                'kots' => $orderDetails['kots'],
                'order_id' => $orderDetails['order']['id'],
                'status' => $orderDetails['order']['status'],
                'token_number' => $orderDetails['order']['token_number'],
                'customer_name' => $orderDetails['order']['customer_name']
            ]);
        } else {
            $this->json(['active' => false]);
        }
    }

    public function customerItemReceived($params) {
        $orderId = (int)($params['id'] ?? 0);
        if ($orderId <= 0) {
            $this->json(['success' => false]);
            return;
        }
        $orderModel = new Order();
        $success = $orderModel->markOrderCompleted($orderId);
        $this->json(['success' => $success]);
    }

    // Request billing/close order
    public function closeOrder($params) {
        $orderId = (int)($params['id'] ?? 0);
        $orderModel = new Order();
        
        $success = $orderModel->closeOrder($orderId);
        if ($success) {
            $this->json(['success' => true, 'message' => 'Order ticket closed, bill generated at cashier.']);
        } else {
            $this->json(['error' => 'Failed to close order ticket'], 500);
        }
    }

    // Get notifications for waiter (marked ready but not dispatched)
    public function getWaiterNotifications() {
        // Must be logged in as waiter
        $waiterId = $_SESSION['user_id'] ?? null;
        if (!$waiterId) {
            $this->json(['notifications' => []]);
        }

        $kotModel = new Kot();
        $notifications = $kotModel->getWaiterNotifications($waiterId);
        $this->json(['notifications' => $notifications]);
    }

    // Dispatch item
    public function dispatchKotItem($params = []) {
        $data = $this->getJsonInput();
        $kotItemId = (int)($data['kot_item_id'] ?? $params['id'] ?? 0);

        if ($kotItemId <= 0) {
            $this->json(['error' => 'Invalid item ID'], 400);
        }

        $kotModel = new Kot();
        $success = $kotModel->dispatchKotItem($kotItemId);
        $this->json(['success' => $success]);
    }

    // Cancel / delete order if KOT is deleted
    public function cancelOrder($params) {
        $orderId = (int)($params['id'] ?? 0);
        if ($orderId <= 0) {
            $this->json(['success' => false, 'error' => 'Invalid order ID']);
            return;
        }

        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        try {
            // Delete order
            $stmt = $db->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->execute([$orderId]);

            $db->commit();
            $this->json(['success' => true]);
        } catch (Exception $e) {
            $db->rollBack();
            $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
