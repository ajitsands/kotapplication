<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Display (KOT) | <?= htmlspecialchars($settings['restaurant_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <style>
        :root {
            --bg-color: #080b11;
            --card-bg: rgba(255, 255, 255, 0.02);
            --card-border: rgba(255, 255, 255, 0.06);
            --primary-grad: linear-gradient(135deg, #ec4899, #f43f5e);
            --text-color: #f3f4f6;
            --text-muted: #9ca3af;
            --accent-green: #10b981;
            --accent-orange: #f59e0b;
            --accent-red: #ef4444;
            --ready-color: #06b6d4;
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

        body.light-theme .item-row {
            border-bottom-color: rgba(0,0,0,0.05);
        }

        body.light-theme .item-row.pending .item-status {
            background: rgba(0, 0, 0, 0.03);
            color: #6b7280;
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
                radial-gradient(at 0% 100%, rgba(236, 72, 153, 0.08) 0px, transparent 40%),
                radial-gradient(at 100% 100%, rgba(244, 63, 94, 0.08) 0px, transparent 40%);
            min-height: 100vh;
            padding-bottom: 40px;
            overflow-x: hidden;
        }

        header {
            background: rgba(8, 11, 17, 0.85);
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
            background: linear-gradient(135deg, #ec4899, #f43f5e);
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
            color: var(--accent-red);
            border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-logout:hover {
            background: rgba(239, 68, 68, 0.2);
            color: #ff6b6b;
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.2);
        }

        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .screen-title {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .live-indicator {
            font-size: 13px;
            font-weight: 600;
            background: rgba(16, 185, 129, 0.1);
            color: var(--accent-green);
            padding: 6px 14px;
            border-radius: 20px;
            border: 1px solid rgba(16, 185, 129, 0.2);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background: var(--accent-green);
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(0.9); opacity: 0.5; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(0.9); opacity: 0.5; }
        }

        /* Kitchen Order Grid */
        .kot-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 25px;
        }

        .kot-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            position: relative;
            backdrop-filter: blur(8px);
        }

        /* Urgent pulsing alert if order older than 10 mins */
        .kot-card.urgent {
            border-color: rgba(239, 68, 68, 0.4);
            box-shadow: 0 10px 30px rgba(239, 68, 68, 0.15);
            animation: urgentPulse 2s infinite alternate;
        }

        @keyframes urgentPulse {
            0% { border-color: rgba(239, 68, 68, 0.3); }
            100% { border-color: rgba(239, 68, 68, 0.8); }
        }

        .kot-header {
            padding: 20px;
            background: rgba(255, 255, 255, 0.02);
            border-bottom: 1px solid var(--card-border);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .table-badge {
            font-size: 22px;
            font-weight: 800;
            background: var(--primary-grad);
            color: white;
            padding: 4px 14px;
            border-radius: 12px;
        }

        .kot-meta {
            text-align: right;
        }

        .kot-num {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-muted);
            font-family: monospace;
        }

        .kot-time {
            font-size: 12px;
            color: var(--accent-orange);
            margin-top: 4px;
            font-weight: 600;
        }

        .kot-waiter {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .kot-body {
            padding: 20px;
            flex-grow: 1;
        }

        .items-list {
            list-style: none;
        }

        .item-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .item-row:hover {
            opacity: 0.8;
        }

        .item-qty {
            font-size: 16px;
            font-weight: 800;
            color: var(--accent-orange);
            background: rgba(245, 158, 11, 0.1);
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            flex-shrink: 0;
        }

        .item-details {
            flex-grow: 1;
        }

        .item-name {
            font-size: 15px;
            font-weight: 600;
        }

        .item-notes {
            font-size: 11px;
            color: var(--accent-red);
            background: rgba(239, 68, 68, 0.05);
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 3px;
            font-weight: 500;
        }

        /* Item statuses */
        .item-status {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 4px 8px;
            border-radius: 6px;
            flex-shrink: 0;
        }

        .item-row.pending .item-status {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-muted);
        }

        .item-row.ready {
            text-decoration: line-through;
            opacity: 0.4;
        }

        .item-row.ready .item-status {
            background: rgba(6, 182, 212, 0.1);
            color: var(--ready-color);
        }

        .kot-footer {
            padding: 20px;
            background: rgba(0, 0, 0, 0.15);
            border-top: 1px solid var(--card-border);
            display: flex;
            gap: 10px;
        }

        .btn-action {
            flex-grow: 1;
            padding: 10px;
            border-radius: 10px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            text-decoration: none;
        }

        .btn-ready {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: var(--accent-green);
        }

        .btn-ready:hover {
            background: var(--accent-green);
            color: white;
        }

        .btn-delete-kot {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: var(--accent-red);
        }

        .btn-delete-kot:hover {
            background: var(--accent-red);
            color: white;
        }

        .btn-delete-item {
            background: none;
            border: none;
            color: var(--accent-red);
            cursor: pointer;
            padding: 2px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            opacity: 0.7;
            transition: opacity 0.2s, transform 0.2s;
        }

        .btn-delete-item:hover {
            opacity: 1;
            transform: scale(1.15);
        }

        .btn-print {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--card-border);
            color: var(--text-color);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-print:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 100px 20px;
            background: var(--card-bg);
            border: 1px dashed var(--card-border);
            border-radius: 24px;
            color: var(--text-muted);
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
            background: linear-gradient(135deg, #ec4899, #f43f5e) !important;
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

        .limit-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }

        .limit-btn:hover {
            color: var(--text-color);
        }

        .limit-btn.active {
            background: var(--primary-grad);
            color: white;
        }

        body.light-theme .limit-btn.active {
            color: white;
        }

        .completed-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 14px;
            padding: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }

        .completed-card:hover {
            background: var(--surface-hover);
            border-color: var(--primary-light);
            transform: translateX(2px);
        }

        /* DataTables Custom Theme Overrides for Completed KOTs */
        .dataTables_wrapper {
            color: var(--text-color) !important;
            margin-top: 10px;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--card-border);
            padding: 8px 12px;
            border-radius: 10px;
            color: var(--text-color);
            outline: none;
            width: 100% !important;
            box-sizing: border-box;
            font-family: inherit;
            font-size: 13px;
        }

        body.light-theme .dataTables_wrapper .dataTables_filter input {
            background: rgba(0, 0, 0, 0.03) !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
            color: #1f2937 !important;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--ready-color);
        }

        .dataTables_wrapper .dataTables_paginate {
            color: var(--text-muted) !important;
            font-size: 12px;
            margin-top: 12px;
            display: flex;
            justify-content: center;
            gap: 4px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: var(--text-muted) !important;
            border: 1px solid var(--card-border) !important;
            background: rgba(255, 255, 255, 0.02) !important;
            border-radius: 8px !important;
            padding: 4px 10px !important;
            cursor: pointer;
            transition: all 0.2s;
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
            margin-top: 10px !important;
        }

        #completed-kots-table th {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            font-weight: 600;
            border-bottom: 2px solid var(--card-border) !important;
            padding: 8px 6px !important;
        }

        #completed-kots-table td {
            border-bottom: 1px solid var(--card-border) !important;
            padding: 10px 6px !important;
            vertical-align: middle;
        }

        #completed-kots-table {
            border-bottom: none !important;
        }

        .completed-row-item {
            transition: all 0.2s ease;
        }

        .completed-row-item:hover {
            background: rgba(255, 255, 255, 0.04) !important;
            transform: translateX(2px);
        }

        body.light-theme .completed-row-item:hover {
            background: rgba(0, 0, 0, 0.03) !important;
        }

        body.light-theme #completed-date-filter {
            background: rgba(0, 0, 0, 0.03) !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
            color: #1f2937 !important;
        }

        .dt-header {
            margin-bottom: 8px;
        }
        
        .dt-header .dataTables_filter {
            float: none !important;
            text-align: left !important;
            margin: 0 !important;
        }

        .dt-footer {
            margin-top: 8px;
        }

        @media (max-width: 900px) {
            .container {
                grid-template-columns: 1fr !important;
            }
            .completed-sidebar {
                position: relative !important;
                top: 0 !important;
                max-height: 400px !important;
                margin-bottom: 20px;
            }
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
            <span class="header-title"><?= htmlspecialchars($settings['restaurant_name']) ?> | Kitchen Display</span>
        </div>
        <div class="header-nav">
            <a href="admin" class="nav-link">Admin Dashboard</a>
            <a href="kot" class="nav-link active">KOT Monitor</a>
            <a href="counter" class="nav-link">Billing Counter</a>
            <a href="javascript:void(0)" onclick="changeOwnPasswordPrompt()" class="nav-link" style="margin-right: 5px;">🔑 Change Password</a>
            <button onclick="toggleTheme()" style="background: rgba(255,255,255,0.05); border: 1px solid var(--card-border); color: var(--text-color); cursor: pointer; font-size: 15px; width: 34px; height: 34px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; vertical-align: middle; margin-right: 10px; transition: all 0.3s;">🌓</button>
            <a href="logout" class="btn-logout">Logout</a>
        </div>
    </header>

    <div class="container" style="max-width: 100%; display: grid; grid-template-columns: 360px 1fr; gap: 30px; margin: 30px 20px;">
        <!-- Left Sidebar: Completed KOTs -->
        <div class="completed-sidebar" style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 24px; padding: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); backdrop-filter: blur(8px); display: flex; flex-direction: column; max-height: calc(100vh - 150px); position: sticky; top: 100px;">
            <div style="margin-bottom: 15px;">
                <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 12px; color: var(--text-color); display: flex; align-items: center; justify-content: space-between;">
                    <span>Completed KOTs</span>
                    <span style="font-size: 11px; background: rgba(6,182,212,0.15); color: var(--ready-color); padding: 2px 8px; border-radius: 6px;">History</span>
                </h3>
                
                <!-- Date Filter -->
                <div style="margin-bottom: 12px;">
                    <label style="font-size: 11px; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 4px;">Filter Date:</label>
                    <input type="date" id="completed-date-filter" onchange="fetchCompletedKots()" style="width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--card-border); color: var(--text-color); padding: 8px 12px; border-radius: 10px; font-family: inherit; font-size: 13px; outline: none;" value="<?= date('Y-m-d') ?>">
                </div>

                <!-- Limit Selector -->
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 4px; background: rgba(0,0,0,0.15); padding: 4px; border-radius: 10px; border: 1px solid var(--card-border);">
                    <span style="font-size: 11px; color: var(--text-muted); padding-left: 6px;">Limit:</span>
                    <div style="display: flex; gap: 2px;">
                        <button onclick="setCompletedLimit(20)" class="limit-btn active" id="limit-20">20</button>
                        <button onclick="setCompletedLimit(30)" class="limit-btn" id="limit-30">30</button>
                        <button onclick="setCompletedLimit(50)" class="limit-btn" id="limit-50">50</button>
                        <button onclick="setCompletedLimit(100)" class="limit-btn" id="limit-100">100</button>
                        <button onclick="setCompletedLimit('all')" class="limit-btn" id="limit-all" title="All Completed">ALL</button>
                    </div>
                </div>
            </div>

            <!-- Completed DataTable -->
            <div style="overflow-y: auto; flex-grow: 1; padding-right: 4px; margin-top: 5px;">
                <table id="completed-kots-table" class="display" style="width: 100%; text-align: left;">
                    <thead>
                        <tr>
                            <th>KOT & Waiter</th>
                            <th style="width: 60px; text-align: center;">Table</th>
                            <th style="width: 80px; text-align: right;">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Loaded by DataTables AJAX -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right Side: Active Tickets -->
        <div>
            <h1 class="screen-title" style="margin-top: 0;">
                Active Kitchen Tickets
                <div class="live-indicator">
                    <span class="live-dot"></span> LIVE MONITOR
                </div>
            </h1>

            <div class="kot-grid" id="kot-display-container">
                <!-- Loaded by AJAX -->
                <div class="empty-state">
                    <h3>Loading active kitchen orders...</h3>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fetch active KOT list
        function fetchKots() {
            fetch('kot/items')
                .then(response => response.json())
                .then(data => {
                    renderKots(data.kots);
                })
                .catch(err => console.error('Error fetching KOTs:', err));
        }

        function renderKots(kots) {
            const container = document.getElementById('kot-display-container');
            if (!kots || kots.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-bottom:15px; opacity:0.5;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                        <h3>No Active Kitchen Orders</h3>
                        <p style="margin-top:5px; font-size:14px;">Incoming table orders will automatically appear here.</p>
                    </div>
                `;
                return;
            }

            let html = '';
            kots.forEach(kot => {
                // Check if urgent (older than 10 mins = 600000 ms)
                const createdTime = new Date(kot.created_at).getTime();
                const now = new Date().getTime();
                const timeDiffMins = Math.floor((now - createdTime) / 60000);
                const isUrgent = timeDiffMins >= 10;
                
                // Format relative time label
                let timeLabel = timeDiffMins + 'm ago';
                if (timeDiffMins === 0) timeLabel = 'Just now';

                // Class list for card
                let cardClass = 'kot-card';
                if (isUrgent && kot.status === 'pending') cardClass += ' urgent';

                html += `
                    <div class="${cardClass}" id="kot-${kot.id}">
                        <div class="kot-header">
                            <span class="table-badge">T${kot.table_number}</span>
                            <div class="kot-meta">
                                <div class="kot-num">${kot.kot_number}</div>
                                <div class="kot-time">${timeLabel}</div>
                                <div class="kot-waiter">By: ${kot.waiter_name || 'Self-Order'}</div>
                            </div>
                        </div>
                        <div class="kot-body">
                            <ul class="items-list">
                `;

                kot.items.forEach(item => {
                    const rowClass = item.status === 'ready' || item.status === 'dispatched' ? 'ready' : 'pending';
                    const showDelete = item.status === 'pending';
                    html += `
                        <li class="item-row ${rowClass}" onclick="toggleItemStatus(${item.id})">
                            <div style="display:flex; align-items:center; flex-grow:1; min-width:0;">
                                ${showDelete ? `
                                    <button class="btn-delete-item" onclick="deleteItem(${item.id}, event)" title="Remove item" style="margin-right:8px; flex-shrink:0;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </button>
                                ` : ''}
                                <span class="item-qty">${item.quantity}</span>
                                <div class="item-details" style="min-width:0;">
                                    <span class="item-name" style="word-break:break-word;">${escapeHtml(item.product_name)}</span>
                                    ${item.notes ? `<br><span class="item-notes">Note: ${escapeHtml(item.notes)}</span>` : ''}
                                </div>
                            </div>
                            <span class="item-status" style="margin-left:8px;">${item.status === 'ready' || item.status === 'dispatched' ? 'Ready' : 'Pending'}</span>
                        </li>
                    `;
                });

                html += `
                            </ul>
                        </div>
                        <div class="kot-footer">
                            <button onclick="printKot(${kot.id})" class="btn-action btn-print">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:2px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-3a2 2 0 00-2-2H9a2 2 0 00-2 2v3a2 2 0 002 2zm0-19h8a2 2 0 012 2v3H7V3a2 2 0 012-2z"></path>
                                </svg>
                                Print KOT
                            </button>
                            ${kot.status === 'pending' ? `
                                <button onclick="markKotReady(${kot.id})" class="btn-action btn-ready">Mark Ready</button>
                                <button onclick="deleteKot(${kot.id})" class="btn-action btn-delete-kot">Delete KOT</button>
                            ` : `
                                <span style="flex-grow:1; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; color:var(--ready-color); background:rgba(6,182,212,0.1); border-radius:10px; border:1px dashed rgba(6,182,212,0.2);">TICKET READY</span>
                            `}
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        function toggleItemStatus(itemId) {
            fetch('kot/items/ready/' + itemId, { method: 'POST' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        fetchKots(); // Reload immediately
                    }
                });
        }

        function markKotReady(kotId) {
            fetch('kot/ready/' + kotId, { method: 'POST' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        fetchKots(); // Reload immediately
                    }
                });
        }

        function deleteItem(itemId, event) {
            if (event) event.stopPropagation(); // Prevent toggling the status
            
            Swal.fire({
                title: 'Remove Item?',
                text: 'Are you sure you want to remove this item from the kitchen ticket?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#4b5563',
                confirmButtonText: 'Yes, remove it',
                background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('kot/items/delete/' + itemId, { method: 'POST' })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                fetchKots(); // Reload immediately
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Failed to remove item. It may not be in pending status.',
                                    icon: 'error',
                                    background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                                    color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
                                });
                            }
                        })
                        .catch(err => console.error('Error deleting item:', err));
                }
            });
        }

        function deleteKot(kotId) {
            Swal.fire({
                title: 'Delete KOT?',
                text: 'Are you sure you want to delete this entire kitchen ticket?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#4b5563',
                confirmButtonText: 'Yes, delete it',
                background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('kot/delete/' + kotId, { method: 'POST' })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                fetchKots(); // Reload immediately
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Failed to delete kitchen ticket. It may not be in pending status.',
                                    icon: 'error',
                                    background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                                    color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
                                });
                            }
                        })
                        .catch(err => console.error('Error deleting KOT:', err));
                }
            });
        }

        function printKot(kotId) {
            // Open print window in a small popup
            const url = 'kot/print/' + kotId;
            const printWin = window.open(url, '_blank', 'width=450,height=600,menubar=no,toolbar=no,location=no');
        }

        function escapeHtml(str) {
            return str
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        let completedLimit = 20;
        let completedKotsData = [];
        let completedTable;

        $(document).ready(function() {
            // Initialize completed KOTs DataTable
            completedTable = $('#completed-kots-table').DataTable({
                paging: true,
                searching: true,
                info: false,
                lengthChange: false,
                pageLength: 5,
                ordering: false,
                dom: '<"dt-header"f>t<"dt-footer"p>',
                language: {
                    search: "",
                    searchPlaceholder: "Search KOTs..."
                },
                createdRow: function(row, data, dataIndex) {
                    const kotId = data[3];
                    $(row).attr('onclick', `showCompletedDetails(${kotId})`);
                    $(row).css('cursor', 'pointer');
                    $(row).addClass('completed-row-item');
                }
            });

            // Initial fetch
            fetchKots();
            fetchCompletedKots();

            // Background polling
            setInterval(() => {
                fetchKots();
                fetchCompletedKots();
            }, 4000);
        });

        function setCompletedLimit(limit) {
            completedLimit = limit;
            document.querySelectorAll('.limit-btn').forEach(btn => btn.classList.remove('active'));
            const btnEl = document.getElementById('limit-' + limit);
            if (btnEl) btnEl.classList.add('active');
            fetchCompletedKots();
        }

        function fetchCompletedKots() {
            if (!completedTable) return;
            
            // Skip updating if user is actively searching
            if ($('#completed-kots-table_filter input').is(':focus')) {
                return;
            }

            const dateVal = document.getElementById('completed-date-filter').value;
            const path = window.location.pathname.endsWith('/') ? window.location.pathname.slice(0, -1) : window.location.pathname;
            const rootPath = path.replace(/\/(admin|counter|kot)$/, '');

            fetch(rootPath + '/kot/completed?limit=' + completedLimit + '&date=' + dateVal)
                .then(response => response.json())
                .then(data => {
                    completedKotsData = data.kots;
                    renderCompletedKots(data.kots);
                })
                .catch(err => console.error('Error fetching completed KOTs:', err));
        }

        function renderCompletedKots(kots) {
            if (!completedTable) return;

            completedTable.clear();

            if (!kots || kots.length === 0) {
                completedTable.draw();
                return;
            }

            let rows = [];
            kots.forEach(kot => {
                const createdTime = new Date(kot.created_at).getTime();
                const now = new Date().getTime();
                const timeDiffMins = Math.floor((now - createdTime) / 60000);
                
                let timeLabel = '';
                if (timeDiffMins < 60) {
                    timeLabel = timeDiffMins + 'm ago';
                } else {
                    const date = new Date(kot.created_at);
                    timeLabel = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                }

                // Render KOT column
                const colKot = `
                    <div style="min-width:0;">
                        <div style="font-size: 11px; font-weight: 700; font-family: monospace; color: var(--text-color);">${kot.kot_number}</div>
                        <div style="font-size: 10px; color: var(--text-muted); margin-top: 2px;">By: ${kot.waiter_name || 'Self-Order'}</div>
                    </div>
                `;

                // Render Table column
                const colTable = `
                    <div style="text-align: center;">
                        <span style="font-size: 12px; font-weight: 800; background: var(--primary-grad); color: white; padding: 2px 6px; border-radius: 6px; display: inline-block;">T${kot.table_number}</span>
                    </div>
                `;

                // Render Time column
                const colTime = `
                    <div style="font-size: 11px; color: var(--text-muted); text-align: right;">${timeLabel}</div>
                `;

                rows.push([colKot, colTable, colTime, kot.id]);
            });

            completedTable.rows.add(rows).draw(false);
        }

        function showCompletedDetails(kotId) {
            const kot = completedKotsData.find(k => k.id == kotId);
            if (!kot) return;

            let itemsHtml = '<div style="text-align:left; max-height:300px; overflow-y:auto; padding: 5px;">';
            itemsHtml += '<table style="width:100%; border-collapse:collapse; font-size:14px; color:inherit;">';
            itemsHtml += '<tr style="border-bottom:1px solid var(--card-border); font-weight:700;"><td style="padding:6px 0;">Item</td><td style="padding:6px 0; text-align:right;">Qty</td></tr>';
            
            kot.items.forEach(item => {
                itemsHtml += `<tr style="border-bottom:1px dashed rgba(255,255,255,0.05);"><td style="padding:8px 0; font-weight:600;">${escapeHtml(item.product_name)}${item.notes ? `<br><span style="font-size:11px; color:var(--accent-red);">Note: ${escapeHtml(item.notes)}</span>` : ''}</td><td style="padding:8px 0; text-align:right; font-weight:700; color:var(--accent-orange);">${item.quantity}</td></tr>`;
            });
            
            itemsHtml += '</table></div>';

            Swal.fire({
                title: `Table T${kot.table_number} - Issued Items`,
                html: itemsHtml,
                confirmButtonText: 'Close',
                background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
            });
        }
    </script>

    <!-- Footer -->
    <div style="padding: 30px 20px; text-align: center; font-size: 12px; color: var(--text-muted); border-top: 1px solid var(--card-border); margin-top: 40px;">
        Powered By <a href="javascript:void(0)" onclick="openSandsModal()" style="color: #818cf8; text-decoration: none; font-weight: 600;">SaNDS Lab</a>. All rights reserved to <?= htmlspecialchars($settings['restaurant_name']) ?>
    </div>

    <!-- SaNDS Lab Popup Modal -->
    <div id="sands-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(11, 15, 25, 0.85); backdrop-filter: blur(10px); z-index: 2000; align-items: center; justify-content: center;">
        <div style="background: #ffffff; border: 1px solid rgba(0,0,0,0.08); padding: 35px 25px; border-radius: 24px; text-align: center; max-width: 340px; width: 90%; box-shadow: 0 20px 50px rgba(0,0,0,0.2); position: relative; color: #1f2937;">
            <button onclick="closeSandsModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; color: #6b7280; font-size: 24px; cursor: pointer;">&times;</button>
            <div style="margin-bottom: 20px;">
                <img src="/logos/SaNDSLab-LogoNewUpdated.png" alt="SaNDS Lab Logo" style="max-width: 220px; height: auto; display: block; margin: 0 auto;">
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
