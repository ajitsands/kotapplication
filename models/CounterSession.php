<?php

class CounterSession extends Model {

    /**
     * Find an open or close_requested session for a cashier (carry-forward).
     */
    public function getActiveSession($cashierId) {
        $stmt = $this->db->prepare(
            "SELECT * FROM counter_sessions
             WHERE cashier_id = ? AND status IN ('open', 'close_requested')
             ORDER BY opened_at DESC LIMIT 1"
        );
        $stmt->execute([$cashierId]);
        return $stmt->fetch();
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
                $stmt = $this->db->prepare("UPDATE counter_sessions SET status = 'open' WHERE id = ?");
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
    public function refreshSessionTotals($sessionId, $startTime = null, $endTime = null) {
        $session = $this->getSession($sessionId);
        if (!$session) return false;

        $start = !empty($startTime) ? $startTime : $session['opened_at'];
        $end = !empty($endTime) ? $endTime : date('Y-m-d H:i:s');

        $stmt = $this->db->prepare(
            "SELECT 
                COALESCE(SUM(CASE WHEN payment_method='cash' THEN grand_total ELSE 0 END), 0) AS cash_total,
                COALESCE(SUM(CASE WHEN payment_method='card' THEN grand_total ELSE 0 END), 0) AS card_total,
                COALESCE(SUM(CASE WHEN payment_method='qr_pay' THEN grand_total ELSE 0 END), 0) AS qr_total,
                COALESCE(SUM(grand_total), 0) AS system_total
             FROM bills
             WHERE cashier_id = ? AND status = 'paid' AND created_at BETWEEN ? AND ?"
        );
        $stmt->execute([$session['cashier_id'], $start, $end]);
        $totals = $stmt->fetch();

        $upd = $this->db->prepare(
            "UPDATE counter_sessions SET cash_total=?, card_total=?, qr_total=?, system_total=?, opened_at=?, close_requested_at=? WHERE id=?"
        );
        $upd->execute([
            $totals['cash_total'], $totals['card_total'], $totals['qr_total'], $totals['system_total'],
            $start, $end, $sessionId
        ]);
        return $totals;
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
        return $stmt->fetchAll();
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
}
