<?php

class CounterSession extends Model {

    /**
     * Ensure at most one active ('open' or 'close_requested') session exists per cashier.
     * If duplicates exist, consolidate into the earliest session so all transactions remain unified.
     */
    public function consolidateActiveSessions($cashierId) {
        $stmt = $this->db->prepare(
            "SELECT * FROM counter_sessions
             WHERE cashier_id = ? AND status IN ('open', 'close_requested')
             ORDER BY opened_at ASC, id ASC"
        );
        $stmt->execute([$cashierId]);
        $sessions = $stmt->fetchAll();

        if (empty($sessions)) {
            return false;
        }

        $primary = $sessions[0];
        if (count($sessions) > 1) {
            $hasOpen = false;
            $dupIds = [];
            foreach ($sessions as $idx => $s) {
                if ($s['status'] === 'open') {
                    $hasOpen = true;
                }
                if ($idx > 0) {
                    $dupIds[] = (int)$s['id'];
                }
            }
            if (!empty($dupIds)) {
                $placeholders = implode(',', array_fill(0, count($dupIds), '?'));
                $delStmt = $this->db->prepare("DELETE FROM counter_sessions WHERE id IN ($placeholders)");
                $delStmt->execute($dupIds);
            }
            if ($hasOpen && $primary['status'] !== 'open') {
                $updStmt = $this->db->prepare("UPDATE counter_sessions SET status = 'open', close_requested_at = NULL WHERE id = ?");
                $updStmt->execute([$primary['id']]);
                $primary['status'] = 'open';
                $primary['close_requested_at'] = null;
            }
            $this->refreshSessionTotals($primary['id']);
            return $this->getSession($primary['id']);
        }

        return $primary;
    }

    /**
     * Find an open or close_requested session for a cashier (carry-forward).
     */
    public function getActiveSession($cashierId) {
        return $this->consolidateActiveSessions($cashierId);
    }

    /**
     * Get the latest session for a cashier (regardless of status).
     */
    public function getLastSession($cashierId) {
        $stmt = $this->db->prepare(
            "SELECT * FROM counter_sessions
             WHERE cashier_id = ?
             ORDER BY opened_at DESC LIMIT 1"
        );
        $stmt->execute([$cashierId]);
        return $stmt->fetch();
    }

    /**
     * Open a new shift session for the cashier.
     */
    public function openSession($cashierId) {
        $stmt = $this->db->prepare(
            "INSERT INTO counter_sessions (cashier_id, status) VALUES (?, 'open')"
        );
        $stmt->execute([$cashierId]);
        return $this->db->lastInsertId();
    }

    /**
     * Get or create active session for cashier on login.
     * Returns the session id.
     */
    public function ensureSession($cashierId) {
        $session = $this->getActiveSession($cashierId);
        if ($session) {
            // Re-open a close_requested session (not yet admin-approved means carry-forward)
            if ($session['status'] === 'close_requested') {
                $stmt = $this->db->prepare("UPDATE counter_sessions SET status = 'open', close_requested_at = NULL WHERE id = ?");
                $stmt->execute([$session['id']]);
            }
            return $session['id'];
        }
        return $this->openSession($cashierId);
    }

    /**
     * Get session by ID.
     */
    public function getSession($sessionId) {
        $stmt = $this->db->prepare(
            "SELECT cs.*, u.name as cashier_name
             FROM counter_sessions cs
             JOIN users u ON cs.cashier_id = u.id
             WHERE cs.id = ?"
        );
        $stmt->execute([$sessionId]);
        return $stmt->fetch();
    }

