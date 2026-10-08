<?php

class User extends Model {
    public function authenticate($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if (isset($user['is_active']) && (int)$user['is_active'] === 0) {
                return 'deactivated';
            }
            unset($user['password']); // Don't return password hash
            return $user;
        }
        return false;
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT id, username, name, role, is_active, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT id, username, name, role, is_active, created_at FROM users ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function add($username, $password, $name, $role) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO users (username, password, name, role, is_active) VALUES (?, ?, ?, ?, 1)");
        return $stmt->execute([$username, $hash, $name, $role]);
    }

    public function toggleStatus($id, $isActive) {
        // Prevent deactivating the main admin with ID 1 or superadmin with ID 6
        if ((int)$id === 1 || (int)$id === 6) {
            return false;
        }
        $stmt = $this->db->prepare("UPDATE users SET is_active = ? WHERE id = ?");
        return $stmt->execute([(int)$isActive, $id]);
    }

    public function delete($id) {
        // Prevent deleting the main admin with ID 1 or superadmin with ID 6
        if ((int)$id === 1 || (int)$id === 6) {
            return false;
        }
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function resetPassword($id, $newPassword) {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hash, $id]);
    }

    public function __construct() {
        parent::__construct();
        $this->ensureSchema();
    }

    private function ensureSchema() {
        try {
            $cols = $this->db->query("SHOW COLUMNS FROM users LIKE 'is_logged_in'")->fetchAll();
            if (empty($cols)) {
                $this->db->exec("ALTER TABLE users ADD COLUMN is_logged_in TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active");
            }
            $cols2 = $this->db->query("SHOW COLUMNS FROM users LIKE 'last_active_at'")->fetchAll();
            if (empty($cols2)) {
                $this->db->exec("ALTER TABLE users ADD COLUMN last_active_at TIMESTAMP NULL DEFAULT NULL AFTER is_logged_in");
            }
        } catch (Exception $e) {
            // Ignore schema errors if already updated or permissions
        }
    }

    public function setLoggedIn($userId, $isLoggedIn = 1) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET is_logged_in = ?, last_active_at = NOW() WHERE id = ?");
            return $stmt->execute([(int)$isLoggedIn, (int)$userId]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function touchActivity($userId) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET last_active_at = NOW() WHERE id = ?");
            return $stmt->execute([(int)$userId]);
        } catch (Exception $e) {
            return false;
        }
    }
}
