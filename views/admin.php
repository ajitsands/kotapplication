<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | <?= htmlspecialchars($settings['restaurant_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Ladda/1.0.6/ladda-themeless.min.css">
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

        body.light-theme .form-input, body.light-theme select, body.light-theme textarea {
            background: rgba(0, 0, 0, 0.03);
            color: #1f2937;
            border-color: rgba(0, 0, 0, 0.08);
        }

        body.light-theme .form-input:focus, body.light-theme select:focus, body.light-theme textarea:focus {
            background: rgba(0, 0, 0, 0.05);
            border-color: #a855f7;
        }

        body.light-theme th {
            color: #4b5563;
            border-bottom-color: rgba(0, 0, 0, 0.08);
        }

        body.light-theme td {
            border-bottom-color: rgba(0, 0, 0, 0.05);
        }

        body.light-theme .nav-link {
            color: #4b5563;
        }

        body.light-theme .nav-link:hover, body.light-theme .nav-link.active {
            color: #111827;
        }

        body.light-theme .btn-print {
            background: rgba(0, 0, 0, 0.03);
            border-color: rgba(0, 0, 0, 0.08);
            color: #1f2937;
        }

        body.light-theme .modal-content {
            background: #ffffff;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }

        body.light-theme tr th {
            background: #e5e7eb;
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

        .header-logo img {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            object-fit: cover;
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
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* DataTables Custom Dark Theme Overrides */
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

        .dt-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }

        .dt-header .dataTables_length,
        .dt-header .dataTables_filter {
            margin-bottom: 0 !important;
            float: none !important;
        }

        .dt-header .dataTables_filter label {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dt-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 15px;
        }

        .dt-footer .dataTables_info,
        .dt-footer .dataTables_paginate {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
            float: none !important;
        }

        /* Tabs — content visibility */
        .tab-content {
            display: none;
            animation: fadeIn 0.4s ease;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Nav Dropdown */
        .nav-dropdown {
            position: relative;
        }

        .nav-dropdown-trigger {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--card-border);
            color: var(--text-color);
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            font-weight: 700;
            padding: 9px 16px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            white-space: nowrap;
            min-width: 210px;
        }

        .nav-dropdown-trigger:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(99, 102, 241, 0.4);
        }

        .nav-dropdown-trigger .trigger-icon {
            width: 26px;
            height: 26px;
            border-radius: 7px;
            background: var(--primary-grad);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
        }

        .nav-dropdown-trigger .trigger-label {
            flex: 1;
            text-align: left;
        }

        .nav-dropdown-trigger .chevron {
            font-size: 10px;
            color: var(--text-muted);
            transition: transform 0.25s;
        }

        .nav-dropdown.open .chevron {
            transform: rotate(180deg);
        }

        .nav-dropdown-menu {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            background: rgba(17, 24, 39, 0.98);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 8px;
            min-width: 240px;
            z-index: 500;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            animation: dropdownFadeIn 0.2s ease;
        }

        body.light-theme .nav-dropdown-menu {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        }

        @keyframes dropdownFadeIn {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .nav-dropdown.open .nav-dropdown-menu {
            display: block;
        }

        .nav-dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            background: none;
            border: none;
            color: var(--text-muted);
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            font-weight: 600;
            padding: 10px 12px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: left;
        }

        .nav-dropdown-item:hover {
            background: rgba(99, 102, 241, 0.1);
            color: var(--text-color);
        }

        .nav-dropdown-item.active {
            background: var(--primary-grad);
            color: #ffffff;
        }

        .nav-dropdown-item .item-icon {
            font-size: 15px;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }

        /* Grid layouts */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 30px;
        }

        .panel-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .panel-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--card-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Forms styling */
        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-input, select, textarea {
            width: 100%;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--card-border);
            padding: 12px 16px;
            border-radius: 12px;
            color: var(--text-color);
            font-family: inherit;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #a855f7;
            background: rgba(255, 255, 255, 0.07);
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            font-weight: 500;
            font-size: 14px;
        }

        .checkbox-container input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--primary-grad);
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(168, 85, 247, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(168, 85, 247, 0.5);
        }

        /* Tables & Lists */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            text-align: left;
            padding: 12px 16px;
            font-size: 13px;
            text-transform: uppercase;
            color: var(--text-muted);
            border-bottom: 1px solid var(--card-border);
        }

        td {
            padding: 16px;
            border-bottom: 1px solid var(--card-border);
            font-size: 14px;
        }

        .img-preview-mini {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            object-fit: cover;
            background: rgba(255, 255, 255, 0.05);
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.15);
            color: var(--accent-red);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-delete:hover {
            background: var(--accent-red);
            color: white;
        }

        /* Tables grid layout */
        .tables-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
        }

        .table-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            position: relative;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .table-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }

        .table-num {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .table-status {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 15px;
        }

        .status-available {
            background: rgba(16, 185, 129, 0.1);
            color: var(--accent-green);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .status-occupied {
            background: rgba(245, 158, 11, 0.1);
            color: var(--accent-orange);
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .status-billing {
            background: rgba(99, 102, 241, 0.1);
            color: #818cf8;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        .btn-qr {
            display: block;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--card-border);
            color: var(--text-color);
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-qr:hover {
            background: var(--primary-grad);
            border-color: transparent;
            color: white;
        }

        /* QR Code Modal preview */
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
            max-width: 320px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
            animation: slideUp 0.3s ease;
        }

        .modal-close {
            margin-top: 20px;
            display: inline-block;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--card-border);
            color: var(--text-color);
            padding: 8px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
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
        
        function toggleSectionDropdown() {
            document.getElementById('section-dropdown').classList.toggle('open');
        }

        function selectSection(id, icon, label) {
            // Switch content pane
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.getElementById(id).classList.add('active');
            // Save state
            localStorage.setItem('admin_active_tab', id);
            // Update dropdown items
            document.querySelectorAll('.nav-dropdown-item').forEach(el => el.classList.remove('active'));
            document.getElementById('item-' + id).classList.add('active');
            // Update trigger label
            document.getElementById('dropdown-icon').textContent = icon;
            document.getElementById('dropdown-label').textContent = label;
            // Close dropdown
            document.getElementById('section-dropdown').classList.remove('open');
            // Fire data-fetch hooks (same as old switchTab)
            if (id === 'closures' && typeof fetchClosures === 'function') fetchClosures();
            else if (id === 'customers' && typeof fetchAdminCustomers === 'function') fetchAdminCustomers();
            else if (id === 'tax_reports' && typeof loadTaxReport === 'function') loadTaxReport();
            else if (id === 'analytics' && typeof loadAnalyticsReport === 'function') loadAnalyticsReport();
            else if (id === 'waiter_report' && typeof loadWaiterPerformanceReport === 'function') loadWaiterPerformanceReport();
        }

        // Close dropdown on outside click
        document.addEventListener('click', function(e) {
            const dd = document.getElementById('section-dropdown');
            if (dd && !dd.contains(e.target)) {
                dd.classList.remove('open');
            }
        });

        // Tab map for restore
        const TAB_MAP = {
            catalog:     { icon: '📋', label: 'Menu Catalog' },
            settings:    { icon: '⚙️', label: 'Settings & Taxes' },
            tables:      { icon: '🪑', label: 'Tables & QR Codes' },
            users:       { icon: '👥', label: 'Users Management' },
            closures:    { icon: '🔄', label: 'Counter Shifts' },
            customers:   { icon: '🎁', label: 'Customers (Loyalty)' },
            tax_reports: { icon: '🧾', label: 'VAT/Tax Reports' },
            analytics:   { icon: '📊', label: 'Sales & Product Analytics' },
            waiter_report: { icon: '🤵', label: 'Waiter Performance' }
        };

        // Restore active tab from localStorage
        (function() {
            const saved = localStorage.getItem('admin_active_tab') || 'catalog';
            const meta = TAB_MAP[saved] || TAB_MAP['catalog'];
            selectSection(saved, meta.icon, meta.label);
        })();
    </script>
    <header>
        <div style="display: flex; align-items: center; gap: 16px;">
            <div class="header-logo">
                <?php if (!empty($settings['logo_path'])): ?>
                    <img src="<?= htmlspecialchars($settings['logo_path']) ?>" alt="Logo">
                <?php endif; ?>
                <span class="header-title"><?= htmlspecialchars($settings['restaurant_name']) ?></span>
            </div>

            <!-- Section Dropdown -->
            <div class="nav-dropdown" id="section-dropdown">
                <button class="nav-dropdown-trigger" onclick="toggleSectionDropdown()" id="dropdown-trigger">
                    <span class="trigger-icon" id="dropdown-icon">📋</span>
                    <span class="trigger-label" id="dropdown-label">Menu Catalog</span>
                    <span class="chevron">▼</span>
                </button>
                <div class="nav-dropdown-menu" id="section-dropdown-menu">
                    <button class="nav-dropdown-item active" id="item-catalog" onclick="selectSection('catalog','📋','Menu Catalog')">
                        <span class="item-icon">📋</span> Menu Catalog
                    </button>
                    <button class="nav-dropdown-item" id="item-settings" onclick="selectSection('settings','⚙️','Settings &amp; Taxes')">
                        <span class="item-icon">⚙️</span> Settings &amp; Taxes
                    </button>
                    <button class="nav-dropdown-item" id="item-tables" onclick="selectSection('tables','🪑','Tables &amp; QR Codes')">
                        <span class="item-icon">🪑</span> Tables &amp; QR Codes
                    </button>
                    <button class="nav-dropdown-item" id="item-users" onclick="selectSection('users','👥','Users Management')">
                        <span class="item-icon">👥</span> Users Management
                    </button>
                    <button class="nav-dropdown-item" id="item-closures" onclick="selectSection('closures','🔄','Counter Shifts')">
                        <span class="item-icon">🔄</span> Counter Shifts
                    </button>
                    <button class="nav-dropdown-item" id="item-customers" onclick="selectSection('customers','🎁','Customers (Loyalty)')">
                        <span class="item-icon">🎁</span> Customers (Loyalty)
                    </button>
                    <button class="nav-dropdown-item" id="item-tax_reports" onclick="selectSection('tax_reports','🧾','VAT/Tax Reports')">
                        <span class="item-icon">🧾</span> VAT/Tax Reports
                    </button>
                    <button class="nav-dropdown-item" id="item-analytics" onclick="selectSection('analytics','📊','Sales &amp; Product Analytics')">
                        <span class="item-icon">📊</span> Sales &amp; Product Analytics
                    </button>
                    <button class="nav-dropdown-item" id="item-waiter_report" onclick="selectSection('waiter_report','🤵','Waiter Performance')">
                        <span class="item-icon">🤵</span> Waiter Performance
                    </button>
                </div>
            </div>
        </div>

        <div class="header-nav">
            <a href="kot" class="nav-link">KOT Monitor</a>
            <a href="counter" class="nav-link">Billing Counter</a>
            <a href="javascript:void(0)" onclick="showWaiterLoginQr()" class="nav-link" style="background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.2); color: #818cf8; padding: 6px 12px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; font-size: 13px; margin-right: 5px;">📱 Waiter QR</a>
            <a href="javascript:void(0)" onclick="changeOwnPasswordPrompt()" class="nav-link" style="margin-right: 5px;">🔑 Change Password</a>
            <button onclick="toggleTheme()" style="background: rgba(255,255,255,0.05); border: 1px solid var(--card-border); color: var(--text-color); cursor: pointer; font-size: 15px; width: 34px; height: 34px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; vertical-align: middle; margin-right: 10px; transition: all 0.3s;">🌓</button>
            <a href="logout" class="btn-logout">Logout</a>
        </div>
    </header>

    <div class="container">

        <!-- Catalog Tab -->
        <div id="catalog" class="tab-content active">
            <div class="grid-2">
                <!-- Categories Card -->
                <div class="panel-card">
                    <h2 class="panel-title" id="cat-form-title">Add Category</h2>
                    <form id="category-form" action="admin/categories" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="cat-id">
                        <div class="form-group">
                            <label class="form-label">Category Name</label>
                            <input class="form-input" type="text" name="name" id="cat-name-input" placeholder="e.g. Starter" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category Banner</label>
                            <input type="file" id="cat-image-input" name="image" accept="image/*">
                            <input type="hidden" name="cropped_image_category" id="cat-cropped-image-data">
                            <div id="cat-cropped-preview-container" style="display: none; align-items: center; gap: 10px; margin-top: 10px;">
                                <img id="cat-cropped-preview-img" src="" style="width: 100px; height: 50px; border-radius: 8px; object-fit: cover; border: 1px solid var(--card-border);">
                                <span style="font-size: 12px; color: var(--accent-green); font-weight: 600;">✓ Banner Cropped</span>
                            </div>
                        </div>
                        <div style="display:flex; gap:10px;">
                            <button type="submit" class="btn-primary" id="cat-submit-btn">Add Category</button>
                            <button type="button" class="btn-delete" style="display:none; background: rgba(255,255,255,0.05); color: var(--text-color);" id="btn-cancel-cat-edit" onclick="resetCategoryForm()">Cancel Edit</button>
                        </div>
                    </form>

                    <h2 class="panel-title" style="margin-top: 30px;">Categories List</h2>
                    <table id="categories-table">
                        <thead>
                            <tr>
                                <th>Banner</th>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td>
                                        <?php if ($cat['image_url']): ?>
                                            <img src="<?= htmlspecialchars($cat['image_url']) ?>" class="img-preview-mini">
                                        <?php else: ?>
                                            <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; font-size: 10px; color: var(--text-muted);">None</div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-weight: 600; cursor: pointer; color: #818cf8; text-decoration: underline;" onclick="filterProductsByCategory(<?= $cat['id'] ?>, '<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>')">
                                        <?= htmlspecialchars($cat['name']) ?> 📁
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <button type="button" class="btn-primary" style="padding: 6px 10px; font-size: 12px; background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.2); color: #818cf8; box-shadow: none; display: inline-flex; align-items: center; justify-content: center;" onclick='editCategory(<?= json_encode($cat) ?>)' title="Edit Category">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                            </button>
                                            <form action="admin/categories/delete/<?= $cat['id'] ?>" method="POST" style="display:inline;" class="confirm-delete" data-message="Delete category? All associated products will be deleted.">
                                                <button type="submit" class="btn-delete" style="padding: 6px 10px; font-size: 12px; display: inline-flex; align-items: center; justify-content: center;" title="Delete Category">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Products Card -->
                <div class="panel-card">
                    <h2 class="panel-title" id="product-form-title">Add/Edit Product</h2>
                    <form id="product-form" action="admin/products" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="prod-id">
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label class="form-label">Category</label>
                                <select name="category_id" id="prod-category" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Product Name</label>
                                <input class="form-input" type="text" name="name" id="prod-name" placeholder="e.g. Garlic Bread" required>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label class="form-label">Price (<?= htmlspecialchars($settings['currency_code']) ?>)</label>
                                <input class="form-input" type="number" step="0.001" name="price" id="prod-price" placeholder="0.000" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Product Image</label>
                                <input type="file" id="prod-image-input" name="image" accept="image/*">
                                <input type="hidden" name="cropped_image" id="cropped-image-data">
                                <div id="cropped-preview-container" style="display: none; align-items: center; gap: 10px; margin-top: 10px;">
                                    <img id="cropped-preview-img" src="" style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover; border: 1px solid var(--card-border);">
                                    <span style="font-size: 12px; color: var(--accent-green); font-weight: 600;">✓ Image Cropped</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="prod-desc" rows="2" placeholder="Item description..."></textarea>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-container">
                                <input type="checkbox" name="is_available" id="prod-available" checked>
                                Available in Menu
                            </label>
                        </div>

                        <div style="display:flex; gap:10px;">
                            <button type="submit" class="btn-primary">Save Product</button>
                            <button type="button" class="btn-delete" style="display:none; background: rgba(255,255,255,0.05); color: var(--text-color);" id="btn-cancel-edit" onclick="resetProductForm()">Cancel Edit</button>
                        </div>
                    </form>

                    <h2 class="panel-title" style="margin-top: 30px; display: flex; justify-content: space-between; align-items: center;">
                        <span>Products List <span id="current-filter-label" style="font-size:12px; font-weight:600; color:var(--text-muted); background:rgba(255,255,255,0.05); padding:3px 8px; border-radius:6px; margin-left:10px;">Showing All</span></span>
                        <button type="button" onclick="filterProductsByCategory('all', 'Showing All')" class="btn-primary" style="padding: 6px 12px; font-size: 12px; background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.2); color: #818cf8; box-shadow: none;">Show All</button>
                    </h2>
                    <table id="products-table">
                        <thead>
                            <tr>
                                <th style="width: 10%;">Img</th>
                                <th style="width: 30%;">Name</th>
                                <th style="width: 20%;">Category</th>
                                <th style="width: 20%;">Price</th>
                                <th style="width: 20%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $prod): ?>
                                <tr>
                                    <td>
                                        <?php if ($prod['image_url']): ?>
                                            <img src="<?= htmlspecialchars($prod['image_url']) ?>" class="img-preview-mini">
                                        <?php else: ?>
                                            <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; font-size: 10px; color: var(--text-muted);">None</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600;"><?= htmlspecialchars($prod['name']) ?></div>
                                        <small style="color: var(--text-muted); font-size: 11px;"><?= htmlspecialchars(substr($prod['description'] ?? '', 0, 50)) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($prod['category_name']) ?></td>
                                    <td style="font-family: monospace; font-weight: 600;"><?= number_format($prod['price'], 3) ?> <?= htmlspecialchars($settings['currency_code']) ?></td>
                                    <td>
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <button class="btn-primary" style="padding: 6px 10px; font-size: 12px; background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.2); color: #818cf8; box-shadow: none; display: inline-flex; align-items: center; justify-content: center;" 
                                                    onclick='editProduct(<?= json_encode($prod) ?>)' title="Edit Product">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                            </button>
                                            <form action="admin/products/delete/<?= $prod['id'] ?>" method="POST" style="display:inline;" class="confirm-delete" data-message="Delete this product?">
                                                <button type="submit" class="btn-delete" style="padding: 6px 10px; display: inline-flex; align-items: center; justify-content: center;" title="Delete Product">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Settings Tab -->
        <div id="settings" class="tab-content">
            <div class="panel-card" style="max-width: 800px; margin: 0 auto;">
                <h2 class="panel-title">Global settings & Tax configuration</h2>
                <form action="admin/settings" method="POST" enctype="multipart/form-data">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Restaurant Name</label>
                            <input class="form-input" type="text" name="restaurant_name" value="<?= htmlspecialchars($settings['restaurant_name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Currency Code</label>
                            <select name="currency_code" required>
                                <option value="BHD" <?= $settings['currency_code'] === 'BHD' ? 'selected' : '' ?>>BHD - Bahraini Dinar</option>
                                <option value="SAR" <?= $settings['currency_code'] === 'SAR' ? 'selected' : '' ?>>SAR - Saudi Riyal</option>
                                <option value="AED" <?= $settings['currency_code'] === 'AED' ? 'selected' : '' ?>>AED - UAE Dirham</option>
                                <option value="OMR" <?= $settings['currency_code'] === 'OMR' ? 'selected' : '' ?>>OMR - Omani Rial</option>
                                <option value="QAR" <?= $settings['currency_code'] === 'QAR' ? 'selected' : '' ?>>QAR - Qatari Riyal</option>
                                <option value="KWD" <?= $settings['currency_code'] === 'KWD' ? 'selected' : '' ?>>KWD - Kuwaiti Dinar</option>
                                <option value="INR" <?= $settings['currency_code'] === 'INR' ? 'selected' : '' ?>>INR - Indian Rupee</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Time Zone</label>
                            <select name="time_zone" required>
                                <option value="Asia/Bahrain" <?= $settings['time_zone'] === 'Asia/Bahrain' ? 'selected' : '' ?>>Asia/Bahrain</option>
                                <option value="Asia/Riyadh" <?= $settings['time_zone'] === 'Asia/Riyadh' ? 'selected' : '' ?>>Asia/Riyadh</option>
                                <option value="Asia/Dubai" <?= $settings['time_zone'] === 'Asia/Dubai' ? 'selected' : '' ?>>Asia/Dubai</option>
                                <option value="Asia/Muscat" <?= $settings['time_zone'] === 'Asia/Muscat' ? 'selected' : '' ?>>Asia/Muscat</option>
                                <option value="Asia/Qatar" <?= $settings['time_zone'] === 'Asia/Qatar' ? 'selected' : '' ?>>Asia/Qatar</option>
                                <option value="Asia/Kuwait" <?= $settings['time_zone'] === 'Asia/Kuwait' ? 'selected' : '' ?>>Asia/Kuwait</option>
                                <option value="Asia/Kolkata" <?= $settings['time_zone'] === 'Asia/Kolkata' ? 'selected' : '' ?>>Asia/Kolkata</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Thermal Printer Width</label>
                            <select name="printer_size" required>
                                <option value="80" <?= (int)$settings['printer_size'] === 80 ? 'selected' : '' ?>>80 MM Standard Width</option>
                                <option value="58" <?= (int)$settings['printer_size'] === 58 ? 'selected' : '' ?>>58 MM Compact Width</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Tax Configuration System</label>
                            <select name="tax_type" id="tax-type-select" onchange="toggleTaxFields()" required>
                                <option value="VAT" <?= $settings['tax_type'] === 'VAT' ? 'selected' : '' ?>>VAT (Single Tax)</option>
                                <option value="GST" <?= $settings['tax_type'] === 'GST' ? 'selected' : '' ?>>GST India (CGST + SGST)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Restaurant Logo</label>
                            <input type="file" name="logo" accept="image/*">
                            <?php if ($settings['logo_path']): ?>
                                <small style="display:block; margin-top:5px; color:var(--text-muted);">Current: <a href="<?= htmlspecialchars($settings['logo_path']) ?>" target="_blank" style="color:#818cf8; text-decoration:none;">View Logo</a></small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Tax Input Fields Dynamic -->
                    <div id="vat-fields" style="display: <?= $settings['tax_type'] === 'VAT' ? 'block' : 'none' ?>;">
                        <div class="form-group" style="max-width: 50%;">
                            <label class="form-label">VAT Percentage (%)</label>
                            <input class="form-input" type="number" step="0.01" name="vat_percent" value="<?= htmlspecialchars($settings['vat_percent']) ?>">
                        </div>
                    </div>

                    <div id="gst-fields" style="display: <?= $settings['tax_type'] === 'GST' ? 'block' : 'none' ?>; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">CGST Percentage (%)</label>
                            <input class="form-input" type="number" step="0.01" name="cgst_percent" value="<?= htmlspecialchars($settings['cgst_percent']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">SGST Percentage (%)</label>
                            <input class="form-input" type="number" step="0.01" name="sgst_percent" value="<?= htmlspecialchars($settings['sgst_percent']) ?>">
                        </div>
                    </div>

                    <?php if (($_SESSION['username'] ?? '') === 'superadmin'): ?>
                        <div style="border-top: 1px solid var(--card-border); margin-top: 25px; padding-top: 25px;">
                            <h4 style="margin: 0 0 15px 0; color: var(--accent-orange); font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">🔑 Software License Management (Superadmin Only)</h4>
                            <div class="form-group" style="max-width: 50%;">
                                <label class="form-label">Software Expiry Date</label>
                                <input class="form-input" type="date" name="software_expiry_date" value="<?= htmlspecialchars($settings['software_expiry_date'] ?? '2027-12-31') ?>" required style="padding: 10px 14px; border-radius: 12px; font-size: 14px; font-family: inherit;">
                            </div>
                        </div>
                    <?php endif; ?>

                    <div style="margin-top: 30px;">
                        <button type="submit" class="btn-primary" style="padding: 14px 30px;">Save settings</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tables & QR Tab -->
        <div id="tables" class="tab-content">
            <div class="grid-2">
                <!-- Add Table Card -->
                <div class="panel-card" style="align-self: start;">
                    <h2 class="panel-title">Add Dining Table</h2>
                    <form action="admin/tables" method="POST">
                        <div class="form-group">
                            <label class="form-label">Table Number</label>
                            <input class="form-input" type="number" name="table_number" placeholder="e.g. 21" min="1" required>
                        </div>
                        <button type="submit" class="btn-primary">Add Table</button>
                    </form>
                </div>

                <!-- Table Grid Card -->
                <div class="panel-card">
                    <h2 class="panel-title">Restaurant Table Grid (<?= count($tables) ?> Tables)</h2>
                    <div class="tables-grid">
                        <?php foreach ($tables as $tbl): ?>
                            <div class="table-card" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 150px;">
                                <?php if ($tbl['status'] === 'available'): ?>
                                    <form action="admin/tables/delete/<?= $tbl['table_number'] ?>" method="POST" style="position: absolute; top: 10px; right: 10px;" class="confirm-delete" data-message="Delete Table T<?= $tbl['table_number'] ?>?">
                                        <button type="submit" style="background:none; border:none; color:var(--accent-red); cursor:pointer; font-weight:bold; font-size:20px; line-height:1; padding:0;" title="Delete Table">×</button>
                                    </form>
                                <?php endif; ?>
                                <?php if (!empty($tbl['waiter_name']) && ($tbl['status'] === 'occupied' || $tbl['status'] === 'billing')): ?>
                                    <div class="table-waiter-name" style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--accent-orange); margin-bottom: 5px; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        👤 <?= htmlspecialchars($tbl['waiter_name']) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="table-num" style="margin-bottom: 5px;">T<?= $tbl['table_number'] ?></div>
                                <div>
                                    <span class="table-status status-<?= $tbl['status'] ?>">
                                        <?= htmlspecialchars($tbl['status']) ?>
                                    </span>
                                </div>
                                <a href="#" class="btn-qr" onclick="showQrCode(<?= $tbl['table_number'] ?>); return false;">Get QR Code</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Management Tab -->
        <div id="users" class="tab-content">
            <div class="grid-2">
                <!-- Add User Card -->
                <div class="panel-card" style="align-self: start;">
                    <h2 class="panel-title">Add New User</h2>
                    <form action="admin/users" method="POST">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input class="form-input" type="text" name="name" placeholder="e.g. John Doe" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Username</label>
                            <input class="form-input" type="text" name="username" placeholder="e.g. johndoe" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <input class="form-input" type="password" name="password" placeholder="••••••••" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Role</label>
                            <select name="role" required>
                                <option value="waiter">Waiter</option>
                                <option value="kot">Kitchen Chef</option>
                                <option value="counter">Counter User</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-primary">Add User</button>
                    </form>
                </div>

                <!-- Users List Card -->
                <div class="panel-card">
                    <h2 class="panel-title">Users Directory</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $usr): ?>
                                <?php if ($usr['username'] === 'superadmin') continue; ?>
                                <tr>
                                    <td style="font-weight: 600;"><?= htmlspecialchars($usr['name']) ?></td>
                                    <td><?= htmlspecialchars($usr['username']) ?></td>
                                    <td>
                                        <?php 
                                            switch($usr['role']) {
                                                case 'admin': echo '<span class="table-status" style="background:rgba(168,85,247,0.1); color:#c084fc; border:1px solid rgba(168,85,247,0.2);">Admin</span>'; break;
                                                case 'waiter': echo '<span class="table-status status-available">Waiter</span>'; break;
                                                case 'kot': echo '<span class="table-status status-occupied">Kitchen Chef</span>'; break;
                                                case 'counter': echo '<span class="table-status status-billing">Counter User</span>'; break;
                                                default: echo htmlspecialchars($usr['role']);
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ((int)$usr['is_active'] === 1): ?>
                                            <span class="table-status status-available">Active</span>
                                        <?php else: ?>
                                            <span class="table-status status-occupied" style="background:rgba(239,68,68,0.1); color:var(--accent-red); border:1px solid rgba(239,68,68,0.2);">Deactivated</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ((int)$usr['id'] !== 1 && (int)$usr['id'] !== 6): ?>
                                            <form action="admin/users/status/<?= $usr['id'] ?>" method="POST" style="display:inline; margin-right: 5px;">
                                                <input type="hidden" name="is_active" value="<?= (int)$usr['is_active'] === 1 ? 0 : 1 ?>">
                                                <button type="submit" class="btn-primary" style="padding: 6px 12px; font-size: 12px; background: <?= (int)$usr['is_active'] === 1 ? 'rgba(245,158,11,0.1)' : 'rgba(16,185,129,0.1)' ?>; border: 1px solid <?= (int)$usr['is_active'] === 1 ? 'rgba(245,158,11,0.2)' : 'rgba(16,185,129,0.2)' ?>; color: <?= (int)$usr['is_active'] === 1 ? 'var(--accent-orange)' : 'var(--accent-green)' ?>; box-shadow: none;">
                                                    <?= (int)$usr['is_active'] === 1 ? 'Deactivate' : 'Activate' ?>
                                                </button>
                                            </form>
                                            <button type="button" class="btn-primary" style="padding: 6px 12px; font-size: 12px; background: rgba(99,102,241,0.1); border: 1px solid rgba(99,102,241,0.2); color: #818cf8; box-shadow: none; margin-right: 5px;" onclick="resetUserPasswordPrompt(<?= $usr['id'] ?>, '<?= htmlspecialchars($usr['name'], ENT_QUOTES) ?>')">
                                                🔑 Reset Pass
                                            </button>
                                            <form action="admin/users/delete/<?= $usr['id'] ?>" method="POST" style="display:inline;" class="confirm-delete" data-message="Delete user <?= htmlspecialchars($usr['name']) ?>?">
                                                <button type="submit" class="btn-delete" style="padding: 6px 12px;">Delete</button>
                                            </form>
                                        <?php else: ?>
                                            <button type="button" class="btn-primary" style="padding: 6px 12px; font-size: 12px; background: rgba(99,102,241,0.1); border: 1px solid rgba(99,102,241,0.2); color: #818cf8; box-shadow: none; margin-right: 5px;" onclick="resetUserPasswordPrompt(<?= $usr['id'] ?>, '<?= htmlspecialchars($usr['name'], ENT_QUOTES) ?>')">
                                                🔑 Reset Pass
                                            </button>
                                            <span style="color:var(--text-muted); font-size:12px; font-weight:600;">System Protected</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Closures Tab -->
        <div id="closures" class="tab-content">
            <div class="panel-card" style="margin-bottom: 30px;">
                <h2 class="panel-title">Pending Shift Closures</h2>
                <div id="pending-closures-container">
                    <p style="color: var(--text-muted); padding: 20px 0; text-align: center;">Loading pending closures...</p>
                </div>
            </div>

            <div class="panel-card">
                <h2 class="panel-title">Recent Closed Shifts</h2>
                <div id="closed-shifts-history-container" style="overflow-x: auto;">
                    <p style="color: var(--text-muted); padding: 20px 0; text-align: center;">Loading shift history...</p>
                </div>
            </div>
        </div>

        <!-- Customers Tab -->
        <div id="customers" class="tab-content">
            <div class="panel-card">
                <h2 class="panel-title">👥 Customer Loyalty Directory & Insights</h2>
                <div style="overflow-x: auto; margin-top: 15px;">
                    <table id="admin-customers-table">
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
                        <tbody id="admin-customers-table-body">
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted);">Loading customer directory...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- VAT / Tax Reports Tab -->
        <div id="tax_reports" class="tab-content">
            <div class="panel-card">
                <h2 class="panel-title">📄 VAT / Tax Reports (Govt. Compliance)</h2>
                
                <!-- Filter Section -->
                <div class="dt-header" style="margin-bottom: 20px; flex-wrap: wrap; gap: 15px; justify-content: space-between;">
                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-size: 11px; margin-bottom: 2px;">Start Date</label>
                            <input type="date" id="tax-start-date" class="form-input" style="padding: 8px 12px; font-size: 13px; width: 140px; margin-bottom: 0;" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-size: 11px; margin-bottom: 2px;">End Date</label>
                            <input type="date" id="tax-end-date" class="form-input" style="padding: 8px 12px; font-size: 13px; width: 140px; margin-bottom: 0;" value="<?= date('Y-m-d') ?>">
                        </div>
                        <button onclick="loadTaxReport()" class="btn-primary" style="padding: 9px 20px; font-size: 13px; margin-top: 15px; box-shadow: none;">
                            Filter Report
                        </button>
                    </div>
                    <button onclick="exportTaxReportToExcel()" class="btn-checkout" style="padding: 9px 20px; font-size: 13px; margin-top: 15px; background: var(--accent-green); border: none; color: white; font-weight: 700; border-radius: 10px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                        📥 Export to Excel (CSV)
                    </button>
                </div>

                <!-- Report Table -->
                <div style="overflow-x: auto; margin-top: 15px;">
                    <table id="tax-report-table" style="width:100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th style="text-align: left;">Invoice ID</th>
                                <th style="text-align: left;">Date & Time</th>
                                <th style="text-align: right;">Subtotal</th>
                                <th style="text-align: right;">VAT/Tax Amt</th>
                                <th style="text-align: right;">Discount Amt</th>
                                <th style="text-align: right;">Grand Total</th>
                                <th style="text-align: left;">Payment Method</th>
                                <th style="text-align: left;">Cashier</th>
                            </tr>
                        </thead>
                        <tbody id="tax-report-table-body">
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 30px; color: var(--text-muted);">Please select a date range and click filter to generate report.</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr style="border-top: 2px solid var(--card-border); font-weight: 800; background: rgba(255,255,255,0.01);">
                                <td colspan="2" style="padding: 12px; text-align: left;">Total Summary</td>
                                <td id="tax-total-subtotal" style="padding: 12px; text-align: right; color: var(--text-color);">0.000</td>
                                <td id="tax-total-tax" style="padding: 12px; text-align: right; color: var(--accent-orange);">0.000</td>
                                <td id="tax-total-discount" style="padding: 12px; text-align: right; color: var(--accent-red);">0.000</td>
                                <td id="tax-total-grand" style="padding: 12px; text-align: right; color: var(--accent-green);">0.000</td>
                                <td colspan="2" style="padding: 12px;"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sales & Product Analytics Tab -->
        <div id="analytics" class="tab-content">
            <div class="panel-card">
                <h2 class="panel-title">📈 Sales & Product Analytics</h2>
                
                <!-- Filter Section -->
                <div class="dt-header" style="margin-bottom: 25px; flex-wrap: wrap; gap: 15px; justify-content: space-between;">
                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-size: 11px; margin-bottom: 2px;">Start Date</label>
                            <input type="date" id="analytics-start-date" class="form-input" style="padding: 8px 12px; font-size: 13px; width: 140px; margin-bottom: 0;" value="<?= date('Y-m-d', strtotime('-30 days')) ?>">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-size: 11px; margin-bottom: 2px;">End Date</label>
                            <input type="date" id="analytics-end-date" class="form-input" style="padding: 8px 12px; font-size: 13px; width: 140px; margin-bottom: 0;" value="<?= date('Y-m-d') ?>">
                        </div>
                        <button onclick="loadAnalyticsReport()" class="btn-primary" style="padding: 9px 20px; font-size: 13px; margin-top: 15px; box-shadow: none;">
                            Filter Analytics
                        </button>
                    </div>
                    <button onclick="exportAnalyticsToExcel()" class="btn-checkout" style="padding: 9px 20px; font-size: 13px; margin-top: 15px; background: var(--accent-green); border: none; color: white; font-weight: 700; border-radius: 10px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                        📥 Export to Excel (CSV)
                    </button>
                </div>

                <!-- KPI Cards Row -->
                <div class="grid-3" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;">
                    <div class="panel-card" style="background: rgba(255, 255, 255, 0.01); border-color: var(--card-border); padding: 20px; text-align: center;">
                        <div style="font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Total Items Sold</div>
                        <div id="kpi-total-qty" style="font-size: 28px; font-weight: 800; color: var(--text-color);">0</div>
                    </div>
                    <div class="panel-card" style="background: rgba(255, 255, 255, 0.01); border-color: var(--card-border); padding: 20px; text-align: center;">
                        <div style="font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Total Sales Revenue</div>
                        <div id="kpi-total-revenue" style="font-size: 28px; font-weight: 800; color: var(--accent-green);">0.000 <?= htmlspecialchars($settings['currency_code']) ?></div>
                    </div>
                    <div class="panel-card" style="background: rgba(255, 255, 255, 0.01); border-color: var(--card-border); padding: 20px; text-align: center;">
                        <div style="font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Top Performing Item</div>
                        <div id="kpi-top-product" style="font-size: 18px; font-weight: 800; color: var(--accent-orange); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding-top: 5px;">None</div>
                    </div>
                </div>

                <!-- Side-by-Side Tables Grid -->
                <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 20px;">
                    <!-- Fast Moving Items Card -->
                    <div class="panel-card" style="background: rgba(255, 255, 255, 0.01); border-color: var(--card-border); padding: 20px;">
                        <h3 class="panel-title" style="font-size: 16px; margin-bottom: 15px;">🔥 Fast Moving Items (By Quantity)</h3>
                        <div style="overflow-x: auto;">
                            <table id="fast-moving-table" style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr>
                                        <th style="font-size: 11px; padding: 10px 8px; text-align: left;">Product</th>
                                        <th style="font-size: 11px; padding: 10px 8px; text-align: left;">Category</th>
                                        <th style="font-size: 11px; padding: 10px 8px; text-align: right;">Qty Sold</th>
                                    </tr>
                                </thead>
                                <tbody id="fast-moving-tbody">
                                    <tr>
                                        <td colspan="3" style="text-align: center; padding: 20px; color: var(--text-muted);">No sales data available.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Highest Revenue Items Card -->
                    <div class="panel-card" style="background: rgba(255, 255, 255, 0.01); border-color: var(--card-border); padding: 20px;">
                        <h3 class="panel-title" style="font-size: 16px; margin-bottom: 15px;">💰 Highest Revenue Products</h3>
                        <div style="overflow-x: auto;">
                            <table id="high-revenue-table" style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr>
                                        <th style="font-size: 11px; padding: 10px 8px; text-align: left;">Product</th>
                                        <th style="font-size: 11px; padding: 10px 8px; text-align: left;">Category</th>
                                        <th style="font-size: 11px; padding: 10px 8px; text-align: right;">Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="high-revenue-tbody">
                                    <tr>
                                        <td colspan="3" style="text-align: center; padding: 20px; color: var(--text-muted);">No sales data available.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
            
        </div>

        <!-- Waiter Performance Tab -->
        <div id="waiter_report" class="tab-content">
            <div class="panel-card">
                <h2 class="panel-title">🤵 Waiter Performance Report</h2>
                
                <!-- Filter Section -->
                <div class="dt-header" style="margin-bottom: 20px; flex-wrap: wrap; gap: 15px; justify-content: space-between;">
                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-size: 11px; margin-bottom: 2px;">Start Date</label>
                            <input type="date" id="waiter-start-date" class="form-input" style="padding: 8px 12px; font-size: 13px; width: 140px; margin-bottom: 0;" value="<?= date('Y-m-d', strtotime('-30 days')) ?>">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-size: 11px; margin-bottom: 2px;">End Date</label>
                            <input type="date" id="waiter-end-date" class="form-input" style="padding: 8px 12px; font-size: 13px; width: 140px; margin-bottom: 0;" value="<?= date('Y-m-d') ?>">
                        </div>
                        <button onclick="loadWaiterPerformanceReport()" class="btn-primary" style="padding: 9px 20px; font-size: 13px; margin-top: 15px; box-shadow: none;">
                            Filter Report
                        </button>
                    </div>
                    <button onclick="exportWaiterPerformanceToExcel()" class="btn-checkout" style="padding: 9px 20px; font-size: 13px; margin-top: 15px; background: var(--accent-green); border: none; color: white; font-weight: 700; border-radius: 10px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                        📥 Export to Excel (CSV)
                    </button>
                </div>

                <!-- Report Table -->
                <div style="overflow-x: auto; margin-top: 15px;">
                    <table id="waiter-performance-table" style="width:100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th style="text-align: left;">Waiter ID</th>
                                <th style="text-align: left;">Waiter Name</th>
                                <th style="text-align: left;">Username</th>
                                <th style="text-align: center;">Status</th>
                                <th style="text-align: right;">Orders Taken</th>
                                <th style="text-align: right;">Paid Orders</th>
                                <th style="text-align: right;">Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody id="waiter-performance-table-body">
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted);">Please select a date range and click filter to generate report.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="admin-footer" style="padding: 18px 20px; text-align: center; font-size: 12px; color: var(--text-muted); border-top: 1px solid var(--card-border); margin-top: 30px; width: 100%;">
            Powered By <a href="javascript:void(0)" onclick="openSandsModal()" style="color: #818cf8; text-decoration: none; font-weight: 600;">SaNDS Lab</a>. All rights reserved to <?= htmlspecialchars($settings['restaurant_name']) ?>
        </div>
    </div>

    <!-- SaNDS Lab Popup Modal -->
    <div id="sands-modal" class="modal-sands" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(11, 15, 25, 0.85); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); z-index: 2000; align-items: center; justify-content: center;">
        <div class="modal-sands-content" style="background: #ffffff; border: 1px solid rgba(0, 0, 0, 0.08); padding: 35px 25px; border-radius: 24px; text-align: center; max-width: 340px; width: 90%; box-shadow: 0 20px 50px rgba(0,0,0,0.2); position: relative; color: #1f2937;">
            <button onclick="closeSandsModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; color: #6b7280; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
            <div style="margin-bottom: 20px;">
                <img src="/logos/SaNDSLab-LogoNewUpdated.png" alt="SaNDS Lab Logo" style="max-width: 220px; height: auto; display: block; margin: 0 auto;">
            </div>
            <h3 style="font-size: 20px; font-weight: 800; color: #1f2937; margin-bottom: 5px; letter-spacing: -0.5px;">SaNDS Lab</h3>
            <p style="font-size: 13px; font-weight: 600; color: #6b7280; margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.5px;">Custom Software Developers</p>
            <p style="font-size: 11px; font-weight: 700; color: #7c3aed; margin-bottom: 25px; text-transform: uppercase; letter-spacing: 1.5px;">AI Powered</p>
            
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <a href="https://www.sandslab.com" target="_blank" style="display: block; width: 100%; background: var(--primary-grad); border: none; padding: 12px; border-radius: 12px; font-family: inherit; color: white; font-size: 14px; font-weight: 600; text-decoration: none; text-align: center; box-shadow: 0 4px 12px rgba(168, 85, 247, 0.3); transition: all 0.3s;">
                    🌐 Visit Website
                </a>
                <a href="https://wa.me/97335078079" target="_blank" style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; background: #25d366; border: none; padding: 12px; border-radius: 12px; font-family: inherit; color: white; font-size: 14px; font-weight: 600; text-decoration: none; text-align: center; box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3); transition: all 0.3s;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.513 2.262 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.503-5.739-1.45L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.42 9.864-9.864.002-2.637-1.03-5.118-2.91-6.999-1.88-1.882-4.36-2.914-7.001-2.915-5.442 0-9.867 4.42-9.871 9.866-.002 2.015.528 3.985 1.536 5.736l-.991 3.616 3.7-.977zm11.452-6.52c-.29-.145-1.716-.847-1.982-.944-.265-.098-.458-.146-.65.145-.193.292-.748.944-.917 1.138-.17.19-.338.213-.628.068-.29-.145-1.226-.452-2.336-1.443-.864-.77-1.447-1.722-1.616-2.012-.17-.29-.018-.447.127-.59.13-.13.29-.338.435-.508.145-.17.193-.29.29-.483.097-.19.048-.36-.024-.505-.072-.145-.65-1.568-.89-2.146-.233-.56-.47-.483-.65-.492-.168-.008-.362-.01-.555-.01-.193 0-.507.072-.77.36-.266.29-1.014.992-1.014 2.42 0 1.427 1.038 2.805 1.182 3 .145.195 2.043 3.12 4.95 4.377.69.298 1.23.477 1.65.61.693.22 1.325.19 1.822.115.555-.083 1.716-.7 1.96-1.375.242-.676.242-1.256.17-1.376-.073-.12-.266-.194-.556-.34z"/></svg>
                    Contact Now
                </a>
            </div>
        </div>
    </div>

    <!-- Cropper Modal -->
    <div id="cropper-modal" class="modal">
        <div class="modal-content" style="max-width: 600px; width: 90%; text-align: left;">
            <h3 style="margin-bottom: 5px;">✂ Crop Product Image</h3>
            <p style="color: var(--text-muted); font-size:13px; margin-bottom: 20px;">Crop the image. It will be compressed to 400x400 pixels for optimal loading.</p>
            <div style="max-height: 380px; width: 100%; overflow: hidden; background: #000; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; justify-content: center;">
                <img id="cropper-image" src="" style="max-width: 100%; max-height: 350px; display: block;">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="cancelCropping()" class="modal-close" style="margin-top:0; background: rgba(255,255,255,0.05); border: 1px solid var(--card-border); color: var(--text-color); padding: 8px 16px; border-radius: 10px;">Cancel</button>
                <button type="button" onclick="confirmCropping()" class="btn-primary" style="padding: 8px 20px; border-radius: 10px;">Crop & Save</button>
            </div>
        </div>
    </div>

    <!-- QR Code Preview Modal -->
    <div id="qr-modal" class="modal">
        <div class="modal-content">
            <h3 style="margin-bottom: 15px;" id="qr-modal-title">Table QR Code</h3>
            <div id="qr-container" style="background: white; padding: 15px; display: inline-block; border-radius: 12px; margin-bottom: 15px;">
                <!-- QR Image loaded by JS -->
            </div>
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 15px;">Scan this QR code with a mobile device to access customer self-ordering menu.</p>
            <div>
                <a id="qr-download-link" href="#" download class="btn-primary" style="display:inline-block; text-decoration:none; margin-right:10px;">Download</a>
                <button onclick="closeQrCode()" class="modal-close" style="margin-top:0;">Close</button>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabId) {
            localStorage.setItem('admin_active_tab', tabId);
            
            document.querySelectorAll('.tab-btn').forEach(btn => {
                if (btn.getAttribute('onclick') === `switchTab('${tabId}')`) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
            document.querySelectorAll('.tab-content').forEach(content => {
                if (content.id === tabId) {
                    content.classList.add('active');
                } else {
                    content.classList.remove('active');
                }
            });

            if (tabId === 'closures') {
                fetchClosures();
            } else if (tabId === 'customers') {
                fetchAdminCustomers();
            } else if (tabId === 'tax_reports') {
                loadTaxReport();
            } else if (tabId === 'analytics') {
                loadAnalyticsReport();
            }
        }

        function fetchAdminCustomers() {
            const cur = '<?= htmlspecialchars($settings['currency_code']) ?>';
            const basePath = window.location.pathname.endsWith('/') ? window.location.pathname.slice(0, -1) : window.location.pathname;
            const rootPath = basePath.replace(/\/admin$/, '');

            fetch(rootPath + '/counter/customers')
                .then(res => res.json())
                .then(data => {
                    // Destroy DataTable instance if already exists
                    if ($.fn.DataTable.isDataTable('#admin-customers-table')) {
                        $('#admin-customers-table').DataTable().destroy();
                    }

                    const tbody = document.getElementById('admin-customers-table-body');
                    if (data.customers && data.customers.length > 0) {
                        let html = '';
                        data.customers.forEach(c => {
                            const dateStr = new Date(c.created_at).toLocaleDateString([], { year: 'numeric', month: 'short', day: 'numeric' });
                            const spent = parseFloat(c.total_spent).toFixed(3);
                            const discount = parseFloat(c.total_discount || 0).toFixed(3);
                            const genderLabel = c.gender ? c.gender : '<span style="color:var(--text-muted); font-style:italic;">Not Specified</span>';
                            
                            html += `
                                <tr style="border-bottom: 1px solid var(--card-border);">
                                    <td style="padding: 12px 15px; font-weight:600; text-align:left;">${escapeHtmlAdmin(c.name)}</td>
                                    <td style="padding: 12px 15px; text-align:left;">${escapeHtmlAdmin(c.mobile)}</td>
                                    <td style="padding: 12px 15px; text-align:left; font-size: 13px;">${genderLabel}</td>
                                    <td style="padding: 12px 15px; text-align:right; font-weight:600;">${c.visit_count}</td>
                                    <td style="padding: 12px 15px; text-align:right; font-weight:700; color:var(--accent-green);" class="price-text">${spent} ${cur}</td>
                                    <td style="padding: 12px 15px; text-align:right; font-weight:700; color:var(--accent-red);" class="price-text">${discount} ${cur}</td>
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
                    $('#admin-customers-table').DataTable({
                        order: [[4, 'desc']], // sort by total amount spent desc by default
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

        function escapeHtmlAdmin(str) {
            if (!str) return '';
            return str.toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function fetchClosures() {
            const cur = '<?= htmlspecialchars($settings['currency_code']) ?>';
            const basePath = window.location.pathname.endsWith('/') ? window.location.pathname.slice(0, -1) : window.location.pathname;
            const rootPath = basePath.replace(/\/admin$/, '');

            fetch(rootPath + '/counter/session/pending')
                .then(res => res.json())
                .then(data => {
                    renderPendingClosures(data.pending, cur, rootPath);
                    renderClosedShiftsHistory(data.history, cur);
                })
                .catch(err => console.error('Error fetching closures:', err));
        }

        function renderPendingClosures(pending, cur, rootPath) {
            const container = document.getElementById('pending-closures-container');
            if (!pending || pending.length === 0) {
                container.innerHTML = `
                    <p style="color: var(--text-muted); padding: 40px 0; text-align: center; font-size: 14px;">
                        🎉 No pending shift closures to verify. All active counters are operating normally.
                    </p>
                `;
                return;
            }

            let html = `
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--card-border);">
                            <th style="padding:12px; font-size:12px; text-align:left;">Cashier</th>
                            <th style="padding:12px; font-size:12px; text-align:left;">Shift Duration</th>
                            <th style="padding:12px; font-size:12px; text-align:right;">System Calculated</th>
                            <th style="padding:12px; font-size:12px; text-align:right;">Cashier Declared</th>
                            <th style="padding:12px; font-size:12px; text-align:right;">Discrepancy</th>
                            <th style="padding:12px; font-size:12px; max-width:200px; text-align:left;">Cashier Notes</th>
                            <th style="padding:12px; font-size:12px; text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            pending.forEach(row => {
                const sysCash = parseFloat(row.cash_total);
                const sysCard = parseFloat(row.card_total);
                const sysQr = parseFloat(row.qr_total);
                const sysTotal = parseFloat(row.system_total);

                const colCash = parseFloat(row.collected_cash);
                const colCard = parseFloat(row.collected_card);
                const colQr = parseFloat(row.collected_qr);
                const colTotal = parseFloat(row.collected_total);

                const diffCash = colCash - sysCash;
                const diffCard = colCard - sysCard;
                const diffQr = colQr - sysQr;
                const diffTotal = colTotal - sysTotal;

                html += `
                    <tr style="border-bottom: 1px solid var(--card-border); vertical-align: top;">
                        <td style="padding: 16px 12px; font-weight: 700; font-size:14px; text-align:left;">${row.cashier_name}</td>
                        <td style="padding: 16px 12px; font-size: 13px; text-align:left;">
                            <div>📅 <strong>Start:</strong> ${formatDateTime(row.opened_at)}</div>
                            <div style="margin-top: 4px;">🏁 <strong>End:</strong> ${formatDateTime(row.close_requested_at)}</div>
                        </td>
                        <td style="padding: 16px 12px; text-align: right; font-size: 13px; font-family: monospace;">
                            <div>💵 Cash: ${sysCash.toFixed(3)}</div>
                            <div>💳 Card: ${sysCard.toFixed(3)}</div>
                            <div>📱 QR: ${sysQr.toFixed(3)}</div>
                            <div style="border-top:1px dashed var(--card-border); margin-top:4px; padding-top:4px; font-weight:700;">Total: ${sysTotal.toFixed(3)}</div>
                        </td>
                        <td style="padding: 16px 12px; text-align: right; font-size: 13px; font-family: monospace;">
                            <div>💵 Cash: ${colCash.toFixed(3)}</div>
                            <div>💳 Card: ${colCard.toFixed(3)}</div>
                            <div>📱 QR: ${colQr.toFixed(3)}</div>
                            <div style="border-top:1px dashed var(--card-border); margin-top:4px; padding-top:4px; font-weight:700;">Total: ${colTotal.toFixed(3)}</div>
                        </td>
                        <td style="padding: 16px 12px; text-align: right; font-size: 13px; font-family: monospace;">
                            <div>Cash: ${formatDiscrepancy(diffCash, '')}</div>
                            <div>Card: ${formatDiscrepancy(diffCard, '')}</div>
                            <div>QR: ${formatDiscrepancy(diffQr, '')}</div>
                            <div style="border-top:1px dashed var(--card-border); margin-top:4px; padding-top:4px; font-weight:700;">Total: ${formatDiscrepancy(diffTotal, '')}</div>
                        </td>
                        <td style="padding: 16px 12px; font-size: 13px; max-width:200px; word-wrap: break-word; color: var(--text-muted); text-align:left;">
                            ${row.cashier_notes ? htmlspecialchars(row.cashier_notes) : '<i>No notes</i>'}
                        </td>
                        <td style="padding: 16px 12px; text-align: center;">
                            <button onclick="approveClosure(${row.id}, '${row.cashier_name}', ${colTotal.toFixed(3)}, '${cur}', '${rootPath}')" class="btn-primary" style="padding: 6px 12px; font-size: 12px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: var(--accent-green); box-shadow: none; margin-bottom: 6px; width: 110px; cursor:pointer;">
                                Confirm & Close
                            </button>
                            <button onclick="rejectClosure(${row.id}, '${row.cashier_name}', '${rootPath}')" class="btn-delete" style="padding: 6px 12px; font-size: 12px; width: 110px; cursor:pointer;">
                                Reject & Reopen
                            </button>
                        </td>
                    </tr>
                `;
            });

            html += `
                    </tbody>
                </table>
            `;
            container.innerHTML = html;
        }

        function renderClosedShiftsHistory(history, cur) {
            const container = document.getElementById('closed-shifts-history-container');
            if (!history || history.length === 0) {
                container.innerHTML = `
                    <p style="color: var(--text-muted); padding: 40px 0; text-align: center; font-size: 14px;">
                        No shift history records found.
                    </p>
                `;
                return;
            }

            let html = `
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--card-border);">
                            <th style="padding:12px; font-size:12px; text-align:left;">Cashier</th>
                            <th style="padding:12px; font-size:12px; text-align:left;">Opened At</th>
                            <th style="padding:12px; font-size:12px; text-align:left;">Closed At</th>
                            <th style="padding:12px; font-size:12px; text-align:right;">System Total</th>
                            <th style="padding:12px; font-size:12px; text-align:right;">Physical Declared</th>
                            <th style="padding:12px; font-size:12px; text-align:right;">Discrepancy</th>
                            <th style="padding:12px; font-size:12px; text-align:left;">Verified By</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            history.forEach(row => {
                const sysTotal = parseFloat(row.system_total);
                const colTotal = parseFloat(row.collected_total);
                const diffTotal = colTotal - sysTotal;

                html += `
                    <tr style="border-bottom: 1px solid var(--card-border);">
                        <td style="padding: 14px 12px; font-weight: 600; font-size:13px; text-align:left;">${row.cashier_name}</td>
                        <td style="padding: 14px 12px; font-size:13px; text-align:left;">${formatDateTime(row.opened_at)}</td>
                        <td style="padding: 14px 12px; font-size:13px; text-align:left;">${formatDateTime(row.closed_at)}</td>
                        <td style="padding: 14px 12px; text-align: right; font-family: monospace; font-size:13px;">${sysTotal.toFixed(3)} ${cur}</td>
                        <td style="padding: 14px 12px; text-align: right; font-family: monospace; font-size:13px;">${colTotal.toFixed(3)} ${cur}</td>
                        <td style="padding: 14px 12px; text-align: right; font-family: monospace; font-size:13px;">${formatDiscrepancy(diffTotal, cur)}</td>
                        <td style="padding: 14px 12px; font-size:13px; color: var(--text-muted); text-align:left;">
                            👤 ${row.approved_by_name || 'System Admin'}
                        </td>
                    </tr>
                `;
            });

            html += `
                    </tbody>
                </table>
            `;
            container.innerHTML = html;
        }

        function formatDateTime(dateStr) {
            if (!dateStr) return 'N/A';
            const date = new Date(dateStr.replace(' ', 'T'));
            return date.toLocaleDateString([], { month: 'short', day: 'numeric' }) + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        function formatDiscrepancy(diff, cur) {
            const fd = parseFloat(diff).toFixed(3);
            if (diff < 0) {
                return `<span style="color: var(--accent-red); font-weight: 700;">${fd} ${cur}</span>`;
            } else if (diff > 0) {
                return `<span style="color: var(--accent-green); font-weight: 700;">+${fd} ${cur}</span>`;
            } else {
                return `<span style="color: var(--text-muted);">${fd} ${cur}</span>`;
            }
        }

        function htmlspecialchars(str) {
            if (typeof str !== 'string') return '';
            return str
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function approveClosure(sessionId, cashierName, totalAmount, cur, rootPath) {
            Swal.fire({
                title: 'Confirm & Close Shift?',
                text: `Are you sure you want to approve the shift closure request from ${cashierName} for a total of ${totalAmount} ${cur}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#4b5563',
                confirmButtonText: 'Yes, Confirm Close',
                background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(rootPath + '/counter/session/approve/' + sessionId, { method: 'POST' })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Closed!',
                                    text: 'Shift has been successfully closed and finalized.',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false,
                                    background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                                    color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
                                });
                                fetchClosures(); // Refresh tables
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Failed to approve shift closure.',
                                    icon: 'error',
                                    background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                                    color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
                                });
                            }
                        })
                        .catch(err => console.error('Error approving closure:', err));
                }
            });
        }

        function rejectClosure(sessionId, cashierName, rootPath) {
            Swal.fire({
                title: 'Reject & Reopen Shift?',
                text: `Are you sure you want to reject the shift closure request from ${cashierName}? This will reopen their counter session so they can continue operating or re-submit their physical counts.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#4b5563',
                confirmButtonText: 'Yes, Reject & Reopen',
                background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(rootPath + '/counter/session/reject/' + sessionId, { method: 'POST' })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Reopened!',
                                    text: 'Shift has been reopened for the cashier.',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false,
                                    background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                                    color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
                                });
                                fetchClosures(); // Refresh tables
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Failed to reject shift closure.',
                                    icon: 'error',
                                    background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                                    color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
                                });
                            }
                        })
                        .catch(err => console.error('Error rejecting closure:', err));
                }
            });
        }

        function toggleTaxFields() {
            const taxType = document.getElementById('tax-type-select').value;
            if (taxType === 'VAT') {
                document.getElementById('vat-fields').style.display = 'block';
                document.getElementById('gst-fields').style.display = 'none';
            } else {
                document.getElementById('vat-fields').style.display = 'none';
                document.getElementById('gst-fields').style.display = 'grid';
            }
        }

        function editProduct(prod) {
            document.getElementById('prod-id').value = prod.id;
            document.getElementById('prod-category').value = prod.category_id;
            document.getElementById('prod-name').value = prod.name;
            document.getElementById('prod-price').value = prod.price;
            document.getElementById('prod-desc').value = prod.description;
            document.getElementById('prod-available').checked = (parseInt(prod.is_available) === 1);
            
            document.getElementById('product-form-title').innerText = 'Edit Product: ' + prod.name;
            document.getElementById('btn-cancel-edit').style.display = 'inline-block';
            
            // Scroll to form
            document.getElementById('product-form').scrollIntoView({ behavior: 'smooth' });
        }

        function resetProductForm() {
            document.getElementById('prod-id').value = '';
            document.getElementById('product-form').reset();
            document.getElementById('product-form-title').innerText = 'Add/Edit Product';
            document.getElementById('btn-cancel-edit').style.display = 'none';
            document.getElementById('cropped-image-data').value = '';
            document.getElementById('cropped-preview-container').style.display = 'none';
        }

        function editCategory(cat) {
            document.getElementById('cat-id').value = cat.id;
            document.getElementById('cat-form-title').innerText = 'Edit Category: ' + cat.name;
            document.getElementById('cat-name-input').value = cat.name;
            document.getElementById('btn-cancel-cat-edit').style.display = 'inline-block';
            document.getElementById('cat-submit-btn').innerText = 'Save Category';

            // Show current preview if image exists
            const previewContainer = document.getElementById('cat-cropped-preview-container');
            const previewImg = document.getElementById('cat-cropped-preview-img');
            if (cat.image_url) {
                previewImg.src = cat.image_url;
                previewContainer.style.display = 'flex';
            } else {
                previewContainer.style.display = 'none';
            }
            
            // Scroll to form
            document.getElementById('category-form').scrollIntoView({ behavior: 'smooth' });
        }

        function resetCategoryForm() {
            document.getElementById('cat-id').value = '';
            document.getElementById('category-form').reset();
            document.getElementById('cat-form-title').innerText = 'Add Category';
            document.getElementById('btn-cancel-cat-edit').style.display = 'none';
            document.getElementById('cat-cropped-image-data').value = '';
            document.getElementById('cat-cropped-preview-container').style.display = 'none';
            document.getElementById('cat-submit-btn').innerText = 'Add Category';
        }

        function showQrCode(tableNum) {
            const protocol = window.location.protocol;
            const host = window.location.host;
            const path = window.location.pathname.replace('/admin', '');
            
            // Build the customer landing url
            const orderUrl = protocol + '//' + host + path + '/customer/' + tableNum;
            
            // Load QR using QR Server public API
            const qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' + encodeURIComponent(orderUrl);
            
            document.getElementById('qr-modal-title').innerText = 'Table ' + tableNum + ' QR Code';
            document.getElementById('qr-container').innerHTML = '<img src="' + qrImageUrl + '" alt="QR Code" style="width:200px; height:200px;">';
            
            const dlLink = document.getElementById('qr-download-link');
            dlLink.href = qrImageUrl;
            dlLink.download = 'Table_' + tableNum + '_QR.png';

            document.getElementById('qr-modal').style.display = 'flex';
        }

        function closeQrCode() {
            document.getElementById('qr-modal').style.display = 'none';
        }

        function showWaiterLoginQr() {
            const protocol = window.location.protocol;
            const host = window.location.host;
            const path = window.location.pathname.replace('/admin', '');
            const loginUrl = protocol + '//' + host + path + '/login';
            
            const qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + encodeURIComponent(loginUrl);
            
            Swal.fire({
                title: '📱 Waiter Login Link',
                html: `
                    <div style="margin: 15px 0;">
                        <img src="${qrImageUrl}" alt="Login QR Code" style="width: 220px; height: 220px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: 4px solid white;">
                        <p style="font-size: 13px; margin-top: 15px; color: var(--text-muted);">Scan this QR code with a waiter's phone camera to quickly access the system login page.</p>
                        <div style="background: rgba(0,0,0,0.05); padding: 8px 12px; border-radius: 8px; font-size: 12px; font-family: monospace; word-break: break-all; color: var(--text-color); border: 1px solid var(--card-border); margin-top: 10px;">
                            ${loginUrl}
                        </div>
                    </div>
                `,
                showConfirmButton: true,
                confirmButtonText: 'Done',
                confirmButtonColor: '#6366f1',
                background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
            });
        }

        // Global SweetAlert2 delete confirmation listener
        document.addEventListener('submit', function(e) {
            if (e.target && e.target.classList.contains('confirm-delete')) {
                e.preventDefault(); // Stop form submission
                const form = e.target;
                const message = form.getAttribute('data-message') || 'Are you sure you want to delete this?';
                
                Swal.fire({
                    title: 'Confirm Delete',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#4b5563',
                    confirmButtonText: 'Yes, delete it',
                    background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                    color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const btn = form.querySelector('button[type="submit"]');
                        if (btn && typeof Ladda !== 'undefined') {
                            const l = Ladda.create(btn);
                            l.start();
                        }
                        form.submit(); // Submit form
                    }
                });
            }
        });

        let cropper = null;
        let croppingTarget = null; // 'product' or 'category'

        // Product image listener
        document.getElementById('prod-image-input').addEventListener('change', function(e) {
            initCropper(e, 'product');
        });

        // Category image listener
        document.getElementById('cat-image-input').addEventListener('change', function(e) {
            initCropper(e, 'category');
        });

        function initCropper(e, target) {
            const files = e.target.files;
            if (files && files.length > 0) {
                croppingTarget = target;
                const file = files[0];
                const reader = new FileReader();
                reader.onload = function(event) {
                    const img = document.getElementById('cropper-image');
                    img.src = event.target.result;
                    
                    document.getElementById('cropper-modal').style.display = 'flex';
                    
                    if (cropper) {
                        cropper.destroy();
                    }
                    
                    const ratio = target === 'product' ? 1 : 2;
                    cropper = new Cropper(img, {
                        aspectRatio: ratio,
                        viewMode: 1,
                        autoCropArea: 1,
                        background: false
                    });
                };
                reader.readAsDataURL(file);
            }
        }

        function confirmCropping() {
            if (!cropper) return;
            
            const width = croppingTarget === 'product' ? 400 : 600;
            const height = croppingTarget === 'product' ? 400 : 300;
            
            const canvas = cropper.getCroppedCanvas({
                width: width,
                height: height
            });
            
            if (canvas) {
                const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
                
                if (croppingTarget === 'product') {
                    document.getElementById('cropped-image-data').value = dataUrl;
                    document.getElementById('cropped-preview-img').src = dataUrl;
                    document.getElementById('cropped-preview-container').style.display = 'flex';
                } else {
                    document.getElementById('cat-cropped-image-data').value = dataUrl;
                    document.getElementById('cat-cropped-preview-img').src = dataUrl;
                    document.getElementById('cat-cropped-preview-container').style.display = 'flex';
                }
                
                document.getElementById('cropper-modal').style.display = 'none';
                
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
            }
        }

        function cancelCropping() {
            document.getElementById('cropper-modal').style.display = 'none';
            if (croppingTarget === 'product') {
                document.getElementById('prod-image-input').value = '';
                document.getElementById('cropped-image-data').value = '';
                document.getElementById('cropped-preview-container').style.display = 'none';
            } else {
                document.getElementById('cat-image-input').value = '';
                document.getElementById('cat-cropped-image-data').value = '';
                document.getElementById('cat-cropped-preview-container').style.display = 'none';
            }
            
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
        }

        let productsTable = null;

        function filterProductsByCategory(catId, label) {
            document.getElementById('current-filter-label').innerText = label;
            
            const basePath = window.location.pathname.endsWith('/') ? window.location.pathname.slice(0, -1) : window.location.pathname;
            
            fetch(basePath + '/products/list?category_id=' + catId)
                .then(res => res.json())
                .then(data => {
                    const tbody = document.querySelector('#products-table tbody');
                    
                    if (productsTable) {
                        productsTable.destroy();
                    }
                    
                    let html = '';
                    const currencyCode = '<?= htmlspecialchars($settings['currency_code']) ?>';
                    data.products.forEach(prod => {
                        const imgHtml = prod.image_url 
                            ? `<img src="${prod.image_url}" class="img-preview-mini">`
                            : `<div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; font-size: 10px; color: var(--text-muted);">None</div>`;
                        
                        const price = parseFloat(prod.price).toFixed(3);
                        
                        html += `
                            <tr>
                                <td>${imgHtml}</td>
                                <td>
                                    <div style="font-weight: 600;">${htmlspecialchars(prod.name)}</div>
                                    <small style="color: var(--text-muted); font-size: 11px;">${htmlspecialchars(prod.description || '')}</small>
                                </td>
                                <td>${htmlspecialchars(prod.category_name)}</td>
                                <td style="font-family: monospace; font-weight: 600;">${price} ${currencyCode}</td>
                                <td>
                                    <div style="display: flex; gap: 8px; align-items: center;">
                                        <button class="btn-primary" style="padding: 6px 10px; font-size: 12px; background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.2); color: #818cf8; box-shadow: none; display: inline-flex; align-items: center; justify-content: center;" 
                                                onclick='editProduct(${JSON.stringify(prod)})' title="Edit Product">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                        </button>
                                        <form action="admin/products/delete/${prod.id}" method="POST" style="display:inline;" class="confirm-delete" data-message="Delete this product?">
                                            <button type="submit" class="btn-delete" style="padding: 6px 10px; display: inline-flex; align-items: center; justify-content: center;" title="Delete Product">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                    
                    tbody.innerHTML = html;
                    
                    productsTable = $('#products-table').DataTable({
                        dom: '<"dt-header"f>rt<"dt-footer"ip>',
                        pageLength: 10,
                        lengthChange: false,
                        language: {
                            search: "🔍 Search:"
                        }
                    });
                })
                .catch(err => console.error('Error filtering products:', err));
        }

        let taxReportsTable = null;

        function loadTaxReport() {
            const startDate = document.getElementById('tax-start-date').value;
            const endDate = document.getElementById('tax-end-date').value;
            const cur = '<?= htmlspecialchars($settings['currency_code']) ?>';

            const basePath = window.location.pathname.endsWith('/') ? window.location.pathname.slice(0, -1) : window.location.pathname;

            fetch(basePath + '/tax-report/json?start_date=' + startDate + '&end_date=' + endDate)
                .then(res => res.json())
                .then(data => {
                    if (taxReportsTable) {
                        taxReportsTable.destroy();
                    }

                    const tbody = document.getElementById('tax-report-table-body');
                    tbody.innerHTML = '';

                    let totalSubtotal = 0;
                    let totalTax = 0;
                    let totalDiscount = 0;
                    let totalGrand = 0;

                    if (data.success && data.report && data.report.length > 0) {
                        data.report.forEach(b => {
                            const sub = parseFloat(b.subtotal);
                            const tax = parseFloat(b.tax_amount);
                            const disc = parseFloat(b.discount_amount || 0);
                            const grand = parseFloat(b.grand_total);

                            totalSubtotal += sub;
                            totalTax += tax;
                            totalDiscount += disc;
                            totalGrand += grand;

                            const dateStr = new Date(b.created_at).toLocaleDateString([], { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
                            const payLabel = b.payment_method === 'cash' ? '💵 Cash' : (b.payment_method === 'card' ? '💳 Card' : '📱 QR Pay');

                            tbody.innerHTML += `
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                                    <td style="padding: 12px; font-weight:600;">#INV-${b.id}</td>
                                    <td style="padding: 12px;">${dateStr}</td>
                                    <td style="padding: 12px; text-align:right;">${sub.toFixed(3)} ${cur}</td>
                                    <td style="padding: 12px; text-align:right; color:var(--accent-orange);">${tax.toFixed(3)} ${cur}</td>
                                    <td style="padding: 12px; text-align:right; color:var(--accent-red);">${disc.toFixed(3)} ${cur}</td>
                                    <td style="padding: 12px; text-align:right; font-weight:700; color:var(--accent-green);">${grand.toFixed(3)} ${cur}</td>
                                    <td style="padding: 12px;">${payLabel}</td>
                                    <td style="padding: 12px;">${b.cashier_name || 'System / Auto'}</td>
                                </tr>
                            `;
                        });
                    } else {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 30px; color: var(--text-muted);">No matching records found.</td>
                            </tr>
                        `;
                    }

                    // Populate summary footer fields
                    document.getElementById('tax-total-subtotal').innerText = totalSubtotal.toFixed(3) + ' ' + cur;
                    document.getElementById('tax-total-tax').innerText = totalTax.toFixed(3) + ' ' + cur;
                    document.getElementById('tax-total-discount').innerText = totalDiscount.toFixed(3) + ' ' + cur;
                    document.getElementById('tax-total-grand').innerText = totalGrand.toFixed(3) + ' ' + cur;

                    // Initialize DataTable
                    taxReportsTable = $('#tax-report-table').DataTable({
                        dom: '<"dt-header"fl>rt<"dt-footer"ip>',
                        pageLength: 10,
                        lengthChange: true,
                        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                        language: {
                            search: "🔍 Search:",
                            lengthMenu: "Show _MENU_ entries"
                        }
                    });
                })
                .catch(err => console.error('Error fetching tax report:', err));
        }

        function exportTaxReportToExcel() {
            if (!taxReportsTable) return;

            let csvContent = "";
            
            // Add Header Row
            csvContent += '"Invoice ID","Date & Time","Subtotal","VAT/Tax Amt","Discount Amt","Grand Total","Payment Method","Cashier"\n';

            // Get all filtered data from DataTable (across all pages)
            const data = taxReportsTable.rows({ search: 'applied' }).data();

            // Loop through each row
            data.each(function(row) {
                const cleanRow = Array.from(row).map(cell => {
                    const temp = document.createElement("div");
                    temp.innerHTML = cell;
                    let text = temp.innerText.trim().replace(/(\r\n|\n|\r)/gm, " ");
                    text = text.replace(/"/g, '""');
                    return `"${text}"`;
                }).join(",");
                csvContent += cleanRow + "\n";
            });

            // Add totals row at the bottom
            const subtotalText = document.getElementById('tax-total-subtotal').innerText.trim().replace(/"/g, '""');
            const taxText = document.getElementById('tax-total-tax').innerText.trim().replace(/"/g, '""');
            const discountText = document.getElementById('tax-total-discount').innerText.trim().replace(/"/g, '""');
            const grandText = document.getElementById('tax-total-grand').innerText.trim().replace(/"/g, '""');
            
            csvContent += `\n"Total Summary","","${subtotalText}","${taxText}","${discountText}","${grandText}","",""\n`;

            // Set filename based on filter dates
            const start = document.getElementById('tax-start-date').value || 'report';
            const end = document.getElementById('tax-end-date').value || 'report';
            const filename = `VAT_Tax_Report_${start}_to_${end}.csv`;

            const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
            const link = document.createElement("a");
            if (link.download !== undefined) {
                const url = URL.createObjectURL(blob);
                link.setAttribute("href", url);
                link.setAttribute("download", filename);
                link.style.visibility = "hidden";
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }

        let fastMovingTable = null;
        let highRevenueTable = null;
        let analyticsDataGlobal = null;

        function loadAnalyticsReport() {
            const startDate = document.getElementById('analytics-start-date').value;
            const endDate = document.getElementById('analytics-end-date').value;
            const cur = '<?= htmlspecialchars($settings['currency_code']) ?>';

            const basePath = window.location.pathname.endsWith('/') ? window.location.pathname.slice(0, -1) : window.location.pathname;

            fetch(basePath + '/analytics/json?start_date=' + startDate + '&end_date=' + endDate)
                .then(res => res.json())
                .then(data => {
                    analyticsDataGlobal = (data.success && data.report) ? data.report : [];

                    if (fastMovingTable) fastMovingTable.destroy();
                    if (highRevenueTable) highRevenueTable.destroy();

                    const fmTbody = document.getElementById('fast-moving-tbody');
                    const hrTbody = document.getElementById('high-revenue-tbody');
                    fmTbody.innerHTML = '';
                    hrTbody.innerHTML = '';

                    let totalQty = 0;
                    let totalRevenue = 0.0;
                    let topQtyProduct = 'None';
                    let maxQty = 0;
                    let topRevProduct = 'None';
                    let maxRevenue = 0.0;

                    if (data.success && data.report && data.report.length > 0) {
                        const fmData = [...data.report].sort((a, b) => parseInt(b.total_qty_sold) - parseInt(a.total_qty_sold));
                        const hrData = [...data.report].sort((a, b) => parseFloat(b.total_revenue) - parseFloat(a.total_revenue));

                        data.report.forEach(item => {
                            const qty = parseInt(item.total_qty_sold);
                            const rev = parseFloat(item.total_revenue);

                            totalQty += qty;
                            totalRevenue += rev;

                            if (qty > maxQty) {
                                maxQty = qty;
                                topQtyProduct = item.product_name;
                            }
                            if (rev > maxRevenue) {
                                maxRevenue = rev;
                                topRevProduct = item.product_name;
                            }
                        });

                        fmData.forEach(item => {
                            fmTbody.innerHTML += `
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                                    <td style="padding: 12px; font-weight:600; text-align:left;">${htmlspecialchars(item.product_name)}</td>
                                    <td style="padding: 12px; color: var(--text-muted); text-align:left;">${htmlspecialchars(item.category_name)}</td>
                                    <td style="padding: 12px; text-align:right; font-weight:700;">${item.total_qty_sold}</td>
                                </tr>
                            `;
                        });

                        hrData.forEach(item => {
                            hrTbody.innerHTML += `
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                                    <td style="padding: 12px; font-weight:600; text-align:left;">${htmlspecialchars(item.product_name)}</td>
                                    <td style="padding: 12px; color: var(--text-muted); text-align:left;">${htmlspecialchars(item.category_name)}</td>
                                    <td style="padding: 12px; text-align:right; font-weight:700; color: var(--accent-green); font-family: monospace;">${parseFloat(item.total_revenue).toFixed(3)} ${cur}</td>
                                </tr>
                            `;
                        });
                    } else {
                        fmTbody.innerHTML = `<tr><td colspan="3" style="text-align: center; padding: 20px; color: var(--text-muted);">No sales data available.</td></tr>`;
                        hrTbody.innerHTML = `<tr><td colspan="3" style="text-align: center; padding: 20px; color: var(--text-muted);">No sales data available.</td></tr>`;
                    }

                    document.getElementById('kpi-total-qty').innerText = totalQty;
                    document.getElementById('kpi-total-revenue').innerText = totalRevenue.toFixed(3) + ' ' + cur;
                    document.getElementById('kpi-top-product').innerText = totalQty > 0 ? `${topQtyProduct} (${maxQty} sold)` : 'None';

                    fastMovingTable = $('#fast-moving-table').DataTable({
                        dom: 'rtip',
                        pageLength: 5,
                        order: [[2, 'desc']],
                        language: {
                            paginate: { previous: "◀", next: "▶" }
                        }
                    });

                    highRevenueTable = $('#high-revenue-table').DataTable({
                        dom: 'rtip',
                        pageLength: 5,
                        order: [[2, 'desc']],
                        language: {
                            paginate: { previous: "◀", next: "▶" }
                        }
                    });
                })
                .catch(err => console.error('Error loading sales analytics:', err));
        }

        function exportAnalyticsToExcel() {
            if (!analyticsDataGlobal || analyticsDataGlobal.length === 0) {
                Swal.fire({
                    title: 'No Data',
                    text: 'There is no data to export for the selected period.',
                    icon: 'info',
                    background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                    color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
                });
                return;
            }

            let csvContent = "";
            csvContent += '"Product ID","Product Name","Category","Price","Total Quantity Sold","Total Revenue"\n';

            analyticsDataGlobal.forEach(item => {
                const name = item.product_name.replace(/"/g, '""');
                const cat = item.category_name.replace(/"/g, '""');
                csvContent += `"${item.id}","${name}","${cat}","${parseFloat(item.price).toFixed(3)}","${item.total_qty_sold}","${parseFloat(item.total_revenue).toFixed(3)}"\n`;
            });

            const start = document.getElementById('analytics-start-date').value || 'report';
            const end = document.getElementById('analytics-end-date').value || 'report';
            const filename = `Product_Sales_Analytics_${start}_to_${end}.csv`;

            const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
            const link = document.createElement("a");
            if (link.download !== undefined) {
                const url = URL.createObjectURL(blob);
                link.setAttribute("href", url);
                link.setAttribute("download", filename);
                link.style.visibility = "hidden";
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }

        let waiterPerformanceTable = null;
        let waiterPerformanceDataGlobal = null;

        function loadWaiterPerformanceReport() {
            const startDate = document.getElementById('waiter-start-date').value;
            const endDate = document.getElementById('waiter-end-date').value;
            const cur = '<?= htmlspecialchars($settings['currency_code']) ?>';

            const basePath = window.location.pathname.endsWith('/') ? window.location.pathname.slice(0, -1) : window.location.pathname;

            fetch(basePath + '/waiter-performance/json?start_date=' + startDate + '&end_date=' + endDate)
                .then(res => res.json())
                .then(data => {
                    waiterPerformanceDataGlobal = (data.success && data.report) ? data.report : [];

                    if (waiterPerformanceTable) {
                        waiterPerformanceTable.destroy();
                    }

                    const tbody = document.getElementById('waiter-performance-table-body');
                    tbody.innerHTML = '';

                    if (data.success && data.report && data.report.length > 0) {
                        data.report.forEach(row => {
                            const statusLabel = parseInt(row.is_active) === 1 
                                ? '<span style="background:rgba(16,185,129,0.15); color:var(--accent-green); padding:2px 8px; border-radius:6px; font-size:11px; font-weight:600;">Active</span>' 
                                : '<span style="background:rgba(239,68,68,0.15); color:var(--accent-red); padding:2px 8px; border-radius:6px; font-size:11px; font-weight:600;">Deactivated</span>';
                            
                            tbody.innerHTML += `
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                                    <td style="padding: 12px; font-weight:600;">#${row.waiter_id}</td>
                                    <td style="padding: 12px; font-weight:600;">${row.waiter_name}</td>
                                    <td style="padding: 12px; color:var(--text-muted);">${row.username}</td>
                                    <td style="padding: 12px; text-align:center;">${statusLabel}</td>
                                    <td style="padding: 12px; text-align:right; font-weight:700;">${row.total_orders}</td>
                                    <td style="padding: 12px; text-align:right; font-weight:700; color:var(--accent-orange);">${row.paid_orders}</td>
                                    <td style="padding: 12px; text-align:right; font-weight:700; color:var(--accent-green);">${parseFloat(row.total_revenue).toFixed(3)} ${cur}</td>
                                </tr>
                            `;
                        });
                    } else {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted);">No records found.</td>
                            </tr>
                        `;
                    }

                    // Initialize DataTable
                    waiterPerformanceTable = $('#waiter-performance-table').DataTable({
                        dom: '<"dt-header"fl>rt<"dt-footer"ip>',
                        pageLength: 10,
                        lengthChange: true,
                        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                        order: [[6, 'desc']],
                        language: {
                            search: "🔍 Search:",
                            lengthMenu: "Show _MENU_ entries"
                        }
                    });
                })
                .catch(err => console.error('Error fetching waiter performance report:', err));
        }

        function exportWaiterPerformanceToExcel() {
            if (!waiterPerformanceDataGlobal || waiterPerformanceDataGlobal.length === 0) {
                Swal.fire({
                    title: 'No Data',
                    text: 'There is no data to export for the selected period.',
                    icon: 'info',
                    background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                    color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
                });
                return;
            }

            let csvContent = "";
            csvContent += '"Waiter ID","Waiter Name","Username","Status","Orders Taken","Paid Orders","Total Revenue"\n';

            waiterPerformanceDataGlobal.forEach(row => {
                const statusText = parseInt(row.is_active) === 1 ? "Active" : "Deactivated";
                const wName = row.waiter_name.replace(/"/g, '""');
                const uName = row.username.replace(/"/g, '""');
                csvContent += `"${row.waiter_id}","${wName}","${uName}","${statusText}","${row.total_orders}","${row.paid_orders}","${parseFloat(row.total_revenue).toFixed(3)}"\n`;
            });

            const start = document.getElementById('waiter-start-date').value || 'report';
            const end = document.getElementById('waiter-end-date').value || 'report';
            const filename = `Waiter_Performance_Report_${start}_to_${end}.csv`;

            const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
            const link = document.createElement("a");
            if (link.download !== undefined) {
                const url = URL.createObjectURL(blob);
                link.setAttribute("href", url);
                link.setAttribute("download", filename);
                link.style.visibility = "hidden";
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }

        $(document).ready(function() {
            $('#categories-table').DataTable({
                dom: '<"dt-header"f>rt<"dt-footer"ip>',
                pageLength: 5,
                lengthChange: false,
                language: {
                    search: "🔍 Search:"
                }
            });
            
            productsTable = $('#products-table').DataTable({
                dom: '<"dt-header"f>rt<"dt-footer"ip>',
                pageLength: 10,
                lengthChange: false,
                language: {
                    search: "🔍 Search:"
                }
            });
        });

        function resetUserPasswordPrompt(userId, userName) {
            Swal.fire({
                title: 'Reset Password',
                text: 'Enter a new password for ' + userName + ':',
                input: 'password',
                inputPlaceholder: 'Enter new password...',
                inputAttributes: {
                    autocapitalize: 'off',
                    autocorrect: 'off'
                },
                showCancelButton: true,
                confirmButtonText: 'Reset Password',
                showLoaderOnConfirm: true,
                background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6',
                preConfirm: (newPassword) => {
                    if (!newPassword || newPassword.trim() === '') {
                        Swal.showValidationMessage('Password cannot be empty');
                        return false;
                    }
                    const basePath = window.location.pathname.endsWith('/') ? window.location.pathname.slice(0, -1) : window.location.pathname;
                    return fetch(basePath + '/users/reset-password/' + userId, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'new_password=' + encodeURIComponent(newPassword)
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(response.statusText);
                        }
                        return response.json();
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Request failed: ${error}`);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed && result.value && result.value.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Password has been successfully updated.',
                        icon: 'success',
                        background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                        color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
                    });
                }
            });
        }

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

        function openSandsModal() {
            document.getElementById('sands-modal').style.display = 'flex';
        }

        function closeSandsModal() {
            document.getElementById('sands-modal').style.display = 'none';
        }

        // Auto-initialize Ladda on all submit buttons
        document.querySelectorAll('form button[type="submit"]').forEach(function(btn) {
            if (!btn.classList.contains('ladda-button')) {
                btn.classList.add('ladda-button');
            }
            if (!btn.getAttribute('data-style')) {
                btn.setAttribute('data-style', 'expand-right');
            }
            if (!btn.querySelector('.ladda-label')) {
                var label = document.createElement('span');
                label.className = 'ladda-label';
                label.innerHTML = btn.innerHTML;
                btn.innerHTML = '';
                btn.appendChild(label);
            }
        });

        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                // If it is a confirm-delete form, only start Ladda after user confirms
                if (form.classList.contains('confirm-delete') || form.classList.contains('confirm-action')) {
                    // Let the SweetAlert2 handler deal with it
                    return;
                }
                var btn = form.querySelector('button[type="submit"]');
                if (btn) {
                    var l = Ladda.create(btn);
                    l.start();
                }
            });
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Ladda/1.0.6/spin.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Ladda/1.0.6/ladda.min.js"></script>
</body>
</html>
