<?php

class Delivery extends Model {
    public function __construct() {
        parent::__construct();
        $this->ensureSchema();
    }

    private function ensureSchema() {
        try {
            $this->db->exec("CREATE TABLE IF NOT EXISTS `deliveries` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `order_id` INT NOT NULL,
                `bill_id` INT NULL,
                `driver_id` INT NULL,
                `customer_name` VARCHAR(150) NULL,
                `customer_mobile` VARCHAR(50) NOT NULL,
                `customer_address` TEXT NULL,
                `google_maps_link` VARCHAR(500) NULL,
                `delivery_notes` TEXT NULL,
                `payment_type` ENUM('paid', 'cod') DEFAULT 'paid',
                `cod_amount` DECIMAL(10,3) DEFAULT 0.000,
                `cod_collected` TINYINT(1) DEFAULT 0,
                `cod_handed_over` TINYINT(1) DEFAULT 0,
                `handover_session_id` INT NULL,
                `status` ENUM('pending_pickup', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'pending_pickup',
                `commission_rate` DECIMAL(5,2) DEFAULT 0.00,
                `commission_amount` DECIMAL(10,3) DEFAULT 0.000,
                `commission_settled` TINYINT(1) DEFAULT 0,
                `created_by` INT NULL,
                `accepted_at` DATETIME NULL,
                `delivered_at` DATETIME NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (`status`),
                INDEX (`driver_id`),
                INDEX (`order_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $this->db->exec("CREATE TABLE IF NOT EXISTS `driver_wallet_ledger` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `driver_id` INT NOT NULL,
                `entry_type` ENUM('commission_credit', 'commission_payout', 'cod_collected', 'cod_handover') NOT NULL,
                `amount` DECIMAL(10,3) NOT NULL,
                `delivery_id` INT NULL,
                `session_id` INT NULL,
                `notes` VARCHAR(255) NULL,
                `created_by` INT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (`driver_id`),
                INDEX (`entry_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // Migration: Alter status to VARCHAR(50) to support workflow stages
            try {
                $this->db->exec("ALTER TABLE `deliveries` MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'pending_pickup'");
            } catch (Exception $e) {}

            // Migration: Add confirm_token and confirmed_at for customer WhatsApp location confirmation
            try {
                $this->db->exec("ALTER TABLE `deliveries` ADD COLUMN `confirm_token` VARCHAR(64) NULL AFTER `status`, ADD INDEX (`confirm_token`)");
            } catch (Exception $e) {}

            try {
                $this->db->exec("ALTER TABLE `deliveries` ADD COLUMN `confirmed_at` DATETIME NULL AFTER `confirm_token`");
            } catch (Exception $e) {}

            // Migration: Add payment collection breakdown columns
            try {
                $this->db->exec("ALTER TABLE `deliveries` ADD COLUMN `collection_method` VARCHAR(30) NULL DEFAULT 'cash' AFTER `payment_type`");
            } catch (Exception $e) {}
            try {
                $this->db->exec("ALTER TABLE `deliveries` ADD COLUMN `cash_collected_amount` DECIMAL(10,3) NOT NULL DEFAULT 0.000 AFTER `collection_method`");
            } catch (Exception $e) {}
            try {
                $this->db->exec("ALTER TABLE `deliveries` ADD COLUMN `qr_collected_amount` DECIMAL(10,3) NOT NULL DEFAULT 0.000 AFTER `cash_collected_amount`");
            } catch (Exception $e) {}
            try {
                $this->db->exec("ALTER TABLE `deliveries` ADD COLUMN `card_collected_amount` DECIMAL(10,3) NOT NULL DEFAULT 0.000 AFTER `qr_collected_amount`");
            } catch (Exception $e) {}
            try {
                $this->db->exec("ALTER TABLE `deliveries` ADD COLUMN `payment_proof` VARCHAR(255) NULL AFTER `card_collected_amount`");
            } catch (Exception $e) {}
        } catch (Exception $e) {
            error_log("Delivery ensureSchema error: " . $e->getMessage());
        }
    }

    public function createDelivery($data) {
        $stmtSetting = $this->db->query("SELECT delivery_commission_percent FROM settings LIMIT 1");
        $rate = 5.00;
        if ($stmtSetting) {
            $set = $stmtSetting->fetch();
            if (isset($set['delivery_commission_percent'])) {
                $rate = (float)$set['delivery_commission_percent'];
            }
        }

        $status = $data['status'] ?? 'pending_pickup';
        $confirmToken = $data['confirm_token'] ?? null;

        $sql = "INSERT INTO deliveries 
                (order_id, bill_id, customer_name, customer_mobile, customer_address, 
                 google_maps_link, delivery_notes, payment_type, cod_amount, 
                 commission_rate, status, confirm_token, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $res = $stmt->execute([
            (int)$data['order_id'],
            !empty($data['bill_id']) ? (int)$data['bill_id'] : null,
            trim($data['customer_name'] ?? ''),
            trim($data['customer_mobile'] ?? ''),
            trim($data['customer_address'] ?? ''),
            trim($data['google_maps_link'] ?? ''),
            trim($data['delivery_notes'] ?? ''),
            ($data['payment_type'] ?? 'paid') === 'cod' ? 'cod' : 'paid',
            (float)($data['cod_amount'] ?? 0),
            $rate,
            $status,
            $confirmToken,
            !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null
        ]);

        if ($res) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function getDeliveryById($id) {
        $sql = "SELECT d.*, o.token_number, 
                       COALESCE(b.grand_total, d.cod_amount, 0) as order_total,
                       u.name as driver_name, u.username as driver_username
                FROM deliveries d
                JOIN orders o ON d.order_id = o.id
                LEFT JOIN bills b ON d.bill_id = b.id
                LEFT JOIN users u ON d.driver_id = u.id
                WHERE d.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$id]);
        $delivery = $stmt->fetch();
        if ($delivery) {
            $delivery['items'] = $this->getOrderItems($delivery['order_id']);
        }
        return $delivery;
    }

    public function getDeliveryByOrderId($orderId) {
        $sql = "SELECT d.*, u.name as driver_name 
                FROM deliveries d
                LEFT JOIN users u ON d.driver_id = u.id
                WHERE d.order_id = ? 
                ORDER BY d.id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$orderId]);
        return $stmt->fetch();
    }

    public function getAvailableDeliveries() {
        $sql = "SELECT d.*, o.token_number, 
                       COALESCE(b.grand_total, d.cod_amount, 0) as order_total
                FROM deliveries d
                JOIN orders o ON d.order_id = o.id
                LEFT JOIN bills b ON d.bill_id = b.id
                WHERE d.status = 'pending_pickup'
                ORDER BY d.id ASC";
        $stmt = $this->db->query($sql);
        $deliveries = $stmt->fetchAll();

        foreach ($deliveries as &$d) {
            $d['items'] = $this->getOrderItems($d['order_id']);
        }
        return $deliveries;
    }

    public function getActiveDeliveryForDriver($driverId) {
        $sql = "SELECT d.*, o.token_number, 
                       COALESCE(b.grand_total, d.cod_amount, 0) as order_total
                FROM deliveries d
                JOIN orders o ON d.order_id = o.id
                LEFT JOIN bills b ON d.bill_id = b.id
                WHERE d.driver_id = ? AND d.status = 'out_for_delivery'
                ORDER BY d.id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$driverId]);
        $delivery = $stmt->fetch();
        if ($delivery) {
            $delivery['items'] = $this->getOrderItems($delivery['order_id']);
        }
        return $delivery;
    }

    public function claimDelivery($deliveryId, $driverId) {
        // Enforce 1-at-a-time rule: Driver must not have an existing active delivery
        $active = $this->getActiveDeliveryForDriver($driverId);
        if ($active) {
            return [
                'success' => false,
                'error' => "You already have an active delivery (Token #{$active['token_number']}). Please complete it before accepting another."
            ];
        }

        // Atomic lock: Only claim if status is currently 'pending_pickup'
        $stmt = $this->db->prepare("UPDATE deliveries 
                                    SET driver_id = ?, status = 'out_for_delivery', accepted_at = NOW() 
                                    WHERE id = ? AND status = 'pending_pickup'");
        $stmt->execute([(int)$driverId, (int)$deliveryId]);

        if ($stmt->rowCount() > 0) {
            return ['success' => true];
        } else {
            return [
                'success' => false,
                'error' => 'This delivery has already been accepted by another driver or is no longer available.'
            ];
        }
    }

    public function markDelivered($deliveryId, $driverId, $notes = '', $paymentData = []) {
        $sql = "SELECT d.*, 
                       COALESCE(b.grand_total, d.cod_amount, 0) as order_total 
                FROM deliveries d
                JOIN orders o ON d.order_id = o.id
                LEFT JOIN bills b ON d.bill_id = b.id
                WHERE d.id = ? AND d.driver_id = ? AND d.status = 'out_for_delivery'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$deliveryId, (int)$driverId]);
        $d = $stmt->fetch();

        if (!$d) {
            return ['success' => false, 'error' => 'Delivery not found or not currently active for you.'];
        }

        // Calculate commission
        $baseAmount = (float)$d['order_total'];
        $commissionRate = (float)$d['commission_rate'];
        $commissionAmount = round(($baseAmount * ($commissionRate / 100)), 3);

        $isCod = ($d['payment_type'] === 'cod');
        $codAmount = (float)$d['cod_amount'];

        $method = $paymentData['collection_method'] ?? 'cash';
        $cashCollected = 0.0;
        $qrCollected = 0.0;
        $cardCollected = 0.0;
        $codHandedOver = 0;

        if ($isCod) {
            if ($method === 'qr_pay') {
                $qrCollected = $codAmount;
                $codHandedOver = 1; // Direct digital payment, driver owes no cash
            } elseif ($method === 'card') {
                $cardCollected = $codAmount;
                $codHandedOver = 1; // Direct card payment, driver owes no cash
            } elseif ($method === 'split') {
                $cashCollected = max(0, (float)($paymentData['cash_amount'] ?? 0));
                $qrCollected = max(0, (float)($paymentData['qr_amount'] ?? 0));
                $cardCollected = max(0, (float)($paymentData['card_amount'] ?? 0));
                if ($cashCollected <= 0) {
                    $codHandedOver = 1;
                }
            } else {
                $method = 'cash';
                $cashCollected = $codAmount;
            }
        } else {
            // Already prepaid
            $codHandedOver = 1;
        }

        $this->db->beginTransaction();
        try {
            $paymentProof = !empty($paymentData['payment_proof']) ? $paymentData['payment_proof'] : null;

            // Update delivery record
            $stmtUp = $this->db->prepare("UPDATE deliveries 
                                          SET status = 'delivered', 
                                              delivered_at = NOW(),
                                              cod_collected = ?,
                                              cod_handed_over = ?,
                                              collection_method = ?,
                                              cash_collected_amount = ?,
                                              qr_collected_amount = ?,
                                              card_collected_amount = ?,
                                              payment_proof = COALESCE(?, payment_proof),
                                              commission_amount = ?,
                                              delivery_notes = CASE WHEN ? != '' THEN CONCAT(IFNULL(delivery_notes, ''), ' | Note: ', ?) ELSE delivery_notes END
                                          WHERE id = ?");
            $stmtUp->execute([
                $isCod ? 1 : 0,
                $codHandedOver,
                $method,
                $cashCollected,
                $qrCollected,
                $cardCollected,
                $paymentProof,
                $commissionAmount,
                trim($notes),
                trim($notes),
                (int)$deliveryId
            ]);

            // Ledger 1: COD Cash Collected (Debit to driver's cash-in-hand liability)
            // Only the cash collected portion affects driver's wallet!
            if ($isCod && $cashCollected > 0) {
                $stmtLedgerCod = $this->db->prepare("INSERT INTO driver_wallet_ledger 
                                                     (driver_id, entry_type, amount, delivery_id, notes) 
                                                     VALUES (?, 'cod_collected', ?, ?, ?)");
                $stmtLedgerCod->execute([
                    (int)$driverId,
                    $cashCollected,
                    (int)$deliveryId,
                    "COD cash collected for Token #{$d['order_id']} (" . ucfirst($method) . ")"
                ]);
            }

            // Ledger 2: Commission Earned (Credit to driver's earning wallet)
            if ($commissionAmount > 0) {
                $stmtLedgerComm = $this->db->prepare("INSERT INTO driver_wallet_ledger 
                                                      (driver_id, entry_type, amount, delivery_id, notes) 
                                                      VALUES (?, 'commission_credit', ?, ?, ?)");
                $stmtLedgerComm->execute([
                    (int)$driverId,
                    $commissionAmount,
                    (int)$deliveryId,
                    "Delivery commission ({$commissionRate}%)"
                ]);
            }

            // Update bill if exists
            if (!empty($d['bill_id']) && $isCod) {
                if ($method === 'qr_pay') {
                    $stmtBill = $this->db->prepare("UPDATE bills SET status = 'paid', payment_method = 'qr_pay', qr_amount = ?, cash_amount = 0, card_amount = 0, created_at = NOW() WHERE id = ?");
                    $stmtBill->execute([$qrCollected, (int)$d['bill_id']]);
                } elseif ($method === 'card') {
                    $stmtBill = $this->db->prepare("UPDATE bills SET status = 'paid', payment_method = 'card', card_amount = ?, cash_amount = 0, qr_amount = 0, created_at = NOW() WHERE id = ?");
                    $stmtBill->execute([$cardCollected, (int)$d['bill_id']]);
                } elseif ($method === 'split') {
                    $stmtBill = $this->db->prepare("UPDATE bills SET status = 'paid', payment_method = 'split', cash_amount = ?, qr_amount = ?, card_amount = ?, created_at = NOW() WHERE id = ?");
                    $stmtBill->execute([$cashCollected, $qrCollected, $cardCollected, (int)$d['bill_id']]);
                }
            }

            // Also mark the order status as completed/delivered
            $stmtOrder = $this->db->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
            $stmtOrder->execute([(int)$d['order_id']]);

            $this->db->commit();
            return [
                'success' => true,
                'commission_earned' => $commissionAmount,
                'collection_method' => $method,
                'cash_collected' => $cashCollected,
                'qr_collected' => $qrCollected,
                'card_collected' => $cardCollected
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getDriverStats($driverId) {
        $today = date('Y-m-d');
        
        // Today completed deliveries count
        $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM deliveries 
                                         WHERE driver_id = ? AND status = 'delivered' AND DATE(delivered_at) = ?");
        $stmtCount->execute([(int)$driverId, $today]);
        $todayCompleted = (int)$stmtCount->fetchColumn();

        // Cash In Hand = (Total cod_collected) - (Total cod_handover)
        $stmtCash = $this->db->prepare("SELECT 
            SUM(CASE WHEN entry_type = 'cod_collected' THEN amount ELSE 0 END) -
            SUM(CASE WHEN entry_type = 'cod_handover' THEN amount ELSE 0 END) as cash_in_hand
            FROM driver_wallet_ledger WHERE driver_id = ?");
        $stmtCash->execute([(int)$driverId]);
        $cashInHand = max(0, (float)$stmtCash->fetchColumn());

        // Commission Wallet = (Total commission_credit) - (Total commission_payout)
        $stmtComm = $this->db->prepare("SELECT 
            SUM(CASE WHEN entry_type = 'commission_credit' THEN amount ELSE 0 END) -
            SUM(CASE WHEN entry_type = 'commission_payout' THEN amount ELSE 0 END) as commission_balance
            FROM driver_wallet_ledger WHERE driver_id = ?");
        $stmtComm->execute([(int)$driverId]);
        $commissionBalance = max(0, (float)$stmtComm->fetchColumn());

        return [
            'today_completed' => $todayCompleted,
            'cash_in_hand' => $cashInHand,
            'commission_balance' => $commissionBalance
        ];
    }

    public function getDriverDeliveriesHistory($driverId, $startDate = null, $endDate = null, $limit = 100) {
        $sql = "SELECT d.*, o.token_number, 
                       COALESCE(b.grand_total, d.cod_amount, 0) as order_total
                FROM deliveries d
                JOIN orders o ON d.order_id = o.id
                LEFT JOIN bills b ON d.bill_id = b.id
                WHERE d.driver_id = ? AND d.status = 'delivered'";
        $params = [(int)$driverId];

        if (!empty($startDate) && !empty($endDate)) {
            $sql .= " AND DATE(d.delivered_at) BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }

        $sql .= " ORDER BY d.delivered_at DESC LIMIT " . (int)$limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $deliveries = $stmt->fetchAll();

        foreach ($deliveries as &$d) {
            $d['items'] = $this->getOrderItems($d['order_id']);
        }
        return $deliveries;
    }

    public function getCodPendingHandoverSummary() {
        $sql = "SELECT d.driver_id, u.name as driver_name, u.username as driver_username,
                       COUNT(d.id) as orders_count,
                       SUM(CASE WHEN d.collection_method = 'split' THEN d.cash_collected_amount WHEN d.collection_method IN ('qr_pay', 'card') THEN 0 ELSE d.cod_amount END) as total_cash
                FROM deliveries d
                JOIN users u ON d.driver_id = u.id
                WHERE d.payment_type = 'cod' AND d.cod_collected = 1 AND d.cod_handed_over = 0
                GROUP BY d.driver_id, u.name, u.username
                HAVING total_cash > 0
                ORDER BY total_cash DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getCodPendingDeliveriesForDriver($driverId) {
        $sql = "SELECT d.*, o.token_number
                FROM deliveries d
                JOIN orders o ON d.order_id = o.id
                WHERE d.driver_id = ? AND d.payment_type = 'cod' AND d.cod_collected = 1 AND d.cod_handed_over = 0
                ORDER BY d.delivered_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$driverId]);
        return $stmt->fetchAll();
    }

    public function handoverCodCash($driverId, $sessionId = null, $adminUserId = null) {
        $pending = $this->getCodPendingDeliveriesForDriver($driverId);
        if (empty($pending)) {
            return ['success' => false, 'error' => 'No pending cash handover for this driver.'];
        }

        $totalCash = 0;
        $deliveryIds = [];
        foreach ($pending as $p) {
            $cashAmt = ($p['collection_method'] === 'split') ? (float)$p['cash_collected_amount'] : ((!in_array($p['collection_method'], ['qr_pay', 'card'])) ? (float)$p['cod_amount'] : 0);
            $totalCash += $cashAmt;
            $deliveryIds[] = (int)$p['id'];
        }

        $this->db->beginTransaction();
        try {
            // Update deliveries as handed over
            $inClause = implode(',', $deliveryIds);
            $this->db->exec("UPDATE deliveries 
                             SET cod_handed_over = 1, handover_session_id = " . ($sessionId ? (int)$sessionId : "NULL") . " 
                             WHERE id IN ($inClause)");

            // Also mark any associated pending bills as paid in cash
            $cashierVal = $adminUserId ? (int)$adminUserId : "NULL";
            $this->db->exec("UPDATE bills 
                             SET status = 'paid', 
                                 payment_method = 'cash', 
                                 cash_amount = grand_total, 
                                 cashier_id = COALESCE($cashierVal, cashier_id), 
                                 created_at = NOW() 
                             WHERE id IN (SELECT bill_id FROM deliveries WHERE id IN ($inClause) AND bill_id IS NOT NULL) 
                               AND status != 'paid'");

            // Record in ledger
            if ($totalCash > 0) {
                $stmtLedger = $this->db->prepare("INSERT INTO driver_wallet_ledger 
                                                  (driver_id, entry_type, amount, session_id, notes, created_by) 
                                                  VALUES (?, 'cod_handover', ?, ?, ?, ?)");
                $stmtLedger->execute([
                    (int)$driverId,
                    $totalCash,
                    $sessionId ? (int)$sessionId : null,
                    "Handover of $totalCash to Counter (Session #$sessionId)",
                    $adminUserId ? (int)$adminUserId : null
                ]);
            }

            $this->db->commit();
            return ['success' => true, 'total_cash' => $totalCash, 'orders_count' => count($deliveryIds)];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function confirmSingleDeliveryHandover($deliveryId, $sessionId = null, $adminUserId = null) {
        $sql = "SELECT d.*, u.name as driver_name, o.token_number, b.id as bill_id, b.status as bill_status
                FROM deliveries d
                LEFT JOIN users u ON d.driver_id = u.id
                LEFT JOIN orders o ON d.order_id = o.id
                LEFT JOIN bills b ON d.bill_id = b.id
                WHERE d.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$deliveryId]);
        $d = $stmt->fetch();

        if (!$d) {
            return ['success' => false, 'error' => 'Delivery record not found.'];
        }

        if ($d['payment_type'] !== 'cod') {
            return ['success' => false, 'error' => 'This order was already prepaid, no COD collection required.'];
        }

        if ((int)$d['cod_handed_over'] === 1) {
            return ['success' => false, 'error' => 'COD cash for this delivery has already been handed over.'];
        }

        $cashAmount = ($d['collection_method'] === 'split') ? (float)$d['cash_collected_amount'] : ((!in_array($d['collection_method'], ['qr_pay', 'card'])) ? (float)$d['cod_amount'] : 0);
        $driverId = (int)$d['driver_id'];

        $this->db->beginTransaction();
        try {
            // 1. Mark delivery as handed over
            $stmtUp = $this->db->prepare("UPDATE deliveries 
                                          SET cod_collected = 1, 
                                              cod_handed_over = 1, 
                                              handover_session_id = ? 
                                          WHERE id = ?");
            $stmtUp->execute([
                $sessionId ? (int)$sessionId : null,
                (int)$deliveryId
            ]);

            // 2. Log in driver wallet ledger (entry_type = 'cod_handover' -> reduces driver liability)
            if ($driverId > 0 && $cashAmount > 0) {
                $stmtLedger = $this->db->prepare("INSERT INTO driver_wallet_ledger 
                                                  (driver_id, entry_type, amount, delivery_id, session_id, notes, created_by) 
                                                  VALUES (?, 'cod_handover', ?, ?, ?, ?, ?)");
                $stmtLedger->execute([
                    $driverId,
                    $cashAmount,
                    (int)$deliveryId,
                    $sessionId ? (int)$sessionId : null,
                    "COD cash handover for Order #" . ($d['token_number'] ?: $d['order_id']) . " confirmed by Counter",
                    $adminUserId ? (int)$adminUserId : null
                ]);
            }

            // 3. If there is a bill and it was pending, mark bill as paid in cash by this cashier
            if (!empty($d['bill_id'])) {
                $stmtBill = $this->db->prepare("UPDATE bills 
                                                SET status = 'paid', 
                                                    payment_method = 'cash', 
                                                    cash_amount = ?, 
                                                    cashier_id = COALESCE(?, cashier_id), 
                                                    created_at = NOW() 
                                                WHERE id = ? AND status != 'paid'");
                $stmtBill->execute([
                    $cashAmount,
                    $adminUserId ? (int)$adminUserId : null,
                    (int)$d['bill_id']
                ]);
            }

            // 4. Ensure order status is completed
            $stmtOrder = $this->db->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
            $stmtOrder->execute([(int)$d['order_id']]);

            $this->db->commit();
            return [
                'success' => true,
                'cod_amount' => $codAmount,
                'driver_name' => $d['driver_name'],
                'delivery_id' => (int)$deliveryId
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function payoutDriverCommission($driverId, $amount, $notes = '', $adminUserId = null) {
        $amount = (float)$amount;
        if ($amount <= 0) {
            return ['success' => false, 'error' => 'Invalid payout amount.'];
        }

        $stats = $this->getDriverStats($driverId);
        if ($amount > $stats['commission_balance']) {
            return ['success' => false, 'error' => "Payout amount exceeds available commission balance of {$stats['commission_balance']}."];
        }

        $stmt = $this->db->prepare("INSERT INTO driver_wallet_ledger 
                                    (driver_id, entry_type, amount, notes, created_by) 
                                    VALUES (?, 'commission_payout', ?, ?, ?)");
        $res = $stmt->execute([
            (int)$driverId,
            $amount,
            trim($notes) ?: "Commission payout to driver",
            $adminUserId ? (int)$adminUserId : null
        ]);

        return ['success' => (bool)$res];
    }

    public function getDriverLedger($driverId, $startDate = null, $endDate = null) {
        $sql = "SELECT l.*, d.order_id, o.token_number, u.name as created_by_name
                FROM driver_wallet_ledger l
                LEFT JOIN deliveries d ON l.delivery_id = d.id
                LEFT JOIN orders o ON d.order_id = o.id
                LEFT JOIN users u ON l.created_by = u.id
                WHERE l.driver_id = ?";
        $params = [(int)$driverId];

        if (!empty($startDate) && !empty($endDate)) {
            $sql .= " AND DATE(l.created_at) BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }
        $sql .= " ORDER BY l.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getDeliveryReport($startDate = null, $endDate = null, $driverId = null) {
        $sql = "SELECT d.*, o.token_number, 
                       COALESCE(b.grand_total, d.cod_amount, 0) as order_total, 
                       u.name as driver_name, u.username as driver_username
                FROM deliveries d
                JOIN orders o ON d.order_id = o.id
                LEFT JOIN bills b ON d.bill_id = b.id
                LEFT JOIN users u ON d.driver_id = u.id
                WHERE 1=1";
        $params = [];

        if (!empty($startDate) && !empty($endDate)) {
            $sql .= " AND DATE(d.created_at) BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }

        if (!empty($driverId)) {
            $sql .= " AND d.driver_id = ?";
            $params[] = (int)$driverId;
        }

        $sql .= " ORDER BY d.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getDriversPerformanceSummary($startDate = null, $endDate = null) {
        $sql = "SELECT u.id as driver_id, u.name as driver_name, u.username as driver_username,
                       COUNT(d.id) as total_deliveries,
                       SUM(CASE WHEN d.status = 'delivered' THEN 1 ELSE 0 END) as completed_deliveries,
                       SUM(CASE WHEN d.status = 'delivered' THEN COALESCE(b.grand_total, d.cod_amount, 0) ELSE 0 END) as total_delivered_sales,
                       SUM(CASE WHEN d.status = 'delivered' AND d.payment_type = 'cod' THEN d.cod_amount ELSE 0 END) as total_cod_collected,
                       SUM(CASE WHEN d.status = 'delivered' THEN d.commission_amount ELSE 0 END) as total_commission_earned
                FROM users u
                LEFT JOIN deliveries d ON d.driver_id = u.id";
        
        $params = [];
        if (!empty($startDate) && !empty($endDate)) {
            $sql .= " AND DATE(d.created_at) BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
        }
        $sql .= " LEFT JOIN orders o ON d.order_id = o.id
                  LEFT JOIN bills b ON d.bill_id = b.id
                  WHERE u.role = 'delivery'
                  GROUP BY u.id, u.name, u.username
                  ORDER BY completed_deliveries DESC, u.name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // Also fetch current live wallet balances for each driver
        foreach ($rows as &$r) {
            $stats = $this->getDriverStats($r['driver_id']);
            $r['cash_in_hand'] = $stats['cash_in_hand'];
            $r['commission_balance'] = $stats['commission_balance'];
        }
        return $rows;
    }

    private function getOrderItems($orderId) {
        $sql = "SELECT ki.quantity, ki.status, p.name as product_name,
                       COALESCE(ki.price, p.price, 0) as price,
                       COALESCE(ki.price, p.price, 0) as unit_price
                FROM kot_items ki
                JOIN products p ON ki.product_id = p.id
                JOIN kots k ON ki.kot_id = k.id
                WHERE k.order_id = ? AND ki.status != 'cancelled'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$orderId]);
        return $stmt->fetchAll();
    }

    public function getDeliveryByConfirmToken($token) {
        $sql = "SELECT d.*, o.token_number, o.created_at as order_created_at,
                       COALESCE(b.grand_total, d.cod_amount, 0) as order_total
                FROM deliveries d
                JOIN orders o ON d.order_id = o.id
                LEFT JOIN bills b ON d.bill_id = b.id
                WHERE d.confirm_token = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([trim($token)]);
        $delivery = $stmt->fetch();

        if ($delivery) {
            $delivery['items'] = $this->getOrderItems($delivery['order_id']);
        }
        return $delivery;
    }

    public function customerConfirmLocation($token, $googleMapsLink, $address, $notes = '') {
        $d = $this->getDeliveryByConfirmToken($token);
        if (!$d) {
            return ['success' => false, 'error' => 'Invalid or expired confirmation link.'];
        }

        $stmt = $this->db->prepare("UPDATE deliveries 
                                    SET google_maps_link = ?,
                                        customer_address = CASE WHEN ? != '' THEN ? ELSE customer_address END,
                                        delivery_notes = CASE WHEN ? != '' THEN ? ELSE delivery_notes END,
                                        confirmed_at = NOW(),
                                        status = 'location_received'
                                    WHERE confirm_token = ?");
        $stmt->execute([
            trim($googleMapsLink),
            trim($address),
            trim($address),
            trim($notes),
            trim($notes),
            trim($token)
        ]);

        return ['success' => true, 'delivery_id' => $d['id'], 'order_id' => $d['order_id']];
    }

    public function manualUpdateDeliveryLocation($deliveryId, $googleMapsLink, $address, $notes = '') {
        $stmt = $this->db->prepare("UPDATE deliveries 
                                    SET google_maps_link = ?,
                                        customer_address = ?,
                                        delivery_notes = ?,
                                        confirmed_at = COALESCE(confirmed_at, NOW()),
                                        status = 'location_received'
                                    WHERE id = ?");
        $stmt->execute([
            trim($googleMapsLink),
            trim($address),
            trim($notes),
            (int)$deliveryId
        ]);
        return ['success' => true];
    }

    public function getDeliveryRequests() {
        // Fetch orders in confirmation stages or preparing in kitchen
        $sql = "SELECT d.*, o.token_number, o.created_at as order_created_at,
                       COALESCE(b.grand_total, d.cod_amount, 0) as order_total,
                       k.id as kot_id, k.status as kot_status
                FROM deliveries d
                JOIN orders o ON d.order_id = o.id
                LEFT JOIN bills b ON d.bill_id = b.id
                LEFT JOIN kots k ON k.order_id = o.id
                WHERE d.status IN ('pending_confirmation', 'location_received', 'preparing_in_kitchen')
                ORDER BY d.id DESC";
        $stmt = $this->db->query($sql);
        $requests = $stmt->fetchAll();

        foreach ($requests as &$r) {
            $r['items'] = $this->getOrderItems($r['order_id']);
        }
        return $requests;
    }

    public function sendDeliveryRequestToKot($deliveryId, $cashierId = null) {
        $d = $this->getDeliveryById($deliveryId);
        if (!$d) {
            return ['success' => false, 'error' => 'Delivery request not found.'];
        }

        $orderId = (int)$d['order_id'];

        $this->db->beginTransaction();
        try {
            // 1. Activate KOT tickets
            $stmtKot = $this->db->prepare("UPDATE kots SET status = 'pending' WHERE order_id = ?");
            $stmtKot->execute([$orderId]);

            $stmtItems = $this->db->prepare("UPDATE kot_items ki JOIN kots k ON ki.kot_id = k.id SET ki.status = 'pending' WHERE k.order_id = ?");
            $stmtItems->execute([$orderId]);

            // 2. Ensure bill exists
            $stmtBillCheck = $this->db->prepare("SELECT id FROM bills WHERE order_id = ? LIMIT 1");
            $stmtBillCheck->execute([$orderId]);
            $billId = $stmtBillCheck->fetchColumn();

            if (!$billId) {
                // Compute totals from items
                $stmtTot = $this->db->prepare("SELECT SUM(ki.price * ki.quantity) FROM kot_items ki JOIN kots k ON ki.kot_id = k.id WHERE k.order_id = ? AND ki.status != 'cancelled'");
                $stmtTot->execute([$orderId]);
                $subtotal = (float)$stmtTot->fetchColumn();

                $stmtSetting = $this->db->query("SELECT vat_percent, tax_type, cgst_percent, sgst_percent FROM settings LIMIT 1");
                $set = $stmtSetting->fetch();
                $taxRate = (float)($set['vat_percent'] ?? 10.0);
                $taxAmount = round($subtotal * ($taxRate / 100), 3);
                $grandTotal = $subtotal + $taxAmount;

                $stmtInsertBill = $this->db->prepare("INSERT INTO bills (order_id, subtotal, tax_amount, grand_total, payment_method, status, cashier_id) 
                                                     VALUES (?, ?, ?, ?, ?, 'pending', ?)");
                $stmtInsertBill->execute([
                    $orderId,
                    $subtotal,
                    $taxAmount,
                    $grandTotal,
                    $d['payment_type'] === 'cod' ? 'cash' : 'card',
                    $cashierId ? (int)$cashierId : null
                ]);
                $billId = $this->db->lastInsertId();
            }

            // 3. Update delivery status to preparing_in_kitchen
            $stmtDeliv = $this->db->prepare("UPDATE deliveries 
                                             SET status = 'preparing_in_kitchen',
                                                 bill_id = ?
                                             WHERE id = ?");
            $stmtDeliv->execute([(int)$billId, (int)$deliveryId]);

            // 4. Update order status to closed
            $stmtOrder = $this->db->prepare("UPDATE orders SET status = 'closed' WHERE id = ?");
            $stmtOrder->execute([$orderId]);

            $this->db->commit();
            return [
                'success' => true,
                'delivery_id' => $deliveryId,
                'order_id' => $orderId,
                'bill_id' => $billId,
                'token_number' => $d['token_number']
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