    /**
     * Recalculate system totals for this session from all paid bills by this cashier 
     * from opened_at to now.
     */
    public function refreshSessionTotals($sessionId, $startTime = null, $endTime = null, $persistWindow = false) {
        $session = $this->getSession($sessionId);
        if (!$session) return false;

        $start = !empty($startTime) ? $startTime : $session['opened_at'];
        if (!empty($endTime)) {
            $end = $endTime;
        } elseif ($session['status'] === 'close_requested' && !empty($session['close_requested_at'])) {
            $end = $session['close_requested_at'];
        } else {
            $end = date('Y-m-d H:i:s');
        }

        $stmt = $this->db->prepare(
            "SELECT 
                COUNT(id) AS total_bills,
                COALESCE(SUM(CASE WHEN payment_method='split' THEN cash_amount WHEN payment_method='cash' THEN grand_total ELSE 0 END), 0) AS cash_total,
                COALESCE(SUM(CASE WHEN payment_method='split' THEN card_amount WHEN payment_method='card' THEN grand_total ELSE 0 END), 0) AS card_total,
                COALESCE(SUM(CASE WHEN payment_method='split' THEN qr_amount WHEN payment_method='qr_pay' THEN grand_total ELSE 0 END), 0) AS qr_total,
                COALESCE(SUM(grand_total), 0) AS system_total
             FROM bills
             WHERE cashier_id = ? AND status = 'paid' AND created_at BETWEEN ? AND ?"
        );
        $stmt->execute([$session['cashier_id'], $start, $end]);
        $totals = $stmt->fetch();

        if ($persistWindow) {
            $upd = $this->db->prepare(
                "UPDATE counter_sessions SET cash_total=?, card_total=?, qr_total=?, system_total=?, opened_at=?, close_requested_at=? WHERE id=?"
            );
            $upd->execute([
                $totals['cash_total'], $totals['card_total'], $totals['qr_total'], $totals['system_total'],
                $start, $end, $sessionId
            ]);
        } else {
            // Keep open session window open-ended (close_requested_at = NULL) so multi-location transactions are never cut off
            $keepCloseReqAt = ($session['status'] === 'open') ? null : $session['close_requested_at'];
            $upd = $this->db->prepare(
                "UPDATE counter_sessions SET cash_total=?, card_total=?, qr_total=?, system_total=?, close_requested_at=? WHERE id=?"
            );
            $upd->execute([
                $totals['cash_total'], $totals['card_total'], $totals['qr_total'], $totals['system_total'],
                $keepCloseReqAt, $sessionId
            ]);
        }
        return $totals;
    }

    /**
     * Get day-by-day collection breakdown for a cashier between startTime and endTime.
     */
    public function getSessionDailyBreakdown($cashierId, $startTime, $endTime = null) {
        $end = !empty($endTime) ? $endTime : date('Y-m-d H:i:s');
        $stmt = $this->db->prepare(
            "SELECT 
                DATE(created_at) AS collection_date,
                COUNT(id) AS total_bills,
                COALESCE(SUM(CASE WHEN payment_method='split' THEN cash_amount WHEN payment_method='cash' THEN grand_total ELSE 0 END), 0) AS cash_total,
                COALESCE(SUM(CASE WHEN payment_method='split' THEN card_amount WHEN payment_method='card' THEN grand_total ELSE 0 END), 0) AS card_total,
                COALESCE(SUM(CASE WHEN payment_method='split' THEN qr_amount WHEN payment_method='qr_pay' THEN grand_total ELSE 0 END), 0) AS qr_total,
                COALESCE(SUM(grand_total), 0) AS day_total
             FROM bills
             WHERE cashier_id = ? AND status = 'paid' AND created_at BETWEEN ? AND ?
             GROUP BY DATE(created_at)
             ORDER BY collection_date DESC"
        );
        $stmt->execute([$cashierId, $startTime, $end]);
        return $stmt->fetchAll();
    }

