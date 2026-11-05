<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Driver App | <?= htmlspecialchars($settings['restaurant_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --bg-color: #0b0f19;
            --card-bg: #111827;
            --card-border: rgba(255, 255, 255, 0.08);
            --text-color: #f3f4f6;
            --text-muted: #9ca3af;
            --primary: #0284c7;
            --primary-grad: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            --accent-cyan: #06b6d4;
            --accent-green: #10b981;
            --accent-orange: #f59e0b;
            --accent-red: #ef4444;
        }

        body.light-theme {
            --bg-color: #f1f5f9;
            --card-bg: #ffffff;
            --card-border: #e2e8f0;
            --text-color: #0f172a;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 0 0 80px 0;
            min-height: 100vh;
        }

        /* Top Header */
        .app-header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(17, 24, 39, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--card-border);
            padding: 12px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        body.light-theme .app-header {
            background: rgba(255, 255, 255, 0.88);
        }

        .driver-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .driver-avatar {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--primary-grad);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
        }

        .driver-title {
            font-weight: 800;
            font-size: 16px;
            line-height: 1.2;
        }

        .driver-status {
            font-size: 11px;
            color: var(--accent-green);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .driver-status::before {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--accent-green);
            display: inline-block;
            box-shadow: 0 0 8px var(--accent-green);
        }

        .header-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .icon-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--card-border);
            color: var(--text-color);
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .icon-btn:active {
            transform: scale(0.92);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            padding: 14px 16px 6px;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 12px 10px;
            text-align: center;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
        }

        .stat-value {
            font-size: 17px;
            font-weight: 800;
            margin-bottom: 2px;
            letter-spacing: -0.3px;
        }

        .stat-label {
            font-size: 10px;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        /* Section Container */
        .content-area {
            padding: 12px 16px;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 16px 0 10px;
        }

        .section-title {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .badge-count {
            background: var(--primary);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
        }

        /* Active Delivery Card */
        .active-card {
            background: var(--card-bg);
            border: 2px solid var(--accent-cyan);
            border-radius: 20px;
            padding: 18px;
            margin-bottom: 20px;
            box-shadow: 0 12px 30px rgba(6, 182, 212, 0.18);
            position: relative;
            overflow: hidden;
        }

        .active-card::before {
            content: 'ACTIVE DELIVERY IN PROGRESS';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(90deg, #0284c7, #06b6d4);
            color: white;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1px;
            text-align: center;
            padding: 4px 0;
        }

        .active-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 10px;
            margin-bottom: 14px;
        }

        .token-num {
            font-size: 26px;
            font-weight: 900;
            color: var(--accent-cyan);
            letter-spacing: -0.5px;
        }

        .active-time {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 600;
        }

        .customer-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--card-border);
            border-radius: 14px;
            padding: 14px;
            margin-bottom: 14px;
        }

        .cust-name {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .cust-phone {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .cust-address {
            font-size: 13px;
            color: var(--text-color);
            line-height: 1.4;
            padding: 8px 10px;
            background: rgba(0, 0, 0, 0.15);
            border-radius: 8px;
            margin-bottom: 8px;
        }

        .cust-notes {
            font-size: 12px;
            color: var(--accent-orange);
            font-style: italic;
        }

        .action-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 14px;
        }

        .btn-call {
            background: rgba(16, 185, 129, 0.15);
            color: var(--accent-green);
            border: 1px solid rgba(16, 185, 129, 0.3);
            padding: 12px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.15s;
        }

        .btn-call:active {
            transform: scale(0.97);
            background: var(--accent-green);
            color: white;
        }

        .btn-map {
            background: rgba(2, 132, 199, 0.15);
            color: #38bdf8;
            border: 1px solid rgba(2, 132, 199, 0.3);
            padding: 12px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.15s;
        }

        .btn-map:active {
            transform: scale(0.97);
            background: var(--primary);
            color: white;
        }

        /* Payment Banner */
        .pay-banner {
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 16px;
        }

        .pay-paid {
            background: rgba(16, 185, 129, 0.12);
            color: var(--accent-green);
            border: 1px solid rgba(16, 185, 129, 0.25);
        }

        .pay-cod {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
            font-size: 15px;
        }

        /* Order Items Box */
        .items-box {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 16px;
        }

        .items-title {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            padding: 4px 0;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.05);
        }

        .item-row:last-child {
            border-bottom: none;
        }

        .btn-deliver {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.35);
            transition: all 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-deliver:active {
            transform: scale(0.98);
        }

        /* Available Order Cards */
        .order-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 18px;
            padding: 16px;
            margin-bottom: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            transition: all 0.2s;
        }

        .order-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .token-badge {
            font-size: 18px;
            font-weight: 800;
            color: var(--text-color);
        }

        .btn-accept {
            background: var(--primary-grad);
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
            transition: all 0.15s;
        }

        .btn-accept:active {
            transform: scale(0.96);
        }

        /* Locked notice */
        .lock-notice {
            background: rgba(245, 158, 11, 0.08);
            border: 1px dashed rgba(245, 158, 11, 0.3);
            border-radius: 16px;
            padding: 16px;
            text-align: center;
            color: var(--accent-orange);
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
        }

        .empty-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }

        /* Bottom Tab Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(17, 24, 39, 0.95);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-top: 1px solid var(--card-border);
            display: flex;
            justify-content: space-around;
            padding: 8px 10px;
            z-index: 100;
        }

        body.light-theme .bottom-nav {
            background: rgba(255, 255, 255, 0.95);
        }

        .nav-tab {
            background: none;
            border: none;
            color: var(--text-muted);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            padding: 6px 14px;
            border-radius: 12px;
            transition: all 0.2s;
        }

        .nav-tab .tab-icon {
            font-size: 20px;
        }

        .nav-tab.active {
            color: var(--accent-cyan);
            background: rgba(6, 182, 212, 0.1);
        }

        /* History & Ledger Tables */
        .history-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 14px;
            padding: 14px;
            margin-bottom: 10px;
        }

        .ledger-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .ledger-credit { background: rgba(16, 185, 129, 0.15); color: var(--accent-green); }
        .ledger-debit { background: rgba(239, 68, 68, 0.15); color: #f87171; }
        .ledger-cod { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
    </style>
</head>
<body>

    <!-- App Header -->
    <header class="app-header">
        <div class="driver-info">
            <div class="driver-avatar">🚴</div>
            <div>
                <div class="driver-title"><?= htmlspecialchars($driver_name) ?></div>
                <div class="driver-status">Online • GPS Ready</div>
            </div>
        </div>
        <div class="header-actions">
            <button class="icon-btn" onclick="toggleTheme()" title="Toggle Theme">🌓</button>
            <button class="icon-btn" onclick="fetchDashboardData(true)" title="Refresh">🔄</button>
            <a href="javascript:void(0)" onclick="window.location.href = basePath + '/logout'" class="icon-btn" style="color:var(--accent-red); text-decoration:none;" title="Logout">🚪</a>
        </div>
    </header>

    <!-- Top Stats -->
    <div class="stats-grid" style="<?= empty($commission_enabled) ? 'grid-template-columns: 1fr 1fr;' : '' ?>">
        <div class="stat-card">
            <div class="stat-value" id="stat-completed" style="color:var(--accent-green);">0</div>
            <div class="stat-label">Delivered Today</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="stat-cash" style="color:var(--accent-orange);">0.000</div>
            <div class="stat-label">Cash In Hand</div>
        </div>
        <?php if (!empty($commission_enabled)): ?>
        <div class="stat-card" id="stat-card-commission">
            <div class="stat-value" id="stat-wallet" style="color:var(--accent-cyan);">0.000</div>
            <div class="stat-label">Commission Earned</div>
        </div>
        <?php endif; ?>
    </div>

    <!-- MAIN VIEW CONTAINER -->
    <main class="content-area">
        
        <!-- TAB 1: DELIVERIES (Default) -->
        <div id="tab-pane-deliveries">
            <!-- Active Delivery Section -->
            <div id="active-delivery-container" style="display: none;"></div>

            <!-- Available Pickup Section -->
            <div id="available-section">
                <div class="section-header">
                    <div class="section-title">
                        <span>📦 Available Deliveries</span>
                        <span class="badge-count" id="available-count">0</span>
                    </div>
                    <span style="font-size: 11px; color: var(--text-muted);">Auto-updating</span>
                </div>
                <div id="available-deliveries-list">
                    <div class="empty-state">
                        <div class="empty-icon">⏳</div>
                        <div>Checking for deliveries...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: HISTORY -->
        <div id="tab-pane-history" style="display: none;">
            <div class="section-header">
                <div class="section-title">📜 Delivered History</div>
                <button onclick="fetchHistory(true)" style="background:none; border:none; color:var(--primary); font-weight:700; font-size:12px; cursor:pointer;">Refresh</button>
            </div>

            <!-- Date Filter Bar for History -->
            <div style="background:var(--card-bg); border:1px solid var(--card-border); border-radius:14px; padding:12px; margin-bottom:14px;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:10px;">
                    <div>
                        <label style="font-size:10px; font-weight:700; color:var(--text-muted); display:block; margin-bottom:4px; text-transform:uppercase;">From Date</label>
                        <input type="date" id="history-start-date" style="width:100%; box-sizing:border-box; background:rgba(255,255,255,0.06); border:1px solid var(--card-border); color:var(--text-color); border-radius:8px; padding:7px 8px; font-size:12px;">
                    </div>
                    <div>
                        <label style="font-size:10px; font-weight:700; color:var(--text-muted); display:block; margin-bottom:4px; text-transform:uppercase;">To Date</label>
                        <input type="date" id="history-end-date" style="width:100%; box-sizing:border-box; background:rgba(255,255,255,0.06); border:1px solid var(--card-border); color:var(--text-color); border-radius:8px; padding:7px 8px; font-size:12px;">
                    </div>
                </div>
                <div style="display:flex; gap:8px;">
                    <button onclick="fetchHistory(true)" style="flex:2; background:var(--primary); color:white; border:none; padding:8px; border-radius:8px; font-size:12px; font-weight:700; cursor:pointer;">🔍 Search History</button>
                    <button onclick="resetHistoryToToday()" style="flex:1; background:rgba(255,255,255,0.08); color:var(--text-color); border:1px solid var(--card-border); padding:8px; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">Today</button>
                </div>
            </div>

            <div id="history-list"></div>
        </div>

        <!-- TAB 3: WALLET & LEDGER -->
        <div id="tab-pane-wallet" style="display: none;">
            <div class="section-header">
                <div class="section-title">💰 Driver Ledger & Wallet</div>
                <button onclick="fetchLedger(true)" style="background:none; border:none; color:var(--primary); font-weight:700; font-size:12px; cursor:pointer;">Refresh</button>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div style="background:var(--card-bg); border:1px solid var(--card-border); border-radius:16px; padding:16px;">
                    <div style="font-size:11px; color:var(--text-muted); font-weight:700; margin-bottom:4px;">CASH TO HAND OVER</div>
                    <div style="font-size:20px; font-weight:800; color:var(--accent-orange); margin-bottom:6px;" id="wallet-cash-total">0.000 <?= htmlspecialchars($settings['currency_code']) ?></div>
                    <div style="font-size:11px; color:var(--text-muted); line-height:1.3;">
                        Collected COD cash. Clears when Counter confirms handover.
                    </div>
                </div>
                <div style="background:var(--card-bg); border:1px solid var(--card-border); border-radius:16px; padding:16px;">
                    <div style="font-size:11px; color:var(--text-muted); font-weight:700; margin-bottom:4px;">COMMISSION BALANCE</div>
                    <div style="font-size:20px; font-weight:800; color:var(--accent-green); margin-bottom:6px;" id="wallet-commission-total">0.000 <?= htmlspecialchars($settings['currency_code']) ?></div>
                    <div style="font-size:11px; color:var(--text-muted); line-height:1.3;">
                        Your earnings wallet. Paid out by Counter/Admin.
                    </div>
                </div>
            </div>

            <!-- Date Filter Bar for Ledger -->
            <div style="background:var(--card-bg); border:1px solid var(--card-border); border-radius:14px; padding:12px; margin-bottom:14px;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:10px;">
                    <div>
                        <label style="font-size:10px; font-weight:700; color:var(--text-muted); display:block; margin-bottom:4px; text-transform:uppercase;">From Date</label>
                        <input type="date" id="ledger-start-date" style="width:100%; box-sizing:border-box; background:rgba(255,255,255,0.06); border:1px solid var(--card-border); color:var(--text-color); border-radius:8px; padding:7px 8px; font-size:12px;">
                    </div>
                    <div>
                        <label style="font-size:10px; font-weight:700; color:var(--text-muted); display:block; margin-bottom:4px; text-transform:uppercase;">To Date</label>
                        <input type="date" id="ledger-end-date" style="width:100%; box-sizing:border-box; background:rgba(255,255,255,0.06); border:1px solid var(--card-border); color:var(--text-color); border-radius:8px; padding:7px 8px; font-size:12px;">
                    </div>
                </div>
                <div style="display:flex; gap:8px;">
                    <button onclick="fetchLedger(true)" style="flex:2; background:var(--primary); color:white; border:none; padding:8px; border-radius:8px; font-size:12px; font-weight:700; cursor:pointer;">🔍 Search Ledger</button>
                    <button onclick="resetLedgerToToday()" style="flex:1; background:rgba(255,255,255,0.08); color:var(--text-color); border:1px solid var(--card-border); padding:8px; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">Today</button>
                </div>
            </div>

            <div id="ledger-list"></div>
        </div>

    </main>

    <!-- Bottom Navigation Bar -->
    <nav class="bottom-nav">
        <button class="nav-tab active" id="btn-tab-deliveries" onclick="switchTab('deliveries')">
            <span class="tab-icon">🚀</span>
            <span>Deliveries</span>
        </button>
        <button class="nav-tab" id="btn-tab-history" onclick="switchTab('history')">
            <span class="tab-icon">📜</span>
            <span>History</span>
        </button>
        <?php if (!empty($commission_enabled)): ?>
        <button class="nav-tab" id="btn-tab-wallet" onclick="switchTab('wallet')">
            <span class="tab-icon">💰</span>
            <span>Wallet</span>
        </button>
        <?php endif; ?>
    </nav>

    <script>
        const basePath = window.location.pathname.endsWith('/') ? window.location.pathname.slice(0, -1) : window.location.pathname;
        const currencyCode = '<?= htmlspecialchars($settings['currency_code']) ?>';
        const commissionEnabled = <?= !empty($commission_enabled) ? 'true' : 'false' ?>;
        let benefitPayQr = <?= json_encode(!empty($benefit_pay_qr) ? $benefit_pay_qr : null) ?>;
        let currentTab = 'deliveries';
        let pollTimer = null;
        let lastAvailableCount = 0;

        function toggleTheme() {
            document.body.classList.toggle('light-theme');
            localStorage.setItem('driver_theme', document.body.classList.contains('light-theme') ? 'light' : 'dark');
        }

        if (localStorage.getItem('driver_theme') === 'light') {
            document.body.classList.add('light-theme');
        }

        function switchTab(tab) {
            currentTab = tab;
            document.getElementById('tab-pane-deliveries').style.display = tab === 'deliveries' ? 'block' : 'none';
            document.getElementById('tab-pane-history').style.display = tab === 'history' ? 'block' : 'none';
            document.getElementById('tab-pane-wallet').style.display = tab === 'wallet' ? 'block' : 'none';

            document.getElementById('btn-tab-deliveries').classList.toggle('active', tab === 'deliveries');
            document.getElementById('btn-tab-history').classList.toggle('active', tab === 'history');
            document.getElementById('btn-tab-wallet').classList.toggle('active', tab === 'wallet');

            if (tab === 'history') fetchHistory();
            if (tab === 'wallet') fetchLedger();
            if (tab === 'deliveries') fetchDashboardData();
        }

        function playNewOrderChime() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
                osc.frequency.setValueAtTime(880, ctx.currentTime + 0.15); // A5
                gain.gain.setValueAtTime(0.3, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.6);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.6);
            } catch(e) {}
        }

        function fetchDashboardData(manual = false) {
            fetch(basePath + '/api/dashboard?_=' + Date.now())
                .then(res => res.json())
                .then(data => {
                    if (!data.success) return;

                    // Update Top Stats
                    if (document.getElementById('stat-completed')) document.getElementById('stat-completed').innerText = data.stats.today_completed || 0;
                    if (document.getElementById('stat-cash')) document.getElementById('stat-cash').innerText = parseFloat(data.stats.cash_in_hand || 0).toFixed(3);
                    if (document.getElementById('stat-wallet')) document.getElementById('stat-wallet').innerText = parseFloat(data.stats.commission_balance || 0).toFixed(3);
                    if (document.getElementById('wallet-cash-total')) document.getElementById('wallet-cash-total').innerText = parseFloat(data.stats.cash_in_hand || 0).toFixed(3) + ' ' + currencyCode;
                    if (document.getElementById('wallet-commission-total')) {
                        document.getElementById('wallet-commission-total').innerText = parseFloat(data.stats.commission_balance || 0).toFixed(3) + ' ' + currencyCode;
                    }
                    if (data.benefit_pay_qr !== undefined) {
                        benefitPayQr = data.benefit_pay_qr;
                    }

                    // Active Delivery Section
                    renderActiveDelivery(data.active_delivery);

                    // Available Deliveries Section
                    renderAvailableDeliveries(data.available_deliveries, data.active_delivery);

                    if (manual) {
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Refreshed', showConfirmButton: false, timer: 1000 });
                    }
                })
                .catch(err => console.error('Dashboard error:', err));
        }

        function renderActiveDelivery(active) {
            const container = document.getElementById('active-delivery-container');
            if (!active) {
                container.style.display = 'none';
                container.innerHTML = '';
                return;
            }

            container.style.display = 'block';

            const isCod = active.payment_type === 'cod';
            const codAmt = parseFloat(active.cod_amount || 0).toFixed(3);

            let mapButton = '';
            if (active.google_maps_link) {
                mapButton = `<a href="${escapeHtml(active.google_maps_link)}" target="_blank" class="btn-map">📍 Open Maps</a>`;
            } else if (active.customer_address) {
                const mapsSearch = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(active.customer_address)}`;
                mapButton = `<a href="${mapsSearch}" target="_blank" class="btn-map">📍 Search Location</a>`;
            }

            let itemsHtml = '';
            if (active.items && active.items.length > 0) {
                itemsHtml = `
                    <div class="items-box">
                        <div class="items-title">Order Items (${active.items.length})</div>
                        ${active.items.map(it => `
                            <div class="item-row">
                                <span><strong>${it.quantity}x</strong> ${escapeHtml(it.product_name)}</span>
                                <span style="color:var(--text-muted);">${(it.quantity * it.unit_price).toFixed(3)}</span>
                            </div>
                        `).join('')}
                    </div>
                `;
            }

            let qrBoxHtml = '';
            if (isCod && benefitPayQr) {
                qrBoxHtml = `
                    <div style="margin: 15px 0 10px 0; background: rgba(16, 185, 129, 0.08); border: 1.5px dashed rgba(16, 185, 129, 0.45); border-radius: 14px; padding: 14px; text-align: center;">
                        <div style="font-weight: 800; color: #10b981; font-size: 14px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                            <span>📱</span> Pay via BenefitPay QR
                        </div>
                        <div style="font-size: 11.5px; color: var(--text-muted); margin: 4px 0 10px 0;">
                            Show this QR to the customer to scan with BenefitPay App
                        </div>
                        <div style="background: #ffffff; padding: 8px; border-radius: 12px; display: inline-block; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                            <img src="${benefitPayQr}" alt="BenefitPay QR" style="width: 170px; height: 170px; object-fit: contain; display: block; border-radius: 6px;" onclick="window.open('${benefitPayQr}', '_blank')">
                        </div>
                        <div style="margin-top: 8px; font-weight: 800; color: #10b981; font-size: 15px;">
                            Amount to Pay: ${codAmt} ${currencyCode}
                        </div>
                    </div>
                `;
            }

            container.innerHTML = `
                <div class="active-card">
                    <div class="active-header">
                        <div class="token-num">Token #${escapeHtml(active.token_number || active.order_id)}</div>
                        <div class="active-time">Accepted: ${active.accepted_at ? new Date(active.accepted_at.replace(' ', 'T')).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}) : 'Just now'}</div>
                    </div>

                    <div class="customer-card">
                        <div class="cust-name">👤 ${escapeHtml(active.customer_name || 'Guest Customer')}</div>
                        <div class="cust-phone">📱 ${escapeHtml(active.customer_mobile)}</div>
                        ${active.customer_address ? `<div class="cust-address">🏠 ${escapeHtml(active.customer_address)}</div>` : ''}
                        ${active.delivery_notes ? `<div class="cust-notes">📝 Note: ${escapeHtml(active.delivery_notes)}</div>` : ''}
                    </div>

                    <div class="action-grid">
                        <a href="tel:${escapeHtml(active.customer_mobile)}" class="btn-call">📞 Call Customer</a>
                        ${mapButton}
                    </div>

                    <div class="pay-banner ${isCod ? 'pay-cod' : 'pay-paid'}">
                        ${isCod ? `💵 COLLECT ON DELIVERY: <strong>${codAmt} ${currencyCode}</strong>` : `✅ ALREADY PAID AT COUNTER`}
                    </div>

                    ${itemsHtml}

                    ${qrBoxHtml}

                    <button class="btn-deliver" onclick="confirmMarkDelivered(${active.id}, ${isCod}, '${codAmt}')">
                        <span>✅</span> Mark as Delivered
                    </button>
                </div>
            `;
        }

        function renderAvailableDeliveries(deliveries, activeDelivery) {
            const listEl = document.getElementById('available-deliveries-list');
            const countEl = document.getElementById('available-count');
            countEl.innerText = deliveries ? deliveries.length : 0;

            // Chime if new orders arrived
            if (deliveries && deliveries.length > lastAvailableCount && lastAvailableCount !== 0) {
                playNewOrderChime();
            }
            lastAvailableCount = deliveries ? deliveries.length : 0;

            // If active delivery exists, lock available queue
            if (activeDelivery) {
                listEl.innerHTML = `
                    <div class="lock-notice">
                        🔒 You are currently delivering <strong>Token #${escapeHtml(activeDelivery.token_number || activeDelivery.order_id)}</strong>.
                        <br>Finish this delivery before you can accept another one.
                    </div>
                `;
                return;
            }

            if (!deliveries || deliveries.length === 0) {
                listEl.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">🛵</div>
                        <div style="font-weight:700; margin-bottom:4px;">No Pending Deliveries</div>
                        <div style="font-size:12px;">Waiting for counter to dispatch orders...</div>
                    </div>
                `;
                return;
            }

            let html = '';
            deliveries.forEach(d => {
                const isCod = d.payment_type === 'cod';
                const codAmt = parseFloat(d.cod_amount || 0).toFixed(3);
                const itemsCount = d.items ? d.items.length : 0;

                html += `
                    <div class="order-card">
                        <div class="order-card-header">
                            <div class="token-badge">Token #${escapeHtml(d.token_number || d.order_id)}</div>
                            <span style="font-size:12px; color:var(--text-muted);">${d.created_at ? new Date(d.created_at.replace(' ', 'T')).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}) : ''}</span>
                        </div>

                        <div style="font-size:14px; font-weight:700; margin-bottom:4px;">👤 ${escapeHtml(d.customer_name || 'Customer')} • ${escapeHtml(d.customer_mobile)}</div>
                        ${d.customer_address ? `<div style="font-size:13px; color:var(--text-muted); margin-bottom:8px;">🏠 ${escapeHtml(d.customer_address)}</div>` : ''}

                        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px; padding-top:10px; border-top:1px solid var(--card-border);">
                            <div>
                                <span class="ledger-badge ${isCod ? 'ledger-cod' : 'ledger-credit'}">${isCod ? '💵 COD: ' + codAmt + ' ' + currencyCode : '✅ PAID'}</span>
                                <span style="font-size:11px; color:var(--text-muted); margin-left:6px;">${itemsCount} items</span>
                            </div>
                            <button class="btn-accept" onclick="acceptDelivery(${d.id})">🚀 Accept</button>
                        </div>
                    </div>
                `;
            });
            listEl.innerHTML = html;
        }

        function acceptDelivery(deliveryId) {
            Swal.fire({
                title: 'Accept Delivery?',
                text: 'Are you ready to deliver this order?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0284c7',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Accept & Start'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch(basePath + '/api/claim/' + deliveryId, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Accepted!', text: 'You can now view customer details and navigate.', timer: 1500, showConfirmButton: false });
                            fetchDashboardData();
                        } else {
                            Swal.fire('Cannot Accept', data.error || 'Failed to claim delivery.', 'warning');
                            fetchDashboardData();
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Network error. Try again.', 'error'));
                }
            });
        }

        function toggleDeliveryPayMode(mode, total) {
            const splitBox = document.getElementById('swal_split_container');
            if (splitBox) {
                splitBox.style.display = (mode === 'split') ? 'block' : 'none';
            }
        }

        function recalcDeliverySplit(total) {
            const cash = parseFloat(document.getElementById('swal_split_cash').value) || 0;
            const qr = parseFloat(document.getElementById('swal_split_qr').value) || 0;
            const card = parseFloat(document.getElementById('swal_split_card').value) || 0;
            const sum = Math.round((cash + qr + card) * 1000) / 1000;

            const totalValEl = document.getElementById('swal_split_total_val');
            const statusEl = document.getElementById('swal_split_status');

            if (totalValEl) totalValEl.innerText = sum.toFixed(3);
            if (statusEl) {
                if (Math.abs(sum - total) <= 0.005) {
                    statusEl.style.color = '#10b981';
                    statusEl.innerText = '✅ Match';
                } else {
                    const diff = Math.round((total - sum) * 1000) / 1000;
                    statusEl.style.color = '#ef4444';
                    statusEl.innerText = diff > 0 ? `⚠️ Need +${diff.toFixed(3)}` : `⚠️ Excess +${(-diff).toFixed(3)}`;
                }
            }
        }

        function confirmMarkDelivered(deliveryId, isCod, codAmt) {
            if (!isCod) {
                Swal.fire({
                    title: 'Mark as Delivered?',
                    text: 'Confirm that the customer received their order?',
                    icon: 'question',
                    input: 'text',
                    inputPlaceholder: 'Optional delivery note (e.g. handed to security)...',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, Order Delivered'
                }).then(result => {
                    if (result.isConfirmed) {
                        submitDeliveryComplete(deliveryId, {
                            collection_method: 'cash',
                            cash_amount: 0,
                            qr_amount: 0,
                            card_amount: 0,
                            notes: result.value || ''
                        });
                    }
                });
                return;
            }

            const totalNum = parseFloat(codAmt);
            const totalStr = totalNum.toFixed(window.PRICE_DECIMALS || 3);

            Swal.fire({
                title: '💵 Payment Collection',
                html: `
                    <div style="text-align:left; font-size:13px;">
                        <div style="text-align:center; margin-bottom:14px; padding:10px; background:rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.3); border-radius:10px;">
                            <div style="font-size:12px; color:var(--text-muted);">Total Order Amount</div>
                            <div style="font-size:22px; font-weight:800; color:#f59e0b;">${totalStr} ${currencyCode}</div>
                        </div>

                        <label style="font-weight:700; display:block; margin-bottom:8px;">How did the customer pay?</label>
                        <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:14px;">
                            <label style="display:flex; align-items:center; gap:8px; padding:10px; background:rgba(255,255,255,0.05); border:1px solid var(--card-border); border-radius:8px; cursor:pointer;">
                                <input type="radio" name="swal_pay_method" value="cash" checked onchange="toggleDeliveryPayMode('cash', ${totalNum})">
                                <div>
                                    <div style="font-weight:700;">💵 Full Cash (${totalStr} ${currencyCode})</div>
                                    <div style="font-size:11px; color:var(--text-muted);">Collected in hand. Added to your wallet to hand over to counter.</div>
                                </div>
                            </label>

                            <label style="display:flex; align-items:center; gap:8px; padding:10px; background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.3); border-radius:8px; cursor:pointer;">
                                <input type="radio" name="swal_pay_method" value="qr_pay" onchange="toggleDeliveryPayMode('qr_pay', ${totalNum})">
                                <div>
                                    <div style="font-weight:700; color:#10b981;">📱 Paid via BenefitPay QR (${totalStr} ${currencyCode})</div>
                                    <div style="font-size:11px; color:var(--text-muted);">Direct to restaurant account. <strong style="color:#10b981;">NOT added to your wallet!</strong></div>
                                </div>
                            </label>

                            <label style="display:flex; align-items:center; gap:8px; padding:10px; background:rgba(255,255,255,0.05); border:1px solid var(--card-border); border-radius:8px; cursor:pointer;">
                                <input type="radio" name="swal_pay_method" value="card" onchange="toggleDeliveryPayMode('card', ${totalNum})">
                                <div>
                                    <div style="font-weight:700;">💳 Card / POS Machine (${totalStr} ${currencyCode})</div>
                                    <div style="font-size:11px; color:var(--text-muted);">Direct card collection. NOT added to your wallet.</div>
                                </div>
                            </label>

                            <label style="display:flex; align-items:center; gap:8px; padding:10px; background:rgba(99,102,241,0.08); border:1px solid rgba(99,102,241,0.3); border-radius:8px; cursor:pointer;">
                                <input type="radio" name="swal_pay_method" value="split" onchange="toggleDeliveryPayMode('split', ${totalNum})">
                                <div>
                                    <div style="font-weight:700; color:#818cf8;">⚖️ Split Payment (Cash + Benefit/Card)</div>
                                    <div style="font-size:11px; color:var(--text-muted);">Customer paid part in Cash and part digitally.</div>
                                </div>
                            </label>
                        </div>

                        <!-- Split inputs container -->
                        <div id="swal_split_container" style="display:none; background:rgba(0,0,0,0.15); padding:12px; border-radius:10px; margin-bottom:14px; border:1px solid var(--card-border);">
                            <div style="font-weight:700; font-size:12px; margin-bottom:8px; color:#818cf8;">Specify Split Breakdown:</div>
                            
                            <div style="margin-bottom:8px;">
                                <label style="font-size:11.5px; font-weight:700; display:block; margin-bottom:3px;">💵 Cash Portion (Debited to your wallet)</label>
                                <input type="number" step="0.001" min="0" max="${totalNum}" id="swal_split_cash" class="swal2-input" style="width:100%; margin:0; box-sizing:border-box; height:38px; font-size:14px;" value="0.000" oninput="recalcDeliverySplit(${totalNum})">
                            </div>

                            <div style="margin-bottom:8px;">
                                <label style="font-size:11.5px; font-weight:700; display:block; margin-bottom:3px; color:#10b981;">📱 BenefitPay QR Portion (Direct to Counter)</label>
                                <input type="number" step="0.001" min="0" max="${totalNum}" id="swal_split_qr" class="swal2-input" style="width:100%; margin:0; box-sizing:border-box; height:38px; font-size:14px;" value="${totalStr}" oninput="recalcDeliverySplit(${totalNum})">
                            </div>

                            <div style="margin-bottom:8px;">
                                <label style="font-size:11.5px; font-weight:700; display:block; margin-bottom:3px;">💳 Card Portion (Optional)</label>
                                <input type="number" step="0.001" min="0" max="${totalNum}" id="swal_split_card" class="swal2-input" style="width:100%; margin:0; box-sizing:border-box; height:38px; font-size:14px;" value="0.000" oninput="recalcDeliverySplit(${totalNum})">
                            </div>

                            <div id="swal_split_summary" style="font-size:12px; font-weight:700; margin-top:8px; padding-top:6px; border-top:1px dashed var(--card-border); display:flex; justify-content:space-between;">
                                <span>Split Total: <span id="swal_split_total_val">${totalStr}</span> / ${totalStr}</span>
                                <span id="swal_split_status" style="color:#10b981;">✅ Match</span>
                            </div>
                        </div>

                        <div>
                            <label style="font-weight:700; display:block; margin-bottom:4px; font-size:12px;">Delivery Notes (Optional)</label>
                            <input type="text" id="swal_deliv_note" class="swal2-input" style="width:100%; margin:0; box-sizing:border-box; height:38px; font-size:13px;" placeholder="e.g. Handed to security, left at door...">
                        </div>

                        <div style="margin-top:12px;">
                            <label style="font-weight:700; display:block; margin-bottom:4px; font-size:12px; color:var(--text-color);">📸 Payment Screen Photo (Optional)</label>
                            <div style="border:1.5px dashed var(--card-border); background:rgba(255,255,255,0.03); border-radius:10px; padding:10px; text-align:center;">
                                <input type="file" id="swal_pay_proof" accept="image/*" capture="environment" style="display:none;" onchange="previewSwalProof(this)">
                                <button type="button" onclick="document.getElementById('swal_pay_proof').click()" style="background:rgba(2,132,199,0.15); border:1px solid #0284c7; color:#38bdf8; padding:7px 14px; border-radius:8px; font-size:12px; font-weight:700; cursor:pointer;">
                                    📷 Take Photo / Upload Screenshot
                                </button>
                                <div id="swal_proof_name" style="font-size:11px; color:var(--text-muted); margin-top:4px;">Not mandatory — upload if needed</div>
                                <div id="swal_proof_preview_wrap" style="display:none; margin-top:8px;">
                                    <img id="swal_proof_img" src="" style="max-height:90px; border-radius:6px; border:1px solid var(--card-border); object-fit:contain;">
                                </div>
                            </div>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Confirm & Complete Delivery',
                preConfirm: () => {
                    const method = document.querySelector('input[name="swal_pay_method"]:checked').value;
                    const notes = document.getElementById('swal_deliv_note').value.trim();
                    const proofEl = document.getElementById('swal_pay_proof');
                    const proofFile = (proofEl && proofEl.files && proofEl.files.length > 0) ? proofEl.files[0] : null;
                    let cash = 0, qr = 0, card = 0;

                    if (method === 'cash') {
                        cash = totalNum;
                    } else if (method === 'qr_pay') {
                        qr = totalNum;
                    } else if (method === 'card') {
                        card = totalNum;
                    } else if (method === 'split') {
                        cash = parseFloat(document.getElementById('swal_split_cash').value) || 0;
                        qr = parseFloat(document.getElementById('swal_split_qr').value) || 0;
                        card = parseFloat(document.getElementById('swal_split_card').value) || 0;
                        const sum = Math.round((cash + qr + card) * 1000) / 1000;
                        if (Math.abs(sum - totalNum) > 0.005) {
                            Swal.showValidationMessage(`Split sum (${sum.toFixed(3)}) does not equal total (${totalStr})!`);
                            return false;
                        }
                    }

                    return {
                        collection_method: method,
                        cash_amount: cash,
                        qr_amount: qr,
                        card_amount: card,
                        notes: notes,
                        payment_proof: proofFile
                    };
                }
            }).then(result => {
                if (result.isConfirmed && result.value) {
                    submitDeliveryComplete(deliveryId, result.value);
                }
            });
        }

        function previewSwalProof(input) {
            const nameEl = document.getElementById('swal_proof_name');
            const wrapEl = document.getElementById('swal_proof_preview_wrap');
            const imgEl = document.getElementById('swal_proof_img');
            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (nameEl) nameEl.innerText = 'Selected: ' + file.name;
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (imgEl) imgEl.src = e.target.result;
                    if (wrapEl) wrapEl.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }

        function viewPaymentProof(url) {
            Swal.fire({
                title: 'Customer Payment Screenshot',
                imageUrl: url,
                imageAlt: 'Payment Screen Proof',
                imageWidth: 320,
                confirmButtonColor: '#0284c7',
                confirmButtonText: 'Close'
            });
        }

        function submitDeliveryComplete(deliveryId, payload) {
            const formData = new FormData();
            formData.append('notes', payload.notes || '');
            formData.append('collection_method', payload.collection_method || 'cash');
            formData.append('cash_amount', payload.cash_amount || 0);
            formData.append('qr_amount', payload.qr_amount || 0);
            formData.append('card_amount', payload.card_amount || 0);
            if (payload.payment_proof) {
                formData.append('payment_proof', payload.payment_proof);
            }

            fetch(basePath + '/api/deliver/' + deliveryId, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const earned = parseFloat(data.commission_earned || 0);
                    let infoMsg = '';
                    if (payload.collection_method === 'qr_pay') {
                        infoMsg = `<div style="font-size:12.5px; color:#10b981; margin-top:6px;">📱 Paid via BenefitPay QR (${parseFloat(data.qr_collected || 0).toFixed(3)} ${currencyCode})<br>Directly credited to Restaurant Counter!</div>`;
                    } else if (payload.collection_method === 'split') {
                        infoMsg = `<div style="font-size:12.5px; color:#818cf8; margin-top:6px;">⚖️ Split: Cash ${parseFloat(data.cash_collected || 0).toFixed(3)} + Digital ${parseFloat((data.qr_collected || 0) + (data.card_collected || 0)).toFixed(3)} ${currencyCode}<br>Only cash (${parseFloat(data.cash_collected || 0).toFixed(3)}) added to your wallet.</div>`;
                    } else if (payload.collection_method === 'card') {
                        infoMsg = `<div style="font-size:12.5px; color:#38bdf8; margin-top:6px;">💳 Paid by Card (${parseFloat(data.card_collected || 0).toFixed(3)} ${currencyCode})<br>No cash liability for driver.</div>`;
                    } else if (parseFloat(data.cash_collected || 0) > 0) {
                        infoMsg = `<div style="font-size:12.5px; color:#f59e0b; margin-top:6px;">💵 Cash Collected: ${parseFloat(data.cash_collected || 0).toFixed(3)} ${currencyCode}<br>Please hand over to the counter upon return.</div>`;
                    }

                    const commMsg = (commissionEnabled && earned > 0)
                        ? `<div style="margin-top:6px;">Commission earned: <strong>${earned.toFixed(window.PRICE_DECIMALS || 3)} ${currencyCode}</strong><br>Added to your earning wallet.</div>`
                        : '';

                    Swal.fire({
                        icon: 'success',
                        title: 'Delivered Successfully! 🎉',
                        html: `<div>${infoMsg}${commMsg}</div>`,
                        confirmButtonColor: '#10b981'
                    });
                    fetchDashboardData();
                } else {
                    Swal.fire('Error', data.error || 'Failed to mark delivered.', 'error');
                }
            })
            .catch(() => Swal.fire('Error', 'Network error. Try again.', 'error'));
        }

        function getTodayDateString() {
            const now = new Date();
            const y = now.getFullYear();
            const m = String(now.getMonth() + 1).padStart(2, '0');
            const d = String(now.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        }

        function initDateFilters() {
            const today = getTodayDateString();
            const hStart = document.getElementById('history-start-date');
            const hEnd = document.getElementById('history-end-date');
            const lStart = document.getElementById('ledger-start-date');
            const lEnd = document.getElementById('ledger-end-date');

            if (hStart && !hStart.value) hStart.value = today;
            if (hEnd && !hEnd.value) hEnd.value = today;
            if (lStart && !lStart.value) lStart.value = today;
            if (lEnd && !lEnd.value) lEnd.value = today;
        }

        function resetHistoryToToday() {
            const today = getTodayDateString();
            document.getElementById('history-start-date').value = today;
            document.getElementById('history-end-date').value = today;
            fetchHistory(true);
        }

        function resetLedgerToToday() {
            const today = getTodayDateString();
            document.getElementById('ledger-start-date').value = today;
            document.getElementById('ledger-end-date').value = today;
            fetchLedger(true);
        }

        function fetchHistory(showToast = false) {
            initDateFilters();
            const start = document.getElementById('history-start-date') ? document.getElementById('history-start-date').value : getTodayDateString();
            const end = document.getElementById('history-end-date') ? document.getElementById('history-end-date').value : getTodayDateString();

            const listEl = document.getElementById('history-list');
            listEl.innerHTML = '<div class="empty-state">Loading history...</div>';

            fetch(basePath + '/api/history?start=' + encodeURIComponent(start) + '&end=' + encodeURIComponent(end) + '&_=' + Date.now())
                .then(res => res.json())
                .then(data => {
                    if (showToast) {
                        Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: `Loaded (${data.deliveries ? data.deliveries.length : 0})`, showConfirmButton: false, timer: 1000 });
                    }
                    if (!data.success || !data.deliveries || data.deliveries.length === 0) {
                        listEl.innerHTML = '<div class="empty-state"><div class="empty-icon">📜</div><div>No deliveries completed for this date range.</div></div>';
                        return;
                    }

                    let html = '';
                    data.deliveries.forEach(d => {
                        const deliveredDate = d.delivered_at ? new Date(d.delivered_at.replace(' ', 'T')).toLocaleString([], {hour:'2-digit', minute:'2-digit', month:'short', day:'numeric'}) : '-';
                        let methodBadge = '';
                        if (d.payment_type === 'cod') {
                            if (d.collection_method === 'qr_pay') {
                                methodBadge = `<span class="ledger-badge" style="background:rgba(16,185,129,0.15); color:#10b981; border:1px solid rgba(16,185,129,0.3);">📱 BenefitPay QR: ${parseFloat(d.qr_collected_amount || d.cod_amount).toFixed(3)}</span>`;
                            } else if (d.collection_method === 'split') {
                                methodBadge = `<span class="ledger-badge" style="background:rgba(99,102,241,0.15); color:#818cf8; border:1px solid rgba(99,102,241,0.3);">⚖️ Split: ${parseFloat(d.cash_collected_amount).toFixed(3)} Cash + ${parseFloat(d.qr_collected_amount).toFixed(3)} QR</span>`;
                            } else if (d.collection_method === 'card') {
                                methodBadge = `<span class="ledger-badge" style="background:rgba(56,189,248,0.15); color:#38bdf8; border:1px solid rgba(56,189,248,0.3);">💳 Card: ${parseFloat(d.card_collected_amount || d.cod_amount).toFixed(3)}</span>`;
                            } else {
                                methodBadge = `<span class="ledger-badge ledger-cod">💵 COD Cash: ${parseFloat(d.cod_amount).toFixed(3)}</span>`;
                            }
                        } else {
                            methodBadge = `<span class="ledger-badge ledger-credit">✅ PREPAID</span>`;
                        }

                        const proofBtn = d.payment_proof ? `<button type="button" onclick="viewPaymentProof('/${escapeHtml(d.payment_proof)}')" style="background:rgba(2,132,199,0.15); border:1px solid #0284c7; color:#38bdf8; padding:3px 8px; border-radius:6px; font-size:11px; font-weight:700; cursor:pointer;">📷 Proof</button>` : '';

                        html += `
                            <div class="history-card">
                                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                                    <strong>Token #${escapeHtml(d.token_number || d.order_id)}</strong>
                                    <span style="font-size:12px; color:var(--text-muted);">${deliveredDate}</span>
                                </div>
                                <div style="font-size:13px; color:var(--text-color); margin-bottom:4px;">👤 ${escapeHtml(d.customer_name || 'Guest')} (${escapeHtml(d.customer_mobile)})</div>
                                <div style="display:flex; justify-content:space-between; align-items:center; font-size:12px; margin-top:8px; flex-wrap:wrap; gap:6px;">
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        ${methodBadge}
                                        ${proofBtn}
                                    </div>
                                    ${commissionEnabled ? `<span style="font-weight:700; color:var(--accent-green);">+${parseFloat(d.commission_amount).toFixed(3)} ${currencyCode} Comm.</span>` : ''}
                                </div>
                            </div>
                        `;
                    });
                    listEl.innerHTML = html;
                });
        }

        function fetchLedger(showToast = false) {
            initDateFilters();
            const start = document.getElementById('ledger-start-date') ? document.getElementById('ledger-start-date').value : getTodayDateString();
            const end = document.getElementById('ledger-end-date') ? document.getElementById('ledger-end-date').value : getTodayDateString();

            const listEl = document.getElementById('ledger-list');
            listEl.innerHTML = '<div class="empty-state">Loading ledger...</div>';

            fetch(basePath + '/api/ledger?start=' + encodeURIComponent(start) + '&end=' + encodeURIComponent(end) + '&_=' + Date.now())
                .then(res => res.json())
                .then(data => {
                    if (showToast) {
                        Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: `Loaded (${data.ledger ? data.ledger.length : 0})`, showConfirmButton: false, timer: 1000 });
                    }
                    if (!data.success || !data.ledger || data.ledger.length === 0) {
                        listEl.innerHTML = '<div class="empty-state"><div class="empty-icon">💰</div><div>No ledger transactions for this date range.</div></div>';
                        return;
                    }

                    let html = '';
                    data.ledger.forEach(l => {
                        const dateStr = l.created_at ? new Date(l.created_at.replace(' ', 'T')).toLocaleString([], {hour:'2-digit', minute:'2-digit', month:'short', day:'numeric'}) : '-';
                        let badgeClass = 'ledger-credit';
                        let typeTitle = 'Commission Credit';
                        let sign = '+';

                        if (l.entry_type === 'commission_credit') {
                            badgeClass = 'ledger-credit';
                            typeTitle = 'Commission Earned';
                            sign = '+';
                        } else if (l.entry_type === 'commission_payout') {
                            badgeClass = 'ledger-debit';
                            typeTitle = 'Payout / Withdrawn';
                            sign = '-';
                        } else if (l.entry_type === 'cod_collected') {
                            badgeClass = 'ledger-cod';
                            typeTitle = 'Cash In Hand (COD)';
                            sign = '+';
                        } else if (l.entry_type === 'cod_handover') {
                            badgeClass = 'ledger-debit';
                            typeTitle = 'Handed to Counter';
                            sign = '-';
                        }

                        html += `
                            <div class="history-card">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                    <span class="ledger-badge ${badgeClass}">${typeTitle}</span>
                                    <span style="font-size:15px; font-weight:800;">${sign} ${parseFloat(l.amount).toFixed(3)} ${currencyCode}</span>
                                </div>
                                <div style="font-size:12px; color:var(--text-muted); margin-bottom:2px;">${escapeHtml(l.notes || '')}</div>
                                <div style="font-size:11px; color:var(--text-muted);">${dateStr}</div>
                            </div>
                        `;
                    });
                    listEl.innerHTML = html;
                });
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Start Dashboard Polling every 5 seconds
        fetchDashboardData();
        pollTimer = setInterval(() => {
            if (currentTab === 'deliveries') {
                fetchDashboardData();
            }
        }, 5000);
    </script>
</body>
</html>

