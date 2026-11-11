<?php

require_once __DIR__ . '/../core/Model.php';

class Pack extends Model {

    public function __construct() {
        parent::__construct();
        $this->ensureTablesExist();
    }

    /**
     * Ensure packs & pack_slots tables exist and products has is_pack column
     */
    private function ensureTablesExist() {
        try {
            // 1. packs table
            $this->db->exec("CREATE TABLE IF NOT EXISTS `packs` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `product_id` INT NULL,
                `name` VARCHAR(100) NOT NULL,
                `description` TEXT NULL,
                `price` DECIMAL(10,3) NOT NULL DEFAULT 0.000,
                `image_url` VARCHAR(255) NULL,
                `is_available` TINYINT(1) DEFAULT 1,
                `sort_order` INT DEFAULT 0,
                `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_product_id` (`product_id`),
                INDEX `idx_is_available` (`is_available`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // 2. pack_slots table
            $this->db->exec("CREATE TABLE IF NOT EXISTS `pack_slots` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `pack_id` INT NOT NULL,
                `category_id` INT NOT NULL,
                `slot_name` VARCHAR(100) NULL,
                `quantity` INT NOT NULL DEFAULT 1,
                `sort_order` INT DEFAULT 0,
                INDEX `idx_pack_id` (`pack_id`),
                INDEX `idx_category_id` (`category_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // 3. Ensure is_pack column exists on products table
            $cols = $this->db->query("SHOW COLUMNS FROM products")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('is_pack', $cols)) {
                $this->db->exec("ALTER TABLE products ADD COLUMN `is_pack` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_counter_item`");
            }

            // 4. Ensure a 'Packs' category exists in categories so pack products have a valid category
            $catStmt = $this->db->query("SELECT id FROM categories WHERE name IN ('Packs', 'Meal Packs', 'Combo Packs') ORDER BY id ASC LIMIT 1");
            $catId = $catStmt->fetchColumn();
            if (!$catId) {
                $this->db->exec("INSERT INTO categories (name, sort_order) VALUES ('Meal Packs', 99)");
            }
        } catch (Exception $e) {
            error_log('Error ensuring Pack tables: ' . $e->getMessage());
        }
    }

    /**
     * Get the ID of the Packs category in categories table
     */
    public function getPacksCategoryId() {
        $stmt = $this->db->query("SELECT id FROM categories WHERE name IN ('Packs', 'Meal Packs', 'Combo Packs') ORDER BY id ASC LIMIT 1");
        $id = $stmt->fetchColumn();
        if ($id) return (int)$id;

        // Fallback: create if missing
        $this->db->exec("INSERT INTO categories (name, sort_order) VALUES ('Meal Packs', 99)");
        return (int)$this->db->lastInsertId();
    }

    /**
     * Fetch all packs (for Admin)
     */
    public function getAll($onlyAvailable = false) {
        $sql = "SELECT p.*, prod.image_url as product_image_url 
                FROM packs p
                LEFT JOIN products prod ON p.product_id = prod.id";
        if ($onlyAvailable) {
            $sql .= " WHERE p.is_available = 1";
        }
        $sql .= " ORDER BY p.sort_order ASC, p.id DESC";
        $stmt = $this->db->query($sql);
        $packs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Attach slots to each pack
        foreach ($packs as &$pack) {
            $pack['slots'] = $this->getSlotsForPack($pack['id']);
        }
        return $packs;
    }

    /**
     * Get a single pack by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM packs WHERE id = ?");
        $stmt->execute([(int)$id]);
        $pack = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($pack) {
            $pack['slots'] = $this->getSlotsForPack($pack['id']);
        }
        return $pack;
    }

    /**
     * Get slots for a specific pack
     */
    public function getSlotsForPack($packId) {
        $stmt = $this->db->prepare("
            SELECT ps.*, c.name as category_name
            FROM pack_slots ps
            JOIN categories c ON ps.category_id = c.id
            WHERE ps.pack_id = ?
            ORDER BY ps.sort_order ASC, ps.id ASC
        ");
        $stmt->execute([(int)$packId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create or update a Pack
     */
    public function savePack($data) {
        $this->db->beginTransaction();
        try {
            $id = !empty($data['id']) ? (int)$data['id'] : null;
            $name = trim($data['name'] ?? '');
            $description = trim($data['description'] ?? '');
            $price = (float)($data['price'] ?? 0.0);
            $imageUrl = !empty($data['image_url']) ? trim($data['image_url']) : null;
            $isAvailable = isset($data['is_available']) ? (int)$data['is_available'] : 1;
            $sortOrder = isset($data['sort_order']) ? (int)$data['sort_order'] : 0;
            $slots = isset($data['slots']) && is_array($data['slots']) ? $data['slots'] : [];

            if (empty($name)) {
                throw new Exception('Pack name is required.');
            }

            $packsCatId = $this->getPacksCategoryId();

            // 1. Sync corresponding row in products table
            $productId = null;
            if ($id) {
                $cur = $this->getById($id);
                if ($cur && !empty($cur['product_id'])) {
                    $productId = (int)$cur['product_id'];
                }
            }

            if ($productId) {
                // Update existing product
                $stmtProd = $this->db->prepare("
                    UPDATE products 
                    SET name = ?, description = ?, price = ?, is_available = ?, category_id = ?, is_pack = 1
                    WHERE id = ?
                ");
                $stmtProd->execute([$name, $description, $price, $isAvailable, $packsCatId, $productId]);
                if ($imageUrl) {
                    $this->db->prepare("UPDATE products SET image_url = ? WHERE id = ?")->execute([$imageUrl, $productId]);
                }
            } else {
                // Create new product for this pack
                $stmtProd = $this->db->prepare("
                    INSERT INTO products (category_id, name, description, price, image_url, is_available, is_pack, is_counter_item)
                    VALUES (?, ?, ?, ?, ?, ?, 1, 0)
                ");
                $stmtProd->execute([$packsCatId, $name, $description, $price, $imageUrl, $isAvailable]);
                $productId = (int)$this->db->lastInsertId();
            }

            // 2. Insert or update packs table
            if ($id) {
                $stmtPack = $this->db->prepare("
                    UPDATE packs 
                    SET product_id = ?, name = ?, description = ?, price = ?, image_url = ?, is_available = ?, sort_order = ?
                    WHERE id = ?
                ");
                $stmtPack->execute([$productId, $name, $description, $price, $imageUrl, $isAvailable, $sortOrder, $id]);
            } else {
                $stmtPack = $this->db->prepare("
                    INSERT INTO packs (product_id, name, description, price, image_url, is_available, sort_order)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmtPack->execute([$productId, $name, $description, $price, $imageUrl, $isAvailable, $sortOrder]);
                $id = (int)$this->db->lastInsertId();
            }

            // 3. Save slots
            $this->db->prepare("DELETE FROM pack_slots WHERE pack_id = ?")->execute([$id]);

            $stmtSlot = $this->db->prepare("
                INSERT INTO pack_slots (pack_id, category_id, slot_name, quantity, sort_order)
                VALUES (?, ?, ?, ?, ?)
            ");

            $slotOrder = 0;
            foreach ($slots as $slot) {
                $catId = (int)($slot['category_id'] ?? 0);
                if ($catId <= 0) continue;

                $qty = max(1, (int)($slot['quantity'] ?? 1));
                $slotName = !empty($slot['slot_name']) ? trim($slot['slot_name']) : null;

                $stmtSlot->execute([$id, $catId, $slotName, $qty, $slotOrder++]);
            }

            $this->db->commit();
            return $id;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Toggle active availability status
     */
    public function toggleAvailability($id) {
        $pack = $this->getById($id);
        if (!$pack) return false;

        $newStatus = $pack['is_available'] == 1 ? 0 : 1;
        $stmt = $this->db->prepare("UPDATE packs SET is_available = ? WHERE id = ?");
        $stmt->execute([$newStatus, (int)$id]);

        // Sync with linked product
        if (!empty($pack['product_id'])) {
            $this->db->prepare("UPDATE products SET is_available = ? WHERE id = ?")->execute([$newStatus, (int)$pack['product_id']]);
        }
        return $newStatus;
    }

    /**
     * Delete pack and clean up slots and linked product
     */
    public function deletePack($id) {
        $pack = $this->getById($id);
        if (!$pack) return false;

        $this->db->beginTransaction();
        try {
            $this->db->prepare("DELETE FROM pack_slots WHERE pack_id = ?")->execute([(int)$id]);
            $this->db->prepare("DELETE FROM packs WHERE id = ?")->execute([(int)$id]);
            if (!empty($pack['product_id'])) {
                // Soft delete or mark product unavailable so old order history doesn't break
                $this->db->prepare("UPDATE products SET is_available = 0 WHERE id = ?")->execute([(int)$pack['product_id']]);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Get all active packs enriched with their slots and eligible products
     * Ready for Customer QR Menu and POS
     */
    public function getPacksForMenu() {
        $packs = $this->getAll(true); // only available packs

        $formatted = [];
        foreach ($packs as $p) {
            $slotsData = [];
            foreach ($p['slots'] as $s) {
                // Fetch eligible products from this slot's category
                $stmtProds = $this->db->prepare("
                    SELECT id, category_id, name, description, price, image_url 
                    FROM products 
                    WHERE category_id = ? AND is_available = 1 AND is_pack = 0
                    ORDER BY sort_order ASC, name ASC
                ");
                $stmtProds->execute([(int)$s['category_id']]);
                $products = $stmtProds->fetchAll(PDO::FETCH_ASSOC);

                // Format slot label
                $slotTitle = !empty($s['slot_name']) ? $s['slot_name'] : $s['category_name'];

                $slotsData[] = [
                    'id'            => (int)$s['id'],
                    'category_id'   => (int)$s['category_id'],
                    'category_name' => $s['category_name'],
                    'slot_name'     => $slotTitle,
                    'quantity'      => (int)$s['quantity'],
                    'products'      => $products
                ];
            }

            $formatted[] = [
                'id'          => (int)$p['id'],
                'product_id'  => (int)$p['product_id'],
                'name'        => $p['name'],
                'description' => $p['description'],
                'price'       => (float)$p['price'],
                'image_url'   => $p['image_url'] ?: ($p['product_image_url'] ?? null),
                'slots'       => $slotsData
            ];
        }

        return $formatted;
    }
}
