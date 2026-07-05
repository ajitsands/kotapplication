<?php

class Order extends Model {
    public function getActiveOrderByTable($tableNumber) {
        $stmt = $this->db->prepare("SELECT o.*, IF(u.name LIKE 'Waiter %', SUBSTRING(u.name, 8), u.name) as waiter_name 
                                    FROM orders o 
                                    LEFT JOIN users u ON o.waiter_id = u.id 
                                    WHERE o.table_number = ? AND o.status = 'active'");
        $stmt->execute([$tableNumber]);
        return $stmt->fetch();
    }

    public function createOrder($tableNumber, $waiterId) {
        // Check if there is already an active order for this table
        $existing = $this->getActiveOrderByTable($tableNumber);
        if ($existing) {
            return $existing['id'];
        }

        $stmt = $this->db->prepare("INSERT INTO orders (table_number, waiter_id, status) VALUES (?, ?, 'active')");
        $stmt->execute([$tableNumber, $waiterId]);
        return $this->db->lastInsertId();
    }

    public function getOrderItemsSummary($orderId) {
        $sql = "SELECT p.name, p.price, SUM(ki.quantity) as total_quantity, (p.price * SUM(ki.quantity)) as subtotal_price 
                FROM kot_items ki 
                JOIN kots k ON ki.kot_id = k.id 
                JOIN products p ON ki.product_id = p.id 
                WHERE k.order_id = ? 
                GROUP BY ki.product_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function closeOrder($orderId) {
        $this->db->beginTransaction();
        try {
            // Get order details
            $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();

            if (!$order || $order['status'] !== 'active') {
                throw new Exception("Order is not active or not found.");
            }

            // Calculate Subtotal from KOT items
            $items = $this->getOrderItemsSummary($orderId);
            $subtotal = 0.0;
            foreach ($items as $item) {
                $subtotal += (float)$item['subtotal_price'];
            }

            // Get settings for Taxes
            $stmtSettings = $this->db->query("SELECT * FROM settings ORDER BY id DESC LIMIT 1");
            $settings = $stmtSettings->fetch();
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

            // Create Pending Bill
            $stmtBill = $this->db->prepare("INSERT INTO bills (order_id, subtotal, tax_amount, grand_total, status) 
                                            VALUES (?, ?, ?, ?, 'pending')");
            $stmtBill->execute([$orderId, $subtotal, $taxAmount, $grandTotal]);

            // Update order status to closed
            $stmtOrder = $this->db->prepare("UPDATE orders SET status = 'closed' WHERE id = ?");
            $stmtOrder->execute([$orderId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getTablesState() {
        // Query active or closed orders to map state, joining user's name as waiter_name
        $stmt = $this->db->query("SELECT o.table_number, o.status, o.id, IF(u.name LIKE 'Waiter %', SUBSTRING(u.name, 8), u.name) as waiter_name 
                                  FROM orders o 
                                  LEFT JOIN users u ON o.waiter_id = u.id 
                                  WHERE o.status IN ('active', 'closed')");
        $activeOrders = $stmt->fetchAll();
        
        // Fetch all tables from dining_tables
        $stmtTables = $this->db->query("SELECT table_number FROM dining_tables ORDER BY table_number ASC");
        $tablesList = $stmtTables->fetchAll();

        $tableStates = [];
        foreach ($tablesList as $t) {
            $num = (int)$t['table_number'];
            $tableStates[$num] = [
                'table_number' => $num,
                'status' => 'available',
                'order_id' => null,
                'waiter_name' => null
            ];
        }

        foreach ($activeOrders as $order) {
            $tableNum = (int)$order['table_number'];
            if (isset($tableStates[$tableNum])) {
                $tableStates[$tableNum]['status'] = ($order['status'] === 'active') ? 'occupied' : 'billing';
                $tableStates[$tableNum]['order_id'] = (int)$order['id'];
                $tableStates[$tableNum]['waiter_name'] = $order['waiter_name'] ?? 'Self-Order';
            }
        }

        return array_values($tableStates);
    }

    public function addTable($tableNumber) {
        $stmt = $this->db->prepare("INSERT INTO dining_tables (table_number) VALUES (?)");
        return $stmt->execute([$tableNumber]);
    }

    public function deleteTable($tableNumber) {
        $this->db->beginTransaction();
        try {
            // Only allow deleting table if it does not have any active or billing orders
            $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE table_number = ? AND status IN ('active', 'closed')");
            $stmtCheck->execute([$tableNumber]);
            if ($stmtCheck->fetchColumn() > 0) {
                $this->db->rollBack();
                return false;
            }

            // 1. Delete the table
            $stmtDel = $this->db->prepare("DELETE FROM dining_tables WHERE table_number = ?");
            $stmtDel->execute([$tableNumber]);

            // 2. Shift all greater table numbers down by 1 in dining_tables
            $stmtShiftTbl = $this->db->prepare("UPDATE dining_tables SET table_number = table_number - 1 WHERE table_number > ?");
            $stmtShiftTbl->execute([$tableNumber]);

            // 3. Shift all greater table numbers down by 1 in orders to maintain correct mapping
            $stmtShiftOrd = $this->db->prepare("UPDATE orders SET table_number = table_number - 1 WHERE table_number > ?");
            $stmtShiftOrd->execute([$tableNumber]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getEngagedTables() {
        $sql = "SELECT o.id as order_id, o.table_number, o.status as order_status, 
                       IF(u.name LIKE 'Waiter %', SUBSTRING(u.name, 8), u.name) as waiter_name,
                       b.id as bill_id, b.status as bill_status,
                       b.subtotal as bill_subtotal, b.tax_amount as bill_tax_amount, b.grand_total as bill_grand_total
                FROM orders o
                LEFT JOIN users u ON o.waiter_id = u.id
                LEFT JOIN bills b ON b.order_id = o.id AND b.status = 'pending'
                WHERE o.status IN ('active', 'closed')
                ORDER BY o.table_number ASC";
        $stmt = $this->db->query($sql);
        $orders = $stmt->fetchAll();

        foreach ($orders as &$order) {
            if ($order['bill_id'] !== null) {
                // Use stored bill totals
                $order['subtotal'] = (float)$order['bill_subtotal'];
                $order['tax_amount'] = (float)$order['bill_tax_amount'];
                $order['grand_total'] = (float)$order['bill_grand_total'];
            } else {
                // Calculate live totals from KOT items for active orders
                $sqlItems = "SELECT SUM(p.price * ki.quantity) as subtotal
                             FROM kot_items ki
                             JOIN kots k ON ki.kot_id = k.id
                             JOIN products p ON ki.product_id = p.id
                             WHERE k.order_id = ?";
                $stmtItems = $this->db->prepare($sqlItems);
                $stmtItems->execute([$order['order_id']]);
                $res = $stmtItems->fetch();
                
                $subtotal = (float)($res['subtotal'] ?? 0.0);
                
                $settings = getSettings();
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
            }
        }
        
        return $orders;
    }

    public function getOrderDetails($orderId) {
        $stmt = $this->db->prepare("SELECT o.*, IF(u.name LIKE 'Waiter %', SUBSTRING(u.name, 8), u.name) as waiter_name 
                                    FROM orders o 
                                    LEFT JOIN users u ON o.waiter_id = u.id 
                                    WHERE o.id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        
        if ($order) {
            $order['items'] = $this->getOrderItemsSummary($orderId);
            
            // Fetch pending bill ID if it exists
            $stmtBill = $this->db->prepare("SELECT id FROM bills WHERE order_id = ? AND status = 'pending' LIMIT 1");
            $stmtBill->execute([$orderId]);
            $bill = $stmtBill->fetch();
            $order['bill_id'] = $bill ? (int)$bill['id'] : null;
        }
        return $order;
    }
}
