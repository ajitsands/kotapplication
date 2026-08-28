<?php

require_once 'core/Controller.php';

class ReportsController extends Controller {

    public function chefKpiJson() {
        $this->requireAuth('admin');
        $db = Database::getInstance()->getConnection();
        
        $startDate = !empty($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-d');
        $endDate = !empty($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d');
        $endDate = $endDate . ' 23:59:59';
        $startDate = $startDate . ' 00:00:00';

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
                AND t.created_at >= ? AND t.created_at <= ?
                GROUP BY t.chef_id
                ORDER BY total_items_consumed DESC";
                
        $stmt = $db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
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

        $startDate = !empty($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-d');
        $endDate = !empty($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d');
        $endDate = $endDate . ' 23:59:59';
        $startDate = $startDate . ' 00:00:00';
        
        try {
            $sql = "SELECT DISTINCT k.kot_number, o.id as order_number, p.name as item_name, ki.quantity, p.price as selling_price, (ki.quantity * p.price) as total_revenue, t.created_at as time
                    FROM kot_items ki
                    JOIN kots k ON ki.kot_id = k.id
                    JOIN orders o ON k.order_id = o.id
                    JOIN products p ON ki.product_id = p.id
                    JOIN inventory_transactions t ON t.reference_id = k.kot_number
                    WHERE t.chef_id = ? AND t.transaction_type = 'consume_kot'
                    AND t.created_at >= ? AND t.created_at <= ?
                    ORDER BY t.created_at DESC";
                    
            $stmt = $db->prepare($sql);
            $stmt->execute([$chefId, $startDate, $endDate]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->json(['status' => 'success', 'data' => $data]);
        } catch (\PDOException $e) {
            $this->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function supplierKpiJson() {
        $this->requireAuth('admin');
        $db = Database::getInstance()->getConnection();
        
        $startDate = !empty($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-d');
        $endDate = !empty($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d');
        $endDate = $endDate . ' 23:59:59';
        $startDate = $startDate . ' 00:00:00';

        // Get the average unit price for items supplied by different suppliers
        $sql = "SELECT s.name as supplier_name, i.name as item_name, AVG(t.unit_price) as avg_price, SUM(t.quantity) as total_supplied
                FROM inventory_transactions t
                JOIN suppliers s ON t.supplier_id = s.id
                JOIN inventory_items i ON t.inventory_item_id = i.id
                WHERE t.transaction_type = 'add_stock'
                AND t.created_at >= ? AND t.created_at <= ?
                GROUP BY t.supplier_id, t.inventory_item_id
                ORDER BY i.name ASC, avg_price ASC";
                
        $stmt = $db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->json(['status' => 'success', 'data' => $data]);
    }

    public function productSalesKpiJson() {
        $this->requireAuth('admin');
        $db = Database::getInstance()->getConnection();
        
        $startDate = !empty($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-d');
        $endDate = !empty($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d');
        $endDate = $endDate . ' 23:59:59';
        $startDate = $startDate . ' 00:00:00';

        $sql = "SELECT p.name as product_name, 
                       SUM(ki.quantity) as total_sold,
                       p.price as menu_price,
                       COALESCE((SELECT SUM(pr.quantity_required * i.selling_price) 
                                 FROM product_recipes pr 
                                 JOIN inventory_items i ON pr.inventory_item_id = i.id 
                                 WHERE pr.product_id = p.id), 0) as recipe_cost
                FROM products p
                JOIN kot_items ki ON ki.product_id = p.id
                JOIN kots k ON ki.kot_id = k.id
                JOIN orders o ON k.order_id = o.id
                WHERE o.status = 'completed'
                AND o.created_at >= ? AND o.created_at <= ?
                GROUP BY p.id, p.name, p.price
                ORDER BY total_sold DESC";
                
        $stmt = $db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $reportData = [];
        foreach ($sales as $sale) {
            $qty = (float)$sale['total_sold'];
            $menuPrice = (float)$sale['menu_price'];
            $recipeCost = (float)$sale['recipe_cost'];
            
            $totalRevenue = $qty * $menuPrice;
            $totalExpense = $qty * $recipeCost;
            $totalProfit = $totalRevenue - $totalExpense;
            
            $reportData[] = [
                'product_name' => $sale['product_name'],
                'total_sold' => $qty,
                'menu_price' => $menuPrice,
                'recipe_cost' => $recipeCost,
                'total_revenue' => $totalRevenue,
                'total_expense' => $totalExpense,
                'total_profit' => $totalProfit
            ];
        }
        
        $this->json(['status' => 'success', 'data' => $reportData]);
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

    public function itemInsightsJson() {
        $this->requireAuth('admin');
        $db = Database::getInstance()->getConnection();
        
        $startDate = !empty($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-d');
        $endDate = !empty($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d');
        $endDate = $endDate . ' 23:59:59';
        $startDate = $startDate . ' 00:00:00';

        $sql = "SELECT p.name as product_name, 
                       SUM(ki.quantity) as total_sold,
                       p.price as menu_price,
                       COALESCE((SELECT SUM(pr.quantity_required * i.selling_price) 
                                 FROM product_recipes pr 
                                 JOIN inventory_items i ON pr.inventory_item_id = i.id 
                                 WHERE pr.product_id = p.id), 0) as recipe_cost
                FROM products p
                JOIN kot_items ki ON ki.product_id = p.id
                JOIN kots k ON ki.kot_id = k.id
                JOIN orders o ON k.order_id = o.id
                WHERE o.status = 'completed'
                AND o.created_at >= ? AND o.created_at <= ?
                GROUP BY p.id, p.name, p.price
                ORDER BY total_sold DESC";
                
        $stmt = $db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $reportData = [];
        foreach ($sales as $sale) {
            $qty = (float)$sale['total_sold'];
            $menuPrice = (float)$sale['menu_price'];
            $recipeCost = (float)$sale['recipe_cost'];
            
            $totalRevenue = $qty * $menuPrice;
            $totalExpense = $qty * $recipeCost;
            $totalProfit = $totalRevenue - $totalExpense;
            $marginPercent = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;
            
            $reportData[] = [
                'product_name' => $sale['product_name'],
                'total_sold' => $qty,
                'menu_price' => $menuPrice,
                'recipe_cost' => $recipeCost,
                'total_revenue' => $totalRevenue,
                'total_expense' => $totalExpense,
                'total_profit' => $totalProfit,
                'margin_percent' => round($marginPercent, 2)
            ];
        }
        
        $this->json(['status' => 'success', 'data' => $reportData]);
    }

    public function transactionsJson() {
        $this->requireAuth('admin');
        $db = Database::getInstance()->getConnection();
        
        $startDate = !empty($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-d');
        $endDate = !empty($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d');
        $endDate = $endDate . ' 23:59:59';
        $startDate = $startDate . ' 00:00:00';

        $sql = "SELECT 
                    o.id as order_id,
                    o.order_type,
                    o.waiter_id,
                    o.status as order_status,
                    o.created_at,
                    o.table_number,
                    b.id as bill_id,
                    b.payment_method,
                    b.grand_total as bill_total,
                    op.name as platform_name,
                    (SELECT COALESCE(SUM(p.price * ki.quantity), 0) 
                     FROM kots k 
                     JOIN kot_items ki ON k.id = ki.kot_id 
                     JOIN products p ON ki.product_id = p.id
                     WHERE k.order_id = o.id AND ki.status != 'cancelled') as kot_subtotal
                FROM orders o
                LEFT JOIN bills b ON o.id = b.order_id
                LEFT JOIN online_platforms op ON o.platform_id = op.id
                WHERE o.created_at BETWEEN :start AND :end
                ORDER BY o.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute(['start' => $startDate, 'end' => $endDate]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $transactions = [];
        foreach ($rows as $row) {
            $cat = 'all'; // baseline
            if ($row['order_type'] === 'take_away') {
                $cat = 'takeaway';
            } else if ($row['order_type'] === 'online') {
                $cat = 'online';
            } else if ($row['order_type'] === 'dine_in') {
                if ($row['waiter_id'] === null) {
                    $cat = 'self_order';
                } else {
                    $cat = 'waiter_order';
                }
            }

            // Calculate total
            $total = 0;
            if (!empty($row['bill_total'])) {
                $total = (float)$row['bill_total'];
            } else {
                // If no bill yet (e.g. online order or active dine_in), calculate from kot_subtotal
                $sub = (float)$row['kot_subtotal'];
                $tax = $sub * 0.10; // Hardcoded 10% tax for now as per system logic
                $total = $sub + $tax;
            }

            $transactions[] = [
                'order_id' => $row['order_id'],
                'category' => $cat,
                'order_type' => $row['order_type'],
                'created_at' => date('d M Y, h:i A', strtotime($row['created_at'])),
                'status' => ucfirst($row['order_status']),
                'total' => $total,
                'payment_method' => $row['payment_method'] ? ucfirst(str_replace('_', ' ', $row['payment_method'])) : 'Pending',
                'details' => $cat === 'online' ? $row['platform_name'] : ($row['table_number'] ? 'Table '.$row['table_number'] : 'N/A'),
                'bill_id' => $row['bill_id']
            ];
        }

        $this->json(['status' => 'success', 'data' => $transactions]);
    }
}
