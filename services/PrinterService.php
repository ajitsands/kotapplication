<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../models/Kot.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Bill.php';

class PrinterService {

    // ESC/POS Command Constants
    const ESC = "\x1B";
    const GS  = "\x1D";

    // Initialize printer
    public static function cmdInit() {
        return self::ESC . "@";
    }

    // Text Alignment (0: Left, 1: Center, 2: Right)
    public static function cmdAlign($align = 0) {
        return self::ESC . "a" . chr($align);
    }

    // Bold ON/OFF
    public static function cmdBold($enable = true) {
        return self::ESC . "E" . ($enable ? "\x01" : "\x00");
    }

    // Double Height & Double Width (0: Normal, 1: Double Height, 2: Double Width, 3: Quad / Big)
    public static function cmdSize($widthMultiplier = 1, $heightMultiplier = 1) {
        $w = max(0, min(7, $widthMultiplier - 1));
        $h = max(0, min(7, $heightMultiplier - 1));
        $val = ($w << 4) | $h;
        return self::GS . "!" . chr($val);
    }

    // Paper Cut (Feed lines then cut)
    public static function cmdCut($feedLines = 3) {
        return str_repeat("\n", $feedLines) . self::GS . "V" . "\x41" . chr($feedLines);
    }

    // Open Cash Drawer (Pin 2 / Pin 5 pulse)
    public static function cmdKickDrawer() {
        return self::ESC . "p" . "\x00" . "\x19" . "\xFA";
    }

    // Line helper - format two columns left and right aligned
    public static function formatRow($left, $right, $maxWidth = 48) {
        $left = (string)$left;
        $right = (string)$right;
        $spaces = $maxWidth - strlen($left) - strlen($right);
        if ($spaces < 1) {
            $spaces = 1;
        }
        return $left . str_repeat(' ', $spaces) . $right . "\n";
    }

    // Table columns helper (Item, Qty, Price)
    public static function formatThreeCols($col1, $col2, $col3, $w1 = 28, $w2 = 6, $w3 = 14) {
        $col1 = mb_strimwidth($col1, 0, $w1, '..');
        $c1 = str_pad($col1, $w1, ' ', STR_PAD_RIGHT);
        $c2 = str_pad($col2, $w2, ' ', STR_PAD_BOTH);
        $c3 = str_pad($col3, $w3, ' ', STR_PAD_LEFT);
        return $c1 . $c2 . $c3 . "\n";
    }

    /**
     * Send raw ESC/POS bytes directly to printer over TCP Socket
     */
    public static function sendToPrinter($rawBytes, $ip = null, $port = null, $timeout = 3) {
        $settingsModel = new Setting();
        $settings = $settingsModel->getSettings();

        $printerIp = !empty($ip) ? $ip : ($settings['printer_ip'] ?? '192.168.8.101');
        $printerPort = !empty($port) ? (int)$port : (int)($settings['printer_port'] ?? 9100);

        if (empty($printerIp)) {
            return ['success' => false, 'error' => 'Printer IP is not configured'];
        }

        $errno = 0;
        $errstr = '';
        $fp = @fsockopen($printerIp, $printerPort, $errno, $errstr, $timeout);

        if (!$fp) {
            return [
                'success' => false,
                'error' => "Unable to connect to printer at $printerIp:$printerPort. Error: $errstr ($errno)"
            ];
        }

        // Set stream timeout
        stream_set_timeout($fp, $timeout);

        // Write raw commands
        $bytesWritten = @fwrite($fp, $rawBytes);
        @fflush($fp);
        @fclose($fp);

        if ($bytesWritten === false || $bytesWritten === 0) {
            return [
                'success' => false,
                'error' => "Failed to transmit print data to $printerIp:$printerPort"
            ];
        }

        return [
            'success' => true,
            'bytes' => $bytesWritten,
            'message' => "Successfully sent to printer at $printerIp:$printerPort"
        ];
    }

