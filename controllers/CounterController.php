<?php
require_once __DIR__ . '/../models/Bill.php';
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../models/CounterSession.php';
require_once __DIR__ . '/../models/Order.php';

class CounterController extends Controller {
    public function __construct() {
        if (strpos($_SERVER['REQUEST_URI'], '/counter/print/') !== 0) {
            $this->requireAuth('counter');
        }
    }

    public function index() {
        $settingsModel = new Setting();
        $settings = $settingsModel->getSettings();

        // Ensure an active shift session exists for cashier users
        $cashierId = $_SESSION['user_id'] ?? null;
        $sessionId = null;
        $pendingApproval = false;
        $activeSession = null;

        if ($cashierId && ($_SESSION['user_role'] ?? '') === 'counter') {
            $csModel = new CounterSession();
            $activeSession = $csModel->getActiveSession($cashierId);
            if ($activeSession && $activeSession['status'] === 'close_requested') {
                $pendingApproval = true;
                unset($_SESSION['counter_session_id']);
            } else {
                $sessionId = $csModel->ensureSession($cashierId);
                $_SESSION['counter_session_id'] = $sessionId;
                $activeSession = $csModel->getSession($sessionId);
            }
        }

        $this->render('counter_display', [
            'settings'  => $settings,
            'sessionId' => $sessionId,
            'pendingApproval' => $pendingApproval,
            'activeSession' => $activeSession,
        ]);
    }

    public function billsList() {
        $billModel = new Bill();
        $bills = $billModel->getPendingBills();
        $this->json(['bills' => $bills]);
    }

    public function billDetails($params) {
        $billId = (int)($params['id'] ?? 0);
        $billModel = new Bill();
        $bill = $billModel->getBillDetails($billId);
        if ($bill) {
            $this->json(['bill' => $bill]);
        } else {
            $this->json(['error' => 'Bill not found'], 404);
        }
    }

    public function payBill($params) {
        $billId = (int)($params['id'] ?? 0);
        $data = $this->getJsonInput();
        if (empty($data)) {
            $data = $_POST;
        }
        $method = $data['payment_method'] ?? 'cash';
        $discountPercent = (float)($data['discount_percent'] ?? 0.00);
        $customerName = trim($data['customer_name'] ?? '');
        $customerMobile = trim($data['customer_mobile'] ?? '');
        $gender = trim($data['gender'] ?? '');
        $cashierId = $_SESSION['user_id'] ?? null;

        // Safety check: Ensure cashier has an active open session
        if ($cashierId && ($_SESSION['user_role'] ?? '') === 'counter') {
            $csModel = new CounterSession();
            $activeSession = $csModel->getActiveSession($cashierId);
            if (!$activeSession || $activeSession['status'] !== 'open') {
                $this->json(['error' => 'No active open counter session. Cannot perform payments.'], 403);
                return;
            }
        }

        $billModel = new Bill();
        $success = $billModel->payBill($billId, $method, $discountPercent, $cashierId, $customerName, $customerMobile, $gender);
        $this->json(['success' => $success]);
    }

    public function lookupCustomer() {
        $mobile = $_GET['mobile'] ?? '';
        if (empty($mobile)) {
            $this->json(['success' => false, 'error' => 'Mobile number is required']);
            return;
        }

        $billModel = new Bill();
        $data = $billModel->lookupCustomerByMobile($mobile);

        if ($data) {
            $this->json([
                'success' => true,
                'exists' => true,
                'customer' => $data['customer'],
                'total_spent' => $data['total_spent'],
                'total_discount' => $data['total_discount'],
                'visit_count' => $data['visit_count'],
                'visits' => $data['visits']
            ]);
        } else {
            $this->json([
                'success' => true,
                'exists' => false
            ]);
        }
    }

    public function customersList() {
        $billModel = new Bill();
        $customers = $billModel->getCustomersList();
        $this->json(['customers' => $customers]);
    }

    public function deleteBill($params) {
        $billId = (int)($params['id'] ?? 0);
        $billModel = new Bill();
        $success = $billModel->deleteBill($billId);
        $this->json(['success' => $success]);
    }

    public function mergeBills($params) {
        $tableNumber = (int)($params['table'] ?? 0);
        $billModel = new Bill();
        $success = $billModel->mergeTableBills($tableNumber);
        $this->json(['success' => $success]);
    }

    public function printBill($params) {
        $billId = (int)($params['id'] ?? 0);
        $billModel = new Bill();
        $bill = $billModel->getBillDetails($billId);
        if (!$bill) {
            echo "<h1>Bill Not Found</h1>";
            exit;
        }
        $settingsModel = new Setting();
        $settings = $settingsModel->getSettings();
        $this->render('print_bill', ['bill' => $bill, 'settings' => $settings]);
    }

