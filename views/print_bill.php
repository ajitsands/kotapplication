<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Bill | <?= htmlspecialchars(!empty($bill['table_number']) && $bill['table_number'] !== '-' ? 'Table '.$bill['table_number'] : ('Order #'.($bill['order_id'] ?? $bill['id']))) ?></title>
    <style>
        @page {
            margin: 0;
            size: auto;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Courier New", Courier, monospace;
            width: <?= (int)$settings['printer_size'] === 58 ? '52mm' : '76mm' ?>;
            max-width: 100%;
            margin: 0 auto;
            padding: 8px 4px;
            font-size: 12px;
            color: #000;
            background: #fff;
            line-height: 1.35;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        
        .header {
            border-bottom: 1px dashed #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }
        .logo-img {
            max-width: 55px;
            height: auto;
            margin-bottom: 4px;
        }
        .restaurant-name {
            font-size: 16px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Table Layouts for solid columnar structure */
        table.meta-table, table.items-table, table.totals-table {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0;
        }
        table.meta-table td {
            padding: 2px 0;
            font-size: 11px;
            vertical-align: top;
        }
        table.items-table {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            margin: 6px 0;
        }
        table.items-table th {
            border-bottom: 1px dashed #000;
            padding: 4px 0;
            font-size: 11px;
            font-weight: bold;
        }
        table.items-table td {
            padding: 4px 0;
            font-size: 11px;
            vertical-align: top;
        }
        table.totals-table td {
            padding: 2px 0;
            font-size: 11px;
        }
        .grand-total-row td {
            border-top: 1px double #000;
            border-bottom: 1px double #000;
            font-size: 14px;
            font-weight: bold;
            padding: 5px 0;
        }
        .footer {
            border-top: 1px dashed #000;
            margin-top: 10px;
            padding-top: 6px;
            font-size: 10px;
        }
        
        /* On-screen control bar (Hidden during Print) */
        .no-print-bar {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 15px;
            display: flex;
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-print {
            background: #4f46e5;
            color: #fff;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-rawbt {
            background: #10b981;
            color: #fff;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
        }

        @media print {
            .no-print-bar {
                display: none !important;
            }
            body {
                width: 100%;
                padding: 0;
                margin: 0;
            }
        }
    </style>
</head>
<body onload="initPrint()">

    <!-- Control bar for touch screens and mobile tablets -->
    <div class="no-print-bar">
        <button class="btn-print" onclick="window.print()">🖨️ Print Receipt</button>
        <button class="btn-rawbt" onclick="printViaRawBt()">⚡ RawBT (Tab Direct)</button>
        <button class="btn-print" style="background:#6b7280;" onclick="window.close()">❌ Close</button>
    </div>

    <!-- Header Section -->
    <div class="header text-center">
        <?php if (!empty($settings['logo_path'])): 
            $logoUrl = '/' . ltrim($settings['logo_path'], '/');
        ?>
            <img class="logo-img" src="<?= $logoUrl ?>" alt="Logo"><br>
        <?php endif; ?>
        <span class="restaurant-name"><?= htmlspecialchars($settings['restaurant_name']) ?></span><br>
        <span style="font-size: 11px; font-weight: bold;">TAX INVOICE</span>
    </div>

    <!-- Metadata Section -->
    <table class="meta-table">
        <tr>
            <td class="text-left" style="width: 55%;">
                Invoice: <b>#<?= str_pad($bill['id'] ?? $bill['order_id'], 6, '0', STR_PAD_LEFT) ?></b>
            </td>
            <td class="text-right" style="width: 45%;">
                <?php if (!empty($bill['order_type']) && $bill['order_type'] === 'online'): ?>
                    <b>🌐 <?= htmlspecialchars($bill['platform_name'] ?? 'Online') ?></b>
                <?php elseif (!empty($bill['order_type']) && $bill['order_type'] === 'take_away'): ?>
                    <b>🛍️ Takeaway #<?= htmlspecialchars($bill['token_number'] ?? $bill['order_id'] ?? $bill['id']) ?></b>
                <?php else: ?>
                    <b>Table: T<?= htmlspecialchars($bill['table_number'] ?? '-') ?></b>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td class="text-left">
                <?php if (!empty($bill['customer_name'])): ?>
                    Cust: <b><?= htmlspecialchars($bill['customer_name']) ?></b>
                <?php else: ?>
                    Staff: <b><?= htmlspecialchars($bill['waiter_name'] ?? 'Self-Order') ?></b>
                <?php endif; ?>
            </td>
            <td class="text-right">
                Date: <?= date('d-M-Y', strtotime($bill['created_at'])) ?>
            </td>
        </tr>
        <tr>
            <td class="text-left">
                <?php if (!empty($bill['customer_mobile'])): ?>
                    Mob: <b><?= htmlspecialchars($bill['customer_mobile']) ?></b>
                <?php else: ?>
                    Status: <b><?= strtoupper($bill['status'] ?? 'PAID') ?></b>
                <?php endif; ?>
            </td>
            <td class="text-right">
                Time: <?= date('h:i A', strtotime($bill['created_at'])) ?>
            </td>
        </tr>
        <?php if (!empty($bill['payment_method'])): ?>
        <tr>
            <td class="text-left">
                Pay: <b><?= strtoupper(str_replace('_', ' ', $bill['payment_method'])) ?></b>
            </td>
            <td class="text-right">
                Status: <b><?= strtoupper($bill['status'] ?? 'PAID') ?></b>
            </td>
        </tr>
        <?php endif; ?>
    </table>

    <!-- Line Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="text-left" style="width: 50%;">Item</th>
                <th class="text-center" style="width: 15%;">Qty</th>
                <th class="text-right" style="width: 35%;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bill['items'] as $item): 
                $pName = $item['product_name'] ?? $item['name'] ?? 'Item';
                $pQty = $item['total_quantity'] ?? $item['quantity'] ?? 1;
                $pPrice = (float)($item['price'] ?? 0);
                $pSubtotal = (float)($item['subtotal_price'] ?? ($pPrice * $pQty));
            ?>
                <tr>
                    <td class="text-left">
                        <b><?= htmlspecialchars($pName) ?></b><br>
                        <small style="color:#333; font-size:9px;">@ <?= format_price($pPrice) ?></small>
                    </td>
                    <td class="text-center" style="vertical-align: middle;"><b><?= $pQty ?></b></td>
                    <td class="text-right" style="vertical-align: middle;"><b><?= format_price($pSubtotal) ?></b></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Totals Table -->
    <table class="totals-table">
        <tr>
            <td class="text-left">Subtotal:</td>
            <td class="text-right"><b><?= format_price($bill['subtotal']) ?> <?= htmlspecialchars($settings['currency_code']) ?></b></td>
        </tr>
        <?php if ($settings['tax_type'] === 'VAT'): ?>
            <tr>
                <td class="text-left">VAT (<?= htmlspecialchars($settings['vat_percent']) ?>%):</td>
                <td class="text-right"><?= format_price($bill['tax_amount']) ?> <?= htmlspecialchars($settings['currency_code']) ?></td>
            </tr>
        <?php else: // GST ?>
            <?php $halfTax = (float)$bill['tax_amount'] / 2.0; ?>
            <tr>
                <td class="text-left">CGST (<?= htmlspecialchars($settings['cgst_percent']) ?>%):</td>
                <td class="text-right"><?= format_price($halfTax) ?> <?= htmlspecialchars($settings['currency_code']) ?></td>
            </tr>
            <tr>
                <td class="text-left">SGST (<?= htmlspecialchars($settings['sgst_percent']) ?>%):</td>
                <td class="text-right"><?= format_price($halfTax) ?> <?= htmlspecialchars($settings['currency_code']) ?></td>
            </tr>
        <?php endif; ?>

        <?php if (isset($bill['discount_amount']) && (float)$bill['discount_amount'] > 0): ?>
            <tr>
                <td class="text-left">Discount (<?= htmlspecialchars($bill['discount_percent']) ?>%):</td>
                <td class="text-right">-<?= format_price($bill['discount_amount']) ?> <?= htmlspecialchars($settings['currency_code']) ?></td>
            </tr>
        <?php endif; ?>

        <tr class="grand-total-row">
            <td class="text-left">GRAND TOTAL:</td>
            <td class="text-right"><?= format_price($bill['grand_total']) ?> <?= htmlspecialchars($settings['currency_code']) ?></td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer text-center">
        <span>Thank you for dining with us!</span><br>
        <span style="font-size: 9px; display:block; margin-top:2px;">Powered by Gourmet KOT & Bill System</span>
    </div>

    <script>
        function initPrint() {
            // Check if not inside an iframe and auto-trigger print
            if (window.self === window.top) {
                setTimeout(function() {
                    window.print();
                }, 400);
            }
        }

        function printViaRawBt() {
            // Build simple formatted text for RawBT thermal app
            let text = "<?= addslashes(strtoupper($settings['restaurant_name'])) ?>\n";
            text += "TAX INVOICE\n";
            text += "--------------------------------\n";
            text += "Bill: #<?= str_pad($bill['id'] ?? $bill['order_id'], 6, '0', STR_PAD_LEFT) ?>  Table: T<?= $bill['table_number'] ?? '-' ?>\n";
            text += "Date: <?= date('d-M-Y h:i A') ?>\n";
            text += "--------------------------------\n";
            <?php foreach ($bill['items'] as $item): 
                $pName = $item['product_name'] ?? $item['name'] ?? 'Item';
                $pQty = $item['total_quantity'] ?? $item['quantity'] ?? 1;
                $pSubtotal = (float)($item['subtotal_price'] ?? (($item['price'] ?? 0) * $pQty));
            ?>
            text += "<?= addslashes(mb_strimwidth($pName, 0, 18, '..')) ?> x<?= $pQty ?>  <?= format_price($pSubtotal) ?>\n";
            <?php endforeach; ?>
            text += "--------------------------------\n";
            text += "Subtotal: <?= format_price($bill['subtotal']) ?> <?= $settings['currency_code'] ?>\n";
            text += "VAT:      <?= format_price($bill['tax_amount']) ?> <?= $settings['currency_code'] ?>\n";
            text += "TOTAL:    <?= format_price($bill['grand_total']) ?> <?= $settings['currency_code'] ?>\n";
            text += "--------------------------------\n";
            text += "Thank you! Visit again.\n\n\n";

            window.location.href = "rawbt:data:text/plain;base64," + btoa(unescape(encodeURIComponent(text)));
        }
    </script>
</body>
</html>
