<?php

class Product extends Model {
    public function getAll() {
        $stmt = $this->db->query("SELECT p.*, c.name as category_name 
                                  FROM products p 
                                  JOIN categories c ON p.category_id = c.id 
                                  ORDER BY c.name ASC, p.name ASC");
        return $stmt->fetchAll();
    }

    public function getByCategory($categoryId) {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE category_id = ? AND is_available = 1 ORDER BY name ASC");
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }

    public function getByCategoryForAdmin($categoryId) {
        $stmt = $this->db->prepare("SELECT p.*, c.name as category_name 
                                    FROM products p 
                                    JOIN categories c ON p.category_id = c.id 
                                    WHERE p.category_id = ? 
                                    ORDER BY p.name ASC");
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
            $stmt = $this->db->prepare("INSERT INTO products (category_id, name, description, price, image_url, is_available, is_counter_item) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?)");
            return $stmt->execute([
                $data['category_id'],
                $data['name'],
                $data['description'] ?? null,
                $data['price'],
                $data['image_url'] ?? null,
                isset($data['is_available']) ? (int)$data['is_available'] : 1,
                isset($data['is_counter_item']) ? (int)$data['is_counter_item'] : 0
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
}
