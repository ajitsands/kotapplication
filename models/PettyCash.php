<?php

require_once __DIR__ . '/../core/Database.php';

class PettyCash {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureTable();
    }

    public function ensureTable() {
        $this->db->exec("CREATE TABLE IF NOT EXISTS petty_cash_transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            entry_type ENUM('expense', 'income') NOT NULL DEFAULT 'expense',
            title VARCHAR(150) NOT NULL,
            category VARCHAR(80) NOT NULL DEFAULT 'General',
            party_name VARCHAR(150) NULL,
            amount DECIMAL(10,3) NOT NULL DEFAULT 0.000,
            notes TEXT NULL,
            user_id INT NULL,
            user_name VARCHAR(100) NULL,
            entry_datetime DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_petty_cash_dt (entry_datetime),
            INDEX idx_petty_cash_type (entry_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $colCheck = $this->db->query("SHOW COLUMNS FROM petty_cash_transactions LIKE 'party_name'")->fetch();
        if (!$colCheck) {
            $this->db->exec("ALTER TABLE petty_cash_transactions ADD COLUMN party_name VARCHAR(150) NULL AFTER category");
        }
    }

    public function create($data) {
        $sql = "INSERT INTO petty_cash_transactions
                (entry_type, title, category, party_name, amount, notes, user_id, user_name, entry_datetime)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['entry_type'],
            $data['title'],
            $data['category'],
            $data['party_name'] ?? null,
            $data['amount'],
            $data['notes'] ?? null,
            $data['user_id'] ?? null,
            $data['user_name'] ?? 'Staff',
            $data['entry_datetime']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM petty_cash_transactions WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->rowCount() > 0;
    }

    public function getRange($startDateTime, $endDateTime, $userId = null) {
        $sql = "SELECT id, entry_type, title, category, party_name, amount, notes, user_id, user_name, entry_datetime, created_at
                FROM petty_cash_transactions
                WHERE entry_datetime BETWEEN :start AND :end";
        $params = [
            'start' => $startDateTime,
            'end'   => $endDateTime
        ];
        if ($userId !== null && $userId !== '' && $userId !== 'all') {
            $sql .= " AND user_id = :user_id";
            $params['user_id'] = (int)$userId;
        }
        $sql .= " ORDER BY entry_datetime DESC, id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $incomeTotal = 0.0;
        $expenseTotal = 0.0;
        $items = [];

        foreach ($rows as $r) {
            $amt = (float)$r['amount'];
            if ($r['entry_type'] === 'income') {
                $incomeTotal += $amt;
            } else {
                $expenseTotal += $amt;
            }
            $items[] = [
                'id' => (int)$r['id'],
                'entry_type' => $r['entry_type'],
                'title' => $r['title'],
                'category' => $r['category'] ?: 'General',
                'party_name' => $r['party_name'] ?? '',
                'amount' => round($amt, 3),
                'notes' => $r['notes'] ?? '',
                'user_id' => $r['user_id'] ? (int)$r['user_id'] : null,
                'user_name' => $r['user_name'] ?: 'Staff',
                'entry_datetime' => $r['entry_datetime'],
                'entry_datetime_formatted' => date('d M Y, h:i A', strtotime($r['entry_datetime']))
            ];
        }

        return [
            'items' => $items,
            'income_total' => round($incomeTotal, 3),
            'expense_total' => round($expenseTotal, 3),
            'net_total' => round($incomeTotal - $expenseTotal, 3),
            'count' => count($items)
        ];
    }
}
