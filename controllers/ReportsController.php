<?php

require_once 'core/Controller.php';

class ReportsController extends Controller {

    public function chefKpiJson() {
        $this->requireAuth('admin');
        $db = Database::getInstance()->getConnection();
        
        // Group by chef and summarize how many total items (or total value) they consumed
        // The value of consumed items = quantity * current selling_price of the inventory item
        $sql = "SELECT u.id as chef_id, u.name as chef_name, 
                       COUNT(t.id) as total_transactions, 
                       SUM(ABS(t.quantity)) as total_items_consumed,
                       SUM(ABS(t.quantity) * i.selling_price) as total_consumed_cost
                FROM inventory_transactions t
                JOIN users u ON t.chef_id = u.id
                JOIN inventory_items i ON t.inventory_item_id = i.id
                WHERE t.transaction_type = 'consume_kot'
                GROUP BY t.chef_id
                ORDER BY total_items_consumed DESC";
                
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->json(['status' => 'success', 'data' => $data]);
    }

    public function chefDetailsJson($params) {
        $this->requireAuth('admin');
        $db = Database::getInstance()->getConnection();
        
        $chefId = $params['id'] ?? null;
        if (!$chefId) {
            $this->json(['status' => 'error', 'message' => 'Chef ID required']);
            return;
        }
        
        try {
            $sql = "SELECT DISTINCT k.kot_number, o.id as order_number, p.name as item_name, ki.quantity, p.price as selling_price, (ki.quantity * p.price) as total_revenue, t.created_at as time
                    FROM kot_items ki
                    JOIN kots k ON ki.kot_id = k.id
                    JOIN orders o ON k.order_id = o.id
                    JOIN products p ON ki.product_id = p.id
                    JOIN inventory_transactions t ON t.reference_id = k.kot_number
                    WHERE t.chef_id = ? AND t.transaction_type = 'consume_kot'
                    ORDER BY t.created_at DESC";
                    
            $stmt = $db->prepare($sql);
            $stmt->execute([$chefId]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->json(['status' => 'success', 'data' => $data]);
        } catch (\PDOException $e) {
            $this->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function supplierKpiJson() {
        $this->requireAuth('admin');
        $db = Database::getInstance()->getConnection();
        
        // Get the average unit price for items supplied by different suppliers
        $sql = "SELECT s.name as supplier_name, i.name as item_name, AVG(t.unit_price) as avg_price, SUM(t.quantity) as total_supplied
                FROM inventory_transactions t
                JOIN suppliers s ON t.supplier_id = s.id
                JOIN inventory_items i ON t.inventory_item_id = i.id
                WHERE t.transaction_type = 'add_stock'
                GROUP BY t.supplier_id, t.inventory_item_id
                ORDER BY i.name ASC, avg_price ASC";
                
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->json(['status' => 'success', 'data' => $data]);
    }

    public function profitabilityJson() {
        $this->requireAuth('admin');
        $db = Database::getInstance()->getConnection();
        
        // Profitability: Selling Price - (Sum of Recipe Ingredients' Buying Prices)
        $sql = "SELECT p.name as product_name, p.price as selling_price,
                COALESCE((SELECT SUM(pr.quantity_required * i.selling_price) 
                 FROM product_recipes pr 
                 JOIN inventory_items i ON pr.inventory_item_id = i.id 
                 WHERE pr.product_id = p.id), 0) as total_cost
                FROM products p";
                
        $stmt = $db->query($sql);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $profitability = [];
        foreach ($products as $prod) {
            $cost = (float)$prod['total_cost'];
            $sell = (float)$prod['selling_price'];
            $profit = $sell - $cost;
            $margin = $sell > 0 ? ($profit / $sell) * 100 : 0;
            
            $profitability[] = [
                'product_name' => $prod['product_name'],
                'selling_price' => $sell,
                'total_cost' => $cost,
                'profit' => $profit,
                'margin_percent' => round($margin, 2)
            ];
        }
        
        // Sort by margin desc
        usort($profitability, function($a, $b) {
            return $b['margin_percent'] <=> $a['margin_percent'];
        });

        $this->json(['status' => 'success', 'data' => $profitability]);
    }
}
