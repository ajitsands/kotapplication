<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Bill | Table <?= htmlspecialchars($bill['table_number']) ?></title>
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
        <span>Bill ID: <b>#<?= str_pad($bill['id'], 6, '0', STR_PAD_LEFT) ?></b></span>
        <span>Table: <b>T<?= htmlspecialchars($bill['table_number']) ?></b></span>
    </div>
    <div class="meta-row">
        <span>Waiter: <b><?= htmlspecialchars($bill['waiter_name'] ?? 'Self-Order') ?></b></span>
        <span>Date: <?= date('d-M-Y', strtotime($bill['created_at'])) ?></span>
    </div>
    <div class="meta-row">
        <span>Time: <?= date('h:i A', strtotime($bill['created_at'])) ?></span>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 55%; text-align: left;">Item</th>
                <th style="width: 15%; text-align: center;">Qty</th>
                <th style="width: 30%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bill['items'] as $item): ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($item['product_name']) ?><br>
                        <small style="color:#555; font-size:10px;"><?= number_format($item['price'], 3) ?></small>
                    </td>
                    <td class="text-center"><?= $item['total_quantity'] ?></td>
                    <td class="text-right"><?= number_format($item['subtotal_price'], 3) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totals-section">
        <div class="totals-row">
            <span>Subtotal:</span>
            <span><?= number_format($bill['subtotal'], 3) ?> <?= htmlspecialchars($settings['currency_code']) ?></span>
        </div>

        <?php if ($settings['tax_type'] === 'VAT'): ?>
            <div class="totals-row">
                <span>VAT (<?= htmlspecialchars($settings['vat_percent']) ?>%):</span>
                <span><?= number_format($bill['tax_amount'], 3) ?> <?= htmlspecialchars($settings['currency_code']) ?></span>
            </div>
        <?php else: // GST India ?>
            <?php 
                // Split tax amount into equal CGST and SGST
                $halfTax = (float)$bill['tax_amount'] / 2.0;
            ?>
            <div class="totals-row">
                <span>CGST (<?= htmlspecialchars($settings['cgst_percent']) ?>%):</span>
                <span><?= number_format($halfTax, 3) ?> <?= htmlspecialchars($settings['currency_code']) ?></span>
            </div>
            <div class="totals-row">
                <span>SGST (<?= htmlspecialchars($settings['sgst_percent']) ?>%):</span>
                <span><?= number_format($halfTax, 3) ?> <?= htmlspecialchars($settings['currency_code']) ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($bill['discount_amount']) && (float)$bill['discount_amount'] > 0): ?>
            <div class="totals-row">
                <span>Discount (<?= htmlspecialchars($bill['discount_percent']) ?>%):</span>
                <span>-<?= number_format($bill['discount_amount'], 3) ?> <?= htmlspecialchars($settings['currency_code']) ?></span>
            </div>
        <?php endif; ?>

        <div class="grand-total-row">
            <span>GRAND TOTAL:</span>
            <span><?= number_format($bill['grand_total'], 3) ?> <?= htmlspecialchars($settings['currency_code']) ?></span>
        </div>
    </div>

    <div class="footer text-center">
        <span>Thank you for dining with us!</span><br>
        <span style="font-size: 9px; display:block; margin-top:3px;">Powered by Gourmet KOT & Bill System</span>
    </div>
</body>
</html>
