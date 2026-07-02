<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print KOT | <?= htmlspecialchars($kot['kot_number']) ?></title>
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
        }
        .text-center {
            text-align: center;
        }
        .header {
            border-bottom: 1px dashed #000;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .logo-img {
            max-width: 45px;
            height: auto;
            margin-bottom: 4px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin: 2px 0;
        }
        .table-num {
            font-size: 26px;
            font-weight: bold;
            border: 2px solid #000;
            display: inline-block;
            padding: 2px 10px;
            margin: 6px 0;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin: 3px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th {
            border-bottom: 1px solid #000;
            padding: 4px 0;
            font-size: 11px;
        }
        td {
            padding: 6px 0;
            vertical-align: top;
        }
        .item-qty {
            font-size: 18px;
            font-weight: bold;
        }
        .item-name {
            font-size: 14px;
            font-weight: bold;
        }
        .item-notes {
            font-size: 11px;
            font-weight: bold;
            font-style: italic;
            margin-top: 2px;
            display: block;
        }
        .footer {
            border-top: 1px dashed #000;
            margin-top: 12px;
            padding-top: 6px;
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
            // convert logo_path to absolute URL or simple path
            $logoUrl = '/' . ltrim($settings['logo_path'], '/');
        ?>
            <img class="logo-img" src="<?= $logoUrl ?>" alt="Logo"><br>
        <?php endif; ?>
        <span class="title">KITCHEN ORDER TICKET</span><br>
        <div class="table-num">TABLE <?= htmlspecialchars($kot['table_number']) ?></div>
    </div>

    <div class="meta-row">
        <span>No: <b><?= htmlspecialchars($kot['kot_number']) ?></b></span>
        <span>Waiter: <b><?= htmlspecialchars($kot['waiter_name'] ?? 'Self-Order') ?></b></span>
    </div>
    <div class="meta-row">
        <span>Date: <?= date('d-M-Y h:i A', strtotime($kot['created_at'])) ?></span>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%; text-align: left;">Qty</th>
                <th style="width: 85%; text-align: left;">Item / Prep Note</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($kot['items'] as $item): ?>
                <tr style="border-bottom: 1px dashed #ddd;">
                    <td class="item-qty"><?= $item['quantity'] ?></td>
                    <td>
                        <span class="item-name"><?= htmlspecialchars($item['product_name']) ?></span>
                        <?php if (!empty($item['notes'])): ?>
                            <span class="item-notes">* <?= htmlspecialchars($item['notes']) ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer text-center">
        <span>Printed at <?= date('h:i:s A') ?></span>
    </div>
</body>
</html>
