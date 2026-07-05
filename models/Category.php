<?php

class Category extends Model {
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM categories ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function add($name, $imageUrl = null) {
        try {
            $stmt = $this->db->prepare("INSERT INTO categories (name, image_url) VALUES (?, ?)");
            return $stmt->execute([$name, $imageUrl]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function update($id, $name, $imageUrl = null) {
        try {
            $sql = "UPDATE categories SET name = ?";
            $params = [$name];
            if ($imageUrl !== null) {
                $sql .= ", image_url = ?";
                $params[] = $imageUrl;
            }
            $sql .= " WHERE id = ?";
            $params[] = $id;

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }
}
