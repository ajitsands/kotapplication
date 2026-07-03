<?php

class Kot extends Model {
    public function createKot($orderId, $waiterId, $items) {
        $this->db->beginTransaction();
        try {
            $kotNumber = 'KOT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
            
            $stmt = $this->db->prepare("INSERT INTO kots (order_id, waiter_id, kot_number, status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$orderId, $waiterId, $kotNumber]);
            $kotId = $this->db->lastInsertId();

            $stmtItem = $this->db->prepare("INSERT INTO kot_items (kot_id, product_id, quantity, notes, status) VALUES (?, ?, ?, ?, 'pending')");
            foreach ($items as $item) {
                $stmtItem->execute([
                    $kotId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['notes'] ?? null
                ]);
            }

            $this->db->commit();
            return $kotId;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
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
        $stmt = $this->db->query("SELECT k.*, o.table_number, IF(u.name LIKE 'Waiter %', SUBSTRING(u.name, 8), u.name) as waiter_name 
                                  FROM kots k 
                                  JOIN orders o ON k.order_id = o.id 
                                  LEFT JOIN users u ON k.waiter_id = u.id 
                                  WHERE k.status != 'dispatched' AND o.status = 'active'
                                  ORDER BY k.created_at ASC");
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

    public function markItemReady($kotItemId) {
        $this->db->beginTransaction();
        try {
            // Update item status
            $stmt = $this->db->prepare("UPDATE kot_items SET status = 'ready' WHERE id = ?");
            $stmt->execute([$kotItemId]);

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
            $stmt = $this->db->prepare("UPDATE kots SET status = 'ready' WHERE id = ?");
            $stmt->execute([$kotId]);

            $stmtItems = $this->db->prepare("UPDATE kot_items SET status = 'ready' WHERE kot_id = ? AND status = 'pending'");
            $stmtItems->execute([$kotId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
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

    public function getWaiterNotifications($waiterId) {
        // Fetch ready items that waiter should deliver
        $sql = "SELECT ki.id as kot_item_id, p.name as product_name, ki.quantity, o.table_number, k.kot_number, ki.notes
                FROM kot_items ki
                JOIN kots k ON ki.kot_id = k.id
                JOIN orders o ON k.order_id = o.id
                JOIN products p ON ki.product_id = p.id
                WHERE o.status = 'active' 
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
                return false;
            }

            $kotId = $item['kot_id'];

            // Delete the item
            $stmtDel = $this->db->prepare("DELETE FROM kot_items WHERE id = ?");
            $stmtDel->execute([$kotItemId]);

            // Check if any items remain in this KOT
            $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM kot_items WHERE kot_id = ?");
            $stmtCount->execute([$kotId]);
            $remaining = $stmtCount->fetchColumn();

            if ($remaining == 0) {
                // If no items remain, delete the KOT ticket itself
                $stmtDelKot = $this->db->prepare("DELETE FROM kots WHERE id = ?");
                $stmtDelKot->execute([$kotId]);
            } else {
                // Check if all remaining items are ready
                $stmtCheckReady = $this->db->prepare("SELECT COUNT(*) FROM kot_items WHERE kot_id = ? AND status = 'pending'");
                $stmtCheckReady->execute([$kotId]);
                $pendingCount = $stmtCheckReady->fetchColumn();

                if ($pendingCount == 0) {
                    // Update KOT to ready if all remaining items are ready
                    $stmtUpdateKot = $this->db->prepare("UPDATE kots SET status = 'ready' WHERE id = ?");
                    $stmtUpdateKot->execute([$kotId]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
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
                return false;
            }

            // Delete the KOT (will cascade delete items)
            $stmtDel = $this->db->prepare("DELETE FROM kots WHERE id = ?");
            $stmtDel->execute([$kotId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getCompletedKots($limit = 20) {
        $sql = "SELECT k.*, o.table_number, IF(u.name LIKE 'Waiter %', SUBSTRING(u.name, 8), u.name) as waiter_name 
                FROM kots k 
                JOIN orders o ON k.order_id = o.id 
                LEFT JOIN users u ON k.waiter_id = u.id 
                WHERE k.status = 'dispatched'
                ORDER BY k.created_at DESC";
        
        if (is_numeric($limit)) {
            $limitVal = (int)$limit;
            $sql .= " LIMIT " . $limitVal;
        } else {
            // ALL: set high limit to prevent database memory exhaustion while returning substantial history
            $sql .= " LIMIT 1000";
        }

        $stmt = $this->db->query($sql);
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
