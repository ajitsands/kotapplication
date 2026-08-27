<?php

require_once 'core/Controller.php';

class InventoryController extends Controller {
    public function index() {
        $this->requireAuth('admin');
        $this->view('admin/inventory');
    }

    public function itemsListJson() {
        $this->requireAuth('admin');
        $db = Database::getInstance()->getConnection();
        
        $query = "
            SELECT i.*, 
            (
                SELECT MIN(expiry_date)
                FROM (
                    SELECT expiry_date, SUM(quantity) as net_qty
                    FROM inventory_transactions
                    WHERE inventory_item_id = i.id AND expiry_date IS NOT NULL AND expiry_date >= CURDATE()
                    GROUP BY expiry_date
                    HAVING net_qty > 0
                ) as valid_batches
            ) as nearest_expiry,
            (
                SELECT SUM(quantity)
                FROM inventory_transactions
                WHERE inventory_item_id = i.id
                AND expiry_date = (
                    SELECT MIN(expiry_date)
                    FROM (
                        SELECT expiry_date, SUM(quantity) as net_qty
                        FROM inventory_transactions
                        WHERE inventory_item_id = i.id AND expiry_date IS NOT NULL AND expiry_date >= CURDATE()
                        GROUP BY expiry_date
                        HAVING net_qty > 0
                    ) as valid_batches
                )
            ) as expiring_qty
            FROM inventory_items i 
            ORDER BY name ASC
        ";
        $stmt = $db->query($query);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->json(['status' => 'success', 'data' => $items]);
    }

