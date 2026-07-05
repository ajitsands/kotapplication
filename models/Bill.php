<?php

class Bill extends Model {
    public function getPendingBills() {
        $stmt = $this->db->query("SELECT b.*, o.table_number, IF(u.name LIKE 'Waiter %', SUBSTRING(u.name, 8), u.name) as waiter_name, o.created_at as order_created_at
                                  FROM bills b
                                  JOIN orders o ON b.order_id = o.id
                                  LEFT JOIN users u ON o.waiter_id = u.id
                                  WHERE b.status = 'pending'
                                  ORDER BY b.created_at DESC");
        return $stmt->fetchAll();
    }

    public function getBillDetails($billId) {
        $stmt = $this->db->prepare("SELECT b.*, o.table_number, IF(u.name LIKE 'Waiter %', SUBSTRING(u.name, 8), u.name) as waiter_name, o.id as order_id
                                    FROM bills b
                                    JOIN orders o ON b.order_id = o.id
                                    LEFT JOIN users u ON o.waiter_id = u.id
                                    WHERE b.id = ?");
        $stmt->execute([$billId]);
        $bill = $stmt->fetch();

        if ($bill) {
            // Fetch item summary
            $sqlItems = "SELECT p.name as product_name, p.price, SUM(ki.quantity) as total_quantity, (p.price * SUM(ki.quantity)) as subtotal_price 
                         FROM kot_items ki 
                         JOIN kots k ON ki.kot_id = k.id 
                         JOIN products p ON ki.product_id = p.id 
                         WHERE k.order_id = ? 
                         GROUP BY ki.product_id";
            $stmtItems = $this->db->prepare($sqlItems);
            $stmtItems->execute([$bill['order_id']]);
            $bill['items'] = $stmtItems->fetchAll();
        }

        return $bill;
    }

    public function payBill($billId, $paymentMethod, $discountPercent = 0.00, $cashierId = null, $customerName = '', $customerMobile = '', $gender = '') {
        $this->db->beginTransaction();
        try {
            // Get bill details
            $stmt = $this->db->prepare("SELECT * FROM bills WHERE id = ?");
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

            // Update bill status, payment method, discount, grand total, cashier_id, and customer_id
            $stmtBill = $this->db->prepare("UPDATE bills SET status = 'paid', payment_method = ?, discount_percent = ?, discount_amount = ?, grand_total = ?, cashier_id = ?, customer_id = ? WHERE id = ?");
            $stmtBill->execute([$paymentMethod, $discountPercent, $discountAmount, $newGrandTotal, $cashierId, $customerId, $billId]);

            // Update order status to completed
            $stmtOrder = $this->db->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
            $stmtOrder->execute([$bill['order_id']]);

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
                // Set order status back to active
                $stmtOrder = $this->db->prepare("UPDATE orders SET status = 'active' WHERE id = ?");
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
            $stmtSum = $this->db->prepare("SELECT SUM(p.price * ki.quantity) 
                                           FROM kot_items ki 
                                           JOIN kots k ON ki.kot_id = k.id 
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
                    COALESCE(SUM(CASE WHEN payment_method = 'cash' THEN grand_total ELSE 0 END), 0) as cash_total,
                    COALESCE(SUM(CASE WHEN payment_method = 'card' THEN grand_total ELSE 0 END), 0) as card_total,
                    COALESCE(SUM(CASE WHEN payment_method = 'qr_pay' THEN grand_total ELSE 0 END), 0) as qr_total,
                    COALESCE(SUM(grand_total), 0) as grand_total
                FROM bills 
                WHERE status = 'paid' AND DATE(created_at) BETWEEN ? AND ?";
                
        $params = [$startDate, $endDate];
        if ($userId !== null) {
            $sql .= " AND cashier_id = ?";
            $params[] = $userId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function getCashiersBreakdown($startDate, $endDate = null) {
        if ($endDate === null) {
            $endDate = $startDate;
        }
        // Query to get summary per cashier in date range
        $sql = "SELECT 
                    u.name as cashier_name,
                    COALESCE(SUM(CASE WHEN b.payment_method = 'cash' THEN b.grand_total ELSE 0 END), 0) as cash_total,
                    COALESCE(SUM(CASE WHEN b.payment_method = 'card' THEN b.grand_total ELSE 0 END), 0) as card_total,
                    COALESCE(SUM(CASE WHEN b.payment_method = 'qr_pay' THEN b.grand_total ELSE 0 END), 0) as qr_total,
                    COALESCE(SUM(b.grand_total), 0) as grand_total
                FROM bills b
                JOIN users u ON b.cashier_id = u.id
                WHERE b.status = 'paid' AND DATE(b.created_at) BETWEEN ? AND ?
                GROUP BY b.cashier_id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
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

    public function getWaiterPerformance($startDate, $endDate) {
        $sql = "SELECT 
                    u.id as waiter_id,
                    u.name as waiter_name,
                    u.username,
                    u.is_active,
                    COUNT(DISTINCT o.id) as total_orders,
                    COUNT(DISTINCT b.id) as paid_orders,
                    COALESCE(SUM(b.grand_total), 0) as total_revenue
                FROM users u
                LEFT JOIN orders o ON o.waiter_id = u.id AND DATE(o.created_at) BETWEEN ? AND ?
                LEFT JOIN bills b ON b.order_id = o.id AND b.status = 'paid'
                WHERE u.role = 'waiter' OR o.waiter_id IS NOT NULL
                GROUP BY u.id
                ORDER BY total_revenue DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }
}
