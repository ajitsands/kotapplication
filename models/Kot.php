<?php

class Kot extends Model {
    public function createKot($orderId, $waiterId, $items) {
        $this->db->beginTransaction();
        try {
            $kotNumber = 'KOT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
            
            $stmt = $this->db->prepare("INSERT INTO kots (order_id, waiter_id, kot_number, status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$orderId, $waiterId, $kotNumber]);
            $kotId = $this->db->lastInsertId();

            $stmtItem = $this->db->prepare("INSERT INTO kot_items (kot_id, product_id, quantity, price, notes, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            foreach ($items as $item) {
                $price = isset($item['price']) && $item['price'] !== null ? (float)$item['price'] : null;
                $stmtItem->execute([
                    $kotId,
                    $item['product_id'],
                    $item['quantity'],
                    $price,
                    $item['notes'] ?? null
                ]);
            }

            // Option A: If a pending bill already exists for this order (table already closed for billing),
            // automatically recalculate and update the pending bill so new items reflect in the bill total immediately.
            $this->syncPendingBillTotals($orderId);

            $this->db->commit();
            return $kotId;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function syncPendingBillTotals($orderId) {
        $stmtBillCheck = $this->db->prepare("SELECT id, discount_percent, discount_amount FROM bills WHERE order_id = ? AND status = 'pending' ORDER BY id DESC LIMIT 1");
        $stmtBillCheck->execute([$orderId]);
        $bill = $stmtBillCheck->fetch();
        if (!$bill) {
            return;
        }

        $sqlItems = "SELECT COALESCE(SUM(COALESCE(ki.price, IF(o.order_type = 'online' AND op.markup_percent > 0, ROUND(p.price * (1 + op.markup_percent / 100), 3), p.price)) * ki.quantity), 0.0) as subtotal
                     FROM kot_items ki 
                     JOIN kots k ON ki.kot_id = k.id 
                     JOIN orders o ON k.order_id = o.id
                     LEFT JOIN online_platforms op ON o.platform_id = op.id
                     JOIN products p ON ki.product_id = p.id 
                     WHERE k.order_id = ? AND ki.status != 'cancelled'";
        $stmtSum = $this->db->prepare($sqlItems);
        $stmtSum->execute([$orderId]);
        $subtotal = (float)($stmtSum->fetchColumn() ?: 0.0);

        $stmtSettings = $this->db->query("SELECT * FROM settings ORDER BY id DESC LIMIT 1");
        $settings = $stmtSettings->fetch();
        $taxType = $settings['tax_type'] ?? 'VAT';
        $taxAmount = 0.0;
        if ($taxType === 'VAT') {
            $vatPercent = (float)($settings['vat_percent'] ?? 10.00);
            $taxAmount = $subtotal * ($vatPercent / 100.0);
        } else {
            $cgstPercent = (float)($settings['cgst_percent'] ?? 2.50);
            $sgstPercent = (float)($settings['sgst_percent'] ?? 2.50);
            $taxAmount = $subtotal * (($cgstPercent + $sgstPercent) / 100.0);
        }

        $discountPercent = (float)($bill['discount_percent'] ?? 0.0);
        $discountAmount = 0.0;
        if ($discountPercent > 0) {
            $discountAmount = $subtotal * ($discountPercent / 100.0);
        } else {
            $discountAmount = min((float)($bill['discount_amount'] ?? 0.0), $subtotal);
        }

        $grandTotal = max(0.0, ($subtotal - $discountAmount) + $taxAmount);
        $stmtUpdateBill = $this->db->prepare("UPDATE bills SET subtotal = ?, discount_amount = ?, tax_amount = ?, grand_total = ? WHERE id = ?");
        $stmtUpdateBill->execute([$subtotal, $discountAmount, $taxAmount, $grandTotal, $bill['id']]);
    }

    public function getKotDetails($kotId) {
        $stmt = $this->db->prepare("SELECT k.*, o.table_number, IF(u.name LIKE 'Waiter %', SUBSTRING(u.name, 8), u.name) as waiter_name 
                                    FROM kots k 
                                    JOIN orders o ON k.order_id = o.id 
                                    LEFT JOIN users u ON k.waiter_id = u.id 
                                    WHERE k.id = ?");
        $stmt->execute([$kotId]);
        $kot = $stmt->fetch();

        if ($kot) {
            $stmtItems = $this->db->prepare("SELECT ki.*, p.name as product_name 
                                             FROM kot_items ki 
                                             JOIN products p ON ki.product_id = p.id 
                                             WHERE ki.kot_id = ?");
            $stmtItems->execute([$kotId]);
            $kot['items'] = $stmtItems->fetchAll();
        }

        return $kot;
    }

    public function getActiveKots() {
        // Fetch KOTs that are not fully dispatched
        $stmt = $this->db->query("SELECT k.*, o.table_number, o.order_type, o.token_number, o.platform_order_number, p.name as platform_name, IF(u.name LIKE 'Waiter %', SUBSTRING(u.name, 8), u.name) as waiter_name 
                                  FROM kots k 
                                  JOIN orders o ON k.order_id = o.id 
                                  LEFT JOIN online_platforms p ON o.platform_id = p.id
                                  LEFT JOIN users u ON k.waiter_id = u.id 
                                  WHERE k.status NOT IN ('dispatched', 'cancelled')
                                  AND (
                                      (o.order_type = 'dine_in' AND o.status != 'cancelled') 
                                      OR 
                                      (o.order_type = 'take_away' AND o.status != 'completed' AND (EXISTS (SELECT 1 FROM bills b WHERE b.order_id = o.id AND b.status = 'paid') OR k.waiter_id IS NOT NULL))
                                      OR
                                      (o.order_type = 'online' AND o.status != 'cancelled')
                                  )
                                  ORDER BY k.created_at ASC");
        $kots = $stmt->fetchAll();

        $activeKots = [];
        foreach ($kots as &$kot) {
            $stmtItems = $this->db->prepare("SELECT ki.*, p.name as product_name 
                                             FROM kot_items ki 
                                             JOIN products p ON ki.product_id = p.id 
                                             WHERE ki.kot_id = ? AND ki.status != 'cancelled'");
            $stmtItems->execute([$kot['id']]);
            $kot['items'] = $stmtItems->fetchAll();
            
            if (count($kot['items']) > 0) {
                $activeKots[] = $kot;
            }
        }

        return $activeKots;
    }

    public function markItemReady($kotItemId) {
        $this->db->beginTransaction();
        try {
            $chefId = $_SESSION['user_id'] ?? null;

            $stmtCheckStatus = $this->db->prepare("SELECT status FROM kot_items WHERE id = ?");
            $stmtCheckStatus->execute([$kotItemId]);
            $status = $stmtCheckStatus->fetchColumn();

            if ($status !== 'ready') {
                // Update item status
                $stmt = $this->db->prepare("UPDATE kot_items SET status = 'ready' WHERE id = ?");
                $stmt->execute([$kotItemId]);
                
                $this->deductInventoryForKotItem($kotItemId, $chefId);
            }

            // Get KOT id
            $stmtKotId = $this->db->prepare("SELECT kot_id FROM kot_items WHERE id = ?");
            $stmtKotId->execute([$kotItemId]);
            $kotId = $stmtKotId->fetchColumn();

            // Check if all items in KOT are ready
            $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM kot_items WHERE kot_id = ? AND status = 'pending'");
            $stmtCheck->execute([$kotId]);
            $pendingCount = $stmtCheck->fetchColumn();

            if ($pendingCount == 0) {
                // Mark KOT as ready
                $stmtUpdateKot = $this->db->prepare("UPDATE kots SET status = 'ready' WHERE id = ?");
                $stmtUpdateKot->execute([$kotId]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function markKotReady($kotId) {
        $this->db->beginTransaction();
        try {
            $chefId = $_SESSION['user_id'] ?? null;

            // Find all pending items first
            $stmtFind = $this->db->prepare("SELECT id FROM kot_items WHERE kot_id = ? AND status = 'pending'");
            $stmtFind->execute([$kotId]);
            $pendingItems = $stmtFind->fetchAll(PDO::FETCH_COLUMN);

            $stmt = $this->db->prepare("UPDATE kots SET status = 'ready' WHERE id = ?");
            $stmt->execute([$kotId]);

            $stmtItems = $this->db->prepare("UPDATE kot_items SET status = 'ready' WHERE kot_id = ? AND status = 'pending'");
            $stmtItems->execute([$kotId]);

            foreach ($pendingItems as $itemId) {
                $this->deductInventoryForKotItem($itemId, $chefId);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    private function deductInventoryForKotItem($kotItemId, $chefId) {
        // 1. Get product_id and quantity from kot_items
        $stmt = $this->db->prepare("SELECT product_id, quantity, kot_id FROM kot_items WHERE id = ?");
        $stmt->execute([$kotItemId]);
        $item = $stmt->fetch();
        if (!$item) return;

        // 2. Get kot_number for reference
        $stmtKot = $this->db->prepare("SELECT kot_number FROM kots WHERE id = ?");
        $stmtKot->execute([$item['kot_id']]);
        $kotNumber = $stmtKot->fetchColumn();

        // 3. Get recipe
        $stmtRecipe = $this->db->prepare("SELECT inventory_item_id, quantity_required FROM product_recipes WHERE product_id = ?");
        $stmtRecipe->execute([$item['product_id']]);
        $recipe = $stmtRecipe->fetchAll();

        foreach ($recipe as $ing) {
            $consumeQty = $ing['quantity_required'] * $item['quantity'];
            
            // Update stock
            $stmtUpdate = $this->db->prepare("UPDATE inventory_items SET current_stock = current_stock - ? WHERE id = ?");
            $stmtUpdate->execute([$consumeQty, $ing['inventory_item_id']]);

            // Insert transaction
            $stmtTrans = $this->db->prepare("INSERT INTO inventory_transactions (inventory_item_id, transaction_type, quantity, chef_id, reference_id) VALUES (?, 'consume_kot', ?, ?, ?)");
            $stmtTrans->execute([$ing['inventory_item_id'], -$consumeQty, $chefId, $kotNumber]);
        }
    }

    public function dispatchKotItem($kotItemId) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("UPDATE kot_items SET status = 'dispatched' WHERE id = ?");
            $stmt->execute([$kotItemId]);

            // Get KOT id
            $stmtKotId = $this->db->prepare("SELECT kot_id FROM kot_items WHERE id = ?");
            $stmtKotId->execute([$kotItemId]);
            $kotId = $stmtKotId->fetchColumn();

            // Check if all items in this KOT are dispatched
            $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM kot_items WHERE kot_id = ? AND status != 'dispatched'");
            $stmtCheck->execute([$kotId]);
            $remaining = $stmtCheck->fetchColumn();

            if ($remaining == 0) {
                // Mark KOT as dispatched
                $stmtUpdateKot = $this->db->prepare("UPDATE kots SET status = 'dispatched' WHERE id = ?");
                $stmtUpdateKot->execute([$kotId]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function dispatchKot($kotId) {
        $this->db->beginTransaction();
        try {
            $chefId = $_SESSION['user_id'] ?? null;

            // Find all pending items and deduct inventory if not already deducted
            $stmtFind = $this->db->prepare("SELECT id FROM kot_items WHERE kot_id = ? AND status = 'pending'");
            $stmtFind->execute([$kotId]);
            $pendingItems = $stmtFind->fetchAll(PDO::FETCH_COLUMN);

            foreach ($pendingItems as $itemId) {
                $this->deductInventoryForKotItem($itemId, $chefId);
            }

            // Mark all active/ready items in this KOT as dispatched
            $stmtItems = $this->db->prepare("UPDATE kot_items SET status = 'dispatched' WHERE kot_id = ? AND status != 'cancelled'");
            $stmtItems->execute([$kotId]);

            // Mark the KOT itself as dispatched
            $stmtKot = $this->db->prepare("UPDATE kots SET status = 'dispatched' WHERE id = ?");
            $stmtKot->execute([$kotId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getWaiterNotifications($waiterId) {
        // Fetch ready items that waiter should deliver (even if table bill was already generated)
        $sql = "SELECT ki.id as kot_item_id, p.name as product_name, ki.quantity, o.table_number, k.kot_number, ki.notes
                FROM kot_items ki
                JOIN kots k ON ki.kot_id = k.id
                JOIN orders o ON k.order_id = o.id
                JOIN products p ON ki.product_id = p.id
                WHERE o.status IN ('active', 'closed') 
                  AND o.order_type = 'dine_in'
                  AND (o.waiter_id = ? OR o.waiter_id IS NULL) 
                  AND ki.status = 'ready'
                ORDER BY k.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$waiterId]);
        return $stmt->fetchAll();
    }

    public function deleteKotItem($kotItemId) {
        $this->db->beginTransaction();
        try {
            // First, make sure the item is pending
            $stmtCheck = $this->db->prepare("SELECT kot_id, status FROM kot_items WHERE id = ?");
            $stmtCheck->execute([$kotItemId]);
            $item = $stmtCheck->fetch();

            if (!$item || $item['status'] !== 'pending') {
                $this->db->rollBack();
                return "Item not found or not pending";
            }

            $kotId = $item['kot_id'];
            
            $stmtOrderInfo = $this->db->prepare("SELECT o.id as order_id, o.order_type FROM kots k JOIN orders o ON k.order_id = o.id WHERE k.id = ?");
            $stmtOrderInfo->execute([$kotId]);
            $orderInfo = $stmtOrderInfo->fetch();
            $orderId = $orderInfo['order_id'] ?? null;
            $orderType = $orderInfo['order_type'] ?? 'dine_in';

            if ($orderType === 'take_away') {
                $stmtDel = $this->db->prepare("UPDATE kot_items SET status = 'cancelled', refund_status = 'pending' WHERE id = ?");
                $stmtDel->execute([$kotItemId]);
                
                $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM kot_items WHERE kot_id = ? AND status != 'cancelled'");
                $stmtCount->execute([$kotId]);
                $remaining = $stmtCount->fetchColumn();

                if ($remaining == 0) {
                    $stmtDelKot = $this->db->prepare("UPDATE kots SET status = 'cancelled' WHERE id = ?");
                    $stmtDelKot->execute([$kotId]);
                } else {
                    $stmtCheckReady = $this->db->prepare("SELECT COUNT(*) FROM kot_items WHERE kot_id = ? AND status = 'pending'");
                    $stmtCheckReady->execute([$kotId]);
                    $pendingCount = $stmtCheckReady->fetchColumn();
                    if ($pendingCount == 0) {
                        $stmtUpdateKot = $this->db->prepare("UPDATE kots SET status = 'ready' WHERE id = ?");
                        $stmtUpdateKot->execute([$kotId]);
                    }
                }
            } else {
                // Dine in logic - hard delete
                $stmtDel = $this->db->prepare("DELETE FROM kot_items WHERE id = ?");
                $stmtDel->execute([$kotItemId]);

                $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM kot_items WHERE kot_id = ?");
                $stmtCount->execute([$kotId]);
                $remaining = $stmtCount->fetchColumn();

                if ($remaining == 0) {
                    $stmtDelKot = $this->db->prepare("DELETE FROM kots WHERE id = ?");
                    $stmtDelKot->execute([$kotId]);
                } else {
                    $stmtCheckReady = $this->db->prepare("SELECT COUNT(*) FROM kot_items WHERE kot_id = ? AND status = 'pending'");
                    $stmtCheckReady->execute([$kotId]);
                    $pendingCount = $stmtCheckReady->fetchColumn();
                    if ($pendingCount == 0) {
                        $stmtUpdateKot = $this->db->prepare("UPDATE kots SET status = 'ready' WHERE id = ?");
                        $stmtUpdateKot->execute([$kotId]);
                    }
                }
            }

            if ($orderId) {
                $this->syncPendingBillTotals($orderId);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return "Exception: " . $e->getMessage();
        }
    }

    public function deleteKot($kotId) {
        $this->db->beginTransaction();
        try {
            // Check if the KOT status is pending
            $stmtCheck = $this->db->prepare("SELECT status FROM kots WHERE id = ?");
            $stmtCheck->execute([$kotId]);
            $status = $stmtCheck->fetchColumn();

            if (!$status || $status !== 'pending') {
                $this->db->rollBack();
                return "KOT not found or not pending";
            }
            
            $stmtOrderInfo = $this->db->prepare("SELECT o.id as order_id, o.order_type FROM kots k JOIN orders o ON k.order_id = o.id WHERE k.id = ?");
            $stmtOrderInfo->execute([$kotId]);
            $orderInfo = $stmtOrderInfo->fetch();
            $orderId = $orderInfo['order_id'] ?? null;
            $orderType = $orderInfo['order_type'] ?? 'dine_in';

            if ($orderType === 'take_away') {
                $stmtDelItems = $this->db->prepare("UPDATE kot_items SET status = 'cancelled', refund_status = 'pending' WHERE kot_id = ?");
                $stmtDelItems->execute([$kotId]);
                
                $stmtDelKot = $this->db->prepare("UPDATE kots SET status = 'cancelled' WHERE id = ?");
                $stmtDelKot->execute([$kotId]);
            } else {
                // Delete the KOT (will cascade delete items)
                $stmtDel = $this->db->prepare("DELETE FROM kots WHERE id = ?");
                $stmtDel->execute([$kotId]);
            }

            if ($orderId) {
                $this->syncPendingBillTotals($orderId);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return "Exception: " . $e->getMessage();
        }
    }

    public function getCompletedKots($limit = 20, $date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }

        $sql = "SELECT k.*, o.table_number, o.order_type, o.token_number, o.platform_order_number, p.name as platform_name, IF(u.name LIKE 'Waiter %', SUBSTRING(u.name, 8), u.name) as waiter_name 
                FROM kots k 
                JOIN orders o ON k.order_id = o.id 
                LEFT JOIN online_platforms p ON o.platform_id = p.id
                LEFT JOIN users u ON k.waiter_id = u.id 
                WHERE k.status = 'dispatched'";
        
        $params = [];
        if ($date) {
            $sql .= " AND DATE(k.created_at) = :date";
            $params['date'] = $date;
        }
        
        $sql .= " ORDER BY k.created_at DESC";
        
        if (is_numeric($limit)) {
            $limitVal = (int)$limit;
            $sql .= " LIMIT " . $limitVal;
        } else {
            // ALL: set high limit to prevent database memory exhaustion while returning substantial history
            $sql .= " LIMIT 1000";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $kots = $stmt->fetchAll();

        foreach ($kots as &$kot) {
            $stmtItems = $this->db->prepare("SELECT ki.*, p.name as product_name 
                                             FROM kot_items ki 
                                             JOIN products p ON ki.product_id = p.id 
                                             WHERE ki.kot_id = ?");
            $stmtItems->execute([$kot['id']]);
            $kot['items'] = $stmtItems->fetchAll();
        }

        return $kots;
    }
}