    /**
     * Request session close with cashier-declared collected amounts.
     */
    public function requestClose($sessionId, $collectedCash, $collectedCard, $collectedQr, $notes, $endTime = null) {
        $collectedTotal = $collectedCash + $collectedCard + $collectedQr;
        $end = !empty($endTime) ? $endTime : date('Y-m-d H:i:s');
        $stmt = $this->db->prepare(
            "UPDATE counter_sessions 
             SET status = 'close_requested',
                 close_requested_at = ?,
                 collected_cash = ?,
                 collected_card = ?,
                 collected_qr = ?,
                 collected_total = ?,
                 cashier_notes = ?
             WHERE id = ? AND status = 'open'"
        );
        $stmt->execute([$end, $collectedCash, $collectedCard, $collectedQr, $collectedTotal, $notes, $sessionId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Admin approves and permanently closes a session.
     */
    public function approveClose($sessionId, $adminId) {
        $stmt = $this->db->prepare(
            "UPDATE counter_sessions 
             SET status = 'closed', closed_at = NOW(), approved_by = ?
             WHERE id = ? AND status = 'close_requested'"
        );
        $stmt->execute([$adminId, $sessionId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Admin rejects a close request (reopens the session).
     */
    public function rejectClose($sessionId) {
        $stmt = $this->db->prepare(
            "UPDATE counter_sessions SET status = 'open', close_requested_at = NULL WHERE id = ?"
        );
        $stmt->execute([$sessionId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get all sessions awaiting admin approval.
     */
    public function getPendingClosures() {
        $stmt = $this->db->query(
            "SELECT cs.*, u.name as cashier_name
             FROM counter_sessions cs
             JOIN users u ON cs.cashier_id = u.id
             WHERE cs.status = 'close_requested'
             ORDER BY cs.close_requested_at DESC"
        );
        $closures = $stmt->fetchAll();
        foreach ($closures as &$c) {
            $end = !empty($c['close_requested_at']) ? $c['close_requested_at'] : date('Y-m-d H:i:s');
            $c['daily_breakdown'] = $this->getSessionDailyBreakdown($c['cashier_id'], $c['opened_at'], $end);
        }
        return $closures;
    }

    /**
     * Get recent closed sessions for admin history view.
     */
    public function getClosedSessions($limit = 20) {
        $stmt = $this->db->prepare(
            "SELECT cs.*, u.name as cashier_name, a.name as approved_by_name
             FROM counter_sessions cs
             JOIN users u ON cs.cashier_id = u.id
             LEFT JOIN users a ON cs.approved_by = a.id
             WHERE cs.status = 'closed'
             ORDER BY cs.closed_at DESC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get all active (open or close_requested) cashier sessions with live calculated totals and cashier online status.
     */
    public function getActiveOpenSessions() {
        // First consolidate any duplicate active sessions per cashier
        $cashierIds = $this->db->query(
            "SELECT DISTINCT cashier_id FROM counter_sessions WHERE status IN ('open', 'close_requested')"
        )->fetchAll(PDO::FETCH_COLUMN);
        foreach ($cashierIds as $cid) {
            $this->consolidateActiveSessions($cid);
        }

        $stmt = $this->db->query(
            "SELECT cs.*, u.name as cashier_name,
                    COALESCE(u.is_logged_in, 0) as is_logged_in,
                    u.last_active_at,
                    (CASE 
                        WHEN u.is_logged_in = 1 AND (u.last_active_at IS NULL OR u.last_active_at >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)) THEN 1 
                        ELSE 0 
                     END) as is_online
             FROM counter_sessions cs
             JOIN users u ON cs.cashier_id = u.id
             WHERE cs.status IN ('open', 'close_requested')
             ORDER BY cs.opened_at DESC"
        );
        $sessions = $stmt->fetchAll();
        foreach ($sessions as &$s) {
            $totals = $this->refreshSessionTotals($s['id']);
            if ($totals) {
                $s['total_bills'] = (int)($totals['total_bills'] ?? 0);
                $s['cash_total'] = (float)$totals['cash_total'];
                $s['card_total'] = (float)$totals['card_total'];
                $s['qr_total'] = (float)$totals['qr_total'];
                $s['system_total'] = (float)$totals['system_total'];
            }
            $end = ($s['status'] === 'close_requested' && !empty($s['close_requested_at'])) ? $s['close_requested_at'] : date('Y-m-d H:i:s');
            $s['daily_breakdown'] = $this->getSessionDailyBreakdown($s['cashier_id'], $s['opened_at'], $end);
            $s['is_online'] = (int)($s['is_online'] ?? 0);
            $s['is_logged_in'] = (int)($s['is_logged_in'] ?? 0);
        }
        return $sessions;
    }
}
