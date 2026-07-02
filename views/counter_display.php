<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Counter | <?= htmlspecialchars($settings['restaurant_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <style>
        :root {
            --bg-color: #0b0f19;
            --card-bg: rgba(255, 255, 255, 0.03);
            --card-border: rgba(255, 255, 255, 0.08);
            --primary-grad: linear-gradient(135deg, #6366f1, #a855f7);
            --text-color: #f3f4f6;
            --text-muted: #9ca3af;
            --accent-green: #10b981;
            --accent-orange: #f59e0b;
            --accent-red: #ef4444;
            --billing-blue: #3b82f6;
        }

        body.light-theme {
            --bg-color: #f3f4f6;
            --card-bg: rgba(255, 255, 255, 0.85);
            --card-border: rgba(0, 0, 0, 0.08);
            --text-color: #1f2937;
            --text-muted: #6b7280;
        }

        body.light-theme header {
            background: rgba(255, 255, 255, 0.85);
            border-bottom-color: rgba(0, 0, 0, 0.08);
        }

        body.light-theme .nav-link {
            color: #4b5563;
        }

        body.light-theme .nav-link:hover, body.light-theme .nav-link.active {
            color: #111827;
        }

        body.light-theme th {
            color: #4b5563;
            border-bottom-color: rgba(0, 0, 0, 0.08);
        }

        body.light-theme td {
            border-bottom-color: rgba(0, 0, 0, 0.05);
        }

        body.light-theme .empty-state {
            border-color: rgba(0, 0, 0, 0.08);
            color: #6b7280;
        }

        body.light-theme .btn-print {
            background: rgba(0, 0, 0, 0.03);
            border-color: rgba(0, 0, 0, 0.08);
            color: #1f2937;
        }

        body.light-theme .btn-print:hover {
            background: rgba(0, 0, 0, 0.06);
        }

        body.light-theme .modal-content {
            background: #ffffff;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }

        body.light-theme .pay-method-btn {
            background: rgba(0,0,0,0.02);
            color: #1f2937;
        }

        body.light-theme .pay-method-btn:hover {
            background: rgba(0,0,0,0.05);
        }

        body.light-theme .pay-method-btn.selected {
            background: var(--primary-grad);
            color: #ffffff;
        }

        body.light-theme .modal-close {
            color: #6b7280;
        }

        body.light-theme .modal-close:hover {
            color: #1f2937;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.1) 0px, transparent 40%),
                radial-gradient(at 100% 0%, rgba(168, 85, 247, 0.1) 0px, transparent 40%);
            min-height: 100vh;
            padding-bottom: 50px;
        }

        header {
            background: rgba(11, 15, 25, 0.7);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--card-border);
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-title {
            font-size: 20px;
            font-weight: 800;
            background: var(--primary-grad);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header-nav {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-link {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--text-color);
        }

        .btn-logout {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: var(--accent-red);
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-logout:hover {
            background: var(--accent-red);
            color: white;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .screen-title {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .live-indicator {
            font-size: 13px;
            font-weight: 600;
            background: rgba(59, 130, 246, 0.1);
            color: var(--billing-blue);
            padding: 6px 14px;
            border-radius: 20px;
            border: 1px solid rgba(59, 130, 246, 0.2);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background: var(--billing-blue);
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(0.9); opacity: 0.5; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(0.9); opacity: 0.5; }
        }

        .panel-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        /* Bills Table */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 15px;
            font-size: 13px;
            text-transform: uppercase;
            color: var(--text-muted);
            border-bottom: 1px solid var(--card-border);
        }

        td {
            padding: 18px 15px;
            border-bottom: 1px solid var(--card-border);
            font-size: 15px;
        }

        .table-badge {
            font-size: 18px;
            font-weight: 800;
            background: var(--primary-grad);
            color: white;
            padding: 4px 10px;
            border-radius: 8px;
            display: inline-block;
        }

        .price-text {
            font-family: monospace;
            font-weight: 600;
        }

        .btn-action {
            padding: 8px 16px;
            border-radius: 10px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-print {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--card-border);
            color: var(--text-color);
            margin-right: 8px;
        }

        .btn-print:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .btn-pay {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: var(--accent-green);
        }

        .btn-pay:hover {
            background: var(--accent-green);
            color: white;
        }

        .btn-view-items {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            color: var(--billing-blue);
            margin-right: 8px;
        }

        .btn-view-items:hover {
            background: var(--billing-blue);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: var(--text-muted);
        }

        /* Modal Payment Select */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(11, 15, 25, 0.85);
            backdrop-filter: blur(8px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: #111827;
            border: 1px solid var(--card-border);
            padding: 30px;
            border-radius: 24px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        }

        .payment-methods-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 25px 0;
        }

        .pay-method-btn {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--card-border);
            color: var(--text-color);
            padding: 15px 10px;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            font-family: inherit;
            font-weight: 600;
            font-size: 13px;
        }

        .pay-method-btn:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: #a855f7;
        }

        .pay-method-btn.selected {
            background: var(--primary-grad);
            border-color: transparent;
            color: white;
            box-shadow: 0 4px 12px rgba(168, 85, 247, 0.3);
        }

        .btn-pay-confirm {
            width: 100%;
            background: var(--primary-grad);
            border: none;
            color: white;
            padding: 12px;
            border-radius: 12px;
            font-family: inherit;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            margin-top: 15px;
            font-family: inherit;
            font-weight: 600;
            font-size: 14px;
        }

        .modal-close:hover {
            color: var(--text-color);
        }

        .discount-input {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--card-border);
            color: var(--text-color);
            padding: 8px 12px;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            width: 100%;
            outline: none;
            transition: border-color 0.3s;
        }

        .discount-input:focus {
            border-color: #a855f7;
        }

        body.light-theme .discount-input {
            background: rgba(0, 0, 0, 0.03);
            color: #1f2937;
        }

        .dataTables_wrapper {
            color: var(--text-color) !important;
            margin-top: 15px;
        }
        
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--card-border);
            padding: 8px 12px;
            border-radius: 8px;
            color: var(--text-color);
            outline: none;
        }

        body.light-theme .dataTables_wrapper .dataTables_length select,
        body.light-theme .dataTables_wrapper .dataTables_filter input {
            background: rgba(0, 0, 0, 0.03);
            border-color: rgba(0, 0, 0, 0.08);
            color: #1f2937;
        }

        .dataTables_wrapper .dataTables_length select:focus,
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #a855f7;
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            color: var(--text-muted) !important;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .dataTables_wrapper .dataTables_paginate {
            color: var(--text-muted) !important;
            font-size: 13px;
            margin-top: 15px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: var(--text-muted) !important;
            border: 1px solid var(--card-border) !important;
            background: rgba(255, 255, 255, 0.02) !important;
            border-radius: 8px !important;
            padding: 5px 12px !important;
            transition: all 0.3s;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            color: white !important;
            background: var(--primary-grad) !important;
            border-color: transparent !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            color: white !important;
            background: var(--primary-grad) !important;
            border-color: transparent !important;
            font-weight: 700;
        }

        table.dataTable {
            border-collapse: collapse !important;
            width: 100% !important;
        }

        table.dataTable th, table.dataTable td {
            border-bottom: 1px solid var(--card-border) !important;
        }

        table.dataTable.no-footer {
            border-bottom: 1px solid var(--card-border) !important;
        }

        /* SweetAlert2 Styling overrides */
        .swal2-popup {
            font-family: 'Outfit', sans-serif !important;
            border-radius: 24px !important;
            background: #111827 !important;
            color: #f3f4f6 !important;
            border: 1px solid var(--card-border) !important;
        }
        body.light-theme .swal2-popup {
            background: #ffffff !important;
            color: #1f2937 !important;
            border-color: rgba(0,0,0,0.08) !important;
        }
        .swal2-title {
            color: inherit !important;
            font-family: 'Outfit', sans-serif !important;
        }
        .swal2-html-container {
            color: var(--text-muted) !important;
            font-family: 'Outfit', sans-serif !important;
        }
        .swal2-input {
            font-family: 'Outfit', sans-serif !important;
            border: 1px solid var(--card-border) !important;
            background: rgba(255, 255, 255, 0.05) !important;
            color: var(--text-color) !important;
            border-radius: 12px !important;
            font-size: 14px !important;
            padding: 10px 14px !important;
            box-shadow: none !important;
            margin-top: 10px !important;
        }
        body.light-theme .swal2-input {
            background: rgba(0, 0, 0, 0.03) !important;
            color: #1f2937 !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
        }
        .swal2-confirm {
            background: var(--primary-grad) !important;
            border-radius: 12px !important;
            font-family: 'Outfit', sans-serif !important;
            font-weight: 600 !important;
            font-size: 14px !important;
        }
        .swal2-cancel {
            border-radius: 12px !important;
            font-family: 'Outfit', sans-serif !important;
            font-weight: 600 !important;
            font-size: 14px !important;
        }
        .swal2-validation-message {
            background: rgba(239, 68, 68, 0.1) !important;
            color: var(--accent-red) !important;
            border-radius: 10px !important;
            font-family: 'Outfit', sans-serif !important;
        }
    </style>