    public function transactionsJson($params) {
        $this->requireAuth('admin');
        $item_id = $params['item_id'] ?? null;
        if (!$item_id) {
            $this->json(['status' => 'error', 'message' => 'Item ID required']);
            return;
        }

        $start_date = $_GET['start_date'] ?? null;
        $end_date = $_GET['end_date'] ?? null;
        
        $db = Database::getInstance()->getConnection();
        
        // Fetch current stock
        $stmt = $db->prepare("SELECT current_stock FROM inventory_items WHERE id = ?");
        $stmt->execute([$item_id]);
        $current_stock = (float)$stmt->fetchColumn();

        $opening_stock = 0;
        $closing_stock = $current_stock;

        // Base query for transactions
        $query = "
            SELECT t.*, s.name as supplier_name, u.username as chef_name
            FROM inventory_transactions t
            LEFT JOIN suppliers s ON t.supplier_id = s.id
            LEFT JOIN users u ON t.chef_id = u.id
            WHERE t.inventory_item_id = ?
        ";
        $queryParams = [$item_id];

        if ($start_date) {
            $query .= " AND t.created_at >= ?";
            $queryParams[] = $start_date . ' 00:00:00';
            
            // Calculate opening stock: current_stock - SUM(all transactions >= start_date)
            $sum_stmt = $db->prepare("SELECT COALESCE(SUM(quantity), 0) FROM inventory_transactions WHERE inventory_item_id = ? AND created_at >= ?");
            $sum_stmt->execute([$item_id, $start_date . ' 00:00:00']);
            $sum_after_start = (float)$sum_stmt->fetchColumn();
            $opening_stock = $current_stock - $sum_after_start;
        }

        if ($end_date) {
            $query .= " AND t.created_at <= ?";
            $queryParams[] = $end_date . ' 23:59:59';
            
            // Calculate closing stock: current_stock - SUM(all transactions > end_date)
            $sum_stmt = $db->prepare("SELECT COALESCE(SUM(quantity), 0) FROM inventory_transactions WHERE inventory_item_id = ? AND created_at > ?");
            $sum_stmt->execute([$item_id, $end_date . ' 23:59:59']);
            $sum_after_end = (float)$sum_stmt->fetchColumn();
            $closing_stock = $current_stock - $sum_after_end;
        }

        $query .= " ORDER BY t.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute($queryParams);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->json([
            'status' => 'success', 
            'opening_stock' => $opening_stock,
            'closing_stock' => $closing_stock,
            'data' => $transactions
        ]);
    }
    public function expHistoryJson($params) {
        $this->requireAuth('admin');
        $item_id = $params['item_id'] ?? null;
        if (!$item_id) {
            $this->json(['status' => 'error', 'message' => 'Item ID required']);
            return;
        }

        $db = Database::getInstance()->getConnection();
        
        $query = "
            SELECT 
                MAX(t.created_at) as created_at,
                MAX(s.name) as supplier_name,
                t.expiry_date,
                SUM(t.quantity) as quantity,
                MAX(t.notes) as notes,
                MAX(t.inventory_item_id) as inventory_item_id
            FROM inventory_transactions t
            LEFT JOIN suppliers s ON t.supplier_id = s.id
            WHERE t.inventory_item_id = ? AND t.expiry_date IS NOT NULL
            GROUP BY t.expiry_date
            HAVING quantity > 0
            ORDER BY t.expiry_date ASC
        ";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$item_id]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->json(['status' => 'success', 'data' => $data]);
    }

    public function saveItem() {
        $this->requireAuth('admin');
        $id = $_POST['id'] ?? null;
        $name = $_POST['name'] ?? '';
        $unit = $_POST['unit'] ?? 'Nos';
        $buying_price_per_unit = $_POST['buying_price_per_unit'] ?? 0;
        $selling_price = $_POST['selling_price'] ?? 0;
        $min_stock_level = $_POST['min_stock_level'] ?? 0;

        if (empty($name)) {
            $this->json(['status' => 'error', 'message' => 'Item name is required']);
            return;
        }

        $db = Database::getInstance()->getConnection();
        if ($id) {
            $stmt = $db->prepare("UPDATE inventory_items SET name = ?, unit = ?, buying_price_per_unit = ?, selling_price = ?, min_stock_level = ? WHERE id = ?");
            $stmt->execute([$name, $unit, $buying_price_per_unit, $selling_price, $min_stock_level, $id]);
            $this->json(['status' => 'success', 'message' => 'Item updated successfully']);
        } else {
            $stmt = $db->prepare("INSERT INTO inventory_items (name, unit, buying_price_per_unit, selling_price, min_stock_level) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $unit, $buying_price_per_unit, $selling_price, $min_stock_level]);
            $this->json(['status' => 'success', 'message' => 'Item added successfully']);
        }
    }
    public function markDamage() {
        $this->requireAuth('admin');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $item_id = $_POST['item_id'] ?? null;
            $quantity = floatval($_POST['quantity'] ?? 0);
            $reason = $_POST['reason'] ?? '';
            $expiry_date = $_POST['expiry_date'] ?? null;
            if (empty($expiry_date)) $expiry_date = null;

            if (!$item_id || $quantity <= 0) {
                $this->json(['status' => 'error', 'message' => 'Invalid item or quantity']);
                return;
            }

            $db = Database::getInstance()->getConnection();
            try {
                $db->beginTransaction();

                // Get current stock
                $stmt = $db->prepare("SELECT current_stock FROM inventory_items WHERE id = ? FOR UPDATE");
                $stmt->execute([$item_id]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$item || $item['current_stock'] < $quantity) {
                    throw new Exception('Not enough stock available to mark as damaged');
                }

                $new_stock = $item['current_stock'] - $quantity;
                
                // Update stock
                $updateStmt = $db->prepare("UPDATE inventory_items SET current_stock = ? WHERE id = ?");
                $updateStmt->execute([$new_stock, $item_id]);

                // Log damage transaction
                $logStmt = $db->prepare("INSERT INTO inventory_transactions 
                    (inventory_item_id, transaction_type, quantity, notes, expiry_date, created_at) 
                    VALUES (?, 'damage', ?, ?, ?, NOW())");
                
                $logStmt->execute([
                    $item_id,
                    -$quantity,
                    "Damage/Waste: " . $reason,
                    $expiry_date
                ]);

                $db->commit();
                $this->json(['status' => 'success', 'message' => 'Item moved to damaged stock successfully']);
            } catch (Exception $e) {
                $db->rollBack();
                $this->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    public function deleteItem($params) {
        $this->requireAuth('admin');
        $id = $params['id'] ?? null;
        if ($id) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM inventory_items WHERE id = ?");
            $stmt->execute([$id]);
            $this->json(['status' => 'success']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Invalid ID']);
        }
    }

    public function addStock() {
        $this->requireAuth('admin');
        
        $inventory_item_ids = $_POST['inventory_item_id'] ?? [];
        $quantities = $_POST['quantity'] ?? [];
        $unit_prices = $_POST['unit_price'] ?? [];
        $expiry_dates = $_POST['expiry_date'] ?? [];
        
        $supplier_id = !empty($_POST['supplier_id']) ? $_POST['supplier_id'] : null;
        $notes = $_POST['notes'] ?? '';
        
        if (empty($inventory_item_ids) || !is_array($inventory_item_ids)) {
            $this->json(['status' => 'error', 'message' => 'No items selected for stock addition']);
            return;
        }

        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        try {
            for ($i = 0; $i < count($inventory_item_ids); $i++) {
                $item_id = $inventory_item_ids[$i];
                $qty = (float)($quantities[$i] ?? 0);
                $price = (float)($unit_prices[$i] ?? 0);
                $expiry = !empty($expiry_dates[$i]) ? $expiry_dates[$i] : null;
                
                if (!$item_id || $qty <= 0) continue;

                // Update stock
                $stmt = $db->prepare("UPDATE inventory_items SET current_stock = current_stock + ? WHERE id = ?");
                $stmt->execute([$qty, $item_id]);

                // Add transaction log
                $stmt = $db->prepare("INSERT INTO inventory_transactions (inventory_item_id, transaction_type, quantity, unit_price, supplier_id, notes, expiry_date) VALUES (?, 'add_stock', ?, ?, ?, ?, ?)");
                $stmt->execute([$item_id, $qty, $price, $supplier_id, $notes, $expiry]);
            }

            $db->commit();
            $this->json(['status' => 'success']);
        } catch (Exception $e) {
            $db->rollBack();
            $this->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function downloadItemsTemplate() {
        $this->requireAuth('admin');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=inventory_items_template.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Name', 'Unit', 'Buying Price', 'Selling Price']);
        fclose($output);
        exit;
    }

    public function importItems() {
        $this->requireAuth('admin');
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['status' => 'error', 'message' => 'Please upload a valid CSV file']);
            return;
        }

        $file = $_FILES['csv_file']['tmp_name'];
        if (($handle = fopen($file, 'r')) !== false) {
            $db = Database::getInstance()->getConnection();
            $db->beginTransaction();
            try {
                $header = fgetcsv($handle); // Skip header
                $successCount = 0;
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    if (count($data) < 1 || empty(trim($data[0]))) continue;
                    
                    $name = trim($data[0]);
                    $unit = isset($data[1]) && trim($data[1]) !== '' ? trim($data[1]) : 'Nos';
                    $buy = isset($data[2]) ? (float)trim($data[2]) : 0;
                    $sell = isset($data[3]) ? (float)trim($data[3]) : 0;

                    // Check if item exists
                    $stmt = $db->prepare("SELECT id FROM inventory_items WHERE name = ?");
                    $stmt->execute([$name]);
                    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($existing) {
                        $upd = $db->prepare("UPDATE inventory_items SET unit=?, buying_price_per_unit=?, selling_price=? WHERE id=?");
                        $upd->execute([$unit, $buy, $sell, $existing['id']]);
                    } else {
                        $ins = $db->prepare("INSERT INTO inventory_items (name, unit, buying_price_per_unit, selling_price, current_stock) VALUES (?, ?, ?, ?, 0)");
                        $ins->execute([$name, $unit, $buy, $sell]);
                    }
                    $successCount++;
                }
                $db->commit();
                fclose($handle);
                $this->json(['status' => 'success', 'message' => "Successfully imported $successCount items"]);
            } catch (Exception $e) {
                $db->rollBack();
                fclose($handle);
                $this->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        } else {
            $this->json(['status' => 'error', 'message' => 'Failed to open uploaded file']);
        }
    }

    public function downloadStockTemplate() {
        $this->requireAuth('admin');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=add_stock_template.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Item Name', 'Quantity', 'Unit Price', 'Supplier Name', 'Notes']);
        fclose($output);
        exit;
    }

    public function importStock() {
        $this->requireAuth('admin');
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['status' => 'error', 'message' => 'Please upload a valid CSV file']);
            return;
        }

        $file = $_FILES['csv_file']['tmp_name'];
        if (($handle = fopen($file, 'r')) !== false) {
            $db = Database::getInstance()->getConnection();
            $db->beginTransaction();
            try {
                $header = fgetcsv($handle); // Skip header
                $successCount = 0;
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    if (count($data) < 2 || empty(trim($data[0])) || empty(trim($data[1]))) continue;
                    
                    $itemName = trim($data[0]);
                    $qty = (float)trim($data[1]);
                    if ($qty <= 0) continue;
                    
                    $price = isset($data[2]) ? (float)trim($data[2]) : 0;
                    $supplierName = isset($data[3]) ? trim($data[3]) : '';
                    $notes = isset($data[4]) ? trim($data[4]) : '';

                    // Lookup Item ID
                    $stmtItem = $db->prepare("SELECT id FROM inventory_items WHERE name = ?");
                    $stmtItem->execute([$itemName]);
                    $item = $stmtItem->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$item) {
                        continue; // Skip if item not found
                    }
                    $inventory_item_id = $item['id'];

                    // Lookup Supplier ID
                    $supplier_id = null;
                    if (!empty($supplierName)) {
                        $stmtSupp = $db->prepare("SELECT id FROM suppliers WHERE name = ?");
                        $stmtSupp->execute([$supplierName]);
                        $supp = $stmtSupp->fetch(PDO::FETCH_ASSOC);
                        if ($supp) {
                            $supplier_id = $supp['id'];
                        }
                    }

                    // Update stock
                    $updStock = $db->prepare("UPDATE inventory_items SET current_stock = current_stock + ? WHERE id = ?");
                    $updStock->execute([$qty, $inventory_item_id]);

                    // Add transaction log
                    $insLog = $db->prepare("INSERT INTO inventory_transactions (inventory_item_id, transaction_type, quantity, unit_price, supplier_id, notes) VALUES (?, 'add_stock', ?, ?, ?, ?)");
                    $insLog->execute([$inventory_item_id, $qty, $price, $supplier_id, $notes]);

                    $successCount++;
                }
                $db->commit();
                fclose($handle);
                $this->json(['status' => 'success', 'message' => "Successfully imported $successCount stock additions"]);
            } catch (Exception $e) {
                $db->rollBack();
                fclose($handle);
                $this->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        } else {
            $this->json(['status' => 'error', 'message' => 'Failed to open uploaded file']);
        }
    }

    // --- Recipes (BOM) ---

    public function getRecipe($params) {
        $this->requireAuth('admin');
        $product_id = $params['product_id'] ?? null;
        if (!$product_id) {
            $this->json(['status' => 'error', 'message' => 'Product ID required']);
            return;
        }
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT pr.*, i.name as item_name, i.unit FROM product_recipes pr JOIN inventory_items i ON pr.inventory_item_id = i.id WHERE pr.product_id = ?");
        $stmt->execute([$product_id]);
        $recipe = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->json(['status' => 'success', 'data' => $recipe]);
    }

    public function saveRecipe() {
        $this->requireAuth('admin');
        $product_id = $_POST['product_id'] ?? null;
        
        $inventory_item_ids = $_POST['inventory_item_id'] ?? [];
        $quantities = $_POST['quantity_required'] ?? [];

        $ingredients = [];
        if (is_array($inventory_item_ids)) {
            for ($i = 0; $i < count($inventory_item_ids); $i++) {
                if (!empty($inventory_item_ids[$i]) && !empty($quantities[$i])) {
                    $ingredients[] = [
                        'inventory_item_id' => $inventory_item_ids[$i],
                        'quantity_required' => $quantities[$i]
                    ];
                }
            }
        }

        if (!$product_id) {
            $this->json(['status' => 'error', 'message' => 'Product ID required']);
            return;
        }

        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        try {
            // Remove old recipe
            $stmt = $db->prepare("DELETE FROM product_recipes WHERE product_id = ?");
            $stmt->execute([$product_id]);

            // Insert new ingredients
            if (!empty($ingredients)) {
                $stmt = $db->prepare("INSERT INTO product_recipes (product_id, inventory_item_id, quantity_required) VALUES (?, ?, ?)");
                foreach ($ingredients as $ing) {
                    $stmt->execute([$product_id, $ing['inventory_item_id'], $ing['quantity_required']]);
                }
            }

            $db->commit();
            $this->json(['status' => 'success']);
        } catch (Exception $e) {
            $db->rollBack();
            $this->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