    /**
     * Print Test Page
     */
    public static function testPrint($ip = null, $port = null) {
        $settingsModel = new Setting();
        $settings = $settingsModel->getSettings();

        $printerSize = (int)($settings['printer_size'] ?? 80);
        $width = ($printerSize === 58) ? 32 : 48;
        $divider = str_repeat('-', $width) . "\n";
        $doubleDiv = str_repeat('=', $width) . "\n";

        $targetIp = !empty($ip) ? $ip : ($settings['printer_ip'] ?? '192.168.8.101');
        $targetPort = !empty($port) ? (int)$port : (int)($settings['printer_port'] ?? 9100);

        $data = self::cmdInit();
        $data .= self::cmdAlign(1); // Center
        $data .= self::cmdBold(true);
        $data .= self::cmdSize(2, 2);
        $data .= "EASY+ POS PRINTER\n";
        $data .= self::cmdSize(1, 1);
        $data .= "NETWORK TEST SUCCESSFUL\n";
        $data .= self::cmdBold(false);
        $data .= $doubleDiv;

        $data .= self::cmdAlign(0); // Left
        $data .= self::formatRow("Restaurant:", $settings['restaurant_name'] ?? 'Gourmet POS', $width);
        $data .= self::formatRow("Printer IP:", $targetIp, $width);
        $data .= self::formatRow("Printer Port:", (string)$targetPort, $width);
        $data .= self::formatRow("Paper Width:", $printerSize . "mm (" . $width . " cols)", $width);
        $data .= self::formatRow("Date:", date('d-M-Y'), $width);
        $data .= self::formatRow("Time:", date('h:i:s A'), $width);
        $data .= $divider;

        $data .= self::cmdAlign(1);
        $data .= self::cmdBold(true);
        $data .= "CONNECTION READY FOR KOT & BILLS\n";
        $data .= self::cmdBold(false);
        $data .= self::cmdAlign(0);
        $data .= $divider;

        $data .= self::cmdCut(4);

        return self::sendToPrinter($data, $targetIp, $targetPort);
    }

    /**
     * Print KOT Slip directly to ESC/POS network printer
     */
    public static function printKot($kotId) {
        $kotModel = new Kot();
        $kot = $kotModel->getKotDetails((int)$kotId);

        if (!$kot) {
            return ['success' => false, 'error' => 'KOT not found'];
        }

        $settingsModel = new Setting();
        $settings = $settingsModel->getSettings();

        $printerSize = (int)($settings['printer_size'] ?? 80);
        $width = ($printerSize === 58) ? 32 : 48;
        $divider = str_repeat('-', $width) . "\n";
        $doubleDiv = str_repeat('=', $width) . "\n";

        $data = self::cmdInit();

        // Header
        $data .= self::cmdAlign(1); // Center
        $data .= self::cmdBold(true);
        $data .= self::cmdSize(1, 1);
        $data .= strtoupper($settings['restaurant_name'] ?? 'RESTAURANT') . "\n";
        $data .= "KITCHEN ORDER TICKET\n";
        $data .= $doubleDiv;

        // Table / Token Section (High Visibility)
        $data .= self::cmdAlign(1);
        $data .= self::cmdBold(true);
        $data .= self::cmdSize(2, 2);
        
        $tableNum = !empty($kot['table_number']) ? $kot['table_number'] : '-';
        if (!empty($kot['order_type']) && $kot['order_type'] === 'take_away') {
            $tokenNum = $kot['token_number'] ?? $kot['order_id'] ?? $tableNum;
            $data .= "TAKEAWAY #$tokenNum\n";
        } elseif (!empty($kot['order_type']) && $kot['order_type'] === 'online') {
            $data .= "ONLINE ORDER\n";
        } else {
            $data .= "TABLE: $tableNum\n";
        }

        $data .= self::cmdSize(1, 1);
        $data .= self::cmdBold(false);
        $data .= $doubleDiv;

        // Meta Info
        $data .= self::cmdAlign(0); // Left
        $data .= self::formatRow("KOT No: #" . ($kot['kot_number'] ?? $kot['id']), "Waiter: " . ($kot['waiter_name'] ?? 'Self-Order'), $width);
        $data .= self::formatRow("Date: " . date('d-M-Y', strtotime($kot['created_at'] ?? 'now')), "Time: " . date('h:i A', strtotime($kot['created_at'] ?? 'now')), $width);
        $data .= $divider;

        // Items Header
        $data .= self::cmdBold(true);
        if ($printerSize === 58) {
            $data .= self::formatRow("Item / Prep", "Qty", $width);
        } else {
            $wQty = 8;
            $wItem = $width - $wQty;
            $data .= str_pad("QTY", $wQty, ' ', STR_PAD_RIGHT) . str_pad("ITEM / PREPARATION", $wItem, ' ', STR_PAD_RIGHT) . "\n";
        }
        $data .= self::cmdBold(false);
        $data .= $divider;

        // Items List
        if (!empty($kot['items'])) {
            foreach ($kot['items'] as $item) {
                $qty = (int)$item['quantity'];
                $name = $item['product_name'] ?? 'Item';
                $notes = trim($item['notes'] ?? '');

                $data .= self::cmdBold(true);
                if ($printerSize === 58) {
                    $data .= self::cmdSize(1, 1);
                    $data .= self::formatRow($name, "x$qty", $width);
                } else {
                    $data .= self::cmdSize(1, 2); // Taller font for kitchen readability
                    $wQty = 8;
                    $wItem = $width - $wQty;
                    $data .= str_pad("$qty x", $wQty, ' ', STR_PAD_RIGHT) . str_pad($name, $wItem, ' ', STR_PAD_RIGHT) . "\n";
                }
                $data .= self::cmdSize(1, 1);
                $data .= self::cmdBold(false);

                if (!empty($notes)) {
                    $data .= "  >> NOTE: " . $notes . "\n";
                }
                $data .= "\n";
            }
        }

        $data .= $divider;
        $data .= self::cmdAlign(1);
        $data .= "Printed at " . date('d-M-Y h:i:s A') . "\n";
        
        // Feed & Cut
        $data .= self::cmdCut(4);

        return self::sendToPrinter($data);
    }

