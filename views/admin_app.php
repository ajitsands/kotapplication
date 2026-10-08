<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Admin App | <?= htmlspecialchars($settings['restaurant_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --bg-color: #0b0f19;
            --surface-1: #111827;
            --surface-2: #1f2937;
            --surface-glass: rgba(17, 24, 39, 0.75);
            --card-border: rgba(255, 255, 255, 0.08);
            --card-border-glow: rgba(99, 102, 241, 0.35);
            --text-main: #f9fafb;
            --text-muted: #9ca3af;
            --text-sub: #6b7280;
            --primary: #6366f1;
            --primary-grad: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%);
            --emerald-grad: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --amber-grad: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --blue-grad: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            --rose-grad: linear-gradient(135deg, #f43f5e 0%, #be123c 100%);
            --parrot-grad: linear-gradient(135deg, #84cc16 0%, #22c55e 50%, #15803d 100%);
            --nav-bg: rgba(17, 24, 39, 0.92);
        }

        body.light-theme {
            --bg-color: #f1f5f9;
            --surface-1: #ffffff;
            --surface-2: #f8fafc;
            --surface-glass: rgba(255, 255, 255, 0.85);
            --card-border: rgba(0, 0, 0, 0.08);
            --card-border-glow: rgba(99, 102, 241, 0.25);
            --text-main: #0f172a;
            --text-muted: #475569;
            --text-sub: #64748b;
            --nav-bg: rgba(255, 255, 255, 0.95);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* App Wrapper */
        .app-layout {
            max-width: 960px;
            width: 100%;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            padding-bottom: 85px; /* space for bottom nav */
        }

        /* Header */
        .app-header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: var(--nav-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--card-border);
            padding: 12px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-logo {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            object-fit: cover;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .header-logo-fallback {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--primary-grad);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 18px;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }

        .brand-text-col {
            display: flex;
            flex-direction: column;
        }

        .brand-title {
            font-size: 16px;
            font-weight: 800;
            letter-spacing: -0.3px;
            line-height: 1.2;
            color: var(--text-main);
        }

        .brand-badge-row {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 2px;
        }

        .admin-app-pill {
            font-size: 10px;
            font-weight: 800;
            background: var(--primary-grad);
            color: #fff;
            padding: 1px 7px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .live-indicator {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 600;
            color: #10b981;
        }

        .pulse-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulseDot 1.8s infinite;
        }

        @keyframes pulseDot {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-icon {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            background: var(--surface-2);
            border: 1px solid var(--card-border);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.2s ease;
        }

        .btn-icon:hover {
            transform: scale(1.05);
            border-color: var(--primary);
        }

        .btn-icon:active {
            transform: scale(0.95);
        }

        /* Sync Spin animation */
        .spinning {
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            100% { transform: rotate(360deg); }
        }

        /* Main Content Container */
        .app-body {
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        /* View sections */
        .view-section {
            display: none;
            flex-direction: column;
            gap: 16px;
            animation: fadeIn 0.3s ease;
        }

        .view-section.active {
            display: flex;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Cards & Components */
        .metric-card {
            background: var(--surface-1);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 18px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.06);
            transition: all 0.25s ease;
        }

        .metric-card:hover {
            border-color: var(--card-border-glow);
            box-shadow: 0 14px 30px rgba(99, 102, 241, 0.12);
        }

        /* Hero Revenue Card */
        .hero-revenue-card {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 60%, #4338ca 100%);
            color: #ffffff;
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 22px;
            padding: 22px;
            box-shadow: 0 14px 35px rgba(49, 46, 129, 0.35);
            position: relative;
            overflow: hidden;
        }

        .hero-revenue-card::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -20%;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(217, 70, 239, 0.35) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-top-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .hero-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #c7d2fe;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .hero-date-tag {
            font-size: 11px;
            font-weight: 700;
            background: rgba(255,255,255,0.15);
            padding: 3px 9px;
            border-radius: 8px;
            backdrop-filter: blur(8px);
        }

        .hero-amount {
            font-size: 34px;
            font-weight: 900;
            letter-spacing: -1px;
            line-height: 1.1;
            margin-bottom: 16px;
            text-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }

        .hero-chips-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }

        .hero-chip {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 12px;
            padding: 8px 10px;
            text-align: center;
            backdrop-filter: blur(8px);
        }

        .hero-chip-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #cbd5e1;
            letter-spacing: 0.5px;
        }

        .hero-chip-val {
            font-size: 13px;
            font-weight: 800;
            margin-top: 2px;
            color: #ffffff;
            white-space: nowrap;
        }

        /* Operations Live Grid */
        .operations-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        @media (min-width: 640px) {
            .operations-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .op-card {
            background: var(--surface-1);
            border: 1px solid var(--card-border);
            border-radius: 18px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 6px 16px rgba(0,0,0,0.04);
        }

        .op-card:hover {
            transform: translateY(-3px);
            border-color: var(--card-border-glow);
            box-shadow: 0 10px 22px rgba(99, 102, 241, 0.15);
        }

        .op-card:active {
            transform: translateY(0);
        }

        .op-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .op-icon-box {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .op-badge {
            font-size: 10px;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 6px;
            text-transform: uppercase;
        }

        .badge-live {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .badge-alert {
            background: rgba(245, 158, 11, 0.15);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.3);
            animation: pulseAlert 1.5s infinite;
        }

        @keyframes pulseAlert {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        .op-card-val {
            font-size: 26px;
            font-weight: 900;
            letter-spacing: -0.5px;
            line-height: 1;
            color: var(--text-main);
        }

        .op-card-label {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--text-muted);
            margin-top: 5px;
        }

        .op-card-action {
            font-size: 11px;
            font-weight: 700;
            color: var(--primary);
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Section Titles */
        .section-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .section-title {
            font-size: 17px;
            font-weight: 800;
            letter-spacing: -0.3px;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Cashier Closing Pending Banner */
        .closing-alert-card {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(217, 119, 6, 0.08));
            border: 1.5px solid rgba(245, 158, 11, 0.4);
            border-radius: 18px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.15);
            animation: alertPulseBorder 2s infinite alternate;
        }

        @keyframes alertPulseBorder {
            0% { border-color: rgba(245, 158, 11, 0.4); }
            100% { border-color: rgba(245, 158, 11, 0.85); box-shadow: 0 8px 25px rgba(245, 158, 11, 0.25); }
        }

        .closing-alert-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .closing-cashier-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cashier-avatar {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--amber-grad);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 800;
        }

        .closing-title {
            font-size: 14.5px;
            font-weight: 800;
            color: var(--text-main);
        }

        .closing-time {
            font-size: 11.5px;
            color: var(--text-muted);
            margin-top: 1px;
        }

        .closing-amount-pill {
            background: rgba(245, 158, 11, 0.2);
            border: 1px solid rgba(245, 158, 11, 0.4);
            padding: 6px 12px;
            border-radius: 10px;
            text-align: right;
        }

        .closing-amount-pill .label {
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            color: #d97706;
        }

        .closing-amount-pill .val {
            font-size: 15px;
            font-weight: 900;
            color: #f59e0b;
        }

        /* Verification Comparison Grid */
        .verification-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            background: var(--surface-1);
            border: 1px solid var(--card-border);
            border-radius: 14px;
            padding: 10px;
        }

        .verif-col {
            text-align: center;
            padding: 6px;
            border-radius: 10px;
        }

        .verif-col.highlight {
            background: var(--surface-2);
        }

        .verif-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .verif-expected {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-sub);
            margin-top: 2px;
        }

        .verif-collected {
            font-size: 14px;
            font-weight: 900;
            color: var(--text-main);
            margin-top: 1px;
        }

        .closing-notes-box {
            font-size: 12px;
            color: var(--text-muted);
            background: var(--surface-1);
            border-radius: 10px;
            padding: 8px 12px;
            border-left: 3px solid #f59e0b;
        }

        .closing-actions-row {
            display: flex;
            gap: 10px;
        }

        .btn-confirm-closing {
            flex: 1;
            background: var(--emerald-grad);
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 12px;
            font-family: inherit;
            font-size: 13.5px;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
            transition: all 0.2s ease;
        }

        .btn-confirm-closing:hover {
            transform: scale(1.02);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.6);
        }

        .btn-reject-closing {
            background: var(--surface-2);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 12px 16px;
            border-radius: 12px;
            font-family: inherit;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .btn-reject-closing:hover {
            background: rgba(239, 68, 68, 0.1);
            border-color: #ef4444;
        }

        /* Filter Pills */
        .filter-pills-row {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 4px;
            scrollbar-width: none;
        }

        .filter-pills-row::-webkit-scrollbar {
            display: none;
        }

        .filter-pill {
            background: var(--surface-1);
            border: 1px solid var(--card-border);
            color: var(--text-muted);
            padding: 8px 16px;
            border-radius: 12px;
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s ease;
        }

        .filter-pill.active {
            background: var(--primary-grad);
            color: #ffffff;
            border-color: transparent;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);
        }

        /* Date Custom Box */
        .custom-date-box {
            display: none;
            grid-template-columns: 1fr 1fr auto;
            gap: 8px;
            align-items: center;
            background: var(--surface-1);
            border: 1px solid var(--card-border);
            padding: 10px;
            border-radius: 14px;
        }

        .date-input {
            background: var(--surface-2);
            border: 1px solid var(--card-border);
            color: var(--text-main);
            padding: 8px 12px;
            border-radius: 10px;
            font-family: inherit;
            font-size: 12.5px;
            outline: none;
            width: 100%;
        }

        .btn-apply-date {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 8px 14px;
            border-radius: 10px;
            font-family: inherit;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
        }

        /* Financial Breakdown Grid */
        .collection-breakdown-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        @media (min-width: 640px) {
            .collection-breakdown-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .breakdown-card {
            background: var(--surface-1);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }

        .breakdown-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #fff;
            flex-shrink: 0;
        }

        .breakdown-info {
            flex: 1;
            min-width: 0;
        }

        .breakdown-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.5px;
        }

        .breakdown-val {
            font-size: 16px;
            font-weight: 900;
            color: var(--text-main);
            margin-top: 2px;
            white-space: nowrap;
        }

        /* Visual Distribution Bar */
        .distribution-container {
            background: var(--surface-1);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .dist-bar {
            height: 14px;
            border-radius: 8px;
            background: var(--surface-2);
            overflow: hidden;
            display: flex;
            width: 100%;
        }

        .dist-seg {
            height: 100%;
            transition: width 0.4s ease;
        }

        .dist-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            font-size: 11.5px;
            color: var(--text-muted);
            font-weight: 600;
        }

        .dist-legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .legend-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        /* Lists & Tables */
        .data-card-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .list-item-card {
            background: var(--surface-1);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .list-item-card:hover {
            border-color: var(--card-border-glow);
            transform: translateX(3px);
        }

        .list-item-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .list-item-badge {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: var(--primary-grad);
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 15px;
            line-height: 1;
            flex-shrink: 0;
        }

        .list-item-badge small {
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            opacity: 0.85;
            margin-top: 1px;
        }

        .list-item-title {
            font-size: 14px;
            font-weight: 800;
            color: var(--text-main);
        }

        .list-item-meta {
            font-size: 11.5px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .list-item-right {
            text-align: right;
        }

        .list-item-price {
            font-size: 15px;
            font-weight: 900;
            color: #10b981;
        }

        .list-item-status {
            font-size: 10px;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 6px;
            margin-top: 3px;
            display: inline-block;
        }

        /* Bottom Mobile Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--nav-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-top: 1px solid var(--card-border);
            display: flex;
            justify-content: space-around;
            padding: 8px 12px 14px;
            z-index: 200;
            max-width: 960px;
            margin: 0 auto;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 11px;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .nav-item.active {
            color: var(--primary);
            background: rgba(99, 102, 241, 0.12);
        }

        .nav-icon {
            font-size: 20px;
            line-height: 1;
        }

        .nav-badge {
            position: absolute;
            top: 2px;
            right: 8px;
            background: #ef4444;
            color: #fff;
            font-size: 9px;
            font-weight: 800;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--surface-1);
            animation: pulseAlert 1.5s infinite;
        }

        /* Modal Drawer */
        .app-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            z-index: 1000;
            align-items: flex-end;
            justify-content: center;
        }

        @media (min-width: 640px) {
            .app-modal {
                align-items: center;
            }
        }

        .modal-sheet {
            background: var(--surface-1);
            border: 1px solid var(--card-border);
            width: 100%;
            max-width: 500px;
            max-height: 85vh;
            border-radius: 24px 24px 0 0;
            padding: 24px 20px 30px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            overflow-y: auto;
            animation: slideUpModal 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @media (min-width: 640px) {
            .modal-sheet {
                border-radius: 24px;
            }
        }

        @keyframes slideUpModal {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--text-main);
        }

        .btn-close-modal {
            background: var(--surface-2);
            border: none;
            color: var(--text-muted);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Login Screen if unauthenticated */
        .login-screen {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card-app {
            background: var(--surface-1);
            border: 1px solid var(--card-border);
            border-radius: 26px;
            padding: 36px 26px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 20px 50px rgba(0,0,0,0.25);
            text-align: center;
            backdrop-filter: blur(16px);
        }

        .login-form-group {
            margin-bottom: 16px;
            text-align: left;
        }

        .login-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 6px;
            display: block;
            letter-spacing: 0.5px;
        }

        .login-input {
            width: 100%;
            background: var(--surface-2);
            border: 1px solid var(--card-border);
            color: var(--text-main);
            padding: 14px 16px;
            border-radius: 14px;
            font-family: inherit;
            font-size: 15px;
            outline: none;
            transition: all 0.2s;
        }

        .login-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        .btn-login-app {
            width: 100%;
            background: var(--primary-grad);
            color: #fff;
            border: none;
            padding: 14px;
            border-radius: 14px;
            font-family: inherit;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
            margin-top: 8px;
            transition: all 0.2s;
        }

        .btn-login-app:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.6);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .empty-icon {
            font-size: 40px;
            opacity: 0.7;
        }
    </style>
</head>
<body class="light-theme">

<?php if (!$isLoggedIn): ?>
    <!-- ==================== ADMIN APP LOGIN VIEW ==================== -->
    <div class="login-screen">
        <div class="login-card-app">
            <div style="margin-bottom: 24px;">
                <?php if (!empty($settings['logo_path'])): ?>
                    <img src="<?= htmlspecialchars($settings['logo_path']) ?>" alt="Logo" style="max-width: 70px; height: auto; border-radius: 16px; margin-bottom: 12px; box-shadow: 0 6px 16px rgba(0,0,0,0.15);">
                <?php else: ?>
                    <div style="width: 60px; height: 60px; border-radius: 16px; background: var(--primary-grad); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 24px; margin: 0 auto 12px; box-shadow: 0 6px 18px rgba(99, 102, 241, 0.4);">A</div>
                <?php endif; ?>
                <h1 style="font-size: 22px; font-weight: 900; letter-spacing: -0.5px;"><?= htmlspecialchars($settings['restaurant_name']) ?></h1>
                <div style="display: flex; align-items: center; justify-content: center; gap: 6px; margin-top: 4px;">
                    <span class="admin-app-pill">👑 Admin Portal App</span>
                </div>
                <p style="font-size: 13px; color: var(--text-muted); margin-top: 6px;">Sign in with Administrator credentials</p>
            </div>

            <form id="admin-login-form" onsubmit="handleAdminLogin(event)">
                <div class="login-form-group">
                    <label class="login-label">Admin Username</label>
                    <input type="text" id="login-username" class="login-input" placeholder="e.g. admin" required autocomplete="off">
                </div>
                <div class="login-form-group">
                    <label class="login-label">Password</label>
                    <input type="password" id="login-password" class="login-input" placeholder="••••••••" required>
                </div>
                <button type="submit" id="btn-login-submit" class="btn-login-app">Sign In to Admin App</button>
            </form>

            <div style="margin-top: 24px; font-size: 11.5px; color: var(--text-sub);">
                Powered by <a href="/login" style="color: #6366f1; text-decoration: none; font-weight: 700;">SaNDS Lab</a>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- ==================== MAIN ADMIN APP INTERFACE ==================== -->
    <div class="app-layout">
        <!-- Header -->
        <header class="app-header">
            <div class="header-brand">
                <?php if (!empty($settings['logo_path'])): ?>
                    <img src="<?= htmlspecialchars($settings['logo_path']) ?>" class="header-logo" alt="Logo">
                <?php else: ?>
                    <div class="header-logo-fallback">A</div>
                <?php endif; ?>
                <div class="brand-text-col">
                    <span class="brand-title"><?= htmlspecialchars($settings['restaurant_name']) ?></span>
                    <div class="brand-badge-row">
                        <span class="admin-app-pill">Admin App</span>
                        <span class="live-indicator">
                            <span class="pulse-dot"></span>
                            <span id="sync-countdown">Live (10s)</span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn-icon" id="btn-refresh-data" onclick="triggerManualSync()" title="Refresh Data">
                    🔄
                </button>
                <button class="btn-icon" onclick="toggleAppTheme()" title="Toggle Dark/Light Theme">
                    🌓
                </button>
                <button class="btn-icon" onclick="handleLogout()" title="Logout" style="color: #ef4444;">
                    🚪
                </button>
            </div>
        </header>

        <!-- Body -->
        <main class="app-body">
            
            <!-- ==================== TAB 1: DASHBOARD / LIVE RADAR ==================== -->
            <section id="view-dashboard" class="view-section active">
                <!-- Hero Revenue Card -->
                <div class="hero-revenue-card">
                    <div class="hero-top-row">
                        <span class="hero-label">
                            <span>⚡</span> Today's Net Collection
                        </span>
                        <span class="hero-date-tag" id="dash-today-date"><?= date('d M Y') ?></span>
                    </div>
                    <div class="hero-amount" id="dash-grand-total">
                        <?= htmlspecialchars($settings['currency_code']) ?> 0.000
                    </div>
                    <div class="hero-chips-grid">
                        <div class="hero-chip">
                            <div class="hero-chip-title">💵 Cash</div>
                            <div class="hero-chip-val" id="dash-cash-val">0.000</div>
                        </div>
                        <div class="hero-chip">
                            <div class="hero-chip-title">💳 Card</div>
                            <div class="hero-chip-val" id="dash-card-val">0.000</div>
                        </div>
                        <div class="hero-chip">
                            <div class="hero-chip-title">📱 QR/UPI</div>
                            <div class="hero-chip-val" id="dash-qr-val">0.000</div>
                        </div>
                        <div class="hero-chip">
                            <div class="hero-chip-title">🛵 Online</div>
                            <div class="hero-chip-val" id="dash-online-val">0.000</div>
                        </div>
                    </div>
                </div>

                <!-- Urgent Pending Cashier Closings Alert Container -->
                <div id="dash-closings-container" style="display: none;"></div>

                <!-- Live Operations Grid -->
                <div>
                    <div class="section-header-row">
                        <h2 class="section-title">📡 Live Operations Radar</h2>
                        <span style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Real-Time Status</span>
                    </div>
                    
                    <div class="operations-grid">
                        <!-- Tables Engaged -->
                        <div class="op-card" onclick="switchNavTab('orders'); filterOrdersSubTab('tables');">
                            <div class="op-card-header">
                                <div class="op-icon-box" style="background: var(--primary-grad);">🍽️</div>
                                <span class="op-badge badge-live">Live</span>
                            </div>
                            <div>
                                <div class="op-card-val" id="card-tables-count">0</div>
                                <div class="op-card-label">Engaged Tables</div>
                            </div>
                            <div class="op-card-action">View Tables &rarr;</div>
                        </div>

                        <!-- Online Orders -->
                        <div class="op-card" onclick="switchNavTab('orders'); filterOrdersSubTab('online');">
                            <div class="op-card-header">
                                <div class="op-icon-box" style="background: var(--blue-grad);">🛵</div>
                                <span class="op-badge badge-live" id="badge-online-status">Active</span>
                            </div>
                            <div>
                                <div class="op-card-val" id="card-online-count">0</div>
                                <div class="op-card-label">Online Orders</div>
                            </div>
                            <div class="op-card-action">View Online &rarr;</div>
                        </div>

                        <!-- Takeaways Queue -->
                        <div class="op-card" onclick="switchNavTab('orders'); filterOrdersSubTab('takeaways');">
                            <div class="op-card-header">
                                <div class="op-icon-box" style="background: var(--amber-grad);">🛍️</div>
                                <span class="op-badge badge-live">Queue</span>
                            </div>
                            <div>
                                <div class="op-card-val" id="card-takeaways-count">0</div>
                                <div class="op-card-label">Takeaways</div>
                            </div>
                            <div class="op-card-action">View Queue &rarr;</div>
                        </div>

                        <!-- Cashier Closings Pending -->
                        <div class="op-card" onclick="switchNavTab('closings');">
                            <div class="op-card-header">
                                <div class="op-icon-box" style="background: var(--rose-grad);">🔒</div>
                                <span class="op-badge badge-alert" id="badge-closings-status">0 Shift</span>
                            </div>
                            <div>
                                <div class="op-card-val" id="card-closings-count">0</div>
                                <div class="op-card-label">Cashier Closings</div>
                            </div>
                            <div class="op-card-action" style="color: #f43f5e;">Verify Shift &rarr;</div>
                        </div>
                    </div>
                </div>

                <!-- Quick Today Metrics -->
                <div class="collection-breakdown-grid">
                    <div class="breakdown-card">
                        <div class="breakdown-icon" style="background: var(--emerald-grad);">🧾</div>
                        <div class="breakdown-info">
                            <div class="breakdown-label">Paid Bills</div>
                            <div class="breakdown-val" id="dash-bills-count">0</div>
                        </div>
                    </div>
                    <div class="breakdown-card">
                        <div class="breakdown-icon" style="background: var(--blue-grad);">📊</div>
                        <div class="breakdown-info">
                            <div class="breakdown-label">Tax Collected</div>
                            <div class="breakdown-val" id="dash-tax-val">0.000</div>
                        </div>
                    </div>
                    <div class="breakdown-card">
                        <div class="breakdown-icon" style="background: var(--amber-grad);">🏷️</div>
                        <div class="breakdown-info">
                            <div class="breakdown-label">Discounts</div>
                            <div class="breakdown-val" id="dash-discount-val">0.000</div>
                        </div>
                    </div>
                    <div class="breakdown-card">
                        <div class="breakdown-icon" style="background: var(--rose-grad);">↩️</div>
                        <div class="breakdown-info">
                            <div class="breakdown-label">Refunds</div>
                            <div class="breakdown-val" id="dash-refund-val">0.000</div>
                        </div>
                    </div>
                </div>
            </section>


            <!-- ==================== TAB 2: COLLECTION SUMMARY ==================== -->
            <section id="view-collections" class="view-section">
                <div class="section-header-row">
                    <h2 class="section-title">💵 Collection Summary</h2>
                </div>

                <!-- Date Range Filter Pills -->
                <div class="filter-pills-row">
                    <button class="filter-pill active" onclick="setCollectionDateFilter('today', this)">Today</button>
                    <button class="filter-pill" onclick="setCollectionDateFilter('yesterday', this)">Yesterday</button>
                    <button class="filter-pill" onclick="setCollectionDateFilter('week', this)">Last 7 Days</button>
                    <button class="filter-pill" onclick="setCollectionDateFilter('month', this)">This Month</button>
                    <button class="filter-pill" onclick="toggleCustomDateBox(this)">📅 Custom</button>
                </div>

                <!-- Custom Date Range Form -->
                <div id="custom-date-container" class="custom-date-box">
                    <input type="date" id="col-start-date" class="date-input" value="<?= date('Y-m-d') ?>">
                    <input type="date" id="col-end-date" class="date-input" value="<?= date('Y-m-d') ?>">
                    <button class="btn-apply-date" onclick="applyCustomDateFilter()">Apply</button>
                </div>

                <!-- Grand Summary Card -->
                <div class="metric-card" style="border-left: 4px solid var(--primary);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                        <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--text-muted);">Net Total Revenue</span>
                        <span style="font-size: 11px; font-weight: 700; color: var(--primary);" id="col-range-label">Today</span>
                    </div>
                    <div style="font-size: 32px; font-weight: 900; color: #10b981;" id="col-grand-total">
                        0.000
                    </div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;" id="col-meta-text">
                        Gross: 0.000 • Refunds: 0.000 • Tax: 0.000
                    </div>
                </div>

                <!-- Detailed Breakdown Grid -->
                <div class="collection-breakdown-grid">
                    <div class="breakdown-card">
                        <div class="breakdown-icon" style="background: var(--parrot-grad);">💵</div>
                        <div class="breakdown-info">
                            <div class="breakdown-label">Cash Counter</div>
                            <div class="breakdown-val" id="col-cash-val">0.000</div>
                        </div>
                    </div>
                    <div class="breakdown-card">
                        <div class="breakdown-icon" style="background: var(--blue-grad);">💳</div>
                        <div class="breakdown-info">
                            <div class="breakdown-label">Card / POS</div>
                            <div class="breakdown-val" id="col-card-val">0.000</div>
                        </div>
                    </div>
                    <div class="breakdown-card">
                        <div class="breakdown-icon" style="background: var(--primary-grad);">📱</div>
                        <div class="breakdown-info">
                            <div class="breakdown-label">QR Pay / UPI</div>
                            <div class="breakdown-val" id="col-qr-val">0.000</div>
                        </div>
                    </div>
                    <div class="breakdown-card">
                        <div class="breakdown-icon" style="background: var(--amber-grad);">🛵</div>
                        <div class="breakdown-info">
                            <div class="breakdown-label">Online Orders</div>
                            <div class="breakdown-val" id="col-online-val">0.000</div>
                        </div>
                    </div>
                </div>

                <!-- Payment Distribution Visualization -->
                <div class="distribution-container">
                    <span style="font-size: 12.5px; font-weight: 800; color: var(--text-main);">Payment Method Ratio</span>
                    <div class="dist-bar" id="col-dist-bar">
                        <div class="dist-seg" id="seg-cash" style="width: 25%; background: #22c55e;"></div>
                        <div class="dist-seg" id="seg-card" style="width: 25%; background: #3b82f6;"></div>
                        <div class="dist-seg" id="seg-qr" style="width: 25%; background: #8b5cf6;"></div>
                        <div class="dist-seg" id="seg-online" style="width: 25%; background: #f59e0b;"></div>
                    </div>
                    <div class="dist-legend">
                        <div class="dist-legend-item"><span class="legend-dot" style="background: #22c55e;"></span> Cash (<span id="pct-cash">0%</span>)</div>
                        <div class="dist-legend-item"><span class="legend-dot" style="background: #3b82f6;"></span> Card (<span id="pct-card">0%</span>)</div>
                        <div class="dist-legend-item"><span class="legend-dot" style="background: #8b5cf6;"></span> QR (<span id="pct-qr">0%</span>)</div>
                        <div class="dist-legend-item"><span class="legend-dot" style="background: #f59e0b;"></span> Online (<span id="pct-online">0%</span>)</div>
                    </div>
                </div>

                <!-- Cashier-Wise Breakdown Section -->
                <div>
                    <h3 style="font-size: 15px; font-weight: 800; margin-bottom: 10px;">👤 Cashier-Wise Collections</h3>
                    <div id="col-cashiers-list" class="data-card-list">
                        <div class="empty-state">Loading cashiers...</div>
                    </div>
                </div>
            </section>


            <!-- ==================== TAB 3: CASHIER CLOSINGS ==================== -->
            <section id="view-closings" class="view-section">
                <div class="section-header-row">
                    <h2 class="section-title">🔒 Cashier Shift Closures</h2>
                    <span class="op-badge badge-alert" id="closings-tab-badge">0 Pending</span>
                </div>

                <p style="font-size: 12.5px; color: var(--text-muted); line-height: 1.4;">
                    Review cashier closing requests. Once confirmed & approved by Admin, the shift is finalized and the cashier can log in again to start a new job.
                </p>

                <!-- Pending Approvals -->
                <div id="pending-closings-list" style="display: flex; flex-direction: column; gap: 14px;">
                    <div class="empty-state">
                        <div class="empty-icon">✅</div>
                        <div style="font-size: 15px; font-weight: 800; color: var(--text-main);">No Pending Shift Closures</div>
                        <div style="font-size: 12px;">All cashier shifts are verified and up to date.</div>
                    </div>
                </div>

                <!-- Closed Shift History -->
                <div style="margin-top: 14px;">
                    <h3 style="font-size: 15px; font-weight: 800; margin-bottom: 10px;">📜 Recent Approved Shifts</h3>
                    <div id="closed-history-list" class="data-card-list">
                        <div class="empty-state">Loading history...</div>
                    </div>
                </div>
            </section>


            <!-- ==================== TAB 4: LIVE ORDERS & TABLES ==================== -->
            <section id="view-orders" class="view-section">
                <div class="section-header-row">
                    <h2 class="section-title">🍽️ Live Orders & Tables</h2>
                </div>

                <!-- Sub Tab Switcher -->
                <div class="filter-pills-row">
                    <button class="filter-pill active" id="pill-tables" onclick="filterOrdersSubTab('tables')">🍽️ Engaged Tables (<span id="count-pill-tables">0</span>)</button>
                    <button class="filter-pill" id="pill-online" onclick="filterOrdersSubTab('online')">🛵 Online Orders (<span id="count-pill-online">0</span>)</button>
                    <button class="filter-pill" id="pill-takeaways" onclick="filterOrdersSubTab('takeaways')">🛍️ Takeaways (<span id="count-pill-takeaways">0</span>)</button>
                </div>

                <!-- Sub-Section: Engaged Tables -->
                <div id="orders-sub-tables" class="data-card-list">
                    <div class="empty-state">
                        <div class="empty-icon">🍽️</div>
                        <div style="font-size: 14px; font-weight: 700;">No Tables Currently Engaged</div>
                    </div>
                </div>

                <!-- Sub-Section: Online Orders -->
                <div id="orders-sub-online" class="data-card-list" style="display: none;">
                    <div class="empty-state">
                        <div class="empty-icon">🛵</div>
                        <div style="font-size: 14px; font-weight: 700;">No Active Online Orders</div>
                    </div>
                </div>

                <!-- Sub-Section: Takeaways Queue -->
                <div id="orders-sub-takeaways" class="data-card-list" style="display: none;">
                    <div class="empty-state">
                        <div class="empty-icon">🛍️</div>
                        <div style="font-size: 14px; font-weight: 700;">No Active Takeaways</div>
                    </div>
                </div>
            </section>

        </main>

        <!-- ==================== BOTTOM MOBILE NAVIGATION ==================== -->
        <nav class="bottom-nav">
            <div class="nav-item active" onclick="switchNavTab('dashboard')">
                <span class="nav-icon">📊</span>
                <span>Dashboard</span>
            </div>
            <div class="nav-item" onclick="switchNavTab('collections')">
                <span class="nav-icon">💵</span>
                <span>Collection</span>
            </div>
            <div class="nav-item" onclick="switchNavTab('closings')">
                <span class="nav-icon">🔒</span>
                <span>Shift Close</span>
                <span class="nav-badge" id="nav-badge-closings" style="display: none;">0</span>
            </div>
            <div class="nav-item" onclick="switchNavTab('orders')">
                <span class="nav-icon">🍽️</span>
                <span>Live Orders</span>
            </div>
        </nav>
    </div>

    <!-- ==================== ORDER DETAILS MODAL ==================== -->
    <div id="order-details-modal" class="app-modal">
        <div class="modal-sheet">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title" id="modal-order-title">Order Details</h3>
                    <p style="font-size: 12px; color: var(--text-muted);" id="modal-order-subtitle"></p>
                </div>
                <button class="btn-close-modal" onclick="closeOrderModal()">&times;</button>
            </div>
            <div id="modal-order-items" style="display: flex; flex-direction: column; gap: 8px;">
                Loading items...
            </div>
            <div style="background: var(--surface-2); border-radius: 14px; padding: 12px; display: flex; justify-content: space-between; align-items: center; font-size: 16px; font-weight: 900;">
                <span>Total Amount:</span>
                <span id="modal-order-grand-total" style="color: #10b981;">0.000</span>
            </div>
        </div>
    </div>

<?php endif; ?>

<script>
    const CURRENCY = '<?= htmlspecialchars($settings['currency_code']) ?>';
    let appSyncTimer = null;
    let syncCountdownSec = 10;
    let currentActiveTab = 'dashboard';
    let previousPendingCount = 0;

    // Theme Management
    function initAppTheme() {
        if (localStorage.getItem('admin_app_theme') === 'dark') {
            document.body.classList.remove('light-theme');
        } else {
            document.body.classList.add('light-theme');
        }
    }
    initAppTheme();

    function toggleAppTheme() {
        if (document.body.classList.contains('light-theme')) {
            document.body.classList.remove('light-theme');
            localStorage.setItem('admin_app_theme', 'dark');
        } else {
            document.body.classList.add('light-theme');
            localStorage.setItem('admin_app_theme', 'light');
        }
    }

    // Number formatting helper
    function fmt(val) {
        const num = parseFloat(val) || 0;
        return num.toFixed(3);
    }

    // Navigation Switcher
    function switchNavTab(tabName) {
        currentActiveTab = tabName;
        document.querySelectorAll('.view-section').forEach(sec => sec.classList.remove('active'));
        const targetSec = document.getElementById('view-' + tabName);
        if (targetSec) targetSec.classList.add('active');

        document.querySelectorAll('.nav-item').forEach((item, idx) => {
            item.classList.remove('active');
            if ((tabName === 'dashboard' && idx === 0) ||
                (tabName === 'collections' && idx === 1) ||
                (tabName === 'closings' && idx === 2) ||
                (tabName === 'orders' && idx === 3)) {
                item.classList.add('active');
            }
        });

        if (tabName === 'collections') {
            fetchCollectionSummary();
        }
    }

    // Orders Sub-tab switcher
    function filterOrdersSubTab(sub) {
        ['tables', 'online', 'takeaways'].forEach(s => {
            const el = document.getElementById('orders-sub-' + s);
            const pill = document.getElementById('pill-' + s);
            if (el) el.style.display = (s === sub) ? 'flex' : 'none';
            if (pill) pill.classList.toggle('active', s === sub);
        });
    }

    // Admin Login API Handler
    function handleAdminLogin(e) {
        e.preventDefault();
        const u = document.getElementById('login-username').value.trim();
        const p = document.getElementById('login-password').value.trim();
        const btn = document.getElementById('btn-login-submit');

        btn.innerText = 'Signing In...';
        btn.disabled = true;

        fetch('/api/admin-app/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username: u, password: p })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Welcome Admin',
                    text: 'Logged in successfully.',
                    timer: 1000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: data.error || 'Invalid credentials'
                });
            }
        })
        .catch(err => {
            Swal.fire({ icon: 'error', title: 'Network Error', text: err.message });
        })
        .finally(() => {
            btn.innerText = 'Sign In to Admin App';
            btn.disabled = false;
        });
    }

    function handleLogout() {
        Swal.fire({
            title: 'Logout Admin App?',
            text: 'Are you sure you want to end your administrator session?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#6366f1',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Logout'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '/logout';
            }
        });
    }

    // Audio Notification Chime
    function playAlertChime() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
            osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.15); // A5
            gain.gain.setValueAtTime(0.3, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.4);
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.4);
        } catch(e) {}
    }

    // Core Live Data Fetcher
    function fetchDashboardLive() {
        const refreshBtn = document.getElementById('btn-refresh-data');
        if (refreshBtn) refreshBtn.classList.add('spinning');

        fetch('/api/admin-app/dashboard')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderDashboardData(data);
                }
            })
            .catch(err => {
                console.error('Failed to sync live admin dashboard:', err);
            })
            .finally(() => {
                if (refreshBtn) refreshBtn.classList.remove('spinning');
            });
    }

    function renderDashboardData(data) {
        const ops = data.operations;
        const col = data.collection.summary;
        const stats = data.collection.stats;
        const closures = data.closures;

        // 1. Hero Revenue
        document.getElementById('dash-grand-total').innerText = CURRENCY + ' ' + fmt(col.actual_total || col.grand_total);
        document.getElementById('dash-cash-val').innerText = fmt(col.cash_total);
        document.getElementById('dash-card-val').innerText = fmt(col.card_total);
        document.getElementById('dash-qr-val').innerText = fmt(col.qr_total);
        document.getElementById('dash-online-val').innerText = fmt(col.online_total);

        // Quick today stats
        document.getElementById('dash-bills-count').innerText = stats.total_bills || 0;
        document.getElementById('dash-tax-val').innerText = fmt(stats.total_tax);
        document.getElementById('dash-discount-val').innerText = fmt(stats.total_discount);
        document.getElementById('dash-refund-val').innerText = fmt(col.refund_total);

        // 2. Operational Counters
        document.getElementById('card-tables-count').innerText = ops.tables_engaged_count + ' / ' + ops.total_tables_count;
        document.getElementById('count-pill-tables').innerText = ops.tables_engaged_count;

        document.getElementById('card-online-count').innerText = ops.online_orders_count;
        document.getElementById('count-pill-online').innerText = ops.online_orders_count;

        document.getElementById('card-takeaways-count').innerText = ops.takeaways_count;
        document.getElementById('count-pill-takeaways').innerText = ops.takeaways_count;

        // 3. Closures Badge & Urgent Banner
        const pendingCount = closures.pending_count;
        document.getElementById('card-closings-count').innerText = pendingCount;
        document.getElementById('badge-closings-status').innerText = pendingCount > 0 ? (pendingCount + ' Pending') : 'All Closed';
        document.getElementById('closings-tab-badge').innerText = pendingCount + ' Pending';

        const navBadge = document.getElementById('nav-badge-closings');
        if (pendingCount > 0) {
            navBadge.style.display = 'flex';
            navBadge.innerText = pendingCount;

            // Trigger notification sound if new pending closure arrived
            if (pendingCount > previousPendingCount && previousPendingCount >= 0) {
                playAlertChime();
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'warning',
                    title: '🔔 New Cashier Closing Request!',
                    text: 'Cashier shift is awaiting your confirmation.',
                    showConfirmButton: false,
                    timer: 4000
                });
            }
        } else {
            navBadge.style.display = 'none';
        }
        previousPendingCount = pendingCount;

        // Render dashboard urgent banner & full closings tab
        renderClosingsView(closures);

        // Render live tables and orders list
        renderLiveOrders(ops);
    }

    // Render Closings View & Urgent Banner
    function renderClosingsView(closures) {
        const bannerContainer = document.getElementById('dash-closings-container');
        const pendingList = document.getElementById('pending-closings-list');
        const historyList = document.getElementById('closed-history-list');

        if (!closures.pending || closures.pending.length === 0) {
            bannerContainer.style.display = 'none';
            bannerContainer.innerHTML = '';
            pendingList.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">✅</div>
                    <div style="font-size: 15px; font-weight: 800; color: var(--text-main);">No Pending Shift Closures</div>
                    <div style="font-size: 12px; color: var(--text-muted);">All cashier shifts are verified and up to date.</div>
                </div>
            `;
        } else {
            bannerContainer.style.display = 'block';
            let bannerHtml = '';
            let fullHtml = '';

            closures.pending.forEach(c => {
                const variance = parseFloat(c.variance) || 0;
                let varianceBadge = '';
                if (variance === 0) {
                    varianceBadge = '<span style="color: #10b981; font-weight: 800;">✅ Exact Match</span>';
                } else if (variance > 0) {
                    varianceBadge = `<span style="color: #3b82f6; font-weight: 800;">✨ Excess +${fmt(variance)}</span>`;
                } else {
                    varianceBadge = `<span style="color: #ef4444; font-weight: 800;">⚠️ Shortage ${fmt(variance)}</span>`;
                }

                const cardHtml = `
                    <div class="closing-alert-card">
                        <div class="closing-alert-top">
                            <div class="closing-cashier-info">
                                <div class="cashier-avatar">${(c.cashier_name || 'C').charAt(0).toUpperCase()}</div>
                                <div>
                                    <div class="closing-title">${c.cashier_name || 'Cashier'}</div>
                                    <div class="closing-time">Shift Request: ${c.close_requested_at || c.opened_at}</div>
                                </div>
                            </div>
                            <div class="closing-amount-pill">
                                <div class="label">Collected Total</div>
                                <div class="val">${CURRENCY} ${fmt(c.collected_total)}</div>
                            </div>
                        </div>

                        <!-- Verification Grid -->
                        <div class="verification-grid">
                            <div class="verif-col">
                                <div class="verif-label">💵 Cash</div>
                                <div class="verif-expected">Exp: ${fmt(c.cash_total)}</div>
                                <div class="verif-collected">Col: ${fmt(c.collected_cash)}</div>
                            </div>
                            <div class="verif-col">
                                <div class="verif-label">💳 Card</div>
                                <div class="verif-expected">Exp: ${fmt(c.card_total)}</div>
                                <div class="verif-collected">Col: ${fmt(c.collected_card)}</div>
                            </div>
                            <div class="verif-col highlight">
                                <div class="verif-label">📱 QR/UPI</div>
                                <div class="verif-expected">Exp: ${fmt(c.qr_total)}</div>
                                <div class="verif-collected">Col: ${fmt(c.collected_qr)}</div>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px;">
                            <span>System Exp: <strong>${CURRENCY} ${fmt(c.system_total)}</strong></span>
                            <span>Variance: ${varianceBadge}</span>
                        </div>

                        ${c.cashier_notes ? `<div class="closing-notes-box"><strong>Cashier Note:</strong> ${c.cashier_notes}</div>` : ''}

                        <div class="closing-actions-row">
                            <button class="btn-confirm-closing" onclick="confirmShiftClosure(${c.id}, '${c.cashier_name}', '${fmt(c.collected_total)}')">
                                ✅ Verify & Confirm Closing
                            </button>
                            <button class="btn-reject-closing" onclick="rejectShiftClosure(${c.id}, '${c.cashier_name}')">
                                ❌ Reject
                            </button>
                        </div>
                    </div>
                `;

                bannerHtml += cardHtml;
                fullHtml += cardHtml;
            });

            bannerContainer.innerHTML = bannerHtml;
            pendingList.innerHTML = fullHtml;
        }

        // Render Closed History
        if (closures.history && closures.history.length > 0) {
            let histHtml = '';
            closures.history.forEach(h => {
                histHtml += `
                    <div class="list-item-card" style="cursor: default;">
                        <div class="list-item-left">
                            <div class="list-item-badge" style="background: var(--surface-2); color: var(--text-main); border: 1px solid var(--card-border);">
                                🔒
                            </div>
                            <div>
                                <div class="list-item-title">${h.cashier_name || 'Cashier'}</div>
                                <div class="list-item-meta">Closed: ${h.closed_at || '-'} • Approved by: ${h.approved_by_name || 'Admin'}</div>
                            </div>
                        </div>
                        <div class="list-item-right">
                            <div class="list-item-price">${CURRENCY} ${fmt(h.collected_total || h.system_total)}</div>
                            <span class="list-item-status" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">Finalized</span>
                        </div>
                    </div>
                `;
            });
            historyList.innerHTML = histHtml;
        } else {
            historyList.innerHTML = '<div class="empty-state">No closed shift history available.</div>';
        }
    }

    // Confirm & Approve Shift Closure
    function confirmShiftClosure(sessionId, cashierName, totalAmt) {
        Swal.fire({
            title: 'Verify & Confirm Shift?',
            html: `Confirm closure for <strong>${cashierName}</strong> with collected total of <strong>${CURRENCY} ${totalAmt}</strong>?<br><br><small style="color:#6b7280;">This will close the shift and enable ${cashierName} to log in for a new shift.</small>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '✅ Yes, Confirm & Close'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('/api/admin-app/closures/approve/' + sessionId, { method: 'POST' })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Shift Verified & Closed!',
                                text: data.message || 'Cashier can now start a new shift.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            fetchDashboardLive();
                        } else {
                            Swal.fire({ icon: 'error', title: 'Action Failed', text: data.error });
                        }
                    })
                    .catch(err => Swal.fire({ icon: 'error', title: 'Network Error', text: err.message }));
            }
        });
    }

    // Reject Shift Closure
    function rejectShiftClosure(sessionId, cashierName) {
        Swal.fire({
            title: 'Reject Closing Request?',
            html: `Reject closing request for <strong>${cashierName}</strong>? The cashier will be prompted to recount or adjust bills.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '❌ Reject & Re-open Shift'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('/api/admin-app/closures/reject/' + sessionId, { method: 'POST' })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'info',
                                title: 'Shift Re-opened',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            fetchDashboardLive();
                        } else {
                            Swal.fire({ icon: 'error', title: 'Action Failed', text: data.error });
                        }
                    })
                    .catch(err => Swal.fire({ icon: 'error', title: 'Network Error', text: err.message }));
            }
        });
    }

    // Render Live Orders & Tables
    function renderLiveOrders(ops) {
        // 1. Engaged Tables
        const tablesContainer = document.getElementById('orders-sub-tables');
        if (ops.engaged_tables && ops.engaged_tables.length > 0) {
            let html = '';
            ops.engaged_tables.forEach(t => {
                html += `
                    <div class="list-item-card" onclick="viewOrderDetails(${t.order_id}, 'Table ${t.table_number}')">
                        <div class="list-item-left">
                            <div class="list-item-badge">
                                <span>${t.table_number}</span>
                                <small>Table</small>
                            </div>
                            <div>
                                <div class="list-item-title">Table #${t.table_number} ${t.customer_name ? '• ' + t.customer_name : ''}</div>
                                <div class="list-item-meta">Waiter: ${t.waiter_name || 'Self'} • Started: ${t.order_time || '-'}</div>
                            </div>
                        </div>
                        <div class="list-item-right">
                            <div class="list-item-price">${CURRENCY} ${fmt(t.grand_total)}</div>
                            <span class="list-item-status" style="background: rgba(99, 102, 241, 0.15); color: #6366f1;">Dining Active</span>
                        </div>
                    </div>
                `;
            });
            tablesContainer.innerHTML = html;
        } else {
            tablesContainer.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">🍽️</div>
                    <div style="font-size: 14px; font-weight: 700;">No Tables Currently Engaged</div>
                    <div style="font-size: 12px;">All dining tables are currently free.</div>
                </div>
            `;
        }

        // 2. Online Orders
        const onlineContainer = document.getElementById('orders-sub-online');
        if (ops.online_orders && ops.online_orders.length > 0) {
            let html = '';
            ops.online_orders.forEach(o => {
                html += `
                    <div class="list-item-card" onclick="viewOrderDetails(${o.id}, 'Online Order ${o.token_number || o.id}')">
                        <div class="list-item-left">
                            <div class="list-item-badge" style="background: var(--blue-grad);">
                                <span>${o.platform_name ? o.platform_name.substring(0, 2).toUpperCase() : 'OL'}</span>
                                <small>${o.token_number || 'OL'}</small>
                            </div>
                            <div>
                                <div class="list-item-title">${o.platform_name || 'Online'} #${o.platform_order_number || o.token_number}</div>
                                <div class="list-item-meta">Items: ${o.item_count || 0} • Created: ${o.created_at || '-'}</div>
                            </div>
                        </div>
                        <div class="list-item-right">
                            <div class="list-item-price">${CURRENCY} ${fmt(o.total_amount || 0)}</div>
                            <span class="list-item-status" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;">${o.status}</span>
                        </div>
                    </div>
                `;
            });
            onlineContainer.innerHTML = html;
        } else {
            onlineContainer.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">🛵</div>
                    <div style="font-size: 14px; font-weight: 700;">No Active Online Orders</div>
                </div>
            `;
        }

        // 3. Takeaways Queue
        const takeawayContainer = document.getElementById('orders-sub-takeaways');
        const allTakeaways = [...(ops.takeaway_queue || []), ...(ops.active_takeaways || [])];
        if (allTakeaways.length > 0) {
            let html = '';
            allTakeaways.forEach(t => {
                const isReady = t.status === 'closed';
                html += `
                    <div class="list-item-card" onclick="viewOrderDetails(${t.id}, 'Takeaway Token ${t.token_number || t.id}')">
                        <div class="list-item-left">
                            <div class="list-item-badge" style="background: ${isReady ? 'var(--emerald-grad)' : 'var(--amber-grad)'};">
                                <span>${t.token_number || 'T'}</span>
                                <small>Token</small>
                            </div>
                            <div>
                                <div class="list-item-title">Token #${t.token_number || t.id} ${t.customer_name ? '• ' + t.customer_name : ''}</div>
                                <div class="list-item-meta">${t.customer_mobile || 'Takeaway'} • Created: ${t.created_at || '-'}</div>
                            </div>
                        </div>
                        <div class="list-item-right">
                            <span class="list-item-status" style="background: ${isReady ? 'rgba(16, 185, 129, 0.15)' : 'rgba(245, 158, 11, 0.15)'}; color: ${isReady ? '#10b981' : '#f59e0b'};">
                                ${isReady ? 'Ready for Pickup' : 'Preparing in KOT'}
                            </span>
                        </div>
                    </div>
                `;
            });
            takeawayContainer.innerHTML = html;
        } else {
            takeawayContainer.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">🛍️</div>
                    <div style="font-size: 14px; font-weight: 700;">No Active Takeaways</div>
                </div>
            `;
        }
    }

    // View Order Details Modal
    function viewOrderDetails(orderId, title) {
        document.getElementById('modal-order-title').innerText = title;
        document.getElementById('modal-order-subtitle').innerText = 'Order ID #' + orderId;
        document.getElementById('modal-order-items').innerHTML = 'Loading order items...';
        document.getElementById('order-details-modal').style.display = 'flex';

        fetch('/api/admin-app/order/' + orderId)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.order) {
                    const order = data.order;
                    let itemsHtml = '';
                    let subtotal = 0;

                    if (order.items && order.items.length > 0) {
                        order.items.forEach(item => {
                            const itemTotal = parseFloat(item.subtotal_price) || (item.price * item.total_quantity);
                            subtotal += itemTotal;
                            itemsHtml += `
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid var(--card-border);">
                                    <div>
                                        <div style="font-size: 13.5px; font-weight: 700; color: var(--text-main);">${item.product_name || item.name}</div>
                                        <div style="font-size: 11px; color: var(--text-muted);">${item.total_quantity} × ${CURRENCY} ${fmt(item.price)} ${item.notes ? '• ' + item.notes : ''}</div>
                                    </div>
                                    <div style="font-size: 13.5px; font-weight: 800;">${CURRENCY} ${fmt(itemTotal)}</div>
                                </div>
                            `;
                        });
                    } else {
                        itemsHtml = '<div style="color: var(--text-muted); padding: 10px 0;">No items found in this order.</div>';
                    }

                    document.getElementById('modal-order-items').innerHTML = itemsHtml;
                    document.getElementById('modal-order-grand-total').innerText = CURRENCY + ' ' + fmt(order.grand_total || subtotal);
                } else {
                    document.getElementById('modal-order-items').innerHTML = '<div style="color:#ef4444;">Failed to load order details.</div>';
                }
            })
            .catch(err => {
                document.getElementById('modal-order-items').innerHTML = '<div style="color:#ef4444;">Network error loading details.</div>';
            });
    }

    function closeOrderModal() {
        document.getElementById('order-details-modal').style.display = 'none';
    }

    // Helper to format date in local YYYY-MM-DD
    function formatLocalDate(d) {
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Collection Summary Tab Logic
    function setCollectionDateFilter(range, btn) {
        document.querySelectorAll('.filter-pills-row .filter-pill').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        document.getElementById('custom-date-container').style.display = 'none';

        const today = new Date();
        let start = '';
        let end = '';

        if (range === 'today') {
            start = end = formatLocalDate(today);
            document.getElementById('col-range-label').innerText = 'Today';
        } else if (range === 'yesterday') {
            const y = new Date(today);
            y.setDate(y.getDate() - 1);
            start = end = formatLocalDate(y);
            document.getElementById('col-range-label').innerText = 'Yesterday';
        } else if (range === 'week') {
            const w = new Date(today);
            w.setDate(w.getDate() - 7);
            start = formatLocalDate(w);
            end = formatLocalDate(today);
            document.getElementById('col-range-label').innerText = 'Last 7 Days';
        } else if (range === 'month') {
            const m = new Date(today.getFullYear(), today.getMonth(), 1);
            start = formatLocalDate(m);
            end = formatLocalDate(today);
            document.getElementById('col-range-label').innerText = 'This Month';
        }

        fetchCollectionSummary(start, end);
    }

    function toggleCustomDateBox(btn) {
        document.querySelectorAll('.filter-pills-row .filter-pill').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        const box = document.getElementById('custom-date-container');
        box.style.display = box.style.display === 'grid' ? 'none' : 'grid';
    }

    function applyCustomDateFilter() {
        const s = document.getElementById('col-start-date').value;
        const e = document.getElementById('col-end-date').value;
        document.getElementById('col-range-label').innerText = s + ' to ' + e;
        fetchCollectionSummary(s, e);
    }

    function fetchCollectionSummary(startDate, endDate) {
        const s = startDate || document.getElementById('col-start-date').value || '<?= date('Y-m-d') ?>';
        const e = endDate || document.getElementById('col-end-date').value || '<?= date('Y-m-d') ?>';

        fetch(`/api/admin-app/collection?start_date=${s}&end_date=${e}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderCollectionSummary(data);
                }
            })
            .catch(err => console.error('Failed to load collection summary:', err));
    }

    function renderCollectionSummary(data) {
        const sum = data.summary;
        const stats = data.stats;
        const total = parseFloat(sum.actual_total || sum.grand_total) || 0;

        document.getElementById('col-grand-total').innerText = CURRENCY + ' ' + fmt(total);
        document.getElementById('col-meta-text').innerText = `Gross: ${fmt(sum.grand_total)} • Refunds: ${fmt(sum.refund_total)} • Tax: ${fmt(stats.total_tax)}`;

        document.getElementById('col-cash-val').innerText = fmt(sum.cash_total);
        document.getElementById('col-card-val').innerText = fmt(sum.card_total);
        document.getElementById('col-qr-val').innerText = fmt(sum.qr_total);
        document.getElementById('col-online-val').innerText = fmt(sum.online_total);

        // Calculate distribution percentages
        const cash = parseFloat(sum.cash_total) || 0;
        const card = parseFloat(sum.card_total) || 0;
        const qr = parseFloat(sum.qr_total) || 0;
        const online = parseFloat(sum.online_total) || 0;
        const gross = (cash + card + qr + online) || 1;

        const pCash = ((cash / gross) * 100).toFixed(1);
        const pCard = ((card / gross) * 100).toFixed(1);
        const pQr = ((qr / gross) * 100).toFixed(1);
        const pOnline = ((online / gross) * 100).toFixed(1);

        document.getElementById('seg-cash').style.width = pCash + '%';
        document.getElementById('seg-card').style.width = pCard + '%';
        document.getElementById('seg-qr').style.width = pQr + '%';
        document.getElementById('seg-online').style.width = pOnline + '%';

        document.getElementById('pct-cash').innerText = pCash + '%';
        document.getElementById('pct-card').innerText = pCard + '%';
        document.getElementById('pct-qr').innerText = pQr + '%';
        document.getElementById('pct-online').innerText = pOnline + '%';

        // Render Cashiers List & Active Shift Sessions
        const cashiersContainer = document.getElementById('col-cashiers-list');
        let html = '';

        // 1. Show Active Cashier Shifts (Live in Progress)
        if (data.active_sessions && data.active_sessions.length > 0) {
            data.active_sessions.forEach(s => {
                const isRequested = s.status === 'close_requested';
                html += `
                    <div class="list-item-card" style="cursor: default; border: 1.5px solid ${isRequested ? 'rgba(245, 158, 11, 0.4)' : 'rgba(16, 185, 129, 0.4)'}; background: ${isRequested ? 'rgba(245, 158, 11, 0.05)' : 'rgba(16, 185, 129, 0.05)'};">
                        <div class="list-item-left">
                            <div class="list-item-badge" style="background: ${isRequested ? 'var(--amber-grad)' : 'var(--emerald-grad)'}; color: #ffffff;">
                                ${isRequested ? '⏳' : '🟢'}
                            </div>
                            <div>
                                <div class="list-item-title" style="display: flex; align-items: center; gap: 6px;">
                                    <span>${s.cashier_name || 'Cashier'}</span>
                                    <span style="font-size: 9.5px; padding: 2px 6px; border-radius: 6px; font-weight: 800; background: ${isRequested ? '#f59e0b' : '#10b981'}; color: #fff; text-transform: uppercase;">
                                        ${isRequested ? 'Close Requested' : 'Live Shift'}
                                    </span>
                                </div>
                                <div class="list-item-meta">Shift Started: ${s.opened_at || '-'} • Cash: ${fmt(s.cash_total)} • Card: ${fmt(s.card_total)} • QR: ${fmt(s.qr_total)}</div>
                            </div>
                        </div>
                        <div class="list-item-right">
                            <div class="list-item-price" style="color: ${isRequested ? '#f59e0b' : '#10b981'};
">${CURRENCY} ${fmt(s.system_total || s.collected_total)}</div>
                        </div>
                    </div>
                `;
            });
        }

        // 2. Show Date Period Cashier Breakdown
        if (data.cashiers && data.cashiers.length > 0) {
            data.cashiers.forEach(c => {
                html += `
                    <div class="list-item-card" style="cursor: default;">
                        <div class="list-item-left">
                            <div class="list-item-badge" style="background: var(--surface-2); color: var(--text-main); border: 1px solid var(--card-border);">
                                👤
                            </div>
                            <div>
                                <div class="list-item-title">${c.cashier_name || 'Cashier'}</div>
                                <div class="list-item-meta">Bills: ${c.total_bills || 0} • Cash: ${fmt(c.cash_total)} • Card: ${fmt(c.card_total)} • QR: ${fmt(c.qr_total)}</div>
                            </div>
                        </div>
                        <div class="list-item-right">
                            <div class="list-item-price">${CURRENCY} ${fmt(c.grand_total)}</div>
                        </div>
                    </div>
                `;
            });
        }

        if (html === '') {
            cashiersContainer.innerHTML = '<div class="empty-state">No cashier collections found in selected period.</div>';
        } else {
            cashiersContainer.innerHTML = html;
        }
    }

    // Manual Refresh
    function triggerManualSync() {
        syncCountdownSec = 10;
        fetchDashboardLive();
    }

    // Polling interval (every 10 seconds)
    <?php if ($isLoggedIn): ?>
    fetchDashboardLive();
    setInterval(() => {
        syncCountdownSec--;
        const countdownEl = document.getElementById('sync-countdown');
        if (countdownEl) countdownEl.innerText = `Live (${syncCountdownSec}s)`;

        if (syncCountdownSec <= 0) {
            syncCountdownSec = 10;
            fetchDashboardLive();
        }
    }, 1000);
    <?php endif; ?>
</script>

</body>
</html>
