<?php

class Product extends Model {
    public function __construct() {
        parent::__construct();
        $this->ensureSchema();
    }

    private function ensureSchema() {
        try {
            $cols = $this->db->query("SHOW COLUMNS FROM products")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('sort_order', $cols)) {
                $this->db->exec("ALTER TABLE products ADD COLUMN sort_order INT NOT NULL DEFAULT 0 AFTER price");
                $this->normalizeSortOrders();
            }
            if (!in_array('is_counter_item', $cols)) {
                $this->db->exec("ALTER TABLE products ADD COLUMN is_counter_item TINYINT(1) NOT NULL DEFAULT 0 AFTER is_available");
            }
            // Ensure V3 inventory tables exist so Product::getAll() subqueries never fail on older DBs
            $this->db->exec("CREATE TABLE IF NOT EXISTS `suppliers` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(100) NOT NULL,
                `contact_person` VARCHAR(100) DEFAULT NULL,
                `phone` VARCHAR(20) DEFAULT NULL,
                `email` VARCHAR(100) DEFAULT NULL,
                `address` TEXT DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB");
            $this->db->exec("CREATE TABLE IF NOT EXISTS `inventory_items` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(100) NOT NULL UNIQUE,
                `unit` VARCHAR(50) NOT NULL DEFAULT 'Nos',
                `current_stock` DECIMAL(10,3) NOT NULL DEFAULT 0.000,
                `min_stock_level` DECIMAL(10,3) NOT NULL DEFAULT 0.000,
                `buying_price_per_unit` DECIMAL(10,3) NOT NULL DEFAULT 0.000,
                `selling_price` DECIMAL(10,3) NOT NULL DEFAULT 0.000,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB");
            $this->db->exec("CREATE TABLE IF NOT EXISTS `product_recipes` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `product_id` INT NOT NULL,
                `inventory_item_id` INT NOT NULL,
                `quantity_required` DECIMAL(10,3) NOT NULL,
                FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB");
            $this->db->exec("CREATE TABLE IF NOT EXISTS `inventory_transactions` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `inventory_item_id` INT NOT NULL,
                `transaction_type` ENUM('add_stock', 'consume_kot', 'adjustment', 'damage') NOT NULL,
                `quantity` DECIMAL(10,3) NOT NULL,
                `unit_price` DECIMAL(10,3) DEFAULT NULL,
                `supplier_id` INT DEFAULT NULL,
                `chef_id` INT DEFAULT NULL,
                `reference_id` VARCHAR(50) DEFAULT NULL,
                `notes` TEXT DEFAULT NULL,
                `expiry_date` DATE DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB");
        } catch (Exception $e) {}
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT p.*, c.name as category_name,
                                         (SELECT GROUP_CONCAT(CONCAT(i.name, ' (', pr.quantity_required, ' ', i.unit, ')') SEPARATOR ', ')
                                          FROM product_recipes pr
                                          JOIN inventory_items i ON pr.inventory_item_id = i.id
                                          WHERE pr.product_id = p.id) as recipe_details,
                                         (SELECT GROUP_CONCAT(i.name SEPARATOR ', ')
                                          FROM product_recipes pr
                                          JOIN inventory_items i ON pr.inventory_item_id = i.id
                                          WHERE pr.product_id = p.id) as ingredients
                                   FROM products p 
                                   JOIN categories c ON p.category_id = c.id 
                                   ORDER BY c.sort_order ASC, c.name ASC, p.sort_order ASC, p.name ASC");
        return $stmt->fetchAll();
    }

    public function getByCategory($categoryId) {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE category_id = ? AND is_available = 1 ORDER BY sort_order ASC, name ASC");
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }

    public function getByCategoryForAdmin($categoryId) {
        $stmt = $this->db->prepare("SELECT p.*, c.name as category_name 
                                    FROM products p 
                                    JOIN categories c ON p.category_id = c.id 
                                    WHERE p.category_id = ? 
                                    ORDER BY p.sort_order ASC, p.name ASC");
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function save($data) {
        if (!empty($data['id'])) {
            // Update
            $sql = "UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, is_available = ?, is_counter_item = ?";
            $params = [
                $data['category_id'],
                $data['name'],
                $data['description'] ?? null,
                $data['price'],
                isset($data['is_available']) ? (int)$data['is_available'] : 1,
                isset($data['is_counter_item']) ? (int)$data['is_counter_item'] : 0
            ];

            if (isset($data['sort_order']) && $data['sort_order'] !== '') {
                $sql .= ", sort_order = ?";
                $params[] = (int)$data['sort_order'];
            }

            if (isset($data['image_url'])) {
                $sql .= ", image_url = ?";
                $params[] = $data['image_url'];
            }

            $sql .= " WHERE id = ?";
            $params[] = $data['id'];
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } else {
            // Insert
            $sortOrder = 1;
            if (isset($data['sort_order']) && (int)$data['sort_order'] > 0) {
                $sortOrder = (int)$data['sort_order'];
            } else {
                $stmtMax = $this->db->prepare("SELECT MAX(sort_order) FROM products WHERE category_id = ?");
                $stmtMax->execute([$data['category_id']]);
                $sortOrder = (int)$stmtMax->fetchColumn() + 1;
            }

            $stmt = $this->db->prepare("INSERT INTO products (category_id, name, description, price, image_url, is_available, is_counter_item, sort_order) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            return $stmt->execute([
                $data['category_id'],
                $data['name'],
                $data['description'] ?? null,
                $data['price'],
                $data['image_url'] ?? null,
                isset($data['is_available']) ? (int)$data['is_available'] : 1,
                isset($data['is_counter_item']) ? (int)$data['is_counter_item'] : 0,
                $sortOrder
            ]);
        }
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updateAvailability($id, $isAvailable) {
        $stmt = $this->db->prepare("UPDATE products SET is_available = ? WHERE id = ?");
        return $stmt->execute([(int)$isAvailable, $id]);
    }

    public function updateSortOrder($id, $sortOrder) {
        $stmt = $this->db->prepare("UPDATE products SET sort_order = ? WHERE id = ?");
        return $stmt->execute([(int)$sortOrder, (int)$id]);
    }

    public function reorder($id, $direction) {
        $current = $this->getById($id);
        if (!$current) return false;

        $categoryId = (int)$current['category_id'];
        $currentOrder = (int)$current['sort_order'];
        $target = null;

        if ($direction === 'up') {
            $stmt = $this->db->prepare("SELECT id, sort_order FROM products WHERE category_id = ? AND sort_order < ? ORDER BY sort_order DESC, id DESC LIMIT 1");
            $stmt->execute([$categoryId, $currentOrder]);
            $target = $stmt->fetch();
        } elseif ($direction === 'down') {
            $stmt = $this->db->prepare("SELECT id, sort_order FROM products WHERE category_id = ? AND sort_order > ? ORDER BY sort_order ASC, id ASC LIMIT 1");
            $stmt->execute([$categoryId, $currentOrder]);
            $target = $stmt->fetch();
        }

        if ($target) {
            $targetOrder = (int)$target['sort_order'];
            $this->updateSortOrder($current['id'], $targetOrder);
            $this->updateSortOrder($target['id'], $currentOrder);
            return true;
        }

        return false;
    }

    public function normalizeSortOrders($categoryId = null) {
        try {
            if ($categoryId) {
                $pids = $this->db->prepare("SELECT id FROM products WHERE category_id = ? ORDER BY sort_order ASC, id ASC");
                $pids->execute([(int)$categoryId]);
                $items = $pids->fetchAll(PDO::FETCH_COLUMN);
                $order = 1;
                $stmt = $this->db->prepare("UPDATE products SET sort_order = ? WHERE id = ?");
                foreach ($items as $pid) {
                    $stmt->execute([$order, $pid]);
                    $order++;
                }
            } else {
                $cats = $this->db->query("SELECT id FROM categories")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($cats as $catId) {
                    $this->normalizeSortOrders($catId);
                }
            }
        } catch (Exception $e) {}
    }
}
