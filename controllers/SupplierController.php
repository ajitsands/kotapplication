<?php

require_once 'core/Controller.php';

class SupplierController extends Controller {
    public function index() {
        $this->requireAuth('admin');
        // Render supplier management view (to be created)
        $this->view('admin/suppliers');
    }

    public function suppliersListJson() {
        $this->requireAuth('admin');
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM suppliers ORDER BY id DESC");
        $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->json(['status' => 'success', 'data' => $suppliers]);
    }

    public function saveSupplier() {
        $this->requireAuth('admin');
        $id = $_POST['id'] ?? null;
        $name = $_POST['name'] ?? '';
        $contact_person = $_POST['contact_person'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';
        $address = $_POST['address'] ?? '';

        if (empty($name)) {
            $this->json(['status' => 'error', 'message' => 'Supplier name is required']);
            return;
        }

        $db = Database::getInstance()->getConnection();
        if ($id) {
            $stmt = $db->prepare("UPDATE suppliers SET name = ?, contact_person = ?, phone = ?, email = ?, address = ? WHERE id = ?");
            $stmt->execute([$name, $contact_person, $phone, $email, $address, $id]);
        } else {
            $stmt = $db->prepare("INSERT INTO suppliers (name, contact_person, phone, email, address) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $contact_person, $phone, $email, $address]);
        }
        $this->json(['status' => 'success']);
    }

    public function deleteSupplier($params) {
        $this->requireAuth('admin');
        $id = $params['id'] ?? null;
        if ($id) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM suppliers WHERE id = ?");
            $stmt->execute([$id]);
            $this->json(['status' => 'success']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Invalid ID']);
        }
    }
}
