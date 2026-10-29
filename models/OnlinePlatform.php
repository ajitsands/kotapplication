<?php
require_once 'core/Model.php';

class OnlinePlatform extends Model {
    public function __construct() {
        parent::__construct();
        $this->ensureSchema();
    }

    private function ensureSchema() {
        try {
            $this->db->exec("CREATE TABLE IF NOT EXISTS `online_platforms` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(100) NOT NULL,
                `markup_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                `status` ENUM('active','inactive') DEFAULT 'active',
                `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $cols = $this->db->query("SHOW COLUMNS FROM online_platforms")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('markup_percent', $cols)) {
                $this->db->exec("ALTER TABLE online_platforms ADD COLUMN markup_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER name");
            }

            $count = (int)$this->db->query("SELECT COUNT(*) FROM online_platforms")->fetchColumn();
            if ($count === 0) {
                $this->db->exec("INSERT IGNORE INTO `online_platforms` (`id`, `name`, `status`) VALUES
                    (1, 'Talabat', 'active'),
                    (2, 'Jahez', 'active'),
                    (3, 'Hungerstation', 'active'),
                    (4, 'Deliveroo', 'active'),
                    (5, 'UberEats', 'active')");
            }
        } catch (Exception $e) {}
    }
    
    public function getAllActive() {
        $stmt = $this->db->query("SELECT * FROM online_platforms WHERE status = 'active' ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM online_platforms ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add($name, $markupPercent = 0.00) {
        $stmt = $this->db->prepare("INSERT INTO online_platforms (name, markup_percent) VALUES (?, ?)");
        return $stmt->execute([trim($name), (float)$markupPercent]);
    }

    public function update($id, $name, $markupPercent = 0.00) {
        $stmt = $this->db->prepare("UPDATE online_platforms SET name = ?, markup_percent = ? WHERE id = ?");
        return $stmt->execute([trim($name), (float)$markupPercent, (int)$id]);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM online_platforms WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM online_platforms WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function toggleStatus($id) {
        $stmt = $this->db->prepare("UPDATE online_platforms SET status = IF(status='active', 'inactive', 'active') WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
