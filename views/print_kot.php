<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print KOT | <?= htmlspecialchars($kot['kot_number']) ?></title>
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
            font-size: 13px;
            color: #000;
            background: #fff;
            line-height: 1.35;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        
        .header {
            border-bottom: 2px dashed #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }
        .title {
            font-size: 16px;
            font-weight: 900;
            margin: 2px 0;
        }
        .table-num {
            font-size: 26px;
            font-weight: 900;
            border: 2px solid #000;
            display: inline-block;
            padding: 2px 14px;
            margin: 6px 0;
        }
        
        table.meta-table, table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0;
        }
        table.meta-table td {
            padding: 2px 0;
            font-size: 11px;
        }
        table.items-table {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            margin: 6px 0;
        }
        table.items-table th {
            border-bottom: 1px solid #000;
            padding: 4px 0;
            font-size: 12px;
            font-weight: bold;
        }
        table.items-table td {
            padding: 6px 0;
            vertical-align: top;
            border-bottom: 1px dashed #ddd;
        }
        .item-qty {
            font-size: 18px;
            font-weight: 900;
            text-align: center;
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
            margin-top: 10px;
            padding-top: 6px;
            font-size: 10px;
        }

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

    <div class="no-print-bar">
        <button class="btn-print" onclick="window.print()">🖨️ Print KOT</button>
        <button class="btn-rawbt" onclick="printKotViaRawBt()">⚡ RawBT (Tab Direct)</button>
        <button class="btn-print" style="background:#6b7280;" onclick="window.close()">❌ Close</button>
    </div>

    <div class="header text-center">
        <span class="title">KITCHEN ORDER TICKET</span><br>
        <div class="table-num">
            <?php if (!empty($kot['order_type']) && $kot['order_type'] === 'take_away'): ?>
                TAKEAWAY #<?= htmlspecialchars($kot['token_number'] ?? $kot['order_id'] ?? $kot['table_number']) ?>
            <?php else: ?>
                TABLE <?= htmlspecialchars($kot['table_number']) ?>
            <?php endif; ?>
        </div>
    </div>

    <table class="meta-table">
        <tr>
            <td class="text-left" style="width: 50%;">KOT: <b><?= htmlspecialchars($kot['kot_number'] ?? $kot['id']) ?></b></td>
            <td class="text-right" style="width: 50%;">Waiter: <b><?= htmlspecialchars($kot['waiter_name'] ?? 'Self-Order') ?></b></td>
        </tr>
        <tr>
            <td class="text-left" colspan="2">Date: <?= date('d-M-Y h:i A', strtotime($kot['created_at'])) ?></td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 20%;">Qty</th>
                <th class="text-left" style="width: 80%;">Item / Prep Note</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($kot['items'] as $item): ?>
                <tr>
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

    <script>
        function initPrint() {
            if (window.self === window.top) {
                setTimeout(function() {
                    window.print();
                }, 400);
            }
        }

        function printKotViaRawBt() {
            let text = "--------------------------------\n";
            text += "     KITCHEN ORDER TICKET       \n";
            text += "--------------------------------\n";
            text += "TABLE: <?= $kot['table_number'] ?>\n";
            text += "KOT #: <?= $kot['kot_number'] ?? $kot['id'] ?>\n";
            text += "Waiter: <?= $kot['waiter_name'] ?? 'Staff' ?>\n";
            text += "Date: <?= date('d-M-Y h:i A') ?>\n";
            text += "================================\n";
            <?php foreach ($kot['items'] as $item): ?>
            text += "[ <?= $item['quantity'] ?> x ] <?= addslashes($item['product_name']) ?>\n";
            <?php if (!empty($item['notes'])): ?>
            text += "  >> NOTE: <?= addslashes($item['notes']) ?>\n";
            <?php endif; ?>
            text += "\n";
            <?php endforeach; ?>
            text += "--------------------------------\n\n\n";

            window.location.href = "rawbt:data:text/plain;base64," + btoa(unescape(encodeURIComponent(text)));
        }
    </script>
</body>
</html>