    public function summary() {
        $startDate = $_GET['start_date'] ?? $_GET['date'] ?? date('Y-m-d');
        $endDate = $_GET['end_date'] ?? $_GET['date'] ?? date('Y-m-d');
        $userRole = $_SESSION['user_role'] ?? 'counter';
        $userId = $_SESSION['user_id'] ?? 0;
        $billModel = new Bill();
        if ($userRole === 'admin') {
            $summary   = $billModel->getCollectionSummary($startDate, $endDate, null);
            $breakdown = $billModel->getCashiersBreakdown($startDate, $endDate);
            $this->json(['role' => 'admin', 'summary' => $summary, 'breakdown' => $breakdown]);
        } else {
            // For cashier, default to current active session if date range is exactly today
            if ($startDate === date('Y-m-d') && $endDate === date('Y-m-d')) {
                $csModel = new CounterSession();
                $activeSession = $csModel->getActiveSession($userId);
                if (!$activeSession) {
                    $activeSession = $csModel->getLastSession($userId);
                }

                if ($activeSession) {
                    if ($activeSession['status'] === 'open') {
                        $totals = $csModel->refreshSessionTotals($activeSession['id']);
                    } else {
                        $totals = [
                            'cash_total' => $activeSession['cash_total'],
                            'card_total' => $activeSession['card_total'],
                            'qr_total' => $activeSession['qr_total'],
                            'system_total' => $activeSession['system_total']
                        ];
                    }
                    $summary = [
                        'cash_total' => $totals['cash_total'],
                        'card_total' => $totals['card_total'],
                        'qr_total' => $totals['qr_total'],
                        'grand_total' => $totals['system_total']
                    ];
                } else {
                    $summary = [
                        'cash_total' => 0.000,
                        'card_total' => 0.000,
                        'qr_total' => 0.000,
                        'grand_total' => 0.000
                    ];
                }
            } else {
                $summary = $billModel->getCollectionSummary($startDate, $endDate, $userId);
            }
            $this->json(['role' => 'counter', 'summary' => $summary, 'breakdown' => []]);
        }
    }

    // AJAX: Get current session info + live totals
    public function sessionInfo() {
        $sessionId = $_SESSION['counter_session_id'] ?? null;
        if (!$sessionId) {
            $this->json(['session' => null]);
            return;
        }
        $startTime = $_GET['start_time'] ?? null;
        $endTime   = $_GET['end_time'] ?? null;

        $csModel = new CounterSession();
        $totals  = $csModel->refreshSessionTotals($sessionId, $startTime, $endTime);
        $session = $csModel->getSession($sessionId);
        $this->json(['session' => $session, 'totals' => $totals]);
    }

    // AJAX: Cashier submits close counter request
    public function requestClose() {
        $sessionId = $_SESSION['counter_session_id'] ?? null;
        if (!$sessionId) {
            $this->json(['success' => false, 'error' => 'No active session']);
            return;
        }
        $data          = $this->getJsonInput();
        $collectedCash = (float)($data['collected_cash'] ?? 0);
        $collectedCard = (float)($data['collected_card'] ?? 0);
        $collectedQr   = (float)($data['collected_qr'] ?? 0);
        $notes         = trim($data['notes'] ?? '');
        $startTime     = $data['start_time'] ?? null;
        $endTime       = $data['end_time'] ?? null;

        $csModel = new CounterSession();
        $csModel->refreshSessionTotals($sessionId, $startTime, $endTime);
        $success = $csModel->requestClose($sessionId, $collectedCash, $collectedCard, $collectedQr, $notes, $endTime);
        $this->json(['success' => $success]);
    }

    // AJAX Admin: Get pending closures + history
    public function pendingClosures() {
        $this->requireAuth('admin');
        $csModel = new CounterSession();
        $this->json([
            'pending' => $csModel->getPendingClosures(),
            'history' => $csModel->getClosedSessions(30),
        ]);
    }

    // AJAX Admin: Approve a closure
    public function approveClose($params) {
        $this->requireAuth('admin');
        $sessionId = (int)($params['id'] ?? 0);
        $adminId   = $_SESSION['user_id'];
        $csModel   = new CounterSession();
        $success   = $csModel->approveClose($sessionId, $adminId);
        $this->json(['success' => $success]);
    }

    // AJAX Admin: Reject a closure (reopens session)
    public function rejectClose($params) {
        $this->requireAuth('admin');
        $sessionId = (int)($params['id'] ?? 0);
        $csModel   = new CounterSession();
        $success   = $csModel->rejectClose($sessionId);
        $this->json(['success' => $success]);
    }

    // AJAX: Get engaged tables list
    public function engagedTablesList() {
        $orderModel = new Order();
        $tables = $orderModel->getEngagedTables();
        $this->json(['tables' => $tables]);
    }

