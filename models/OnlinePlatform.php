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

    public function add($name) {
        $stmt = $this->db->prepare("INSERT INTO online_platforms (name) VALUES (?)");
        return $stmt->execute([trim($name)]);
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
