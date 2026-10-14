<?php
require_once 'core/Model.php';

class OnlinePlatform extends Model {
    
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