    // AJAX: Get active/closed order details by ID
    public function orderDetails($params) {
        $orderId = (int)($params['id'] ?? 0);
        $orderModel = new Order();
        $order = $orderModel->getOrderDetails($orderId);
        
        if ($order) {
            // Calculate totals
            $subtotal = 0.0;
            if (!empty($order['items'])) {
                foreach ($order['items'] as $item) {
                    $subtotal += (float)$item['subtotal_price'];
                }
            }
            
            $settingsModel = new Setting();
            $settings = $settingsModel->getSettings();
            $taxType = $settings['tax_type'] ?? 'VAT';
            $taxAmount = 0.0;
            
            if ($taxType === 'VAT') {
                $vatPercent = (float)($settings['vat_percent'] ?? 10.00);
                $taxAmount = $subtotal * ($vatPercent / 100.0);
            } else { // GST
                $cgstPercent = (float)($settings['cgst_percent'] ?? 2.50);
                $sgstPercent = (float)($settings['sgst_percent'] ?? 2.50);
                $taxAmount = $subtotal * (($cgstPercent + $sgstPercent) / 100.0);
            }
            
            $order['subtotal'] = $subtotal;
            $order['tax_amount'] = $taxAmount;
            $order['grand_total'] = $subtotal + $taxAmount;
            
            $this->json(['order' => $order]);
        } else {
            $this->json(['error' => 'Order not found'], 404);
        }
    }

    // AJAX: Cashier closes an active order session and generates a pending bill
    public function closeActiveOrder($params) {
        $orderId = (int)($params['id'] ?? 0);
        $orderModel = new Order();
        $success = $orderModel->closeOrder($orderId);
        $this->json(['success' => $success]);
    }

    // AJAX: Get list of products tagged as counter items
    public function getCounterItems() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT id, name, price FROM products WHERE is_counter_item = 1 ORDER BY name ASC");
        $items = $stmt->fetchAll();
        $this->json(['success' => true, 'items' => $items]);
    }

    // AJAX: Add counter items directly to an order/bill
    public function addCounterItems($params) {
        $orderId = (int)($params['id'] ?? 0);
        $data = $this->getJsonInput();
        
        $productId = (int)($data['product_id'] ?? 0);
        $quantity = (int)($data['quantity'] ?? 1);
        
        if ($orderId <= 0 || $productId <= 0 || $quantity <= 0) {
            $this->json(['success' => false, 'error' => 'Invalid parameters.']);
            return;
        }

        $db = Database::getInstance()->getConnection();
        
        // 1. Verify the order exists and is not completed
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND status IN ('active', 'closed')");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        if (!$order) {
            $this->json(['success' => false, 'error' => 'Order not found or already completed.']);
            return;
        }

        // Get product details
        $stmtProd = $db->prepare("SELECT name, price FROM products WHERE id = ? AND (is_available = 1 OR is_counter_item = 1)");
        $stmtProd->execute([$productId]);
        $product = $stmtProd->fetch();
        if (!$product) {
            $this->json(['success' => false, 'error' => 'Product not found or unavailable.']);
            return;
        }

        $db->beginTransaction();
        try {
            // 2. Create a "Counter" KOT for this item.
            // Since this is added at the counter, status of KOT and item is 'dispatched' so it does not go to the kitchen screen.
            $kotNumber = 'KOT-CNTR-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
            $stmtKot = $db->prepare("INSERT INTO kots (order_id, waiter_id, kot_number, status) VALUES (?, NULL, ?, 'dispatched')");
            $stmtKot->execute([$orderId, $kotNumber]);
            $kotId = $db->lastInsertId();

            $stmtItem = $db->prepare("INSERT INTO kot_items (kot_id, product_id, quantity, status, notes) VALUES (?, ?, ?, 'dispatched', 'Counter Sale')");
            $stmtItem->execute([$kotId, $productId, $quantity]);

            // 3. Recalculate totals
            $sqlItems = "SELECT SUM(p.price * ki.quantity) as subtotal
                         FROM kot_items ki 
                         JOIN kots k ON ki.kot_id = k.id 
                         JOIN products p ON ki.product_id = p.id 
                         WHERE k.order_id = ?";
            $stmtSum = $db->prepare($sqlItems);
            $stmtSum->execute([$orderId]);
            $subtotal = (float)$stmtSum->fetchColumn();

            // Get settings for Taxes
            $settingsModel = new Setting();
            $settings = $settingsModel->getSettings();
            $taxType = $settings['tax_type'] ?? 'VAT';
            $taxAmount = 0.0;
            if ($taxType === 'VAT') {
                $vatPercent = (float)($settings['vat_percent'] ?? 10.00);
                $taxAmount = $subtotal * ($vatPercent / 100.0);
            } else { // GST
                $cgstPercent = (float)($settings['cgst_percent'] ?? 2.50);
                $sgstPercent = (float)($settings['sgst_percent'] ?? 2.50);
                $taxAmount = $subtotal * (($cgstPercent + $sgstPercent) / 100.0);
            }
            $grandTotal = $subtotal + $taxAmount;

            // 4. Update the bill if it exists
            $stmtBillCheck = $db->prepare("SELECT id FROM bills WHERE order_id = ? AND status = 'pending'");
            $stmtBillCheck->execute([$orderId]);
            $bill = $stmtBillCheck->fetch();
            if ($bill) {
                $stmtUpdateBill = $db->prepare("UPDATE bills SET subtotal = ?, tax_amount = ?, grand_total = ? WHERE id = ?");
                $stmtUpdateBill->execute([$subtotal, $taxAmount, $grandTotal, $bill['id']]);
            }

            $db->commit();
            $this->json(['success' => true, 'message' => 'Item added successfully.']);
        } catch (Exception $e) {
            $db->rollBack();
            $this->json(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }
    }
}
