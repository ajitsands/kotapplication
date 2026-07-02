<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu | Table <?= htmlspecialchars($tableNumber) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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

        body.light-theme .search-input {
            background: rgba(0, 0, 0, 0.03);
            color: #1f2937;
            border-color: rgba(0, 0, 0, 0.08);
        }

        body.light-theme .search-input:focus {
            background: rgba(0, 0, 0, 0.05);
            border-color: #a855f7;
        }

        body.light-theme .cat-chip {
            background: rgba(0, 0, 0, 0.02);
            border-color: rgba(0, 0, 0, 0.08);
            color: #4b5563;
        }

        body.light-theme .cat-chip.active {
            background: var(--primary-grad);
            color: #ffffff;
            border-color: transparent;
            box-shadow: 0 4px 12px rgba(168, 85, 247, 0.3);
        }

        body.light-theme .cart-bar {
            background: rgba(255, 255, 255, 0.95);
            border-color: rgba(0, 0, 0, 0.08);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        body.light-theme .cart-count {
            color: #6b7280;
        }

        body.light-theme .modal-content {
            background: #ffffff;
            border-top-color: rgba(0, 0, 0, 0.08);
            box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.15);
        }

        body.light-theme .btn-close-modal {
            background: rgba(0, 0, 0, 0.03);
            border-color: rgba(0, 0, 0, 0.08);
            color: #1f2937;
        }

        body.light-theme .cart-item {
            border-bottom-color: rgba(0, 0, 0, 0.05);
        }

        body.light-theme .item-notes-input {
            background: rgba(0, 0, 0, 0.02);
            border-color: rgba(0, 0, 0, 0.08);
            color: #1f2937;
        }

        body.light-theme .success-screen {
            background: #f3f4f6;
        }

        body.light-theme .success-icon {
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.2);
        }

        /* Active Order Floating Status Bar */
        .active-status-bar {
            position: fixed;
            top: 75px;
            left: 50%;
            transform: translateX(-50%);
            width: calc(100% - 40px);
            max-width: 600px;
            background: rgba(245, 158, 11, 0.15);
            border: 1px solid rgba(245, 158, 11, 0.3);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 99;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .active-status-bar.served-status {
            background: rgba(16, 185, 129, 0.15);
            border-color: rgba(16, 185, 129, 0.3);
        }

        .active-status-bar.deleted-status {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.3);
        }

        .pulse-dot-orange {
            width: 10px;
            height: 10px;
            background: var(--accent-orange);
            border-radius: 50%;
            animation: pulse-orange 1.5s infinite;
            margin-right: 10px;
            flex-shrink: 0;
            display: inline-block;
        }

        .pulse-dot-green {
            width: 10px;
            height: 10px;
            background: var(--accent-green);
            border-radius: 50%;
            animation: pulse-green 1.5s infinite;
            margin-right: 10px;
            flex-shrink: 0;
            display: inline-block;
        }

        .pulse-dot-red {
            width: 10px;
            height: 10px;
            background: var(--accent-red);
            border-radius: 50%;
            animation: pulse-red 1.5s infinite;
            margin-right: 10px;
            flex-shrink: 0;
            display: inline-block;
        }

        @keyframes pulse-orange {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(245, 158, 11, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
        }

        @keyframes pulse-green {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        @keyframes pulse-red {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        .btn-status-view {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-color);
            padding: 6px 14px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-status-view:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Light theme adjustments */
        body.light-theme .active-status-bar {
            background: rgba(245, 158, 11, 0.1);
            border-color: rgba(245, 158, 11, 0.2);
            color: #1f2937;
        }

        body.light-theme .active-status-bar.served-status {
            background: rgba(16, 185, 129, 0.1);
            border-color: rgba(16, 185, 129, 0.2);
        }

        body.light-theme .active-status-bar.deleted-status {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.2);
        }

        body.light-theme .btn-status-view {
            background: rgba(0, 0, 0, 0.05);
            border-color: rgba(0, 0, 0, 0.1);
            color: #1f2937;
        }

        body.light-theme .table-badge {
            background: rgba(0, 0, 0, 0.03);
            border-color: rgba(0, 0, 0, 0.08);
        }

        body.light-theme .cart-control {
            background: rgba(0, 0, 0, 0.02);
            border-color: rgba(0, 0, 0, 0.08);
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
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.08) 0px, transparent 40%),
                radial-gradient(at 100% 0%, rgba(168, 85, 247, 0.08) 0px, transparent 40%);
            min-height: 100vh;
            padding-bottom: 90px; /* Space for sticky cart footer */
        }

        header {
            background: rgba(11, 15, 25, 0.7);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--card-border);
            padding: 15px 20px;
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
            gap: 10px;
        }

        .header-logo img {
            width: 35px;
            height: 35px;
            border-radius: 8px;
            object-fit: cover;
        }

        .header-title {
            font-size: 18px;
            font-weight: 800;
            background: var(--primary-grad);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .table-badge {
            font-size: 13px;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--card-border);
            padding: 4px 10px;
            border-radius: 20px;
        }

        .hero-banner {
            padding: 30px 20px;
            text-align: center;
        }

        .hero-title {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }

        .hero-desc {
            font-size: 14px;
            color: var(--text-muted);
        }

        /* Search Bar */
        .search-container {
            padding: 0 20px;
            margin-bottom: 25px;
        }

        .search-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--card-border);
            padding: 14px 18px;
            border-radius: 16px;
            font-family: inherit;
            color: var(--text-color);
            font-size: 15px;
            outline: none;
            transition: all 0.3s;
        }

        .search-input:focus {
            border-color: #a855f7;
            background: rgba(255, 255, 255, 0.07);
        }

        /* Category Scroll Tabs */
        .categories-scroll {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding: 0 20px 15px;
            scrollbar-width: none; /* Firefox */
        }

        .categories-scroll::-webkit-scrollbar {
            display: none; /* Chrome/Safari */
        }

        .cat-chip {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--card-border);
            color: var(--text-muted);
            padding: 10px 18px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.3s;
        }

        .cat-chip.active {
            background: var(--primary-grad);
            border-color: transparent;
            color: white;
            box-shadow: 0 4px 12px rgba(168, 85, 247, 0.3);
        }

        /* Product Cards Grid */
        .products-section {
            padding: 0 20px;
            margin-top: 10px;
        }

        .category-group {
            margin-bottom: 35px;
        }

        .category-heading {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .category-heading::after {
            content: '';
            flex-grow: 1;
            height: 1px;
            background: var(--card-border);
        }

        .products-grid {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .product-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 15px;
            display: flex;
            gap: 15px;
            align-items: center;
            backdrop-filter: blur(10px);
            transition: transform 0.2s;
        }

        .product-card:active {
            transform: scale(0.98);
        }

        .product-img {
            width: 80px;
            height: 80px;
            border-radius: 14px;
            object-fit: cover;
            background: rgba(255, 255, 255, 0.05);
            flex-shrink: 0;
        }

        .product-details {
            flex-grow: 1;
        }

        .product-name {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .product-desc {
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.4;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .product-price {
            font-size: 15px;
            font-weight: 700;
            color: var(--accent-green);
        }

        /* Cart controls button */
        .cart-control {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 4px;
        }

        .btn-qty {
            width: 28px;
            height: 28px;
            background: var(--primary-grad);
            border: none;
            color: white;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qty-num {
            font-size: 14px;
            font-weight: 700;
            min-width: 20px;
            text-align: center;
        }

        .btn-add {
            background: var(--primary-grad);
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 10px;
            font-family: inherit;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(168, 85, 247, 0.2);
        }

        /* Cart Drawer Sticky Bar */
        .cart-bar {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            background: rgba(17, 24, 39, 0.9);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 16px 20px;
            border-radius: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            z-index: 99;
            transform: translateY(150px);
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .cart-bar.visible {
            transform: translateY(0);
        }

        .cart-info {
            display: flex;
            flex-direction: column;
        }

        .cart-count {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
        }

        .cart-total {
            font-size: 20px;
            font-weight: 800;
            color: var(--accent-green);
        }

        .btn-checkout {
            background: var(--primary-grad);
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(168, 85, 247, 0.4);
        }

        /* Order Review Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(11, 15, 25, 0.9);
            backdrop-filter: blur(10px);
            z-index: 1000;
            align-items: flex-end; /* Slides up from bottom on mobile */
        }

        .modal-content {
            background: #111827;
            border-top: 1px solid var(--card-border);
            width: 100%;
            max-height: 85vh;
            border-top-left-radius: 28px;
            border-top-right-radius: 28px;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 -10px 40px rgba(0,0,0,0.5);
            animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 800;
        }

        .btn-close-modal {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--card-border);
            color: var(--text-color);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }

        .cart-items-list {
            overflow-y: auto;
            flex-grow: 1;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.03);
            padding-bottom: 12px;
        }

        .cart-item-name {
            font-size: 15px;
            font-weight: 600;
        }

        .cart-item-price {
            font-size: 13px;
            color: var(--accent-green);
            margin-top: 2px;
            font-weight: 600;
        }

        .item-notes-input {
            width: 100%;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--card-border);
            border-radius: 8px;
            padding: 8px;
            color: var(--text-color);
            font-family: inherit;
            font-size: 12px;
            margin-top: 6px;
            outline: none;
        }

        .item-notes-input:focus {
            border-color: #a855f7;
        }

        .success-screen {
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px 20px;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #0b0f19;
            z-index: 1100;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .success-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(16, 185, 129, 0.1);
            color: var(--accent-green);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin-bottom: 25px;
            border: 2px solid var(--accent-green);
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.2);
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            <?php if (!empty($settings['logo_path'])): ?>
                <img src="/<?= ltrim($settings['logo_path'], '/') ?>" alt="Logo">
            <?php endif; ?>
            <span class="header-title"><?= htmlspecialchars($settings['restaurant_name']) ?></span>
        </div>
        <div class="table-badge" style="display: flex; align-items: center; gap: 8px;">
            <button onclick="toggleTheme()" style="background: none; border: none; color: var(--text-color); cursor: pointer; font-size: 14px; display: inline-flex; align-items: center; padding: 2px;">🌓</button>
            <span>Table <?= htmlspecialchars($tableNumber) ?></span>
        </div>
    </header>

    <!-- Active Order Floating Status Bar -->
    <div class="active-status-bar" id="active-order-status-bar" style="display:none;" onclick="openStatusModal()">
        <div style="display:flex; align-items:center; min-width:0; flex-grow:1; margin-right:10px;">
            <span class="pulse-dot-orange" id="status-pulse-dot"></span>
            <span id="status-text-label" style="font-weight:600; font-size:13px; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;">Preparing your order...</span>
        </div>
        <button class="btn-status-view">View Details</button>
    </div>

    <div class="hero-banner">
        <h1 class="hero-title">Delicious Dining</h1>
        <p class="hero-desc">Select items and place order directly to kitchen</p>
    </div>

    <!-- Search -->
    <div class="search-container">
        <input type="text" class="search-input" id="search-box" placeholder="Search menu..." oninput="filterMenu()">
    </div>

    <!-- Horizontal Categories -->
    <div class="categories-scroll">
        <button class="cat-chip active" id="chip-all" onclick="selectCategory('all')">All Menu</button>
        <?php foreach ($categories as $cat): ?>
            <button class="cat-chip" id="chip-<?= $cat['id'] ?>" onclick="selectCategory(<?= $cat['id'] ?>)">
                <?= htmlspecialchars($cat['name']) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Products Lists (Loaded Dynamically via AJAX) -->
    <div class="products-section">
        <div style="text-align:center; padding:40px; color:var(--text-muted); font-weight:600;">Loading delicious menus...</div>
    </div>

    <!-- Footer -->
    <div class="login-footer" style="padding: 14px 20px 50px; text-align: center; font-size: 12px; color: var(--text-muted); border-top: 1px solid var(--card-border); margin-top: 20px;">
        Powered By <a href="javascript:void(0)" onclick="openSandsModal()" style="color: #818cf8; text-decoration: none; font-weight: 600;">SaNDS Lab</a>. All rights reserved to <?= htmlspecialchars($settings['restaurant_name']) ?>
    </div>

    <!-- SaNDS Lab Popup Modal -->
    <div id="sands-modal" class="modal-sands" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(11, 15, 25, 0.85); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); z-index: 2000; align-items: center; justify-content: center;">
        <div class="modal-sands-content" style="background: #ffffff; border: 1px solid rgba(0, 0, 0, 0.08); padding: 35px 25px; border-radius: 24px; text-align: center; max-width: 340px; width: 90%; box-shadow: 0 20px 50px rgba(0,0,0,0.2); position: relative; color: #1f2937;">
            <button onclick="closeSandsModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; color: #6b7280; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
            <div style="margin-bottom: 20px;">
                <img src="/logos/SaNDSLab-LogoNewUpdated.png" alt="SaNDS Lab Logo" style="max-width: 170px; height: auto; display: block; margin: 0 auto;">
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

    <!-- Sticky Bottom Cart Bar -->
    <div class="cart-bar" id="cart-drawer-bar">
        <div class="cart-info">
            <span class="cart-count" id="cart-qty-label">0 Items</span>
            <span class="cart-total" id="cart-total-label">0.000 BHD</span>
        </div>
        <button class="btn-checkout" onclick="openReviewModal()">View Order</button>
    </div>

    <!-- Review Order Modal -->
    <div class="modal" id="review-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Review Table Order</h3>
                <button onclick="closeReviewModal()" class="btn-close-modal">×</button>
            </div>
            
            <div class="cart-items-list" id="modal-items-container">
                <!-- Loaded dynamically -->
            </div>

            <div style="border-top:1px solid var(--card-border); padding-top:15px; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center;">
                <span style="font-weight:700; font-size:16px;">Total Bill Estimate:</span>
                <span id="modal-total-price" style="font-weight:800; font-size:22px; color:var(--accent-green);">0.000 BHD</span>
            </div>

            <button onclick="placeOrder()" class="btn-checkout" style="padding:15px; font-size:15px;">Send to Kitchen (KOT)</button>
        </div>
    </div>

    <!-- Active Order Status / Bill Details Modal -->
    <div class="modal" id="status-modal" style="display:none;">
        <div class="modal-content" style="max-width: 500px; text-align: left;">
            <div class="modal-header">
                <h3 class="modal-title" id="status-modal-title">Active Order Status</h3>
                <button onclick="closeStatusModal()" class="btn-close-modal">×</button>
            </div>
            
            <div id="status-modal-body" style="padding-top:10px;">
                <!-- Loaded dynamically -->
            </div>
        </div>
    </div>

    <!-- Product Details Popup Modal -->
    <div class="modal" id="product-details-modal" style="display:none;">
        <div class="modal-content" style="max-width: 500px; text-align: left; margin: 0 auto;">
            <div class="modal-header">
                <h3 class="modal-title" style="font-size: 18px; font-weight: 800;">Product Details</h3>
                <button onclick="closeProductDetailsModal()" class="btn-close-modal">×</button>
            </div>
            
            <div class="modal-body" style="overflow-y: auto; flex-grow: 1; display: flex; flex-direction: column; gap: 15px; align-items: center; text-align: center; margin-top: 10px;">
                <img id="detail-modal-img" src="" alt="Product Image" style="width: 100%; max-height: 220px; border-radius: 16px; object-fit: cover; box-shadow: 0 8px 24px rgba(0,0,0,0.3); display: none;">
                <div id="detail-modal-img-placeholder" style="width: 90px; height: 90px; border-radius: 16px; background: rgba(255,255,255,0.03); display: flex; align-items: center; justify-content: center; font-size: 36px; color: rgba(255,255,255,0.1); border: 2px dashed var(--card-border); font-weight: 800; display: none;"></div>
                
                <div style="width: 100%; text-align: left; margin-top: 10px;">
                    <h2 id="detail-modal-name" style="font-size: 22px; font-weight: 800; color: var(--text-color); margin-bottom: 4px;"></h2>
                    <span id="detail-modal-price" style="font-size: 16px; font-weight: 800; color: var(--accent-green); display: block; margin-bottom: 12px;"></span>
                    <p id="detail-modal-desc" style="font-size: 13px; color: var(--text-muted); line-height: 1.5; margin-bottom: 5px;"></p>
                </div>
            </div>
            
            <div class="modal-footer" style="margin-top: 20px; display: flex; gap: 12px; align-items: center;">
                <button class="btn-checkout" style="flex: 1; background: rgba(255,255,255,0.05); border: 1px solid var(--card-border); color: var(--text-color); padding: 12px; font-size: 14px; font-weight: 700;" onclick="closeProductDetailsModal()">Close</button>
                <div id="detail-modal-control-container" style="flex: 1.5; display: flex; align-items: center; justify-content: center;"></div>
            </div>
        </div>
    </div>

    <!-- Success Screen Overlay -->
    <div class="success-screen" id="success-screen">
        <div class="success-icon">✓</div>
        <h2 style="font-size:28px; font-weight:800; margin-bottom:10px;">Order Placed!</h2>
        <p style="color:var(--text-muted); font-size:14px; max-width:280px; margin-bottom:30px;">Your ticket has been sent directly to the Kitchen KOT printer. Please enjoy your meal!</p>
        <button onclick="dismissSuccess()" class="btn-checkout">Order More</button>
    </div>

    <script>
        const tableNumber = <?= $tableNumber ?>;
        const currencyCode = '<?= htmlspecialchars($settings['currency_code']) ?>';
        const categories = <?= json_encode($categories) ?>;
        const taxType = '<?= $settings['tax_type'] ?? 'VAT' ?>';
        const vatPercent = parseFloat('<?= $settings['vat_percent'] ?? 10.00 ?>');
        const cgstPercent = parseFloat('<?= $settings['cgst_percent'] ?? 2.50 ?>');
        const sgstPercent = parseFloat('<?= $settings['sgst_percent'] ?? 2.50 ?>');
        let products = []; // Populated via AJAX JSON
        let cart = {};
        let activeOrder = null;
        let pollInterval = null;
        let currentModalProductId = null;

        // Details Modal Functions
        function openProductDetailsModal(event, prodId) {
            // Prevent opening modal if clicking Add button or quantity controls
            if (event.target.closest('#control-' + prodId) || event.target.closest('.btn-add') || event.target.closest('.cart-control')) {
                return;
            }
            
            const prod = products.find(p => p.id === prodId);
            if (!prod) return;

            currentModalProductId = prodId;

            // Populate fields
            document.getElementById('detail-modal-name').innerText = prod.name;
            document.getElementById('detail-modal-price').innerText = parseFloat(prod.price).toFixed(3) + ' ' + currencyCode;
            document.getElementById('detail-modal-desc').innerText = prod.description || 'No description available for this item.';

            const imgEl = document.getElementById('detail-modal-img');
            const placeholderEl = document.getElementById('detail-modal-img-placeholder');

            if (prod.image_url) {
                imgEl.src = '/' + prod.image_url.replace(/^\/+/, '');
                imgEl.style.display = 'block';
                placeholderEl.style.display = 'none';
            } else {
                imgEl.style.display = 'none';
                placeholderEl.innerText = prod.name.substring(0, 1).toUpperCase();
                placeholderEl.style.display = 'flex';
            }

            // Sync footer controls
            updateCartUI();

            document.getElementById('product-details-modal').style.display = 'flex';
        }

        function closeProductDetailsModal() {
            document.getElementById('product-details-modal').style.display = 'none';
            currentModalProductId = null;
        }

        // Fetch products via AJAX JSON
        function loadProducts() {
            fetch('../api/products')
                .then(res => res.json())
                .then(data => {
                    products = data;
                    // Render all products initially
                    selectCategory('all');
                })
                .catch(err => console.error('Error loading products:', err));
        }

        // Initialize on DOM load
        document.addEventListener('DOMContentLoaded', loadProducts);

        function addToCart(product) {
            cart[product.id] = {
                product_id: product.id,
                name: product.name,
                price: parseFloat(product.price),
                quantity: 1,
                notes: ''
            };
            updateCartUI();
        }

        function addToCartById(productId) {
            const product = products.find(p => p.id === productId);
            if (product) {
                addToCart(product);
            }
        }

        function changeQty(productId, amount) {
            if (!cart[productId]) return;
            cart[productId].quantity += amount;
            if (cart[productId].quantity <= 0) {
                delete cart[productId];
            }
            updateCartUI();
        }

        function updateCartUI() {
            let totalQty = 0;
            let totalPrice = 0.0;

            // Draw product item add button replacements
            document.querySelectorAll('[id^="control-"]').forEach(div => {
                const id = parseInt(div.id.replace('control-', ''));
                
                if (cart[id]) {
                    totalQty += cart[id].quantity;
                    totalPrice += cart[id].price * cart[id].quantity;

                    div.innerHTML = `
                        <div class="cart-control">
                            <button class="btn-qty" onclick="changeQty(${id}, -1)">-</button>
                            <span class="qty-num">${cart[id].quantity}</span>
                            <button class="btn-qty" onclick="changeQty(${id}, 1)">+</button>
                        </div>
                    `;
                } else {
                    // Reset to add button
                    div.innerHTML = `<button class="btn-add" onclick="addToCartById(${id})">Add</button>`;
                }
            });

            // Update details modal controls if open
            if (typeof currentModalProductId !== 'undefined' && currentModalProductId !== null) {
                const modalCtrl = document.getElementById('detail-modal-control-container');
                if (modalCtrl) {
                    const id = currentModalProductId;
                    if (cart[id]) {
                        modalCtrl.innerHTML = `
                            <div class="cart-control" style="width: 100%; justify-content: space-between; padding: 6px 12px; border-radius: 12px; height: 46px; background: rgba(255,255,255,0.03); border: 1px solid var(--card-border);">
                                <button class="btn-qty" style="width: 32px; height: 32px; font-size: 18px; line-height: 1; display: flex; align-items: center; justify-content: center;" onclick="changeQty(${id}, -1)">-</button>
                                <span class="qty-num" style="font-size: 15px; font-weight: 700; color: var(--text-color);">${cart[id].quantity}</span>
                                <button class="btn-qty" style="width: 32px; height: 32px; font-size: 18px; line-height: 1; display: flex; align-items: center; justify-content: center;" onclick="changeQty(${id}, 1)">+</button>
                            </div>
                        `;
                    } else {
                        modalCtrl.innerHTML = `
                            <button class="btn-primary" style="width: 100%; background: var(--primary-grad); padding: 12px; font-weight: 700; height: 46px; border-radius: 12px; font-family: inherit; font-size: 14px; color: white;" onclick="addToCartById(${id})">Add to Order</button>
                        `;
                    }
                }
            }

            // Re-render add buttons for all products NOT in cart
            // Since we need product details, let's keep a record, or just regenerate them using inline onclick binding
            // Simply trigger complete UI updates based on cart state
            const cartBar = document.getElementById('cart-drawer-bar');
            if (totalQty > 0) {
                document.getElementById('cart-qty-label').innerText = totalQty + (totalQty === 1 ? ' Item' : ' Items');
                document.getElementById('cart-total-label').innerText = totalPrice.toFixed(3) + ' ' + currencyCode;
                cartBar.classList.add('visible');
            } else {
                cartBar.classList.remove('visible');
            }
        }

        function updateItemNotes(prodId, element) {
            if (cart[prodId]) {
                cart[prodId].notes = element.value;
            }
        }

        function openReviewModal() {
            const container = document.getElementById('modal-items-container');
            let html = '';
            let total = 0.0;

            Object.values(cart).forEach(item => {
                const sub = (item.price * item.quantity).toFixed(3);
                total += item.price * item.quantity;
                html += `
                    <div class="cart-item">
                        <div style="flex-grow:1; margin-right:15px;">
                            <div class="cart-item-name">${escapeHtml(item.name)}</div>
                            <div class="cart-item-price">${item.quantity} × ${item.price.toFixed(3)} = ${sub} ${currencyCode}</div>
                            <input class="item-notes-input" type="text" placeholder="Cooking notes (e.g. no spice, extra sauce)" value="${escapeHtml(item.notes)}" oninput="updateItemNotes(${item.product_id}, this)">
                        </div>
                        <div class="cart-control">
                            <button class="btn-qty" onclick="changeQty(${item.product_id}, -1); openReviewModal();">-</button>
                            <span class="qty-num">${item.quantity}</span>
                            <button class="btn-qty" onclick="changeQty(${item.product_id}, 1); openReviewModal();">+</button>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
            document.getElementById('modal-total-price').innerText = total.toFixed(3) + ' ' + currencyCode;
            document.getElementById('review-modal').style.display = 'flex';
        }

        function closeReviewModal() {
            document.getElementById('review-modal').style.display = 'none';
        }

        function placeOrder() {
            const itemsList = Object.values(cart);
            if (itemsList.length === 0) return;

            // Submit order via API
            fetch('../api/orders', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    table_number: tableNumber,
                    items: itemsList
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    closeReviewModal();
                    // Show success overlay
                    document.getElementById('success-screen').style.display = 'flex';
                    // Reset local cart
                    cart = {};
                    updateCartUI();
                    
                    // Reset add buttons manually
                    document.querySelectorAll('[id^="control-"]').forEach(div => {
                        const id = parseInt(div.id.replace('control-', ''));
                        div.innerHTML = `<button class="btn-add" onclick="location.reload()">Add</button>`;
                    });
                }
            })
            .catch(err => console.error('Error placing order:', err));
        }

        function dismissSuccess() {
            document.getElementById('success-screen').style.display = 'none';
            location.reload(); // Reload to reset all clean
        }

        function renderProducts(productsToRender) {
            const container = document.querySelector('.products-section');
            
            // Group products to render by category
            const grouped = {};
            productsToRender.forEach(prod => {
                if (parseInt(prod.is_available) !== 1) return; // Only show available products
                if (!grouped[prod.category_id]) {
                    grouped[prod.category_id] = [];
                }
                grouped[prod.category_id].push(prod);
            });

            let html = '';
            categories.forEach(cat => {
                const catProds = grouped[cat.id] || [];
                if (catProds.length === 0) return;

                html += `
                    <div class="category-group" id="cat-group-${cat.id}">
                        <h3 class="category-heading">${escapeHtml(cat.name)}</h3>
                        <div class="products-grid">
                `;

                catProds.forEach(prod => {
                    const price = parseFloat(prod.price).toFixed(3);
                    const imgHtml = prod.image_url 
                        ? `<img src="/${prod.image_url.replace(/^\/+/, '')}" class="product-img" alt="Img">`
                        : `<div class="product-img" style="background: rgba(255,255,255,0.03); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:18px; color:rgba(255,255,255,0.08);">${escapeHtml(prod.name.substring(0, 1))}</div>`;

                    html += `
                        <div class="product-card" style="cursor: pointer;" onclick="openProductDetailsModal(event, ${prod.id})" data-name="${escapeHtml(prod.name.toLowerCase())}" data-desc="${escapeHtml((prod.description || '').toLowerCase())}">
                            ${imgHtml}
                            <div class="product-details">
                                <h4 class="product-name">${escapeHtml(prod.name)}</h4>
                                <p class="product-desc">${escapeHtml(prod.description || '')}</p>
                                <div class="product-footer">
                                    <span class="product-price">${price} ${currencyCode}</span>
                                    <div id="control-${prod.id}">
                                        <button class="btn-add" onclick="addToCartById(${prod.id})">Add</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                html += `
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html || `<div style="text-align:center; padding:40px; color:var(--text-muted); font-weight:600;">No items found in this category.</div>`;
            
            // Re-apply cart quantities to UI controls
            updateCartUI();
        }

        function selectCategory(catId) {
            // Update chip styling
            document.querySelectorAll('.cat-chip').forEach(chip => chip.classList.remove('active'));
            if (catId === 'all') {
                document.getElementById('chip-all').classList.add('active');
                renderProducts(products);
            } else {
                document.getElementById('chip-' + catId).classList.add('active');
                const filtered = products.filter(p => parseInt(p.category_id) === parseInt(catId));
                renderProducts(filtered);
            }
        }

        function filterMenu() {
            const query = document.getElementById('search-box').value.toLowerCase();
            document.querySelectorAll('.product-card').forEach(card => {
                const name = card.dataset.name;
                const desc = card.dataset.desc;
                if (name.includes(query) || desc.includes(query)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function escapeHtml(str) {
            return str
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // --- ORDER preparación/serving polling system ---
        let autoOpenedOrders = JSON.parse(sessionStorage.getItem('auto_opened_orders') || '{}');

        function startOrderPolling() {
            if (pollInterval) clearInterval(pollInterval);
            pollInterval = setInterval(checkActiveOrderStatus, 4000); // Poll every 4 seconds
            checkActiveOrderStatus();
        }

        function checkActiveOrderStatus() {
            fetch('../api/orders/active/' + tableNumber)
                .then(res => res.json())
                .then(data => {
                    if (data.active) {
                        activeOrder = data;
                        updateOrderStatusUI();
                    } else {
                        activeOrder = null;
                        document.getElementById('active-order-status-bar').style.display = 'none';
                        closeStatusModal();
                        if (pollInterval) {
                            clearInterval(pollInterval);
                            pollInterval = null;
                        }
                    }
                })
                .catch(err => console.error('Error checking order status:', err));
        }

        function updateOrderStatusUI() {
            if (!activeOrder) return;

            let hasPending = false;
            let hasReady = false;
            let hasServed = false;
            let totalItemsCount = 0;
            let servedItemsCount = 0;

            if (activeOrder.kots) {
                activeOrder.kots.forEach(kot => {
                    kot.items.forEach(item => {
                        totalItemsCount += parseInt(item.quantity);
                        if (item.status === 'dispatched') {
                            hasServed = true;
                            servedItemsCount += parseInt(item.quantity);
                        } else if (item.status === 'ready') {
                            hasReady = true;
                        } else {
                            hasPending = true;
                        }
                    });
                });
            }

            const statusBar = document.getElementById('active-order-status-bar');
            const pulseDot = document.getElementById('status-pulse-dot');
            const statusText = document.getElementById('status-text-label');

            statusBar.style.display = 'flex';

            const orderId = activeOrder.order.id;
            const isKotDeleted = (!activeOrder.kots || activeOrder.kots.length === 0);
            const isFullyServed = (totalItemsCount === servedItemsCount && totalItemsCount > 0);

            if (isKotDeleted) {
                statusBar.className = 'active-status-bar deleted-status';
                pulseDot.className = 'pulse-dot-red';
                statusText.innerText = 'Requested Item Not AVL Please reorder another Items.';
            } else if (isFullyServed) {
                statusBar.className = 'active-status-bar served-status';
                pulseDot.className = 'pulse-dot-green';
                statusText.innerText = 'Your order is ready & served! Click to view bill.';

                // Auto popup when first transitioned to fully served
                if (!autoOpenedOrders[orderId]) {
                    autoOpenedOrders[orderId] = true;
                    sessionStorage.setItem('auto_opened_orders', JSON.stringify(autoOpenedOrders));
                    openStatusModal();
                }
            } else {
                statusBar.className = 'active-status-bar';
                pulseDot.className = 'pulse-dot-orange';
                statusText.innerText = 'Your order is being prepared. Wait for a moment.';
            }
        }

        function openStatusModal() {
            if (!activeOrder) return;

            const modal = document.getElementById('status-modal');
            const modalTitle = document.getElementById('status-modal-title');
            const modalBody = document.getElementById('status-modal-body');

            const isKotDeleted = (!activeOrder.kots || activeOrder.kots.length === 0);

            if (isKotDeleted) {
                modalTitle.innerText = 'Order Cancelled / Unavailable';
                modalBody.innerHTML = `
                    <div style="text-align: center; padding: 20px 10px;">
                        <div style="font-size: 50px; margin-bottom: 15px;">⚠️</div>
                        <h3 style="color: var(--accent-red); font-size: 18px; font-weight: 800; margin-bottom: 12px;">Requested Item Not AVL</h3>
                        <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6; margin-bottom: 25px;">
                            The kitchen has reported that the item(s) you requested are currently not available. Please reorder other items.
                        </p>
                        <button onclick="cancelAndReorder(${activeOrder.order.id})" class="btn-checkout" style="padding: 14px; font-size: 14px; border-radius: 12px; background: var(--primary-grad); border: none; color: white; font-weight: 800; width: 100%;">
                            🔄 Reorder / Place New Order
                        </button>
                    </div>
                `;
                modal.style.display = 'flex';
                return;
            }

            let totalItemsCount = 0;
            let servedItemsCount = 0;
            if (activeOrder.kots) {
                activeOrder.kots.forEach(kot => {
                    kot.items.forEach(item => {
                        totalItemsCount += parseInt(item.quantity);
                        if (item.status === 'dispatched') servedItemsCount += parseInt(item.quantity);
                    });
                });
            }
            const isFullyServed = (totalItemsCount === servedItemsCount && totalItemsCount > 0);

            modalTitle.innerText = isFullyServed ? 'Order Served & Bill Details' : 'Active KOT Order Status';

            let itemsHtml = `
                <div style="font-size: 13px; text-transform: uppercase; color: var(--text-muted); font-weight: 700; margin-bottom: 8px; border-bottom: 1px solid var(--card-border); padding-bottom: 6px;">
                    Order Item-wise Status
                </div>
                <div style="max-height: 180px; overflow-y: auto; margin-bottom: 20px;">
            `;

            if (activeOrder.kots && activeOrder.kots.length > 0) {
                activeOrder.kots.forEach(kot => {
                    kot.items.forEach(item => {
                        let statusColor = 'var(--accent-orange)';
                        let statusLabel = 'Preparing';
                        if (item.status === 'dispatched') {
                            statusColor = 'var(--accent-green)';
                            statusLabel = 'Served';
                        } else if (item.status === 'ready') {
                            statusColor = '#60a5fa';
                            statusLabel = 'Ready to Serve';
                        }

                        itemsHtml += `
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid rgba(255,255,255,0.03);">
                                <div>
                                    <span style="font-weight:700; color:var(--text-color);">${item.quantity}</span> × 
                                    <span style="font-weight:500;">${escapeHtml(item.product_name)}</span>
                                    ${item.notes ? `<div style="font-size:11px; color:#ef4444; margin-top:2px;">Note: ${escapeHtml(item.notes)}</div>` : ''}
                                </div>
                                <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: ${statusColor}; background: rgba(255,255,255,0.03); border: 1px solid ${statusColor}33; padding: 2px 8px; border-radius: 6px;">
                                    ${statusLabel}
                                </span>
                            </div>
                        `;
                    });
                });
            } else {
                itemsHtml += `<div style="color:var(--text-muted); padding:10px 0;">No active items found.</div>`;
            }
            itemsHtml += `</div>`;

            let subtotal = 0;
            if (activeOrder.items) {
                activeOrder.items.forEach(item => {
                    subtotal += parseFloat(item.price) * parseInt(item.total_quantity);
                });
            }

            let taxAmount = 0;
            let taxLabel = taxType;
            if (taxType === 'VAT') {
                taxAmount = subtotal * (vatPercent / 100);
                taxLabel = `VAT (${vatPercent}%)`;
            } else {
                taxAmount = subtotal * ((cgstPercent + sgstPercent) / 100);
                taxLabel = `GST (${cgstPercent + sgstPercent}%)`;
            }
            const grandTotal = subtotal + taxAmount;

            let billHtml = `
                <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--card-border); border-radius: 14px; padding: 15px; margin-bottom: 25px;">
                    <div style="font-size: 13px; text-transform: uppercase; color: var(--text-muted); font-weight: 700; margin-bottom: 10px; border-bottom: 1px solid var(--card-border); padding-bottom: 6px;">
                        Item-wise Bill Details
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:6px; color:var(--text-muted);">
                        <span>Subtotal:</span>
                        <span>${subtotal.toFixed(3)} ${currencyCode}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:6px; color:var(--text-muted);">
                        <span>${taxLabel}:</span>
                        <span style="color:var(--accent-orange);">${taxAmount.toFixed(3)} ${currencyCode}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:16px; font-weight:800; border-top:1px dashed var(--card-border); margin-top:8px; padding-top:8px; color:var(--text-color);">
                        <span>Grand Total:</span>
                        <span style="color:var(--accent-green);">${grandTotal.toFixed(3)} ${currencyCode}</span>
                    </div>
                </div>
            `;

            let buttonsHtml = `
                <div style="display:flex; gap:12px; flex-direction:column;">
                    <button onclick="closeStatusModal()" class="btn-checkout" style="background:rgba(255,255,255,0.05); border:1px solid var(--card-border); color:var(--text-color); padding:12px; font-size:14px; border-radius:12px;">
                        ➕ Add New Order / Keep Ordering
                    </button>
                    <button onclick="requestCustomerBilling(${activeOrder.order.id})" class="btn-checkout" style="padding:14px; font-size:14px; border-radius:12px; background:var(--primary-grad); border:none; color:white; font-weight:800;">
                        🏁 Complete & Request Bill
                    </button>
                </div>
            `;

            modalBody.innerHTML = itemsHtml + billHtml + buttonsHtml;
            modal.style.display = 'flex';
        }

        function closeStatusModal() {
            document.getElementById('status-modal').style.display = 'none';
        }

        function cancelAndReorder(orderId) {
            Swal.fire({
                title: 'Cancel & Reorder?',
                text: 'This will cancel the current empty request and clear your status bar so you can place a new order.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#4b5563',
                confirmButtonText: 'Yes, Cancel & Reorder',
                background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../api/orders/cancel/' + orderId, { method: 'POST' })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                closeStatusModal();
                                activeOrder = null;
                                document.getElementById('active-order-status-bar').style.display = 'none';
                                Swal.fire({
                                    title: 'Cleared!',
                                    text: 'You can now select other items and order again.',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false,
                                    background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                                    color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: data.error || 'Failed to cancel order.',
                                    icon: 'error',
                                    background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                                    color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
                                });
                            }
                        })
                        .catch(err => console.error('Error cancelling order:', err));
                }
            });
        }

        function requestCustomerBilling(orderId) {
            Swal.fire({
                title: 'Request Bill?',
                text: 'Are you sure you want to request the final bill? This will complete your table session.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#4b5563',
                confirmButtonText: 'Yes, request bill',
                background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../api/orders/close/' + orderId, { method: 'POST' })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                closeStatusModal();
                                document.getElementById('success-screen').innerHTML = `
                                    <div class="success-icon" style="background:rgba(16, 185, 129, 0.1); color:var(--accent-green); border-color:var(--accent-green); box-shadow: 0 0 20px rgba(16, 185, 129, 0.2);">✓</div>
                                    <h2 style="font-size:28px; font-weight:800; margin-bottom:10px;">Billing Requested!</h2>
                                    <p style="color:var(--text-muted); font-size:14px; max-width:280px; margin-bottom:30px;">Your final bill has been generated. Please proceed to the cashier counter for payment. Thank you for dining with us!</p>
                                    <button onclick="location.reload()" class="btn-checkout">Close Menu</button>
                                `;
                                document.getElementById('success-screen').style.display = 'flex';
                            }
                        })
                        .catch(err => console.error('Error requesting bill:', err));
                }
            });
        }

        function openSandsModal() {
            document.getElementById('sands-modal').style.display = 'flex';
        }

        function closeSandsModal() {
            document.getElementById('sands-modal').style.display = 'none';
        }

        // Start order status poller
        startOrderPolling();
    </script>
</body>
</html>
