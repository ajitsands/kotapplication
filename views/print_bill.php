<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Bill | <?= htmlspecialchars(!empty($bill['table_number']) && $bill['table_number'] !== '-' ? 'Table '.$bill['table_number'] : ('Order #'.($bill['order_id'] ?? $bill['id']))) ?></title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            width: <?= (int)$settings['printer_size'] === 58 ? '52mm' : '76mm' ?>;
            margin: 0;
            padding: 5px;
            font-size: 13px;
            color: #000;
            background: #fff;
            line-height: 1.3;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .header {
            border-bottom: 1px dashed #000;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .logo-img {
            max-width: 55px;
            height: auto;
            margin-bottom: 6px;
        }
        .restaurant-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 8px;
        }
        th {
            border-bottom: 1px solid #000;
            padding: 4px 0;
            font-size: 11px;
        }
        td {
            padding: 5px 0;
            font-size: 12px;
        }
        .totals-section {
            border-top: 1px dashed #000;
            padding-top: 6px;
            margin-top: 6px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 12px;
        }
        .grand-total-row {
            display: flex;
            justify-content: space-between;
            margin: 6px 0;
            font-size: 15px;
            font-weight: bold;
            border-top: 1px double #000;
            border-bottom: 1px double #000;
            padding: 4px 0;
        }
        .footer {
            margin-top: 15px;
            padding-top: 6px;
            border-top: 1px dashed #000;
            font-size: 10px;
        }
        @media print {
            body {
                width: <?= (int)$settings['printer_size'] === 58 ? '52mm' : '76mm' ?>;
            }
        }
    </style>
</head>
<body onload="window.print(); window.onafterprint = function() { window.close(); };">
    <div class="header text-center">
        <?php if (!empty($settings['logo_path'])): 
            $logoUrl = '/' . ltrim($settings['logo_path'], '/');
        ?>
            <img class="logo-img" src="<?= $logoUrl ?>" alt="Logo"><br>
        <?php endif; ?>
        <span class="restaurant-name"><?= htmlspecialchars($settings['restaurant_name']) ?></span><br>
        <span style="font-size: 11px;">TAX INVOICE</span>
    </div>

    <div class="meta-row">
        <span>Order/Bill: <b>#<?= str_pad($bill['id'] ?? $bill['order_id'], 6, '0', STR_PAD_LEFT) ?></b></span>
        <?php if (!empty($bill['order_type']) && $bill['order_type'] === 'online'): ?>
            <span>Type: <b>🌐 <?= htmlspecialchars($bill['platform_name'] ?? 'Online') ?><?= !empty($bill['platform_order_number']) ? ' #'.htmlspecialchars($bill['platform_order_number']) : '' ?></b></span>
        <?php elseif (!empty($bill['order_type']) && $bill['order_type'] === 'take_away'): ?>
            <span>Type: <b>🛍️ Takeaway (Token #<?= htmlspecialchars($bill['token_number'] ?? $bill['order_id'] ?? $bill['id']) ?>)</b></span>
        <?php else: ?>
            <span>Table: <b>T<?= htmlspecialchars($bill['table_number'] ?? '-') ?></b></span>
        <?php endif; ?>
    </div>
    <div class="meta-row">
        <?php if (!empty($bill['customer_name'])): ?>
            <span>Cust: <b><?= htmlspecialchars($bill['customer_name']) ?></b></span>
        <?php else: ?>
            <span>Waiter: <b><?= htmlspecialchars($bill['waiter_name'] ?? 'Self-Order') ?></b></span>
        <?php endif; ?>
        <span>Date: <?= date('d-M-Y', strtotime($bill['created_at'])) ?></span>
    </div>
    <div class="meta-row">
        <?php if (!empty($bill['customer_mobile'])): ?>
            <span>Mobile: <b><?= htmlspecialchars($bill['customer_mobile']) ?></b></span>
        <?php else: ?>
            <span>Status: <b><?= strtoupper($bill['status'] ?? 'PAID') ?></b></span>
        <?php endif; ?>
        <span>Time: <?= date('h:i A', strtotime($bill['created_at'])) ?></span>
    </div>
    <?php if (!empty($bill['payment_method'])): ?>
    <div class="meta-row">
        <span>Payment: <b><?= strtoupper(str_replace('_', ' ', $bill['payment_method'])) ?></b></span>
        <?php if (!empty($bill['customer_mobile'])): ?>
            <span>Status: <b><?= strtoupper($bill['status'] ?? 'PAID') ?></b></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th style="width: 55%; text-align: left;">Item</th>
                <th style="width: 15%; text-align: center;">Qty</th>
                <th style="width: 30%; text-align: right;">Total</th>
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
                    <td>
                        <?= htmlspecialchars($pName) ?><br>
                        <small style="color:#555; font-size:10px;"><?= format_price($pPrice) ?></small>
                    </td>
                    <td class="text-center"><?= $pQty ?></td>
                    <td class="text-right"><?= format_price($pSubtotal) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totals-section">
        <div class="totals-row">
            <span>Subtotal:</span>
            <span><?= format_price($bill['subtotal']) ?> <?= htmlspecialchars($settings['currency_code']) ?></span>
        </div>

        <?php if ($settings['tax_type'] === 'VAT'): ?>
            <div class="totals-row">
                <span>VAT (<?= htmlspecialchars($settings['vat_percent']) ?>%):</span>
                <span><?= format_price($bill['tax_amount']) ?> <?= htmlspecialchars($settings['currency_code']) ?></span>
            </div>
        <?php else: // GST India ?>
            <?php 
                // Split tax amount into equal CGST and SGST
                $halfTax = (float)$bill['tax_amount'] / 2.0;
            ?>
            <div class="totals-row">
                <span>CGST (<?= htmlspecialchars($settings['cgst_percent']) ?>%):</span>
                <span><?= format_price($halfTax) ?> <?= htmlspecialchars($settings['currency_code']) ?></span>
            </div>
            <div class="totals-row">
                <span>SGST (<?= htmlspecialchars($settings['sgst_percent']) ?>%):</span>
                <span><?= format_price($halfTax) ?> <?= htmlspecialchars($settings['currency_code']) ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($bill['discount_amount']) && (float)$bill['discount_amount'] > 0): ?>
            <div class="totals-row">
                <span>Discount (<?= htmlspecialchars($bill['discount_percent']) ?>%):</span>
                <span>-<?= format_price($bill['discount_amount']) ?> <?= htmlspecialchars($settings['currency_code']) ?></span>
            </div>
        <?php endif; ?>

        <div class="grand-total-row">
            <span>GRAND TOTAL:</span>
            <span><?= format_price($bill['grand_total']) ?> <?= htmlspecialchars($settings['currency_code']) ?></span>
        </div>
    </div>

    <div class="footer text-center">
        <span>Thank you for dining with us!</span><br>
        <span style="font-size: 9px; display:block; margin-top:3px;">Powered by Gourmet KOT & Bill System</span>
    </div>
</body>
</html>