    /**
     * Print Bill / Receipt directly to ESC/POS network printer
     */
    public static function printBill($billId) {
        $billModel = new Bill();
        $bill = $billModel->getBillDetails((int)$billId);

        if (!$bill) {
            return ['success' => false, 'error' => 'Bill not found'];
        }

        return self::formatAndSendBill($bill);
    }

    /**
     * Print Order directly as Bill / Receipt
     */
    public static function printOrder($orderId) {
        $orderModel = new Order();
        $order = $orderModel->getOrderDetails((int)$orderId);

        if (!$order) {
            return ['success' => false, 'error' => 'Order not found'];
        }

        if (!empty($order['bill_id'])) {
            $billModel = new Bill();
            $bill = $billModel->getBillDetails($order['bill_id']);
            if ($bill) {
                return self::formatAndSendBill($bill);
            }
        }

        $settingsModel = new Setting();
        $settings = $settingsModel->getSettings();

        // Calculate bill structure from order
        $subtotal = 0.0;
        if (!empty($order['items'])) {
            foreach ($order['items'] as $item) {
                $subtotal += (float)($item['subtotal_price'] ?? ($item['price'] * $item['total_quantity']));
            }
        }
        $taxType = $settings['tax_type'] ?? 'VAT';
        $taxAmount = 0.0;
        if ($taxType === 'VAT') {
            $vatPercent = (float)($settings['vat_percent'] ?? 10.00);
            $taxAmount = $subtotal * ($vatPercent / 100.0);
        } else {
            $cgstPercent = (float)($settings['cgst_percent'] ?? 2.50);
            $sgstPercent = (float)($settings['sgst_percent'] ?? 2.50);
            $taxAmount = $subtotal * (($cgstPercent + $sgstPercent) / 100.0);
        }

        $billData = [
            'id' => $order['id'],
            'order_id' => $order['id'],
            'table_number' => $order['table_number'] ?? '-',
            'order_type' => $order['order_type'] ?? 'dine_in',
            'token_number' => $order['token_number'] ?? null,
            'customer_name' => $order['customer_name'] ?? null,
            'customer_mobile' => $order['customer_mobile'] ?? null,
            'waiter_name' => $order['waiter_name'] ?? 'Self-Order',
            'created_at' => $order['created_at'],
            'status' => $order['status'],
            'payment_method' => $order['payment_method'] ?? 'cash',
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => 0.0,
            'discount_percent' => 0.0,
            'grand_total' => $subtotal + $taxAmount,
            'items' => $order['items'] ?? []
        ];

        return self::formatAndSendBill($billData);
    }