</head>
<body>
    <script>
        // Apply theme immediately on load to prevent flash of theme mismatch
        if (localStorage.getItem('theme') === 'light') {
            document.body.classList.add('light-theme');
        }

        function toggleTheme() {
            if (document.body.classList.contains('light-theme')) {
                document.body.classList.remove('light-theme');
                localStorage.setItem('theme', 'dark');
            } else {
                document.body.classList.add('light-theme');
                localStorage.setItem('theme', 'light');
            }
        }
    </script>
    <header>
        <div class="header-logo">
            <span class="header-title"><?= htmlspecialchars($settings['restaurant_name']) ?> | Cashier</span>
        </div>
        <div class="header-nav">
            <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                <a href="admin" class="nav-link">Admin Dashboard</a>
                <a href="kot" class="nav-link">KOT Monitor</a>
            <?php endif; ?>
            <?php if (($_SESSION['user_role'] ?? '') === 'counter' && !$pendingApproval): ?>
                <button onclick="openCloseCounterModal()" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: var(--accent-red); padding: 6px 14px; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer; margin-right: 10px; transition: all 0.3s;">🔒 Close Counter</button>
            <?php endif; ?>
            <a href="counter" class="nav-link active">Billing Counter</a>
            <a href="javascript:void(0)" onclick="changeOwnPasswordPrompt()" class="nav-link" style="margin-right: 5px;">🔑 Change Password</a>
            <button onclick="toggleTheme()" style="background: rgba(255,255,255,0.05); border: 1px solid var(--card-border); color: var(--text-color); cursor: pointer; font-size: 15px; width: 34px; height: 34px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; vertical-align: middle; margin-right: 10px; transition: all 0.3s;">🌓</button>
            <a href="logout" class="btn-logout">Logout</a>
        </div>
    </header>

    <!-- Close Counter Modal -->
    <div id="close-counter-modal" class="modal">
        <div class="modal-content" style="max-width: 500px; text-align: left;">
            <h3 style="text-align: center; margin-bottom: 5px;">🔒 Close Counter Shift</h3>
            <p style="color: var(--text-muted); font-size:13px; text-align: center; margin-bottom: 20px;">Enter the physical amounts you collected during this shift. Admin will verify and approve.</p>

            <!-- Shift Period Selection -->
            <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--card-border); border-radius: 14px; padding: 15px; margin-bottom: 15px;">
                <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700; margin-bottom: 8px;">Shift Date/Time Bounds</div>
                <div style="display: flex; gap: 10px; margin-bottom: 8px;">
                    <div style="flex: 1;">
                        <label style="font-size: 11px; color: var(--text-muted); font-weight: 600; display:block; margin-bottom:4px;">Shift Start</label>
                        <input type="datetime-local" id="close-start-time" class="discount-input" style="font-size: 12px; padding: 6px 10px;" onchange="refreshCloseModalTotals()">
                    </div>
                    <div style="flex: 1;">
                        <label style="font-size: 11px; color: var(--text-muted); font-weight: 600; display:block; margin-bottom:4px;">Shift End</label>
                        <input type="datetime-local" id="close-end-time" class="discount-input" style="font-size: 12px; padding: 6px 10px;" onchange="refreshCloseModalTotals()">
                    </div>
                </div>
            </div>

            <!-- System-calculated totals display -->
            <div style="background: rgba(99,102,241,0.05); border: 1px solid rgba(99,102,241,0.1); border-radius: 14px; padding: 15px; margin-bottom: 20px;">
                <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700; margin-bottom: 8px;">System Calculated Totals (This Shift)</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 13px;">
                    <div>💵 Cash: <strong id="sys-cash-total" style="color:var(--accent-green);">0.000</strong></div>
                    <div>💳 Card: <strong id="sys-card-total" style="color:#60a5fa;">0.000</strong></div>
                    <div>📱 QR: <strong id="sys-qr-total" style="color:var(--accent-orange);">0.000</strong></div>
                    <div>💰 Total: <strong id="sys-grand-total" style="color:#c084fc;">0.000</strong></div>
                </div>
            </div>

            <!-- Cashier declares physical amounts -->
            <div style="margin-bottom: 15px;">
                <label style="font-size: 12px; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 6px;">Cash Collected (Physical Count)</label>
                <input type="number" id="close-cash" class="discount-input" step="0.001" min="0" value="0" placeholder="0.000">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="font-size: 12px; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 6px;">Card Collected</label>
                <input type="number" id="close-card" class="discount-input" step="0.001" min="0" value="0" placeholder="0.000">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="font-size: 12px; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 6px;">QR Collected</label>
                <input type="number" id="close-qr" class="discount-input" step="0.001" min="0" value="0" placeholder="0.000">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="font-size: 12px; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 6px;">Notes (Optional)</label>
                <textarea id="close-notes" class="discount-input" rows="2" placeholder="e.g. Short by 0.500 due to change issue"></textarea>
            </div>

            <button onclick="submitCloseCounter()" class="btn-pay-confirm" style="margin-top: 10px;">Submit Close Request</button>
            <br>
            <button onclick="document.getElementById('close-counter-modal').style.display='none'" class="modal-close">Cancel</button>
        </div>
    </div>


    <div class="container">
        <?php if ($pendingApproval): ?>
            <!-- Beautiful Glassmorphic Pending Approval Card -->
            <div class="panel-card" style="text-align: center; max-width: 600px; margin: 80px auto; padding: 40px 30px;">
                <div style="font-size: 64px; margin-bottom: 20px;">⏳</div>
                <h2 style="font-size: 24px; font-weight: 800; margin-bottom: 15px; background: var(--primary-grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Shift Closure Pending Approval</h2>
                <p style="color: var(--text-muted); font-size: 15px; line-height: 1.6; margin-bottom: 30px;">
                    Your request to close the counter is currently awaiting administrator confirmation. 
                    Once approved, your transaction history will reset to 0, allowing you to start your next shift.
                </p>
                <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--card-border); border-radius: 16px; padding: 20px; text-align: left; margin-bottom: 30px;">
                    <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700; margin-bottom: 10px; border-bottom: 1px solid var(--card-border); padding-bottom: 5px;">Submitted Details</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 14px;">
                        <div>💵 Cash: <strong style="color:var(--accent-green);"><?= number_format($activeSession['collected_cash'] ?? 0, 3) ?> <?= htmlspecialchars($settings['currency_code']) ?></strong></div>
                        <div>💳 Card: <strong style="color:#60a5fa;"><?= number_format($activeSession['collected_card'] ?? 0, 3) ?> <?= htmlspecialchars($settings['currency_code']) ?></strong></div>
                        <div>📱 QR: <strong style="color:var(--accent-orange);"><?= number_format($activeSession['collected_qr'] ?? 0, 3) ?> <?= htmlspecialchars($settings['currency_code']) ?></strong></div>
                        <div>💰 Total: <strong style="color:#c084fc;"><?= number_format($activeSession['collected_total'] ?? 0, 3) ?> <?= htmlspecialchars($settings['currency_code']) ?></strong></div>
                    </div>
                    <?php if (!empty($activeSession['cashier_notes'])): ?>
                        <div style="margin-top: 15px; font-size: 13px; color: var(--text-muted); border-top: 1px dashed var(--card-border); padding-top: 10px;">
                            <strong>Notes:</strong> <?= htmlspecialchars($activeSession['cashier_notes']) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <a href="logout" class="btn-logout" style="display: inline-block; padding: 12px 30px; font-size: 15px; border-radius: 12px;">Logout</a>
            </div>
        <?php else: ?>
            <!-- Collection Summary Dashboard -->
            <div class="panel-card" style="margin-bottom: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px; border-bottom: 1px solid var(--card-border); padding-bottom: 15px;">
                    <h2 style="font-size: 20px; font-weight: 800; display: flex; align-items: center; gap: 10px;">
                        📊 Collection Summary 
                        <span id="summary-user-badge" class="table-badge" style="background: rgba(99, 102, 241, 0.1); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.2); font-size:12px; padding: 3px 10px;">Loading...</span>
                    </h2>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <span style="font-size: 13px; font-weight: 600; color: var(--text-muted);">Date Range:</span>
                        <input type="date" id="summary-start-date" class="discount-input" style="width: 150px; padding: 8px 12px;" value="<?= date('Y-m-d') ?>" onchange="fetchSummary()">
                        <span style="font-size: 13px; color: var(--text-muted); font-weight: 600;">to</span>
                        <input type="date" id="summary-end-date" class="discount-input" style="width: 150px; padding: 8px 12px;" value="<?= date('Y-m-d') ?>" onchange="fetchSummary()">
                    </div>
                </div>

                <!-- Stats Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
                    <!-- Cash Card -->
                    <div style="background: rgba(16, 185, 129, 0.04); border: 1px solid rgba(16, 185, 129, 0.1); border-radius: 16px; padding: 20px; display: flex; align-items: center; gap: 15px;">
                        <div style="font-size: 32px; background: rgba(16, 185, 129, 0.1); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">💵</div>
                        <div>
                            <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Cash Payment</div>
                            <div id="summary-cash-total" style="font-size: 20px; font-weight: 800; color: var(--accent-green); margin-top: 4px;">0.000 BHD</div>
                        </div>
                    </div>

                    <!-- Card Card -->
                    <div style="background: rgba(59, 130, 246, 0.04); border: 1px solid rgba(59, 130, 246, 0.1); border-radius: 16px; padding: 20px; display: flex; align-items: center; gap: 15px;">
                        <div style="font-size: 32px; background: rgba(59, 130, 246, 0.1); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">💳</div>
                        <div>
                            <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Card Payment</div>
                            <div id="summary-card-total" style="font-size: 20px; font-weight: 800; color: #60a5fa; margin-top: 4px;">0.000 BHD</div>
                        </div>
                    </div>

                    <!-- QR Card -->
                    <div style="background: rgba(245, 158, 11, 0.04); border: 1px solid rgba(245, 158, 11, 0.1); border-radius: 16px; padding: 20px; display: flex; align-items: center; gap: 15px;">
                        <div style="font-size: 32px; background: rgba(245, 158, 11, 0.1); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">📱</div>
                        <div>
                            <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">QR Pay</div>
                            <div id="summary-qr-total" style="font-size: 20px; font-weight: 800; color: var(--accent-orange); margin-top: 4px;">0.000 BHD</div>
                        </div>
                    </div>

                    <!-- Total Card -->
                    <div style="background: rgba(168, 85, 247, 0.05); border: 1px solid rgba(168, 85, 247, 0.15); border-radius: 16px; padding: 20px; display: flex; align-items: center; gap: 15px;">
                        <div style="font-size: 32px; background: rgba(168, 85, 247, 0.1); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">💰</div>
                        <div>
                            <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Total Collected</div>
                            <div id="summary-grand-total" style="font-size: 24px; font-weight: 800; color: #c084fc; margin-top: 4px;">0.000 BHD</div>
                        </div>
                    </div>
                </div>

                <!-- Admin Cashier Breakdown Section -->
                <div id="admin-breakdown-container" style="display: none; border-top: 1px dashed var(--card-border); padding-top: 20px; margin-top: 10px;">
                    <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 12px; color: var(--text-color);">Cashier wise breakdown:</h3>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 1px solid var(--card-border);">
                                    <th style="text-align: left; padding: 8px 10px; font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Cashier Name</th>
                                    <th style="text-align: right; padding: 8px 10px; font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Cash Collected</th>
                                    <th style="text-align: right; padding: 8px 10px; font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Card Collected</th>
                                    <th style="text-align: right; padding: 8px 10px; font-size: 11px; text-transform: uppercase; color: var(--text-muted);">QR Collected</th>
                                    <th style="text-align: right; padding: 8px 10px; font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Total Collected</th>
                                </tr>
                            </thead>
                            <tbody id="breakdown-table-body">
                                <!-- Loaded Dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <h1 class="screen-title">
                Pending Guest Bills
                <div class="live-indicator">
                    <span class="live-dot"></span> REALTIME CHECKOUT
                </div>
            </h1>

            <div class="panel-card" style="margin-bottom: 25px;">
                <div id="bills-table-container">
                    <!-- Loaded by AJAX -->
                    <div class="empty-state">
                        <h3>Loading pending billing checkouts...</h3>
                    </div>
                </div>
            </div>

            <!-- Customer Directory Card -->
            <h1 class="screen-title" style="margin-top: 45px;">
                👥 Registered Customers (Loyalty Directory)
            </h1>

            <div class="panel-card" style="margin-bottom: 25px;">
                <div style="overflow-x: auto;">
                    <table id="cashier-customers-table">
                        <thead>
                            <tr>
                                <th style="text-align: left;">Customer Name</th>
                                <th style="text-align: left;">Mobile Number</th>
                                <th style="text-align: left;">Gender</th>
                                <th style="text-align: right; width: 120px;">Total Visits</th>
                                <th style="text-align: right; width: 160px;">Total Amount Spent</th>
                                <th style="text-align: right; width: 160px;">Total Discount</th>
                                <th style="text-align: right; width: 160px;">Signup Date</th>
                            </tr>
                        </thead>
                        <tbody id="customers-table-body">
                            <tr>
                                <td colspan="7" style="text-align:center; padding:30px; color:var(--text-muted);">Loading customer directory...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Collect Payment Modal -->
    <div id="payment-modal" class="modal">
        <div class="modal-content" style="max-width: 420px;">
            <h3>Collect Payment</h3>
            <p style="color: var(--text-muted); font-size:14px; margin-top:5px;" id="modal-table-label">Table 0</p>
            
            <div style="font-size: 28px; font-weight: 800; margin-top: 15px; color: var(--text-color);" id="modal-amount-label">
                0.000 BHD
            </div>

            <div class="payment-methods-grid">
                <button class="pay-method-btn selected" onclick="selectPaymentMethod('cash', this)">
                    <span style="font-size:20px;">💵</span>
                    Cash
                </button>
                <button class="pay-method-btn" onclick="selectPaymentMethod('card', this)">
                    <span style="font-size:20px;">💳</span>
                    Card
                </button>
                <button class="pay-method-btn" onclick="selectPaymentMethod('qr_pay', this)">
                    <span style="font-size:20px;">📱</span>
                    QR Pay
                </button>
            </div>

            <!-- Customer Loyalty Details -->
            <div style="margin-top: 15px; text-align: left; background: rgba(255,255,255,0.02); padding: 15px; border-radius: 16px; border: 1px solid var(--card-border);">
                <div style="font-size: 13px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; margin-bottom: 10px; border-bottom: 1px solid var(--card-border); padding-bottom: 6px;">Loyalty / Customer Info</div>
                
                <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <div style="flex: 1;">
                        <label style="font-size: 11px; color: var(--text-muted); font-weight: 600; display:block; margin-bottom:4px;">Mobile Number</label>
                        <input type="tel" id="modal-cust-mobile" class="discount-input" placeholder="e.g. 33445566" oninput="lookupCustomerLoyalty()">
                    </div>
                    <div style="flex: 1;">
                        <label style="font-size: 11px; color: var(--text-muted); font-weight: 600; display:block; margin-bottom:4px;">Customer Name</label>
                        <input type="text" id="modal-cust-name" class="discount-input" placeholder="Customer Name">
                    </div>
                </div>

                <div style="margin-bottom: 10px;">
                    <label style="font-size: 11px; color: var(--text-muted); font-weight: 600; display:block; margin-bottom:4px;">Gender</label>
                    <select id="modal-cust-gender" class="discount-input" style="width:100%;">
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <!-- Live Loyalty Info display -->
                <div id="modal-loyalty-info" style="display:none; font-size:12px; padding:10px; border-radius:10px; background:rgba(99, 102, 241, 0.08); border:1px solid rgba(99, 102, 241, 0.2); cursor:pointer;" onclick="showVisitHistory()">
                    <!-- Updated dynamically -->
                </div>
            </div>

            <!-- Discount Section -->
            <div style="margin-top: 20px; text-align: left; background: rgba(255,255,255,0.02); padding: 15px; border-radius: 16px; border: 1px solid var(--card-border);">
                <label style="font-size: 13px; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 8px;">Discount Percentage (%)</label>
                <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 12px;">
                    <input type="number" id="modal-discount-input" min="0" max="100" value="0" oninput="calculateDiscountedTotal()" class="discount-input" placeholder="e.g. 10">
                    <span style="font-weight: 800; font-size: 16px; color: var(--text-muted);">%</span>
                </div>

                <div style="display: flex; justify-content: space-between; font-size: 13px; margin-top: 8px; color: var(--text-muted);">
                    <span>Discount Amount:</span>
                    <span id="modal-discount-amount-label" class="price-text">0.000 BHD</span>
                </div>

                <div style="display: flex; justify-content: space-between; font-size: 22px; font-weight: 800; border-top: 1px dashed var(--card-border); margin-top: 10px; padding-top: 10px;">
                    <span>Net Payable:</span>
                    <span id="modal-net-payable-label" style="color: var(--accent-green);" class="price-text">0.000 BHD</span>
                </div>
            </div>

            <button onclick="confirmPayment()" class="btn-pay-confirm" style="margin-top: 20px;">Complete Checkout</button>
            <br>
            <button onclick="closePaymentModal()" class="modal-close">Cancel</button>
        </div>
    </div>

    <!-- View Bill Items Modal -->
    <div id="bill-items-modal" class="modal">
        <div class="modal-content" style="max-width: 500px; text-align: left;">
            <h3 style="text-align: center; margin-bottom: 5px;">Bill Details</h3>
            <p style="color: var(--text-muted); font-size:14px; text-align: center; margin-bottom: 20px;" id="view-modal-table-label">Table 0</p>
            
            <div style="max-height: 250px; overflow-y: auto; margin-bottom: 20px; border-bottom: 1px solid var(--card-border);">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--card-border);">
                            <th style="padding: 8px 5px; font-size: 11px; text-transform: uppercase; color: var(--text-muted); text-align: left;">Item</th>
                            <th style="padding: 8px 5px; font-size: 11px; text-transform: uppercase; color: var(--text-muted); text-align: center; width: 60px;">Qty</th>
                            <th style="padding: 8px 5px; font-size: 11px; text-transform: uppercase; color: var(--text-muted); text-align: right; width: 90px;">Price</th>
                            <th style="padding: 8px 5px; font-size: 11px; text-transform: uppercase; color: var(--text-muted); text-align: right; width: 100px;">Total</th>
                        </tr>
                    </thead>
                    <tbody id="view-modal-items-body">
                        <!-- Loaded dynamically -->
                    </tbody>
                </table>
            </div>

            <div style="padding-top: 5px; font-size: 14px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color: var(--text-muted); font-weight: 500;">Subtotal:</span>
                    <span id="view-modal-subtotal" class="price-text">0.000 BHD</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color: var(--text-muted); font-weight: 500;" id="view-modal-tax-type">Tax:</span>
                    <span id="view-modal-tax" class="price-text" style="color: var(--accent-orange);">0.000 BHD</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 12px; padding-top: 10px; border-top: 1px dashed var(--card-border); font-size: 18px; font-weight: 800;">
                    <span>Grand Total:</span>
                    <span id="view-modal-grand-total" style="color: var(--accent-green);" class="price-text">0.000 BHD</span>
                </div>
            </div>

            <div style="text-align: center; margin-top: 25px;">
                <button onclick="closeBillItemsModal()" class="btn-pay-confirm" style="width: auto; padding: 10px 40px; display: inline-block;">Close</button>
            </div>
        </div>
    </div>

    <script>
        const basePath = window.location.pathname.endsWith('/') ? window.location.pathname.slice(0, -1) : window.location.pathname;
        const rootPath = basePath.replace(/\/counter$/, '');
        let pendingBills = [];
        let selectedBillId = null;
        let selectedPaymentMethod = 'cash';
        let customerLoyaltyData = null;
        const currencyCode = '<?= htmlspecialchars($settings['currency_code']) ?>';

        function fetchBills() {
            fetch(basePath + '/bills')
                .then(res => res.json())
                .then(data => {
                    pendingBills = data.bills;
                    renderBills(data.bills);
                })
                .catch(err => console.error('Error fetching bills:', err));
        }

        function renderBills(bills) {
            const container = document.getElementById('bills-table-container');
            if (!bills || bills.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-bottom:15px; opacity:0.5;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3>No Bills Awaiting Payment</h3>
                        <p style="margin-top:5px; font-size:14px;">Tables closed by waiters will display here immediately.</p>
                    </div>
                `;
                return;
            }

            // Group bills by table_number
            const groups = {};
            bills.forEach(bill => {
                const tbl = bill.table_number;
                if (!groups[tbl]) {
                    groups[tbl] = [];
                }
                groups[tbl].push(bill);
            });

            // Sort table numbers numerically
            const sortedTables = Object.keys(groups).sort((a, b) => parseInt(a) - parseInt(b));

            let html = '';

            sortedTables.forEach(tableNum => {
                const tableBills = groups[tableNum];
                const isGrouped = tableBills.length > 1;

                html += `
                    <div class="table-group-card" style="margin-bottom: 25px; border: 1px solid var(--card-border); border-radius: 16px; overflow: hidden; background: rgba(255,255,255,0.01);">
                        <div class="table-group-header" style="background: rgba(255,255,255,0.03); padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--card-border);">
                            <div style="display:flex; align-items:center; gap: 10px;">
                                <span class="table-badge" style="font-size:14px; padding:6px 12px;">Table T${tableNum}</span>
                                <span style="font-size:13px; color:var(--text-muted); font-weight:600;">(${tableBills.length} pending bill${tableBills.length > 1 ? 's' : ''})</span>
                            </div>
                            ${isGrouped ? `
                                <button onclick="mergeBills(${tableNum})" class="btn-action" style="background: var(--primary-grad); border: none; color: white; padding: 6px 14px; font-size: 12px; font-weight: 700; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                    </svg>
                                    Merge All Bills
                                </button>
                            ` : ''}
                        </div>
                        <div style="padding: 10px 15px; overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="border-bottom:1px solid var(--card-border);">
                                        <th style="text-align: left; padding: 10px; font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Bill ID</th>
                                        <th style="text-align: left; padding: 10px; font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Closed Time</th>
                                        <th style="text-align: left; padding: 10px; font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Waiter</th>
                                        <th style="text-align: right; padding: 10px; font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Subtotal</th>
                                        <th style="text-align: right; padding: 10px; font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Tax</th>
                                        <th style="text-align: right; padding: 10px; font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Grand Total</th>
                                        <th style="text-align: center; padding: 10px; font-size: 11px; text-transform: uppercase; color: var(--text-muted); width: 380px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;

                tableBills.forEach(bill => {
                    const sub = parseFloat(bill.subtotal).toFixed(3);
                    const tax = parseFloat(bill.tax_amount).toFixed(3);
                    const grand = parseFloat(bill.grand_total).toFixed(3);
                    const date = new Date(bill.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                    html += `
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.02);">
                            <td style="padding: 12px 10px; font-weight: 700; color: var(--text-muted);">#${bill.id}</td>
                            <td style="padding: 12px 10px;">${date}</td>
                            <td style="padding: 12px 10px; font-weight:600;">${bill.waiter_name || 'Self-Order'}</td>
                            <td style="padding: 12px 10px; text-align: right;" class="price-text">${sub} ${currencyCode}</td>
                            <td style="padding: 12px 10px; text-align: right;" class="price-text">${tax} ${currencyCode}</td>
                            <td style="padding: 12px 10px; text-align: right; font-weight:700; color:var(--accent-green);" class="price-text">${grand} ${currencyCode}</td>
                            <td style="padding: 12px 10px; text-align: center;">
                                <button onclick="openBillItemsModal(${bill.id})" class="btn-action btn-view-items" style="margin-right: 4px; padding: 5px 10px; font-size: 11px;">
                                    Items
                                </button>
                                <button onclick="printBill(${bill.id})" class="btn-action btn-print" style="margin-right: 4px; padding: 5px 10px; font-size: 11px;">
                                    Print
                                </button>
                                <button onclick="deleteBill(${bill.id})" class="btn-action" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: var(--accent-red); margin-right: 4px; padding: 5px 10px; font-size: 11px; border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                                    Delete
                                </button>
                                <button onclick="openPaymentModal(${bill.id})" class="btn-action btn-pay" style="padding: 5px 12px; font-size:11px;">Pay</button>
                            </td>
                        </tr>
                    `;
                });

                html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        function openBillItemsModal(billId) {
            fetch(basePath + '/bill/' + billId)
                .then(res => res.json())
                .then(data => {
                    const bill = data.bill;
                    if (!bill) return;

                    document.getElementById('view-modal-table-label').innerText = 'Table ' + bill.table_number + ' • ' + (bill.waiter_name || 'Self-Order');
                    
                    let itemsHtml = '';
                    bill.items.forEach(item => {
                        const price = parseFloat(item.price).toFixed(3);
                        const total = parseFloat(item.subtotal_price).toFixed(3);
                        itemsHtml += `
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                                <td style="padding: 10px 5px; font-weight: 500; font-size: 14px; text-align: left;">${item.product_name}</td>
                                <td style="padding: 10px 5px; text-align: center; font-weight: 600; font-size: 14px;">${item.total_quantity}</td>
                                <td style="padding: 10px 5px; text-align: right; font-size: 14px;" class="price-text">${price} ${currencyCode}</td>
                                <td style="padding: 10px 5px; text-align: right; font-weight: 600; color: var(--text-color); font-size: 14px;" class="price-text">${total} ${currencyCode}</td>
                            </tr>
                        `;
                    });
                    
                    document.getElementById('view-modal-items-body').innerHTML = itemsHtml;
                    document.getElementById('view-modal-subtotal').innerText = parseFloat(bill.subtotal).toFixed(3) + ' ' + currencyCode;
                    
                    const taxLabel = bill.tax_amount > 0 ? 'Tax Amount:' : 'Tax:';
                    document.getElementById('view-modal-tax-type').innerText = taxLabel;
                    document.getElementById('view-modal-tax').innerText = parseFloat(bill.tax_amount).toFixed(3) + ' ' + currencyCode;
                    
                    document.getElementById('view-modal-grand-total').innerText = parseFloat(bill.grand_total).toFixed(3) + ' ' + currencyCode;
                    
                    document.getElementById('bill-items-modal').style.display = 'flex';
                })
                .catch(err => console.error('Error loading bill details:', err));
        }

        function closeBillItemsModal() {
            document.getElementById('bill-items-modal').style.display = 'none';
        }

        function printBill(billId) {
            const url = rootPath + '/counter/print/' + billId;
            const printWin = window.open(url, '_blank', 'width=450,height=600,menubar=no,toolbar=no,location=no');
        }

        function openPaymentModal(billId) {
            const bill = pendingBills.find(b => b.id === billId);
            if (!bill) return;

            selectedBillId = billId;
            selectedPaymentMethod = 'cash';

            document.getElementById('modal-table-label').innerText = 'Complete payment for Table ' + bill.table_number;
            document.getElementById('modal-amount-label').innerText = parseFloat(bill.grand_total).toFixed(3) + ' ' + currencyCode;
            
            // Reset discount fields
            document.getElementById('modal-discount-input').value = 0;
            document.getElementById('modal-discount-amount-label').innerText = '0.000 ' + currencyCode;
            document.getElementById('modal-net-payable-label').innerText = parseFloat(bill.grand_total).toFixed(3) + ' ' + currencyCode;

            // Reset customer loyalty fields
            document.getElementById('modal-cust-mobile').value = '';
            document.getElementById('modal-cust-name').value = '';
            document.getElementById('modal-cust-gender').value = '';
            document.getElementById('modal-loyalty-info').style.display = 'none';
            customerLoyaltyData = null;

            // Reset button highlights
            document.querySelectorAll('.pay-method-btn').forEach(btn => btn.classList.remove('selected'));
            document.querySelector('.pay-method-btn').classList.add('selected');

            document.getElementById('payment-modal').style.display = 'flex';
        }

        function calculateDiscountedTotal() {
            if (!selectedBillId) return;
            const bill = pendingBills.find(b => b.id === selectedBillId);
            if (!bill) return;

            const grandTotal = parseFloat(bill.grand_total);
            const discountInput = document.getElementById('modal-discount-input');
            let discountPercent = parseFloat(discountInput.value) || 0;

            // Constrain between 0 and 100
            if (discountPercent < 0) {
                discountPercent = 0;
                discountInput.value = 0;
            } else if (discountPercent > 100) {
                discountPercent = 100;
                discountInput.value = 100;
            }

            const discountAmount = grandTotal * (discountPercent / 100);
            const netPayable = grandTotal - discountAmount;

            document.getElementById('modal-discount-amount-label').innerText = discountAmount.toFixed(3) + ' ' + currencyCode;
            document.getElementById('modal-net-payable-label').innerText = netPayable.toFixed(3) + ' ' + currencyCode;
        }

        function closePaymentModal() {
            document.getElementById('payment-modal').style.display = 'none';
        }

        function selectPaymentMethod(method, element) {
            selectedPaymentMethod = method;
            document.querySelectorAll('.pay-method-btn').forEach(btn => btn.classList.remove('selected'));
            element.classList.add('selected');
        }

        function confirmPayment() {
            if (!selectedBillId) return;

            const discountPercent = parseFloat(document.getElementById('modal-discount-input').value) || 0;
            const customerName = document.getElementById('modal-cust-name').value.trim();
            const customerMobile = document.getElementById('modal-cust-mobile').value.trim();
            const gender = document.getElementById('modal-cust-gender').value;

            fetch(basePath + '/pay/' + selectedBillId, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    payment_method: selectedPaymentMethod,
                    discount_percent: discountPercent,
                    customer_name: customerName,
                    customer_mobile: customerMobile,
                    gender: gender
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    closePaymentModal();
                    fetchBills(); // Refresh list
                    fetchSummary(); // Refresh summary totals
                    fetchCustomers(); // Refresh customer loyalty directory list
                }
            })
            .catch(err => console.error('Error confirming payment:', err));
        }

        function lookupCustomerLoyalty() {
            const mobile = document.getElementById('modal-cust-mobile').value.trim();
            const loyaltyInfoDiv = document.getElementById('modal-loyalty-info');
            
            if (mobile.length < 4) {
                loyaltyInfoDiv.style.display = 'none';
                customerLoyaltyData = null;
                return;
            }

            fetch(basePath + '/customer/lookup?mobile=' + encodeURIComponent(mobile))
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.exists) {
                        customerLoyaltyData = data;
                        document.getElementById('modal-cust-name').value = data.customer.name;
                        document.getElementById('modal-cust-gender').value = data.customer.gender || '';
                        
                        loyaltyInfoDiv.innerHTML = `
                            ⭐ <strong>Loyalty Member</strong><br>
                            Total Spent: <strong>${parseFloat(data.total_spent).toFixed(3)} ${currencyCode}</strong><br>
                            Total Discount: <strong>${parseFloat(data.total_discount).toFixed(3)} ${currencyCode}</strong><br>
                            Visits: <strong>${data.visit_count} visit${data.visit_count > 1 ? 's' : ''}</strong><br>
                            <span style="font-size:10px; color:#818cf8; text-decoration:underline; font-weight:600; display:inline-block; margin-top:4px;">👁️ Click to view visit history</span>
                        `;
                        loyaltyInfoDiv.style.display = 'block';
                    } else {
                        customerLoyaltyData = null;
                        loyaltyInfoDiv.style.display = 'none';
                    }
                })
                .catch(err => console.error('Error looking up customer loyalty:', err));
        }

        function showVisitHistory() {
            if (!customerLoyaltyData || !customerLoyaltyData.visits || customerLoyaltyData.visits.length === 0) return;
            
            let tableHtml = `
                <div style="max-height: 250px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--card-border); color:var(--text-muted);">
                                <th style="padding: 8px 5px;">Date</th>
                                <th style="padding: 8px 5px;">Payment</th>
                                <th style="padding: 8px 5px; text-align: right;">Discount</th>
                                <th style="padding: 8px 5px; text-align: right;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            customerLoyaltyData.visits.forEach(v => {
                const dateStr = new Date(v.created_at).toLocaleDateString([], { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
                const amount = parseFloat(v.grand_total).toFixed(3);
                const discount = parseFloat(v.discount_amount || 0).toFixed(3);
                const payLabel = v.payment_method === 'cash' ? '💵 Cash' : (v.payment_method === 'card' ? '💳 Card' : '📱 QR Pay');
                tableHtml += `
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                        <td style="padding: 8px 5px;">${dateStr}</td>
                        <td style="padding: 8px 5px;">${payLabel}</td>
                        <td style="padding: 8px 5px; text-align: right; color:var(--accent-orange);">${discount} ${currencyCode}</td>
                        <td style="padding: 8px 5px; text-align: right; font-weight:700; color:var(--accent-green);">${amount} ${currencyCode}</td>
                    </tr>
                `;
            });
            
            tableHtml += `
                        </tbody>
                    </table>
                </div>
            `;
            
            Swal.fire({
                title: 'Customer Visit History',
                html: tableHtml,
                confirmButtonColor: '#6366f1',
                background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
            });
        }

        function deleteBill(billId) {
            Swal.fire({
                title: 'Delete Bill?',
                text: 'Are you sure you want to delete this bill? This will reopen the table order for editing.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#4b5563',
                confirmButtonText: 'Yes, delete & reopen',
                background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(basePath + '/bills/delete/' + billId, { method: 'POST' })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: 'The bill has been deleted, and the table order is now active.',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false,
                                    background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                                    color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
                                });
                                fetchBills();
                                fetchSummary(); // Refresh summary totals
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Failed to delete bill.',
                                    icon: 'error',
                                    background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                                    color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
                                });
                            }
                        })
                        .catch(err => console.error('Error deleting bill:', err));
                }
            });
        }

        function mergeBills(tableNumber) {
            Swal.fire({
                title: 'Merge Bills?',
                text: 'Are you sure you want to merge all pending bills for Table T' + tableNumber + ' into one single bill?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#6366f1',
                cancelButtonColor: '#4b5563',
                confirmButtonText: 'Yes, merge them',
                background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(basePath + '/bills/merge/' + tableNumber, { method: 'POST' })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Merged!',
                                    text: 'All bills for Table T' + tableNumber + ' have been merged successfully.',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false,
                                    background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                                    color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
                                });
                                fetchBills();
                                fetchSummary(); // Refresh summary totals
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Failed to merge bills.',
                                    icon: 'error',
                                    background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                                    color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
                                });
                            }
                        })
                        .catch(err => console.error('Error merging bills:', err));
                }
            });
        }

        function fetchSummary() {
            const startDate = document.getElementById('summary-start-date').value;
            const endDate = document.getElementById('summary-end-date').value;
            fetch(basePath + '/summary?start_date=' + encodeURIComponent(startDate) + '&end_date=' + encodeURIComponent(endDate))
                .then(res => res.json())
                .then(data => {
                    const sum = data.summary;
                    const cur = currencyCode;
                    
                    document.getElementById('summary-cash-total').innerText = parseFloat(sum.cash_total).toFixed(3) + ' ' + cur;
                    document.getElementById('summary-card-total').innerText = parseFloat(sum.card_total).toFixed(3) + ' ' + cur;
                    document.getElementById('summary-qr-total').innerText = parseFloat(sum.qr_total).toFixed(3) + ' ' + cur;
                    document.getElementById('summary-grand-total').innerText = parseFloat(sum.grand_total).toFixed(3) + ' ' + cur;
                    
                    const badge = document.getElementById('summary-user-badge');
                    const breakdownContainer = document.getElementById('admin-breakdown-container');
                    
                    if (data.role === 'admin') {
                        badge.innerText = 'Admin View (All Cashiers)';
                        badge.style.background = 'rgba(168, 85, 247, 0.1)';
                        badge.style.color = '#c084fc';
                        badge.style.borderColor = 'rgba(168, 85, 247, 0.2)';
                        
                        // Show breakdown
                        breakdownContainer.style.display = 'block';
                        
                        const tbody = document.getElementById('breakdown-table-body');
                        if (data.breakdown && data.breakdown.length > 0) {
                            let rows = '';
                            data.breakdown.forEach(row => {
                                rows += `
                                    <tr style="border-bottom: 1px solid var(--card-border);">
                                        <td style="padding: 12px 10px; font-weight:600; text-align:left;">${row.cashier_name}</td>
                                        <td style="padding: 12px 10px; text-align:right;" class="price-text">${parseFloat(row.cash_total).toFixed(3)} ${cur}</td>
                                        <td style="padding: 12px 10px; text-align:right;" class="price-text">${parseFloat(row.card_total).toFixed(3)} ${cur}</td>
                                        <td style="padding: 12px 10px; text-align:right;" class="price-text">${parseFloat(row.qr_total).toFixed(3)} ${cur}</td>
                                        <td style="padding: 12px 10px; text-align:right; font-weight:700; color:var(--accent-green);" class="price-text">${parseFloat(row.grand_total).toFixed(3)} ${cur}</td>
                                    </tr>
                                `;
                            });
                            tbody.innerHTML = rows;
                        } else {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="5" style="text-align:center; padding:15px; color:var(--text-muted); font-size:13px;">No cashier collections recorded for this date.</td>
                                </tr>
                            `;
                        }
                    } else {
                        badge.innerText = 'My Shift Only';
                        badge.style.background = 'rgba(16, 185, 129, 0.1)';
                        badge.style.color = 'var(--accent-green)';
                        badge.style.borderColor = 'rgba(16, 185, 129, 0.2)';
                        
                        breakdownContainer.style.display = 'none';
                    }
                })
                .catch(err => console.error('Error fetching summary:', err));
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function fetchCustomers() {
            fetch(basePath + '/customers')
                .then(res => res.json())
                .then(data => {
                    // Destroy DataTable instance if already exists
                    if ($.fn.DataTable.isDataTable('#cashier-customers-table')) {
                        $('#cashier-customers-table').DataTable().destroy();
                    }

                    const tbody = document.getElementById('customers-table-body');
                    if (data.customers && data.customers.length > 0) {
                        let html = '';
                        data.customers.forEach(c => {
                            const dateStr = new Date(c.created_at).toLocaleDateString([], { year: 'numeric', month: 'short', day: 'numeric' });
                            const spent = parseFloat(c.total_spent).toFixed(3);
                            const discount = parseFloat(c.total_discount || 0).toFixed(3);
                            const genderLabel = c.gender ? c.gender : '<span style="color:var(--text-muted); font-style:italic;">Not Specified</span>';
                            
                            html += `
                                <tr style="border-bottom: 1px solid var(--card-border);">
                                    <td style="padding: 12px 15px; font-weight:600; text-align:left;">${escapeHtml(c.name)}</td>
                                    <td style="padding: 12px 15px; text-align:left;">${escapeHtml(c.mobile)}</td>
                                    <td style="padding: 12px 15px; text-align:left; font-size: 13px;">${genderLabel}</td>
                                    <td style="padding: 12px 15px; text-align:right; font-weight:600;">${c.visit_count}</td>
                                    <td style="padding: 12px 15px; text-align:right; font-weight:700; color:var(--accent-green);" class="price-text">${spent} ${currencyCode}</td>
                                    <td style="padding: 12px 15px; text-align:right; font-weight:700; color:var(--accent-red);" class="price-text">${discount} ${currencyCode}</td>
                                    <td style="padding: 12px 15px; text-align:right; color:var(--text-muted); font-size:13px;">${dateStr}</td>
                                </tr>
                            `;
                        });
                        tbody.innerHTML = html;
                    } else {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="7" style="text-align:center; padding:30px; color:var(--text-muted);">No registered customers found.</td>
                            </tr>
                        `;
                    }

                    // Initialize DataTable
                    $('#cashier-customers-table').DataTable({
                        order: [[4, 'desc']], // sort by total spent desc
                        pageLength: 10,
                        destroy: true,
                        dom: '<"dt-header"f>rt<"dt-footer"ip>',
                        language: {
                            search: "",
                            searchPlaceholder: "Search customers..."
                        }
                    });
                })
                .catch(err => console.error('Error fetching customers list:', err));
        }

        <?php if (!$pendingApproval): ?>
        // Start polling
        fetchBills();
        fetchSummary();
        fetchCustomers();
        setInterval(fetchBills, 4000);
        setInterval(fetchSummary, 10000);

        function openCloseCounterModal() {
            fetch(basePath + '/session')
                .then(res => res.json())
                .then(data => {
                    const session = data.session;
                    if (!session) {
                        Swal.fire({
                            title: 'Error',
                            text: 'No active session found.',
                            icon: 'error',
                            background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                            color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
                        });
                        return;
                    }
                    
                    const cur = currencyCode;
                    document.getElementById('sys-cash-total').innerText = parseFloat(session.cash_total).toFixed(3) + ' ' + cur;
                    document.getElementById('sys-card-total').innerText = parseFloat(session.card_total).toFixed(3) + ' ' + cur;
                    document.getElementById('sys-qr-total').innerText = parseFloat(session.qr_total).toFixed(3) + ' ' + cur;
                    document.getElementById('sys-grand-total').innerText = parseFloat(session.system_total).toFixed(3) + ' ' + cur;
                    
                    // pre-populate inputs with system calculations so cashier can adjust them
                    document.getElementById('close-cash').value = parseFloat(session.cash_total).toFixed(3);
                    document.getElementById('close-card').value = parseFloat(session.card_total).toFixed(3);
                    document.getElementById('close-qr').value = parseFloat(session.qr_total).toFixed(3);
                    document.getElementById('close-notes').value = '';

                    // set start and end time inputs
                    document.getElementById('close-start-time').value = formatSqlDatetime(session.opened_at);
                    document.getElementById('close-end-time').value = formatNowLocal();
                    
                    document.getElementById('close-counter-modal').style.display = 'flex';
                })
                .catch(err => console.error('Error fetching session info:', err));
        }

        function formatSqlDatetime(sqlDateStr) {
            if (!sqlDateStr) return '';
            return sqlDateStr.replace(' ', 'T').slice(0, 16);
        }

        function formatNowLocal() {
            const d = new Date();
            const pad = (n) => n.toString().padStart(2, '0');
            return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
        }

        function refreshCloseModalTotals() {
            const startTimeStr = document.getElementById('close-start-time').value;
            const endTimeStr = document.getElementById('close-end-time').value;
            
            const startTime = startTimeStr ? startTimeStr.replace('T', ' ') + ':00' : '';
            const endTime = endTimeStr ? endTimeStr.replace('T', ' ') + ':00' : '';

            const cur = currencyCode;
            fetch(basePath + '/session?start_time=' + encodeURIComponent(startTime) + '&end_time=' + encodeURIComponent(endTime))
                .then(res => res.json())
                .then(data => {
                    const session = data.session;
                    const totals = data.totals;
                    if (!session || !totals) return;
                    
                    document.getElementById('sys-cash-total').innerText = parseFloat(totals.cash_total).toFixed(3) + ' ' + cur;
                    document.getElementById('sys-card-total').innerText = parseFloat(totals.card_total).toFixed(3) + ' ' + cur;
                    document.getElementById('sys-qr-total').innerText = parseFloat(totals.qr_total).toFixed(3) + ' ' + cur;
                    document.getElementById('sys-grand-total').innerText = parseFloat(totals.system_total).toFixed(3) + ' ' + cur;
                    
                    document.getElementById('close-cash').value = parseFloat(totals.cash_total).toFixed(3);
                    document.getElementById('close-card').value = parseFloat(totals.card_total).toFixed(3);
                    document.getElementById('close-qr').value = parseFloat(totals.qr_total).toFixed(3);
                })
                .catch(err => console.error('Error refreshing session totals:', err));
        }

        function submitCloseCounter() {
            const collectedCash = parseFloat(document.getElementById('close-cash').value) || 0;
            const collectedCard = parseFloat(document.getElementById('close-card').value) || 0;
            const collectedQr = parseFloat(document.getElementById('close-qr').value) || 0;
            const notes = document.getElementById('close-notes').value;

            const startTimeStr = document.getElementById('close-start-time').value;
            const endTimeStr = document.getElementById('close-end-time').value;
            const startTime = startTimeStr ? startTimeStr.replace('T', ' ') + ':00' : '';
            const endTime = endTimeStr ? endTimeStr.replace('T', ' ') + ':00' : '';

            Swal.fire({
                title: 'Submit Close Request?',
                text: 'Are you sure you want to request counter closure? You will be logged out and cannot perform further transactions until Admin confirms.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6366f1',
                cancelButtonColor: '#4b5563',
                confirmButtonText: 'Yes, submit request',
                background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(basePath + '/session/close', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            collected_cash: collectedCash,
                            collected_card: collectedCard,
                            collected_qr: collectedQr,
                            notes: notes,
                            start_time: startTime,
                            end_time: endTime
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Submitted!',
                                text: 'Close request submitted. Redirecting to logout...',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false,
                                background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                                color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
                            }).then(() => {
                                window.location.href = rootPath + '/logout';
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.error || 'Failed to submit close request.',
                                icon: 'error',
                                background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                                color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
                            });
                        }
                    })
                    .catch(err => console.error('Error submitting close request:', err));
                }
            });
        }
        <?php endif; ?>
    </script>

    <!-- Footer -->
    <div class="page-footer" style="padding: 30px 20px; text-align: center; font-size: 12px; color: var(--text-muted); border-top: 1px solid var(--card-border); margin-top: 40px;">
        Powered By <a href="javascript:void(0)" onclick="openSandsModal()" style="color: #818cf8; text-decoration: none; font-weight: 600;">SaNDS Lab</a>. All rights reserved to <?= htmlspecialchars($settings['restaurant_name']) ?>
    </div>

    <!-- SaNDS Lab Popup Modal -->
    <div id="sands-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(11, 15, 25, 0.85); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); z-index: 2000; align-items: center; justify-content: center;">
        <div style="background: #ffffff; border: 1px solid rgba(0,0,0,0.08); padding: 35px 25px; border-radius: 24px; text-align: center; max-width: 340px; width: 90%; box-shadow: 0 20px 50px rgba(0,0,0,0.2); position: relative; color: #1f2937;">
            <button onclick="closeSandsModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; color: #6b7280; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
            <div style="margin-bottom: 20px;">
                <img src="/logos/SaNDSLab-LogoNewUpdated.png" alt="SaNDS Lab Logo" style="max-width: 170px; height: auto; display: block; margin: 0 auto;">
            </div>
            <h3 style="font-size: 20px; font-weight: 800; color: #1f2937; margin-bottom: 5px;">SaNDS Lab</h3>
            <p style="font-size: 13px; font-weight: 600; color: #6b7280; margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.5px;">Custom Software Developers</p>
            <p style="font-size: 11px; font-weight: 700; color: #7c3aed; margin-bottom: 25px; text-transform: uppercase; letter-spacing: 1.5px;">AI Powered</p>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <a href="https://www.sandslab.com" target="_blank" style="display: block; background: linear-gradient(135deg, #6366f1, #a855f7); padding: 12px; border-radius: 12px; color: white; font-size: 14px; font-weight: 600; text-decoration: none; box-shadow: 0 4px 12px rgba(168,85,247,0.3);">🌐 Visit Website</a>
                <a href="https://wa.me/97335078079" target="_blank" style="display: flex; align-items: center; justify-content: center; gap: 8px; background: #25d366; padding: 12px; border-radius: 12px; color: white; font-size: 14px; font-weight: 600; text-decoration: none; box-shadow: 0 4px 12px rgba(37,211,102,0.3);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.513 2.262 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.503-5.739-1.45L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.42 9.864-9.864.002-2.637-1.03-5.118-2.91-6.999-1.88-1.882-4.36-2.914-7.001-2.915-5.442 0-9.867 4.42-9.871 9.866-.002 2.015.528 3.985 1.536 5.736l-.991 3.616 3.7-.977zm11.452-6.52c-.29-.145-1.716-.847-1.982-.944-.265-.098-.458-.146-.65.145-.193.292-.748.944-.917 1.138-.17.19-.338.213-.628.068-.29-.145-1.226-.452-2.336-1.443-.864-.77-1.447-1.722-1.616-2.012-.17-.29-.018-.447.127-.59.13-.13.29-.338.435-.508.145-.17.193-.29.29-.483.097-.19.048-.36-.024-.505-.072-.145-.65-1.568-.89-2.146-.233-.56-.47-.483-.65-.492-.168-.008-.362-.01-.555-.01-.193 0-.507.072-.77.36-.266.29-1.014.992-1.014 2.42 0 1.427 1.038 2.805 1.182 3 .145.195 2.043 3.12 4.95 4.377.69.298 1.23.477 1.65.61.693.22 1.325.19 1.822.115.555-.083 1.716-.7 1.96-1.375.242-.676.242-1.256.17-1.376-.073-.12-.266-.194-.556-.34z"/></svg>
                    Contact Now
                </a>
            </div>
        </div>
    </div>

    <script>
        function openSandsModal() { document.getElementById('sands-modal').style.display = 'flex'; }
        function closeSandsModal() { document.getElementById('sands-modal').style.display = 'none'; }

        function changeOwnPasswordPrompt() {
            Swal.fire({
                title: 'Change Password',
                html:
                    '<input id="swal-current-password" class="swal2-input" type="password" placeholder="Current Password">' +
                    '<input id="swal-new-password" class="swal2-input" type="password" placeholder="New Password">',
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Change Password',
                showLoaderOnConfirm: true,
                background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6',
                preConfirm: () => {
                    const currentPassword = document.getElementById('swal-current-password').value;
                    const newPassword = document.getElementById('swal-new-password').value;
                    if (!currentPassword || !newPassword) {
                        Swal.showValidationMessage('Both fields are required');
                        return false;
                    }
                    const basePath = window.location.pathname.endsWith('/') ? window.location.pathname.slice(0, -1) : window.location.pathname;
                    const rootPath = basePath.replace(/\/(admin|counter|kot)$/, '');
                    return fetch(rootPath + '/user/change-password', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            current_password: currentPassword,
                            new_password: newPassword
                        })
                    })
                    .then(response => {
                        return response.json().then(data => {
                            if (!response.ok) {
                                throw new Error(data.error || response.statusText);
                            }
                            return data;
                        });
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Request failed: ${error.message || error}`);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed && result.value && result.value.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Your password has been successfully changed.',
                        icon: 'success',
                        background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                        color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
                    });
                }
            });
        }
    </script>
</body>
</html>
