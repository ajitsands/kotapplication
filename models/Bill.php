<?php

class Bill extends Model {
    public function __construct() {
        parent::__construct();
        $this->ensureSchema();
    }

    private function ensureSchema() {
        try {
            $allCols = $this->db->query("SHOW COLUMNS FROM bills")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('cash_amount', $allCols)) {
                $this->db->exec("ALTER TABLE `bills` ADD COLUMN `cash_amount` DECIMAL(10,3) NOT NULL DEFAULT 0.000 AFTER `grand_total`");
            }
            if (!in_array('card_amount', $allCols)) {
                $this->db->exec("ALTER TABLE `bills` ADD COLUMN `card_amount` DECIMAL(10,3) NOT NULL DEFAULT 0.000 AFTER `cash_amount`");
            }
            if (!in_array('qr_amount', $allCols)) {
                $this->db->exec("ALTER TABLE `bills` ADD COLUMN `qr_amount` DECIMAL(10,3) NOT NULL DEFAULT 0.000 AFTER `card_amount`");
            }
            if (!in_array('online_amount', $allCols)) {
                $this->db->exec("ALTER TABLE `bills` ADD COLUMN `online_amount` DECIMAL(10,3) NOT NULL DEFAULT 0.000 AFTER `qr_amount`");
            }
            if (!in_array('platform_id', $allCols)) {
                $this->db->exec("ALTER TABLE `bills` ADD COLUMN `platform_id` INT NULL DEFAULT NULL AFTER `online_amount`");
            }
            // Ensure payment_method ENUM supports online
            $this->db->exec("ALTER TABLE `bills` MODIFY COLUMN `payment_method` ENUM('cash', 'card', 'qr_pay', 'split', 'online') DEFAULT NULL");
            if (!in_array('delivery_fee', $allCols)) {
                $this->db->exec("ALTER TABLE `bills` ADD COLUMN `delivery_fee` DECIMAL(10,3) NOT NULL DEFAULT 0.000 AFTER `discount_amount`");
            }
            if (!in_array('service_fee', $allCols)) {
                $this->db->exec("ALTER TABLE `bills` ADD COLUMN `service_fee` DECIMAL(10,3) NOT NULL DEFAULT 0.000 AFTER `delivery_fee`");
            }
        } catch (Exception $e) {}
    }

    public function getPendingBills() {
        $stmt = $this->db->query("SELECT b.*, o.table_number, o.order_type, o.customer_name, o.customer_mobile, o.token_number, 
                                         IF(u.name LIKE 'Waiter %', SUBSTRING(u.name, 8), u.name) as waiter_name, 
                                         o.created_at as order_created_at,
                                         d.id as delivery_id,
                                         d.status as delivery_status,
                                         d.driver_id as delivery_driver_id,
                                         u_d.name as delivery_driver_name,
                                         d.payment_type as delivery_payment_type,
                                         d.cod_amount as delivery_cod_amount,
                                         d.cod_collected as delivery_cod_collected,
                                         d.cod_handed_over as delivery_cod_handed_over
                                  FROM bills b
                                  JOIN orders o ON b.order_id = o.id
                                  LEFT JOIN users u ON o.waiter_id = u.id
                                  LEFT JOIN deliveries d ON d.id = (
                                      SELECT MAX(id) FROM deliveries WHERE (bill_id = b.id OR order_id = o.id) AND status != 'cancelled'
                                  )
                                  LEFT JOIN users u_d ON d.driver_id = u_d.id
                                  WHERE b.status = 'pending' AND o.order_type != 'online'
                                  ORDER BY b.created_at DESC");
        return $stmt->fetchAll();
    }

    public function getBillDetails($billId) {
        $stmt = $this->db->prepare("SELECT b.*, o.table_number, o.order_type, o.customer_name, o.customer_mobile, o.token_number, 
                                           IF(u.name LIKE 'Waiter %', SUBSTRING(u.name, 8), u.name) as waiter_name, 
                                           o.id as order_id,
                                           op.name as platform_name
                                    FROM bills b
                                    JOIN orders o ON b.order_id = o.id
                                    LEFT JOIN users u ON o.waiter_id = u.id
                                    LEFT JOIN online_platforms op ON COALESCE(b.platform_id, o.platform_id) = op.id
                                    WHERE b.id = ?");
        $stmt->execute([$billId]);
        $bill = $stmt->fetch();

        if ($bill) {
            // Fetch item summary (excluding cancelled items and honoring custom/markup price)
            $sqlItems = "SELECT p.id as product_id,
                                p.name as product_name, 
                                p.is_pack,
                                GROUP_CONCAT(DISTINCT ki.notes SEPARATOR '; ') as notes,
                                COALESCE(ki.price, IF(o.order_type = 'online' AND op.markup_percent > 0, ROUND(p.price * (1 + op.markup_percent / 100), 3), p.price)) as price, 
                                SUM(ki.quantity) as total_quantity, 
                                SUM(COALESCE(ki.price, IF(o.order_type = 'online' AND op.markup_percent > 0, ROUND(p.price * (1 + op.markup_percent / 100), 3), p.price)) * ki.quantity) as subtotal_price 
                         FROM kot_items ki 
                         JOIN kots k ON ki.kot_id = k.id 
                         JOIN orders o ON k.order_id = o.id
                         LEFT JOIN online_platforms op ON o.platform_id = op.id
                         JOIN products p ON ki.product_id = p.id 
                         WHERE k.order_id = ? AND ki.status != 'cancelled'
                         GROUP BY p.id, p.name, p.is_pack, COALESCE(ki.price, IF(o.order_type = 'online' AND op.markup_percent > 0, ROUND(p.price * (1 + op.markup_percent / 100), 3), p.price))";
            $stmtItems = $this->db->prepare($sqlItems);
            $stmtItems->execute([$bill['order_id']]);
            $bill['items'] = $stmtItems->fetchAll();

            // Safety guard: if bill is still pending, ensure stored subtotal & grand_total match live items
            if (($bill['status'] ?? '') === 'pending') {
                $liveSubtotal = 0.0;
                foreach ($bill['items'] as $it) {
                    $liveSubtotal += (float)$it['subtotal_price'];
                }
                if (abs($liveSubtotal - (float)$bill['subtotal']) > 0.0005) {
                    $stmtSettings = $this->db->query("SELECT * FROM settings ORDER BY id DESC LIMIT 1");
                    $settings = $stmtSettings->fetch();
                    $taxType = $settings['tax_type'] ?? 'VAT';
                    $taxAmount = ($taxType === 'VAT')
                        ? ($liveSubtotal * ((float)($settings['vat_percent'] ?? 10.00) / 100.0))
                        : ($liveSubtotal * (((float)($settings['cgst_percent'] ?? 2.50) + (float)($settings['sgst_percent'] ?? 2.50)) / 100.0));
                    $discountPercent = (float)($bill['discount_percent'] ?? 0.0);
                    $discountAmount = ($discountPercent > 0)
                        ? ($liveSubtotal * ($discountPercent / 100.0))
                        : min((float)($bill['discount_amount'] ?? 0.0), $liveSubtotal);
                    $deliveryFee = (float)($bill['delivery_fee'] ?? 0.0);
                    $grandTotal = max(0.0, ($liveSubtotal - $discountAmount) + $taxAmount + $deliveryFee);

                    $stmtUpd = $this->db->prepare("UPDATE bills SET subtotal = ?, discount_amount = ?, tax_amount = ?, grand_total = ? WHERE id = ?");
                    $stmtUpd->execute([$liveSubtotal, $discountAmount, $taxAmount, $grandTotal, $billId]);

                    $bill['subtotal'] = $liveSubtotal;
                    $bill['discount_amount'] = $discountAmount;
                    $bill['tax_amount'] = $taxAmount;
                    $bill['grand_total'] = $grandTotal;
                }
            }
        }

        return $bill;
    }

    public function payBill($billId, $paymentMethod, $discountPercent = 0.00, $cashierId = null, $customerName = '', $customerMobile = '', $gender = '', $breakdown = [], $platformId = null) {
        // Ensure pending bill totals are synced with active KOT items before processing payment
        $this->getBillDetails($billId);

        $this->db->beginTransaction();
        try {
            // Get bill details and order type
            $stmt = $this->db->prepare("SELECT b.*, o.order_type FROM bills b JOIN orders o ON b.order_id = o.id WHERE b.id = ?");
            $stmt->execute([$billId]);
            $bill = $stmt->fetch();

            if (!$bill || $bill['status'] !== 'pending') {
                throw new Exception("Bill not found or already paid.");
            }

            // Customer Handling
            $customerId = null;
            if (!empty($customerMobile)) {
                $customerMobile = trim($customerMobile);
                $customerName = trim($customerName);
                $gender = trim($gender);

                $stmtCust = $this->db->prepare("SELECT id FROM customers WHERE mobile = ?");
                $stmtCust->execute([$customerMobile]);
                $existingCust = $stmtCust->fetch();

                if ($existingCust) {
                    $customerId = $existingCust['id'];
                    $updates = [];
                    $params = [];
                    if (!empty($customerName)) {
                        $updates[] = "name = ?";
                        $params[] = $customerName;
                    }
                    if (!empty($gender)) {
                        $updates[] = "gender = ?";
                        $params[] = $gender;
                    }
                    if (!empty($updates)) {
                        $params[] = $customerId;
                        $stmtUpdateCust = $this->db->prepare("UPDATE customers SET " . implode(", ", $updates) . " WHERE id = ?");
                        $stmtUpdateCust->execute($params);
                    }
                } else {
                    $stmtInsertCust = $this->db->prepare("INSERT INTO customers (mobile, name, gender) VALUES (?, ?, ?)");
                    $stmtInsertCust->execute([$customerMobile, $customerName, $gender]);
                    $customerId = $this->db->lastInsertId();
                }
            }

            $grandTotal = (float)$bill['grand_total'];
            $discountAmount = $grandTotal * ($discountPercent / 100);
            $newGrandTotal = $grandTotal - $discountAmount;

            // Handle payment method amounts
            $onlineAmount = 0.000;
            if ($paymentMethod === 'split') {
                $cashAmount   = round((float)($breakdown['cash_amount'] ?? 0), 3);
                $cardAmount   = round((float)($breakdown['card_amount'] ?? 0), 3);
                $qrAmount     = round((float)($breakdown['qr_amount'] ?? 0), 3);
                $onlineAmount = round((float)($breakdown['online_amount'] ?? 0), 3);
            } else if ($paymentMethod === 'card') {
                $cashAmount   = 0.000;
                $cardAmount   = $newGrandTotal;
                $qrAmount     = 0.000;
                $onlineAmount = 0.000;
            } else if ($paymentMethod === 'qr_pay') {
                $cashAmount   = 0.000;
                $cardAmount   = 0.000;
                $qrAmount     = $newGrandTotal;
                $onlineAmount = 0.000;
            } else if ($paymentMethod === 'online') {
                $cashAmount   = 0.000;
                $cardAmount   = 0.000;
                $qrAmount     = 0.000;
                $onlineAmount = $newGrandTotal;
            } else { // default 'cash'
                $paymentMethod = 'cash';
                $cashAmount   = $newGrandTotal;
                $cardAmount   = 0.000;
                $qrAmount     = 0.000;
                $onlineAmount = 0.000;
            }

            // Determine effective platform_id
            $effectivePlatformId = $platformId ? (int)$platformId : (!empty($bill['platform_id']) ? (int)$bill['platform_id'] : null);
            if (!$effectivePlatformId) {
                $stmtOrdPlat = $this->db->prepare("SELECT platform_id FROM orders WHERE id = ?");
                $stmtOrdPlat->execute([$bill['order_id']]);
                $ordPlat = $stmtOrdPlat->fetchColumn();
                if ($ordPlat) {
                    $effectivePlatformId = (int)$ordPlat;
                }
            } else {
                $stmtUpdOrdPlat = $this->db->prepare("UPDATE orders SET platform_id = ? WHERE id = ? AND (platform_id IS NULL OR platform_id = 0)");
                $stmtUpdOrdPlat->execute([$effectivePlatformId, $bill['order_id']]);
            }

            // Update bill status, payment method, breakdown amounts, platform_id, discount, grand total, cashier_id, customer_id, and payment timestamp
            $stmtBill = $this->db->prepare("UPDATE bills SET status = 'paid', payment_method = ?, cash_amount = ?, card_amount = ?, qr_amount = ?, online_amount = ?, platform_id = ?, discount_percent = ?, discount_amount = ?, grand_total = ?, cashier_id = ?, customer_id = ?, created_at = NOW() WHERE id = ?");
            $stmtBill->execute([$paymentMethod, $cashAmount, $cardAmount, $qrAmount, $onlineAmount, $effectivePlatformId, $discountPercent, $discountAmount, $newGrandTotal, $cashierId, $customerId, $billId]);

            // For Dine In orders, payment completes the order flow.
            // For Take Away orders, payment just pays the bill. The order remains 'closed' until it is delivered.
            if (($bill['order_type'] ?? 'dine_in') !== 'take_away') {
                $stmtOrder = $this->db->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
                $stmtOrder->execute([$bill['order_id']]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getCustomersList() {
        $stmt = $this->db->query("SELECT c.id, c.mobile, c.name, c.gender, c.created_at, 
                                         COALESCE(SUM(b.grand_total), 0) as total_spent, 
                                         COALESCE(SUM(b.discount_amount), 0) as total_discount,
                                         COUNT(b.id) as visit_count
                                  FROM customers c
                                  LEFT JOIN bills b ON c.id = b.customer_id AND b.status = 'paid'
                                  GROUP BY c.id
                                  ORDER BY total_spent DESC");
        return $stmt->fetchAll();
    }

    public function getCustomerByMobile($mobile) {
        $stmt = $this->db->prepare("SELECT * FROM customers WHERE mobile = ?");
        $stmt->execute([trim($mobile)]);
        return $stmt->fetch();
    }

    public function getCustomerStats($customerId) {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(grand_total), 0) as total_spent, 
                                           COALESCE(SUM(discount_amount), 0) as total_discount, 
                                           COUNT(*) as visit_count 
                                    FROM bills 
                                    WHERE customer_id = ? AND status = 'paid'");
        $stmt->execute([$customerId]);
        return $stmt->fetch();
    }

    public function getCustomerVisits($customerId) {
        $stmt = $this->db->prepare("SELECT id, grand_total, discount_amount, created_at, payment_method 
                                    FROM bills 
                                    WHERE customer_id = ? AND status = 'paid' 
                                    ORDER BY created_at DESC");
        $stmt->execute([$customerId]);
        return $stmt->fetchAll();
    }

    public function lookupCustomerByMobile($mobile) {
        $customer = $this->getCustomerByMobile($mobile);
        if (!$customer) {
            return null;
        }
        $stats = $this->getCustomerStats($customer['id']);
        $visits = $this->getCustomerVisits($customer['id']);
        return [
            'customer' => $customer,
            'total_spent' => (float)$stats['total_spent'],
            'total_discount' => (float)$stats['total_discount'],
            'visit_count' => (int)$stats['visit_count'],
            'visits' => $visits
        ];
    }

    public function deleteBill($billId) {
        $this->db->beginTransaction();
        try {
            // Get order_id associated with this bill
            $stmt = $this->db->prepare("SELECT order_id FROM bills WHERE id = ?");
            $stmt->execute([$billId]);
            $orderId = $stmt->fetchColumn();

            if ($orderId) {
                // Set order status to cancelled
                $stmtOrder = $this->db->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
                $stmtOrder->execute([$orderId]);
            }

            // Delete the bill
            $stmtBill = $this->db->prepare("DELETE FROM bills WHERE id = ?");
            $stmtBill->execute([$billId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function mergeTableBills($tableNumber) {
        $this->db->beginTransaction();
        try {
            // Find all pending bills for this table
            $stmt = $this->db->prepare("SELECT b.id, b.order_id 
                                        FROM bills b
                                        JOIN orders o ON b.order_id = o.id
                                        WHERE o.table_number = ? AND b.status = 'pending'
                                        ORDER BY b.id ASC");
            $stmt->execute([$tableNumber]);
            $bills = $stmt->fetchAll();

            if (count($bills) < 2) {
                $this->db->rollBack();
                return false;
            }

            $primaryBill = $bills[0];
            $primaryOrderId = $primaryBill['order_id'];
            $primaryBillId = $primaryBill['id'];

            // Loop through others and merge
            for ($i = 1; $i < count($bills); $i++) {
                $otherBill = $bills[$i];
                $otherOrderId = $otherBill['order_id'];
                $otherBillId = $otherBill['id'];

                // 1. Move all KOTs to primary order
                $stmtKots = $this->db->prepare("UPDATE kots SET order_id = ? WHERE order_id = ?");
                $stmtKots->execute([$primaryOrderId, $otherOrderId]);

                // 2. Mark other order as completed
                $stmtOrder = $this->db->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
                $stmtOrder->execute([$otherOrderId]);

                // 3. Delete other bill
                $stmtBillDel = $this->db->prepare("DELETE FROM bills WHERE id = ?");
                $stmtBillDel->execute([$otherBillId]);
            }

            // 4. Recalculate subtotal for primary order
            $stmtSum = $this->db->prepare("SELECT SUM(COALESCE(ki.price, IF(o.order_type = 'online' AND op.markup_percent > 0, ROUND(p.price * (1 + op.markup_percent / 100), 3), p.price)) * ki.quantity) 
                                           FROM kot_items ki 
                                           JOIN kots k ON ki.kot_id = k.id 
                                           JOIN orders o ON k.order_id = o.id
                                           LEFT JOIN online_platforms op ON o.platform_id = op.id
                                           JOIN products p ON ki.product_id = p.id 
                                           WHERE k.order_id = ?");
            $stmtSum->execute([$primaryOrderId]);
            $subtotal = (float)$stmtSum->fetchColumn();

            // 5. Get tax configuration and recalculate tax and grand total
            $settingsModel = new Setting();
            $settings = $settingsModel->getSettings();
            $taxType = $settings['tax_type'] ?? 'VAT';
            $taxAmount = 0.0;
            if ($taxType === 'VAT') {
                $vatPercent = (float)($settings['vat_percent'] ?? 0.0);
                $taxAmount = $subtotal * ($vatPercent / 100.0);
            } else {
                $cgstPercent = (float)($settings['cgst_percent'] ?? 0.0);
                $sgstPercent = (float)($settings['sgst_percent'] ?? 0.0);
                $taxAmount = $subtotal * (($cgstPercent + $sgstPercent) / 100.0);
            }
            $grandTotal = $subtotal + $taxAmount;

            // 6. Update primary bill totals
            $stmtUpdateBill = $this->db->prepare("UPDATE bills SET subtotal = ?, tax_amount = ?, grand_total = ? WHERE id = ?");
            $stmtUpdateBill->execute([$subtotal, $taxAmount, $grandTotal, $primaryBillId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getCollectionSummary($startDate, $endDate = null, $userId = null) {
        if ($endDate === null) {
            $endDate = $startDate;
        }
        // Query to get overall summary totals in date range
        $sql = "SELECT 
                    COALESCE(SUM(CASE WHEN b.payment_method = 'split' THEN b.cash_amount WHEN b.payment_method = 'cash' THEN b.grand_total ELSE 0 END), 0) as cash_total,
                    COALESCE(SUM(CASE WHEN b.payment_method = 'split' THEN b.card_amount WHEN b.payment_method = 'card' THEN b.grand_total ELSE 0 END), 0) as card_total,
                    COALESCE(SUM(CASE WHEN b.payment_method = 'split' THEN b.qr_amount WHEN b.payment_method = 'qr_pay' THEN b.grand_total ELSE 0 END), 0) as qr_total,
                    COALESCE(SUM(CASE WHEN b.payment_method = 'split' THEN b.online_amount WHEN b.payment_method = 'online' THEN b.grand_total ELSE 0 END), 0) as online_received_total,
                    COALESCE(SUM(b.grand_total), 0) as grand_total,
                    COALESCE(SUM(CASE WHEN o.order_type = 'take_away' THEN b.grand_total ELSE 0 END), 0) as takeaway_total
                FROM bills b
                JOIN orders o ON b.order_id = o.id
                WHERE b.status = 'paid' AND DATE(b.created_at) BETWEEN ? AND ?";
                
        $params = [$startDate, $endDate];
        if ($userId !== null) {
            $sql .= " AND b.cashier_id = ?";
            $params[] = $userId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function getRefundTotal($startDate, $endDate = null) {
        if ($endDate === null) {
            $endDate = $startDate;
        }
        
        $sql = "SELECT ki.quantity, p.price as base_price
                FROM kot_items ki
                JOIN products p ON ki.product_id = p.id
                JOIN kots k ON ki.kot_id = k.id
                WHERE ki.refund_status = 'refunded' AND ki.status = 'cancelled'
                AND DATE(k.created_at) BETWEEN ? AND ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        $items = $stmt->fetchAll();

        $stmtSettings = $this->db->query("SELECT tax_type, vat_percent, cgst_percent, sgst_percent FROM settings LIMIT 1");
        $settings = $stmtSettings->fetch();
        
        $taxType = $settings['tax_type'] ?? 'none';
        $vatPercent = (float)($settings['vat_percent'] ?? 0);
        $cgstPercent = (float)($settings['cgst_percent'] ?? 0);
        $sgstPercent = (float)($settings['sgst_percent'] ?? 0);
        
        $taxMultiplier = 1.0;
        if ($taxType === 'vat') {
            $taxMultiplier = 1.0 + ($vatPercent / 100.0);
        } else if ($taxType === 'gst') {
            $taxMultiplier = 1.0 + (($cgstPercent + $sgstPercent) / 100.0);
        }

        $totalRefund = 0;
        foreach ($items as $item) {
            $itemTotal = (float)$item['base_price'] * (int)$item['quantity'];
            $totalRefund += $itemTotal * $taxMultiplier;
        }

        return $totalRefund;
    }

    public function getCashiersBreakdown($startDate, $endDate = null) {
        if ($endDate === null) {
            $endDate = $startDate;
        }
        // Query to get summary per cashier in date range
        $sql = "SELECT 
                    b.cashier_id,
                    u.name as cashier_name,
                    COUNT(b.id) as total_bills,
                    COALESCE(SUM(CASE WHEN b.payment_method = 'split' THEN b.cash_amount WHEN b.payment_method = 'cash' THEN b.grand_total ELSE 0 END), 0) as cash_total,
                    COALESCE(SUM(CASE WHEN b.payment_method = 'split' THEN b.card_amount WHEN b.payment_method = 'card' THEN b.grand_total ELSE 0 END), 0) as card_total,
                    COALESCE(SUM(CASE WHEN b.payment_method = 'split' THEN b.qr_amount WHEN b.payment_method = 'qr_pay' THEN b.grand_total ELSE 0 END), 0) as qr_total,
                    COALESCE(SUM(CASE WHEN b.payment_method = 'split' THEN b.online_amount WHEN b.payment_method = 'online' THEN b.grand_total ELSE 0 END), 0) as online_total,
                    COALESCE(SUM(b.grand_total), 0) as grand_total
                FROM bills b
                JOIN users u ON b.cashier_id = u.id
                WHERE b.status = 'paid' AND DATE(b.created_at) BETWEEN ? AND ?
                GROUP BY b.cashier_id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        $cashiers = $stmt->fetchAll();

        $stmtDaily = $this->db->prepare(
            "SELECT 
                DATE(b.created_at) AS collection_date,
                COUNT(b.id) AS total_bills,
                COALESCE(SUM(CASE WHEN b.payment_method = 'split' THEN b.cash_amount WHEN b.payment_method = 'cash' THEN b.grand_total ELSE 0 END), 0) AS cash_total,
                COALESCE(SUM(CASE WHEN b.payment_method = 'split' THEN b.card_amount WHEN b.payment_method = 'card' THEN b.grand_total ELSE 0 END), 0) AS card_total,
                COALESCE(SUM(CASE WHEN b.payment_method = 'split' THEN b.qr_amount WHEN b.payment_method = 'qr_pay' THEN b.grand_total ELSE 0 END), 0) AS qr_total,
                COALESCE(SUM(b.grand_total), 0) AS day_total
             FROM bills b
             WHERE b.status = 'paid' AND b.cashier_id = ? AND DATE(b.created_at) BETWEEN ? AND ?
             GROUP BY DATE(b.created_at)
             ORDER BY collection_date DESC"
        );

        $dailyStart = ($startDate === $endDate) ? date('Y-m-d', strtotime($startDate . ' -6 days')) : $startDate;
        foreach ($cashiers as &$c) {
            $stmtDaily->execute([$c['cashier_id'], $dailyStart, $endDate]);
            $c['daily_breakdown'] = $stmtDaily->fetchAll();
        }

        return $cashiers;
    }

    public function getTaxReport($startDate, $endDate) {
        $sql = "SELECT b.id, b.created_at, b.subtotal, b.tax_amount, b.grand_total, b.discount_amount, b.payment_method, 
                       u.name as cashier_name, o.table_number
                FROM bills b
                LEFT JOIN users u ON b.cashier_id = u.id
                LEFT JOIN orders o ON b.order_id = o.id
                WHERE b.status = 'paid' AND DATE(b.created_at) BETWEEN ? AND ?
                ORDER BY b.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    public function getProductSalesAnalytics($startDate, $endDate) {
        $sql = "SELECT 
                    p.id, 
                    p.name as product_name, 
                    c.name as category_name,
                    p.price,
                    SUM(ki.quantity) as total_qty_sold,
                    SUM(ki.quantity * p.price) as total_revenue
                FROM kot_items ki
                JOIN kots k ON ki.kot_id = k.id
                JOIN orders o ON k.order_id = o.id
                JOIN bills b ON b.order_id = o.id
                JOIN products p ON ki.product_id = p.id
                JOIN categories c ON p.category_id = c.id
                WHERE b.status = 'paid' AND DATE(b.created_at) BETWEEN ? AND ?
                GROUP BY p.id
                ORDER BY total_qty_sold DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    /**
     * Normalize date/datetime bounds for waiter performance range queries.
     */
    private function normalizeDateTimeBounds($startDateTime, $endDateTime) {
        $start = trim((string)$startDateTime);
        $end = trim((string)$endDateTime);
        if (strlen($start) === 10) {
            $start .= ' 00:00:00';
        } elseif (strlen($start) === 16) {
            $start .= ':00';
        }
        if (strlen($end) === 10) {
            $end .= ' 23:59:59';
        } elseif (strlen($end) === 16) {
            $end .= ':59';
        }
        return [$start, $end];
    }

    public function getWaiterPerformance($startDateTime, $endDateTime) {
        list($start, $end) = $this->normalizeDateTimeBounds($startDateTime, $endDateTime);
        $sql = "SELECT 
                    u.id as waiter_id,
                    u.name as waiter_name,
                    u.username,
                    u.is_active,
                    COUNT(DISTINCT o.id) as total_orders,
                    COUNT(DISTINCT CASE WHEN o.status = 'cancelled' THEN o.id END) as cancelled_orders,
                    COUNT(DISTINCT b.id) as paid_orders,
                    COALESCE(SUM(b.grand_total), 0) as total_revenue,
                    MIN(o.created_at) as first_order_at,
                    MAX(o.created_at) as last_order_at
                FROM users u
                LEFT JOIN orders o ON o.waiter_id = u.id AND o.created_at BETWEEN ? AND ?
                LEFT JOIN bills b ON b.order_id = o.id AND b.status = 'paid'
                WHERE u.role = 'waiter' OR o.waiter_id IS NOT NULL
                GROUP BY u.id
                ORDER BY total_revenue DESC, last_order_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$start, $end]);
        return $stmt->fetchAll();
    }

    /**
     * Get individual orders taken by a waiter within an exact date/time range, sorted by order time.
     */
    public function getWaiterOrdersInRange($waiterId, $startDateTime, $endDateTime, $sortDir = 'DESC') {
        list($start, $end) = $this->normalizeDateTimeBounds($startDateTime, $endDateTime);
        $dir = strtoupper((string)$sortDir) === 'ASC' ? 'ASC' : 'DESC';
        $sql = "SELECT 
                    o.id as order_id,
                    o.table_number,
                    o.order_type,
                    o.status as order_status,
                    o.created_at,
                    b.id as bill_id,
                    b.status as bill_status,
                    b.payment_method,
                    COALESCE(b.grand_total, 0) as grand_total
                FROM orders o
                LEFT JOIN bills b ON b.order_id = o.id AND b.status = 'paid'
                WHERE o.waiter_id = ?
                  AND o.created_at BETWEEN ? AND ?
                ORDER BY o.created_at {$dir}, o.id {$dir}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$waiterId, $start, $end]);
        return $stmt->fetchAll();
    }

    public function getPendingRefunds($startDate = null, $endDate = null) {
        $sql = "SELECT ki.id as item_id, ki.quantity, ki.status as item_status, 
                       p.name as product_name, p.price as base_price,
                       k.kot_number, k.id as kot_id, k.created_at as created_at,
                       o.token_number, o.id as order_id, o.customer_mobile, o.customer_name, o.created_at as order_created_at
                FROM kot_items ki
                JOIN products p ON ki.product_id = p.id
                JOIN kots k ON ki.kot_id = k.id
                JOIN orders o ON k.order_id = o.id
                WHERE ki.refund_status = 'pending' AND ki.status = 'cancelled' AND o.order_type = 'take_away'";
        $params = [];
        if (!empty($startDate) && !empty($endDate)) {
            $sql .= " AND DATE(k.created_at) BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
        }
        $sql .= " ORDER BY ki.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();

        $stmtSettings = $this->db->query("SELECT tax_type, vat_percent, cgst_percent, sgst_percent FROM settings LIMIT 1");
        $settings = $stmtSettings->fetch();
        
        $taxType = $settings['tax_type'] ?? 'none';
        $vatPercent = (float)($settings['vat_percent'] ?? 0);
        $cgstPercent = (float)($settings['cgst_percent'] ?? 0);
        $sgstPercent = (float)($settings['sgst_percent'] ?? 0);
        
        $taxMultiplier = 1.0;
        if ($taxType === 'vat') {
            $taxMultiplier = 1.0 + ($vatPercent / 100.0);
        } else if ($taxType === 'gst') {
            $taxMultiplier = 1.0 + (($cgstPercent + $sgstPercent) / 100.0);
        }

        $grouped = [];
        foreach ($items as $item) {
            $itemTotal = (float)$item['base_price'] * (int)$item['quantity'];
            $item['refund_amount'] = format_price($itemTotal * $taxMultiplier, null, true);
            
            $orderId = $item['order_id'];
            if (!isset($grouped[$orderId])) {
                $grouped[$orderId] = [
                    'order_id' => $orderId,
                    'token_number' => $item['token_number'],
                    'customer_mobile' => $item['customer_mobile'],
                    'customer_name' => $item['customer_name'],
                    'created_at' => $item['created_at'] ?? $item['order_created_at'] ?? null,
                    'total_refund_amount' => 0,
                    'items' => []
                ];
            }
            $grouped[$orderId]['total_refund_amount'] += (float)$item['refund_amount'];
            $grouped[$orderId]['items'][] = $item;
        }

        $result = [];
        foreach ($grouped as $order) {
            $order['total_refund_amount'] = format_price($order['total_refund_amount'], null, true);
            $result[] = $order;
        }

        return $result;
    }

    public function getRefundedItems($startDate, $endDate) {
        $sql = "SELECT ki.id as item_id, ki.quantity, ki.status as item_status, 
                       p.name as product_name, p.price as base_price,
                       k.kot_number, k.id as kot_id,
                       o.token_number, o.id as order_id, o.customer_mobile, o.customer_name
                FROM kot_items ki
                JOIN products p ON ki.product_id = p.id
                JOIN kots k ON ki.kot_id = k.id
                JOIN orders o ON k.order_id = o.id
                WHERE ki.refund_status = 'refunded' AND ki.status = 'cancelled' AND o.order_type = 'take_away'
                AND DATE(k.created_at) BETWEEN ? AND ?
                ORDER BY ki.id DESC LIMIT 100";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        $items = $stmt->fetchAll();

        $stmtSettings = $this->db->query("SELECT tax_type, vat_percent, cgst_percent, sgst_percent FROM settings LIMIT 1");
        $settings = $stmtSettings->fetch();
        
        $taxType = $settings['tax_type'] ?? 'none';
        $vatPercent = (float)($settings['vat_percent'] ?? 0);
        $cgstPercent = (float)($settings['cgst_percent'] ?? 0);
        $sgstPercent = (float)($settings['sgst_percent'] ?? 0);
        
        $taxMultiplier = 1.0;
        if ($taxType === 'vat') {
            $taxMultiplier = 1.0 + ($vatPercent / 100.0);
        } else if ($taxType === 'gst') {
            $taxMultiplier = 1.0 + (($cgstPercent + $sgstPercent) / 100.0);
        }

        $grouped = [];
        foreach ($items as $item) {
            $itemTotal = (float)$item['base_price'] * (int)$item['quantity'];
            $item['refund_amount'] = format_price($itemTotal * $taxMultiplier, null, true);
            
            $orderId = $item['order_id'];
            if (!isset($grouped[$orderId])) {
                $grouped[$orderId] = [
                    'order_id' => $orderId,
                    'token_number' => $item['token_number'],
                    'customer_mobile' => $item['customer_mobile'],
                    'customer_name' => $item['customer_name'],
                    'total_refund_amount' => 0,
                    'items' => []
                ];
            }
            $grouped[$orderId]['total_refund_amount'] += (float)$item['refund_amount'];
            $grouped[$orderId]['items'][] = $item;
        }

        $result = [];
        foreach ($grouped as $order) {
            $order['total_refund_amount'] = format_price($order['total_refund_amount'], null, true);
            $result[] = $order;
        }

        return $result;
    }

    public function processRefund($itemId) {
        $stmt = $this->db->prepare("UPDATE kot_items SET refund_status = 'refunded' WHERE id = ?");
        return $stmt->execute([$itemId]);
    }

    public function processRefundOrder($orderId) {
        $stmt = $this->db->prepare("UPDATE kot_items ki JOIN kots k ON ki.kot_id = k.id SET ki.refund_status = 'refunded' WHERE k.order_id = ? AND ki.refund_status = 'pending'");
        return $stmt->execute([$orderId]);
    }

    public function getOnlineOrdersBreakdown($startDate, $endDate = null) {
        if ($endDate === null) {
            $endDate = $startDate;
        }
        
        // Group by order first to calculate precise totals per order
        $sql = "SELECT 
                    o.id as order_id,
                    COALESCE(b.platform_id, o.platform_id) as platform_id,
                    COALESCE(op.name, 'Other Online') as platform_name,
                    COALESCE(b.delivery_fee, o.delivery_fee, 0.0) as delivery_fee,
                    COALESCE(b.service_fee, o.service_fee, 0.0) as service_fee,
                    COALESCE(b.discount_amount, o.discount_amount, 0.0) as discount_amount,
                    COALESCE(b.grand_total, 0.0) as bill_grand_total,
                    COALESCE(SUM(COALESCE(ki.price, IF(op.markup_percent > 0, ROUND(p.price * (1 + op.markup_percent / 100), 3), p.price)) * ki.quantity), 0) as food_subtotal
                FROM orders o
                LEFT JOIN bills b ON b.order_id = o.id
                LEFT JOIN online_platforms op ON COALESCE(b.platform_id, o.platform_id) = op.id
                JOIN kots k ON k.order_id = o.id
                JOIN kot_items ki ON ki.kot_id = k.id
                JOIN products p ON ki.product_id = p.id
                WHERE (o.order_type = 'online' OR b.payment_method = 'online' OR (b.payment_method = 'split' AND b.online_amount > 0) OR o.platform_id IS NOT NULL) 
                  AND o.status IN ('closed', 'completed') 
                  AND ki.status != 'cancelled'
                  AND DATE(o.created_at) BETWEEN ? AND ?
                GROUP BY o.id, COALESCE(b.platform_id, o.platform_id), op.name, b.delivery_fee, o.delivery_fee, b.service_fee, o.service_fee, b.discount_amount, o.discount_amount, b.grand_total";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        $orders = $stmt->fetchAll();
        
        $settingsModel = new Setting();
        $settings = $settingsModel->getSettings();
        $taxType = $settings['tax_type'] ?? 'VAT';
        $taxPercent = ($taxType === 'VAT') ? (float)($settings['vat_percent'] ?? 10.0) : ((float)($settings['cgst_percent'] ?? 2.5) + (float)($settings['sgst_percent'] ?? 2.5));

        $platformsMap = [];
        $summaryTotals = [
            'order_count'     => 0,
            'food_subtotal'   => 0.0,
            'delivery_fee'    => 0.0,
            'discount_amount' => 0.0,
            'tax_amount'      => 0.0,
            'service_fee'     => 0.0,
            'gross_total'     => 0.0,
            'net_total'       => 0.0
        ];

        foreach ($orders as $ord) {
            $platName = $ord['platform_name'];
            $foodSub = (float)$ord['food_subtotal'];
            $discount = min((float)$ord['discount_amount'], $foodSub);
            $deliveryFee = (float)$ord['delivery_fee'];
            $serviceFee = (float)$ord['service_fee'];
            $billGrand = (float)$ord['bill_grand_total'];
            
            // Formula confirmed from platform invoice:
            // Grand Total = Subtotal (Food) + Service Fee + Delivery Fee - Discount
            // Tax is included in item prices ("VAT (Incl.)")
            $tax = max(0.0, $foodSub - $discount) * ($taxPercent / (100.0 + $taxPercent));
            $gross = ($billGrand > 0) ? $billGrand : max(0.0, ($foodSub - $discount) + $serviceFee + $deliveryFee);
            $net = $gross;

            if (!isset($platformsMap[$platName])) {
                $platformsMap[$platName] = [
                    'platform_name'   => $platName,
                    'order_count'     => 0,
                    'food_subtotal'   => 0.0,
                    'delivery_fee'    => 0.0,
                    'discount_amount' => 0.0,
                    'tax_amount'      => 0.0,
                    'service_fee'     => 0.0,
                    'gross_total'     => 0.0,
                    'net_total'       => 0.0,
                    'total'           => 0.0
                ];
            }

            $platformsMap[$platName]['order_count'] += 1;
            $platformsMap[$platName]['food_subtotal'] += $foodSub;
            $platformsMap[$platName]['delivery_fee'] += $deliveryFee;
            $platformsMap[$platName]['discount_amount'] += $discount;
            $platformsMap[$platName]['tax_amount'] += $tax;
            $platformsMap[$platName]['service_fee'] += $serviceFee;
            $platformsMap[$platName]['gross_total'] += $gross;
            $platformsMap[$platName]['net_total'] += $net;
            $platformsMap[$platName]['total'] += $gross;

            $summaryTotals['order_count'] += 1;
            $summaryTotals['food_subtotal'] += $foodSub;
            $summaryTotals['delivery_fee'] += $deliveryFee;
            $summaryTotals['discount_amount'] += $discount;
            $summaryTotals['tax_amount'] += $tax;
            $summaryTotals['service_fee'] += $serviceFee;
            $summaryTotals['gross_total'] += $gross;
            $summaryTotals['net_total'] += $net;
        }

        return [
            'total'          => $summaryTotals['gross_total'],
            'gross_total'    => $summaryTotals['gross_total'],
            'net_total'      => $summaryTotals['net_total'],
            'summary_totals' => $summaryTotals,
            'breakdown'      => array_values($platformsMap)
        ];
    }

    public function getOnlineProviderCollections($startDate, $endDate = null) {
        if ($endDate === null) {
            $endDate = $startDate;
        }
        $sql = "SELECT 
                    COALESCE(op.name, 'Other Online') as platform_name,
                    COALESCE(op.id, 0) as platform_id,
                    COUNT(b.id) as total_bills,
                    COALESCE(SUM(CASE WHEN b.payment_method = 'split' THEN b.online_amount WHEN b.payment_method = 'online' THEN b.grand_total ELSE 0 END), 0) as total_collected,
                    COALESCE(SUM(COALESCE(b.delivery_fee, 0.0)), 0) as total_delivery_fee,
                    COALESCE(SUM(COALESCE(b.service_fee, 0.0)), 0) as total_service_fee,
                    COALESCE(SUM(COALESCE(b.discount_amount, 0.0)), 0) as total_discount
                FROM bills b
                JOIN orders o ON b.order_id = o.id
                LEFT JOIN online_platforms op ON COALESCE(b.platform_id, o.platform_id) = op.id
                WHERE b.status = 'paid' 
                  AND (b.payment_method = 'online' OR (b.payment_method = 'split' AND b.online_amount > 0) OR o.order_type = 'online')
                  AND DATE(b.created_at) BETWEEN ? AND ?
                GROUP BY COALESCE(op.name, 'Other Online'), COALESCE(op.id, 0)
                ORDER BY total_collected DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    public function getTotalCollectionReport($startDateTime, $endDateTime, $cashierId = null) {
        // Query all paid bills in this date & time range
        $sql = "SELECT 
                    b.id as bill_id,
                    CONCAT('INV-', b.id) as bill_number,
                    b.order_id,
                    b.subtotal,
                    b.tax_amount,
                    COALESCE(b.discount_amount, o.discount_amount, 0) as discount_amount,
                    COALESCE(b.delivery_fee, o.delivery_fee, 0) as delivery_fee,
                    COALESCE(b.service_fee, o.service_fee, 0) as service_fee,
                    b.grand_total,
                    b.payment_method,
                    b.cash_amount,
                    b.card_amount,
                    b.qr_amount,
                    b.online_amount,
                    b.cashier_id,
                    u.name as cashier_name,
                    b.created_at as bill_created_at,
                    o.id as order_number,
                    o.order_type,
                    o.token_number,
                    o.customer_name,
                    o.customer_mobile,
                    o.table_number,
                    o.platform_order_number,
                    COALESCE(op.name, 'Other Online') as platform_name,
                    COALESCE(op.id, 0) as platform_id
                FROM bills b
                JOIN orders o ON b.order_id = o.id
                LEFT JOIN users u ON b.cashier_id = u.id
                LEFT JOIN online_platforms op ON COALESCE(b.platform_id, o.platform_id) = op.id
                WHERE b.status = 'paid'
                  AND b.created_at BETWEEN :start AND :end";

        $params = [
            'start' => $startDateTime,
            'end'   => $endDateTime
        ];
        if ($cashierId !== null && $cashierId !== '' && $cashierId !== 'all') {
            $sql .= " AND b.cashier_id = :cashier_id";
            $params['cashier_id'] = $cashierId;
        }
        $sql .= " ORDER BY b.created_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $cash = ['name' => 'Cash', 'total' => 0.0, 'count' => 0, 'items' => []];
        $card = ['name' => 'Card', 'total' => 0.0, 'count' => 0, 'items' => []];
        $qr   = ['name' => 'QR Pay / BenefitPay', 'total' => 0.0, 'count' => 0, 'items' => []];
        $onlineProviders = [];
        $splits = [];

        $grandTotal = 0.0;
        $totalBillsCount = count($rows);

        foreach ($rows as $row) {
            $billTotal = (float)$row['grand_total'];
            $grandTotal += $billTotal;
            $method = $row['payment_method'];

            $ref = '';
            if ($row['order_type'] === 'online') {
                $ref = $row['platform_name'] . ($row['platform_order_number'] ? ' #' . $row['platform_order_number'] : '');
            } elseif ($row['order_type'] === 'take_away') {
                $ref = 'Token #' . ($row['token_number'] ?: $row['order_id']);
            } else {
                $ref = $row['table_number'] ? 'Table ' . $row['table_number'] : 'Dine In';
            }
            if (!empty($row['customer_name'])) {
                $ref .= ' - ' . $row['customer_name'];
            }

            $baseItem = [
                'bill_id' => (int)$row['bill_id'],
                'bill_number' => $row['bill_number'] ?: ('INV-' . $row['bill_id']),
                'order_id' => (int)$row['order_id'],
                'order_type' => $row['order_type'],
                'reference' => $ref,
                'cashier_name' => $row['cashier_name'] ?: 'Staff',
                'created_at' => $row['bill_created_at'],
                'created_at_formatted' => date('d M Y, h:i A', strtotime($row['bill_created_at'])),
                'subtotal' => (float)$row['subtotal'],
                'tax_amount' => (float)$row['tax_amount'],
                'discount_amount' => (float)$row['discount_amount'],
                'delivery_fee' => (float)$row['delivery_fee'],
                'service_fee' => (float)$row['service_fee'],
                'grand_total' => $billTotal,
                'payment_method' => $method,
                'split_note' => null
            ];

            if ($method === 'cash') {
                $item = $baseItem;
                $item['amount'] = $billTotal;
                $cash['items'][] = $item;
                $cash['total'] += $billTotal;
                $cash['count']++;
            } elseif ($method === 'card') {
                $item = $baseItem;
                $item['amount'] = $billTotal;
                $card['items'][] = $item;
                $card['total'] += $billTotal;
                $card['count']++;
            } elseif ($method === 'qr_pay') {
                $item = $baseItem;
                $item['amount'] = $billTotal;
                $qr['items'][] = $item;
                $qr['total'] += $billTotal;
                $qr['count']++;
            } elseif ($method === 'online' || $row['order_type'] === 'online') {
                $pName = !empty($row['platform_name']) ? $row['platform_name'] : 'Other Online';
                if (!isset($onlineProviders[$pName])) {
                    $onlineProviders[$pName] = [
                        'name' => $pName,
                        'total' => 0.0,
                        'count' => 0,
                        'food_subtotal' => 0.0,
                        'service_fee' => 0.0,
                        'delivery_fee' => 0.0,
                        'discount_amount' => 0.0,
                        'items' => []
                    ];
                }
                $item = $baseItem;
                $item['amount'] = $billTotal;
                $item['platform_name'] = $pName;
                $onlineProviders[$pName]['items'][] = $item;
                $onlineProviders[$pName]['total'] += $billTotal;
                $onlineProviders[$pName]['food_subtotal'] += (float)$row['subtotal'];
                $onlineProviders[$pName]['service_fee'] += (float)$row['service_fee'];
                $onlineProviders[$pName]['delivery_fee'] += (float)$row['delivery_fee'];
                $onlineProviders[$pName]['discount_amount'] += (float)$row['discount_amount'];
                $onlineProviders[$pName]['count']++;
            } elseif ($method === 'split') {
                $cAmount = (float)$row['cash_amount'];
                $kAmount = (float)$row['card_amount'];
                $qAmount = (float)$row['qr_amount'];
                $oAmount = (float)$row['online_amount'];

                if ($cAmount > 0) {
                    $item = $baseItem;
                    $item['amount'] = $cAmount;
                    $item['split_note'] = 'Split (Cash ' . number_format($cAmount, 3) . ' of ' . number_format($billTotal, 3) . ')';
                    $cash['items'][] = $item;
                    $cash['total'] += $cAmount;
                    $cash['count']++;
                }
                if ($kAmount > 0) {
                    $item = $baseItem;
                    $item['amount'] = $kAmount;
                    $item['split_note'] = 'Split (Card ' . number_format($kAmount, 3) . ' of ' . number_format($billTotal, 3) . ')';
                    $card['items'][] = $item;
                    $card['total'] += $kAmount;
                    $card['count']++;
                }
                if ($qAmount > 0) {
                    $item = $baseItem;
                    $item['amount'] = $qAmount;
                    $item['split_note'] = 'Split (QR ' . number_format($qAmount, 3) . ' of ' . number_format($billTotal, 3) . ')';
                    $qr['items'][] = $item;
                    $qr['total'] += $qAmount;
                    $qr['count']++;
                }
                if ($oAmount > 0) {
                    $pName = !empty($row['platform_name']) ? $row['platform_name'] : 'Other Online';
                    if (!isset($onlineProviders[$pName])) {
                        $onlineProviders[$pName] = [
                            'name' => $pName,
                            'total' => 0.0,
                            'count' => 0,
                            'food_subtotal' => 0.0,
                            'service_fee' => 0.0,
                            'delivery_fee' => 0.0,
                            'discount_amount' => 0.0,
                            'items' => []
                        ];
                    }
                    $item = $baseItem;
                    $item['amount'] = $oAmount;
                    $item['split_note'] = 'Split (Online ' . number_format($oAmount, 3) . ' of ' . number_format($billTotal, 3) . ')';
                    $item['platform_name'] = $pName;
                    $onlineProviders[$pName]['items'][] = $item;
                    $onlineProviders[$pName]['total'] += $oAmount;
                    $onlineProviders[$pName]['food_subtotal'] += (float)$row['subtotal'];
                    $onlineProviders[$pName]['service_fee'] += (float)$row['service_fee'];
                    $onlineProviders[$pName]['delivery_fee'] += (float)$row['delivery_fee'];
                    $onlineProviders[$pName]['discount_amount'] += (float)$row['discount_amount'];
                    $onlineProviders[$pName]['count']++;
                }

                $splitItem = $baseItem;
                $splitItem['cash_amount'] = $cAmount;
                $splitItem['card_amount'] = $kAmount;
                $splitItem['qr_amount'] = $qAmount;
                $splitItem['online_amount'] = $oAmount;
                $splits[] = $splitItem;
            } else {
                $item = $baseItem;
                $item['amount'] = $billTotal;
                $cash['items'][] = $item;
                $cash['total'] += $billTotal;
                $cash['count']++;
            }
        }

        $onlineTotalAll = 0.0;
        foreach ($onlineProviders as $p) {
            $onlineTotalAll += (float)$p['total'];
        }

        return [
            'period' => [
                'start' => $startDateTime,
                'end'   => $endDateTime,
                'start_formatted' => date('d M Y, h:i A', strtotime($startDateTime)),
                'end_formatted'   => date('d M Y, h:i A', strtotime($endDateTime))
            ],
            'summary' => [
                'grand_total'  => round($grandTotal, 3),
                'cash_total'   => round($cash['total'], 3),
                'card_total'   => round($card['total'], 3),
                'qr_total'     => round($qr['total'], 3),
                'online_total' => round($onlineTotalAll, 3),
                'total_bills'  => $totalBillsCount,
            ],
            'categories' => [
                'cash'             => $cash,
                'card'             => $card,
                'qr'               => $qr,
                'online_providers' => array_values($onlineProviders),
                'splits'           => $splits
            ]
        ];
    }
}