    /**
     * Format and send receipt data
     */
    private static function formatAndSendBill($bill) {
        $settingsModel = new Setting();
        $settings = $settingsModel->getSettings();

        $printerSize = (int)($settings['printer_size'] ?? 80);
        $width = ($printerSize === 58) ? 32 : 48;
        $divider = str_repeat('-', $width) . "\n";
        $doubleDiv = str_repeat('=', $width) . "\n";
        $currency = $settings['currency_code'] ?? 'BHD';

        $data = self::cmdInit();

        // Kick Cash Drawer on bill printing
        $data .= self::cmdKickDrawer();

        // Restaurant Header
        $data .= self::cmdAlign(1); // Center
        $data .= self::cmdBold(true);
        $data .= self::cmdSize(2, 2);
        $data .= ($settings['restaurant_name'] ?? 'GOURMET RESTAURANT') . "\n";
        $data .= self::cmdSize(1, 1);
        $data .= "TAX INVOICE\n";
        $data .= self::cmdBold(false);
        $data .= $doubleDiv;

        // Metadata
        $data .= self::cmdAlign(0); // Left
        $billNo = "#" . str_pad($bill['id'] ?? $bill['order_id'], 6, '0', STR_PAD_LEFT);
        
        $tableLabel = "Table: T" . ($bill['table_number'] ?? '-');
        if (!empty($bill['order_type']) && $bill['order_type'] === 'take_away') {
            $tableLabel = "Takeaway #" . ($bill['token_number'] ?? $bill['order_id'] ?? $bill['id']);
        } elseif (!empty($bill['order_type']) && $bill['order_type'] === 'online') {
            $tableLabel = "Online (" . ($bill['platform_name'] ?? 'App') . ")";
        }

        $data .= self::formatRow("Invoice: $billNo", $tableLabel, $width);
        $data .= self::formatRow("Date: " . date('d-M-Y', strtotime($bill['created_at'] ?? 'now')), "Time: " . date('h:i A', strtotime($bill['created_at'] ?? 'now')), $width);
        
        if (!empty($bill['customer_name'])) {
            $data .= self::formatRow("Customer:", $bill['customer_name'], $width);
        }
        if (!empty($bill['customer_mobile'])) {
            $data .= self::formatRow("Mobile:", $bill['customer_mobile'], $width);
        }
        if (!empty($bill['waiter_name']) && empty($bill['customer_name'])) {
            $data .= self::formatRow("Staff:", $bill['waiter_name'], $width);
        }

        $data .= $divider;

        // Items Columns
        $data .= self::cmdBold(true);
        if ($printerSize === 58) {
            $w1 = 18; $w2 = 4; $w3 = 10;
            $data .= self::formatThreeCols("ITEM", "QTY", "TOTAL", $w1, $w2, $w3);
        } else {
            $w1 = 28; $w2 = 6; $w3 = 14;
            $data .= self::formatThreeCols("ITEM", "QTY", "TOTAL ($currency)", $w1, $w2, $w3);
        }
        $data .= self::cmdBold(false);
        $data .= $divider;

        // Items List
        if (!empty($bill['items'])) {
            foreach ($bill['items'] as $item) {
                $pName = $item['product_name'] ?? $item['name'] ?? 'Item';
                $pQty = $item['total_quantity'] ?? $item['quantity'] ?? 1;
                $pPrice = (float)($item['price'] ?? 0);
                $pSubtotal = (float)($item['subtotal_price'] ?? ($pPrice * $pQty));

                $subtotalStr = number_format($pSubtotal, 3, '.', '');

                if ($printerSize === 58) {
                    $data .= self::formatThreeCols($pName, (string)$pQty, $subtotalStr, 18, 4, 10);
                } else {
                    $data .= self::formatThreeCols($pName, (string)$pQty, $subtotalStr, 28, 6, 14);
                }
            }
        }

        $data .= $divider;

        // Totals
        $subtotalFmt = number_format((float)($bill['subtotal'] ?? 0), 3, '.', '') . " $currency";
        $data .= self::formatRow("Subtotal:", $subtotalFmt, $width);

        if (($settings['tax_type'] ?? 'VAT') === 'VAT') {
            $vatPct = $settings['vat_percent'] ?? 10.00;
            $vatFmt = number_format((float)($bill['tax_amount'] ?? 0), 3, '.', '') . " $currency";
            $data .= self::formatRow("VAT ($vatPct%):", $vatFmt, $width);
        } else {
            $halfTax = (float)($bill['tax_amount'] ?? 0) / 2.0;
            $cgstFmt = number_format($halfTax, 3, '.', '') . " $currency";
            $sgstFmt = number_format($halfTax, 3, '.', '') . " $currency";
            $data .= self::formatRow("CGST (" . ($settings['cgst_percent'] ?? 2.5) . "%):", $cgstFmt, $width);
            $data .= self::formatRow("SGST (" . ($settings['sgst_percent'] ?? 2.5) . "%):", $sgstFmt, $width);
        }

        if (isset($bill['discount_amount']) && (float)$bill['discount_amount'] > 0) {
            $discFmt = "-" . number_format((float)$bill['discount_amount'], 3, '.', '') . " $currency";
            $data .= self::formatRow("Discount (" . ($bill['discount_percent'] ?? 0) . "%):", $discFmt, $width);
        }

        $data .= $doubleDiv;

        // Grand Total in Bold & Large
        $grandTotalFmt = number_format((float)($bill['grand_total'] ?? 0), 3, '.', '') . " $currency";
        $data .= self::cmdBold(true);
        $data .= self::cmdSize(2, 2);
        $data .= self::cmdAlign(1); // Center
        $data .= "TOTAL: $grandTotalFmt\n";
        $data .= self::cmdSize(1, 1);
        $data .= self::cmdBold(false);
        $data .= self::cmdAlign(0);

        $data .= $doubleDiv;

        // Payment status
        if (!empty($bill['payment_method'])) {
            $payMethod = strtoupper(str_replace('_', ' ', $bill['payment_method']));
            $payStatus = strtoupper($bill['status'] ?? 'PAID');
            $data .= self::formatRow("Payment: $payMethod", "Status: $payStatus", $width);
        }

        // Footer
        $data .= "\n";
        $data .= self::cmdAlign(1); // Center
        $data .= "Thank you for dining with us!\n";
        $data .= "Please visit again.\n";

        // Feed & Cut
        $data .= self::cmdCut(4);

        return self::sendToPrinter($data);
    }
}
