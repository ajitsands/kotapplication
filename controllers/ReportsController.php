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
        $this->requireAuth('counter');
        $db = Database::getInstance()->getConnection();
        
        $startDate = !empty($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-d');
        $endDate = !empty($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d');
        $endDate = $endDate . ' 23:59:59';
        $startDate = $startDate . ' 00:00:00';

        $settingsModel = new Setting();
        $settings = $settingsModel->getSettings();
        $taxType = $settings['tax_type'] ?? 'VAT';
        $taxPercent = 0.0;
        if ($taxType === 'VAT') {
            $taxPercent = (float)($settings['vat_percent'] ?? 10.00);
        } else {
            $taxPercent = (float)($settings['cgst_percent'] ?? 2.50) + (float)($settings['sgst_percent'] ?? 2.50);
        }

        $sql = "SELECT 
                    o.id as order_id,
                    o.order_type,
                    o.waiter_id,
                    o.status as order_status,
                    o.token_number,
                    o.customer_name,
                    o.customer_mobile,
                    o.platform_order_number,
                    o.created_at,
                    o.table_number,
                    o.cancellation_reason,
                    o.cancelled_by,
                    o.cancelled_at,
                    b.id as bill_id,
                    b.payment_method,
                    b.cash_amount,
                    b.card_amount,
                    b.qr_amount,
                    b.grand_total as bill_total,
                    b.status as bill_status,
                    op.name as platform_name,
                    d.id as delivery_id,
                    d.status as delivery_status,
                    d.driver_id as delivery_driver_id,
                    u_d.name as delivery_driver_name,
                    d.payment_type as delivery_payment_type,
                    d.cod_amount as delivery_cod_amount,
                    d.cod_collected as delivery_cod_collected,
                    d.cod_handed_over as delivery_cod_handed_over,
                    (SELECT COALESCE(SUM(COALESCE(ki.price, IF(o.order_type = 'online' AND op.markup_percent > 0, ROUND(p.price * (1 + op.markup_percent / 100), 3), p.price)) * ki.quantity), 0) 
                     FROM kots k 
                     JOIN kot_items ki ON k.id = ki.kot_id 
                     JOIN products p ON ki.product_id = p.id
                     WHERE k.order_id = o.id AND ki.status != 'cancelled') as kot_subtotal
                FROM orders o
                LEFT JOIN bills b ON o.id = b.order_id
                LEFT JOIN online_platforms op ON o.platform_id = op.id
                LEFT JOIN deliveries d ON d.id = (
                    SELECT MAX(id) FROM deliveries WHERE (order_id = o.id OR (b.id IS NOT NULL AND bill_id = b.id)) AND status != 'cancelled'
                )
                LEFT JOIN users u_d ON d.driver_id = u_d.id
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
                $tax = $sub * ($taxPercent / 100.0);
                $total = $sub + $tax;
            }

            $isCancelled = (strtolower($row['order_status']) === 'cancelled');
            $originalTotal = $total;
            if ($isCancelled) {
                // Cancelled bills do not contribute to net sales or collections
                $total = 0.0;
            }

            $details = 'N/A';
            if ($cat === 'online') {
                $details = ($row['platform_name'] ?: 'Online') . ($row['platform_order_number'] ? ' #' . $row['platform_order_number'] : '');
            } elseif ($cat === 'takeaway') {
                $details = 'Token #' . ($row['token_number'] ?: $row['order_id']) . ($row['customer_name'] ? ' (' . $row['customer_name'] . ')' : '');
            } else {
                $details = ($row['table_number'] ? 'Table ' . $row['table_number'] : 'Dine In') . ($row['customer_name'] ? ' (' . $row['customer_name'] . ')' : '');
            }

            $transactions[] = [
                'order_id' => (int)$row['order_id'],
                'category' => $cat,
                'order_type' => $row['order_type'],
                'token_number' => $row['token_number'],
                'customer_name' => $row['customer_name'],
                'customer_mobile' => $row['customer_mobile'],
                'created_at' => date('d M Y, h:i A', strtotime($row['created_at'])),
                'status' => ucfirst($row['order_status']),
                'total' => $total,
                'original_total' => $originalTotal,
                'is_cancelled' => $isCancelled,
                'cancellation_reason' => $row['cancellation_reason'] ?? null,
                'cancelled_by' => $row['cancelled_by'] ?? null,
                'cancelled_at' => !empty($row['cancelled_at']) ? date('d M Y, h:i A', strtotime($row['cancelled_at'])) : null,
                'payment_method' => $row['payment_method'] ? ucfirst(str_replace('_', ' ', $row['payment_method'])) : 'Pending',
                'raw_payment_method' => $row['payment_method'],
                'cash_amount' => (float)($row['cash_amount'] ?? 0),
                'card_amount' => (float)($row['card_amount'] ?? 0),
                'qr_amount' => (float)($row['qr_amount'] ?? 0),
                'details' => $details,
                'bill_id' => $row['bill_id'] ? (int)$row['bill_id'] : null,
                'delivery_id' => $row['delivery_id'] ? (int)$row['delivery_id'] : null,
                'delivery_status' => $row['delivery_status'] ?? null,
                'delivery_driver_id' => $row['delivery_driver_id'] ? (int)$row['delivery_driver_id'] : null,
                'delivery_driver_name' => $row['delivery_driver_name'] ?? null,
                'delivery_payment_type' => $row['delivery_payment_type'] ?? null,
                'delivery_cod_amount' => (float)($row['delivery_cod_amount'] ?? 0),
                'delivery_cod_collected' => (int)($row['delivery_cod_collected'] ?? 0),
                'delivery_cod_handed_over' => (int)($row['delivery_cod_handed_over'] ?? 0)
            ];
        }

        $this->json(['status' => 'success', 'data' => $transactions]);
    }

    private function resolveReportRange() {
        if (!empty($_GET['startDateTime']) && !empty($_GET['endDateTime'])) {
            $startDateTime = $_GET['startDateTime'];
            $endDateTime   = $_GET['endDateTime'];
        } else {
            $startDate = !empty($_GET['startDate']) ? $_GET['startDate'] : null;
            $startTime = !empty($_GET['startTime']) ? $_GET['startTime'] : null;
            $endDate   = !empty($_GET['endDate'])   ? $_GET['endDate']   : null;
            $endTime   = !empty($_GET['endTime'])   ? $_GET['endTime']   : null;

            if ($startDate && $endDate) {
                $startDateTime = $startDate . ' ' . ($startTime ?: '00:00:00');
                $endDateTime   = $endDate . ' ' . ($endTime ?: '23:59:59');
            } else {
                // Default shift: 3:00 PM (15:00) to 4:00 AM next day
                $curHour = (int)date('G');
                if ($curHour < 4) {
                    $startDateTime = date('Y-m-d 15:00:00', strtotime('-1 day'));
                    $endDateTime   = date('Y-m-d 04:00:00');
                } else {
                    $startDateTime = date('Y-m-d 15:00:00');
                    $endDateTime   = date('Y-m-d 04:00:00', strtotime('+1 day'));
                }
            }
        }
        return [$startDateTime, $endDateTime];
    }

    public function totalCollectionJson() {
        $this->requireAuth('counter');
        list($startDateTime, $endDateTime) = $this->resolveReportRange();
        $cashierId = !empty($_GET['cashierId']) ? $_GET['cashierId'] : null;

        $billModel = new Bill();
        $data = $billModel->getTotalCollectionReport($startDateTime, $endDateTime, $cashierId);

        $this->json(['status' => 'success', 'data' => $data]);
    }

    public function totalCollectionExport() {
        $this->requireAuth('counter');
        list($startDateTime, $endDateTime) = $this->resolveReportRange();
        $cashierId = !empty($_GET['cashierId']) ? $_GET['cashierId'] : null;

        $billModel = new Bill();
        $data = $billModel->getTotalCollectionReport($startDateTime, $endDateTime, $cashierId);

        $filename = 'Total_Collection_Report_' . date('Ymd_His', strtotime($startDateTime)) . '_to_' . date('Ymd_His', strtotime($endDateTime)) . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM for Excel
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        // Title and Period
        fputcsv($out, ['TOTAL COLLECTION REPORT']);
        fputcsv($out, ['Period:', $data['period']['start_formatted'] . ' to ' . $data['period']['end_formatted']]);
        fputcsv($out, ['Generated At:', date('d M Y, h:i A')]);
        fputcsv($out, []);

        // Summary
        fputcsv($out, ['COLLECTION SUMMARY']);
        fputcsv($out, ['Stream / Provider', 'Total Amount (BHD)', 'Transactions Count']);
        fputcsv($out, ['Cash Collection', number_format($data['summary']['cash_total'], 3), $data['categories']['cash']['count']]);
        fputcsv($out, ['Card Payment Collection', number_format($data['summary']['card_total'], 3), $data['categories']['card']['count']]);
        fputcsv($out, ['QR / BenefitPay Collection', number_format($data['summary']['qr_total'], 3), $data['categories']['qr']['count']]);
        foreach ($data['categories']['online_providers'] as $p) {
            fputcsv($out, ['Online - ' . $p['name'], number_format($p['total'], 3), $p['count']]);
        }
        fputcsv($out, ['GRAND TOTAL COLLECTION', number_format($data['summary']['grand_total'], 3), $data['summary']['total_bills']]);
        fputcsv($out, []);

        // Cash Table
        $cash = $data['categories']['cash'];
        fputcsv($out, ['--- CASH COLLECTION (Total: ' . number_format($cash['total'], 3) . ' BHD, ' . $cash['count'] . ' Orders) ---']);
        fputcsv($out, ['Bill #', 'Order / Ref', 'Order Type', 'Date & Time', 'Cashier', 'Amount (BHD)', 'Notes']);
        foreach ($cash['items'] as $it) {
            fputcsv($out, [
                $it['bill_number'],
                $it['reference'],
                ucfirst($it['order_type']),
                $it['created_at_formatted'],
                $it['cashier_name'],
                number_format($it['amount'], 3),
                $it['split_note'] ?: ''
            ]);
        }
        fputcsv($out, ['SUBTOTAL CASH', '', '', '', '', number_format($cash['total'], 3), $cash['count'] . ' items']);
        fputcsv($out, []);

        // Card Table
        $card = $data['categories']['card'];
        fputcsv($out, ['--- CARD PAYMENT COLLECTION (Total: ' . number_format($card['total'], 3) . ' BHD, ' . $card['count'] . ' Orders) ---']);
        fputcsv($out, ['Bill #', 'Order / Ref', 'Order Type', 'Date & Time', 'Cashier', 'Amount (BHD)', 'Notes']);
        foreach ($card['items'] as $it) {
            fputcsv($out, [
                $it['bill_number'],
                $it['reference'],
                ucfirst($it['order_type']),
                $it['created_at_formatted'],
                $it['cashier_name'],
                number_format($it['amount'], 3),
                $it['split_note'] ?: ''
            ]);
        }
        fputcsv($out, ['SUBTOTAL CARD', '', '', '', '', number_format($card['total'], 3), $card['count'] . ' items']);
        fputcsv($out, []);

        // QR Table
        $qr = $data['categories']['qr'];
        fputcsv($out, ['--- QR / BENEFITPAY COLLECTION (Total: ' . number_format($qr['total'], 3) . ' BHD, ' . $qr['count'] . ' Orders) ---']);
        fputcsv($out, ['Bill #', 'Order / Ref', 'Order Type', 'Date & Time', 'Cashier', 'Amount (BHD)', 'Notes']);
        foreach ($qr['items'] as $it) {
            fputcsv($out, [
                $it['bill_number'],
                $it['reference'],
                ucfirst($it['order_type']),
                $it['created_at_formatted'],
                $it['cashier_name'],
                number_format($it['amount'], 3),
                $it['split_note'] ?: ''
            ]);
        }
        fputcsv($out, ['SUBTOTAL QR PAY', '', '', '', '', number_format($qr['total'], 3), $qr['count'] . ' items']);
        fputcsv($out, []);

        // Online Providers
        foreach ($data['categories']['online_providers'] as $op) {
            fputcsv($out, ['--- ONLINE: ' . strtoupper($op['name']) . ' (Total: ' . number_format($op['total'], 3) . ' BHD, ' . $op['count'] . ' Orders) ---']);
            fputcsv($out, ['Bill #', 'Order / Platform Ref', 'Date & Time', 'Cashier', 'Food Subtotal', 'Service Fee', 'Delivery Fee', 'Discount', 'Net Collected (BHD)', 'Notes']);
            foreach ($op['items'] as $it) {
                fputcsv($out, [
                    $it['bill_number'],
                    $it['reference'],
                    $it['created_at_formatted'],
                    $it['cashier_name'],
                    number_format($it['subtotal'], 3),
                    number_format($it['service_fee'], 3),
                    number_format($it['delivery_fee'], 3),
                    number_format($it['discount_amount'], 3),
                    number_format($it['amount'], 3),
                    $it['split_note'] ?: ''
                ]);
            }
            fputcsv($out, [
                'SUBTOTAL ' . strtoupper($op['name']),
                '',
                '',
                '',
                number_format($op['food_subtotal'], 3),
                number_format($op['service_fee'], 3),
                number_format($op['delivery_fee'], 3),
                number_format($op['discount_amount'], 3),
                number_format($op['total'], 3),
                $op['count'] . ' orders'
            ]);
            fputcsv($out, []);
        }

        // Split Payments if any
        if (!empty($data['categories']['splits'])) {
            fputcsv($out, ['--- SPLIT PAYMENTS BREAKDOWN ---']);
            fputcsv($out, ['Bill #', 'Order / Ref', 'Date & Time', 'Cashier', 'Bill Grand Total', 'Cash Amount', 'Card Amount', 'QR Amount', 'Online Amount']);
            foreach ($data['categories']['splits'] as $sp) {
                fputcsv($out, [
                    $sp['bill_number'],
                    $sp['reference'],
                    $sp['created_at_formatted'],
                    $sp['cashier_name'],
                    number_format($sp['grand_total'], 3),
                    number_format($sp['cash_amount'], 3),
                    number_format($sp['card_amount'], 3),
                    number_format($sp['qr_amount'], 3),
                    number_format($sp['online_amount'], 3)
                ]);
            }
            fputcsv($out, []);
        }

        fclose($out);
        exit;
    }
}
