<?php

class Category extends Model {
    public function __construct() {
        parent::__construct();
        $this->ensureSchema();
    }

    private function ensureSchema() {
        try {
            $cols = $this->db->query("SHOW COLUMNS FROM categories")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('sort_order', $cols)) {
                $this->db->exec("ALTER TABLE categories ADD COLUMN sort_order INT NOT NULL DEFAULT 0 AFTER name");
                $this->normalizeSortOrders();
            }
            if (!in_array('image_url', $cols)) {
                $this->db->exec("ALTER TABLE categories ADD COLUMN image_url VARCHAR(255) DEFAULT NULL AFTER sort_order");
            }
        } catch (Exception $e) {}
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM categories ORDER BY sort_order ASC, name ASC");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public function add($name, $imageUrl = null, $sortOrder = null) {
        try {
            if ($sortOrder === null || (int)$sortOrder <= 0) {
                $maxOrder = (int)$this->db->query("SELECT MAX(sort_order) FROM categories")->fetchColumn();
                $sortOrder = $maxOrder + 1;
            }

            $stmt = $this->db->prepare("INSERT INTO categories (name, image_url, sort_order) VALUES (?, ?, ?)");
            return $stmt->execute([$name, $imageUrl, (int)$sortOrder]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
        $res = $stmt->execute([(int)$id]);
        $this->normalizeSortOrders();
        return $res;
    }

    public function update($id, $name, $imageUrl = null, $sortOrder = null) {
        try {
            $sql = "UPDATE categories SET name = ?";
            $params = [$name];
            if ($imageUrl !== null) {
                $sql .= ", image_url = ?";
                $params[] = $imageUrl;
            }
            if ($sortOrder !== null) {
                $sql .= ", sort_order = ?";
                $params[] = (int)$sortOrder;
            }
            $sql .= " WHERE id = ?";
            $params[] = (int)$id;

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateSortOrder($id, $sortOrder) {
        $stmt = $this->db->prepare("UPDATE categories SET sort_order = ? WHERE id = ?");
        return $stmt->execute([(int)$sortOrder, (int)$id]);
    }

    public function reorder($id, $direction) {
        $current = $this->getById($id);
        if (!$current) return false;

        $currentOrder = (int)$current['sort_order'];
        $target = null;

        if ($direction === 'up') {
            // Find category directly above
            $stmt = $this->db->prepare("SELECT id, sort_order FROM categories WHERE sort_order < ? ORDER BY sort_order DESC, id DESC LIMIT 1");
            $stmt->execute([$currentOrder]);
            $target = $stmt->fetch();
        } elseif ($direction === 'down') {
            // Find category directly below
            $stmt = $this->db->prepare("SELECT id, sort_order FROM categories WHERE sort_order > ? ORDER BY sort_order ASC, id ASC LIMIT 1");
            $stmt->execute([$currentOrder]);
            $target = $stmt->fetch();
        }

        if ($target) {
            $targetOrder = (int)$target['sort_order'];
            // Swap sort orders
            $this->updateSortOrder($current['id'], $targetOrder);
            $this->updateSortOrder($target['id'], $currentOrder);
            return true;
        }

        return false;
    }

    public function normalizeSortOrders() {
        try {
            $cats = $this->db->query("SELECT id FROM categories ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_COLUMN);
            $order = 1;
            $stmt = $this->db->prepare("UPDATE categories SET sort_order = ? WHERE id = ?");
            foreach ($cats as $catId) {
                $stmt->execute([$order, $catId]);
                $order++;
            }
        } catch (Exception $e) {}
    }
}
