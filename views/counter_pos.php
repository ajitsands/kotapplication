<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script>
        const _cur = '<?= htmlspecialchars($settings['currency_code'] ?? 'BHD') ?>'.toUpperCase().trim();
        window.PRICE_DECIMALS = ['BHD', 'KWD', 'OMR', 'IQD', 'JOD', 'TND', 'LYD'].includes(_cur) ? 3 : 2;
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Counter POS - Take Order | <?= htmlspecialchars($settings['restaurant_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
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
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        header {
            background: rgba(11, 15, 25, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--card-border);
            padding: 12px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        body.light-theme header {
            background: rgba(255, 255, 255, 0.85);
            border-bottom-color: rgba(0, 0, 0, 0.08);
        }

        .header-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-title {
            font-size: 19px;
            font-weight: 800;
            background: var(--primary-grad);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header-nav {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .nav-link {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--text-color);
        }

        body.light-theme .nav-link {
            color: #4b5563;
        }

        body.light-theme .nav-link:hover, body.light-theme .nav-link.active {
            color: #111827;
        }

        .header-dropdown {
            position: relative;
            display: inline-block;
        }

        .header-dropdown-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            background: transparent;
            border: none;
            font-family: inherit;
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 500;
            padding: 6px 10px;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .header-dropdown-btn:hover {
            color: var(--text-color);
            background: rgba(255, 255, 255, 0.05);
        }

        .header-dropdown-content {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 200px;
            background: #111827;
            border: 1px solid var(--card-border);
            border-radius: 14px;
            box-shadow: 0 14px 35px rgba(0, 0, 0, 0.45);
            z-index: 1000;
            padding: 8px 0;
            backdrop-filter: blur(12px);
            animation: fadeInDrop 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes fadeInDrop {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulseGreen {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1.04); box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .header-dropdown.open .header-dropdown-content {
            display: block;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 18px;
            color: var(--text-color);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.06);
            color: #fff;
            padding-left: 22px;
        }

        body.light-theme .header-dropdown-content {
            background: #ffffff;
            box-shadow: 0 14px 35px rgba(0, 0, 0, 0.12);
            border-color: rgba(0, 0, 0, 0.08);
        }

        body.light-theme .header-dropdown-btn { color: #4b5563; }
        body.light-theme .header-dropdown-btn:hover { color: #111827; background: rgba(0, 0, 0, 0.04); }
        body.light-theme .dropdown-item { color: #374151; }
        body.light-theme .dropdown-item:hover { background: rgba(0, 0, 0, 0.04); color: #111827; }

        .btn-logout {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: var(--accent-red);
            padding: 6px 14px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        /* POS Workspace Layout */
        .pos-container {
            display: grid;
            grid-template-columns: 1fr 390px;
            flex: 1;
            height: calc(100vh - 65px);
            overflow: hidden;
        }

        @media (max-width: 1024px) {
            .pos-container {
                grid-template-columns: 1fr 340px;
            }
        }

        @media (max-width: 768px) {
            .pos-container {
                grid-template-columns: 1fr;
                height: auto;
                overflow: visible;
            }
        }

        /* Catalog Section (Left) */
        .catalog-panel {
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--card-border);
            overflow: hidden;
            background: rgba(255, 255, 255, 0.01);
        }

        .catalog-topbar {
            padding: 16px 24px 10px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            border-bottom: 1px solid var(--card-border);
            background: rgba(255, 255, 255, 0.015);
        }

        .search-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--card-border);
            color: var(--text-color);
            padding: 12px 16px 12px 42px;
            border-radius: 14px;
            font-family: inherit;
            font-size: 14px;
            outline: none;
            transition: all 0.2s;
        }

        .search-input:focus {
            border-color: #6366f1;
            background: rgba(255, 255, 255, 0.07);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        body.light-theme .search-input {
            background: #ffffff;
            border-color: rgba(0, 0, 0, 0.1);
            color: #111827;
        }

        .search-icon {
            position: absolute;
            left: 14px;
            font-size: 16px;
            color: var(--text-muted);
            pointer-events: none;
        }

        .categories-scroll {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 6px;
            scrollbar-width: thin;
        }

        .cat-chip {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--card-border);
            color: var(--text-muted);
            padding: 8px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .cat-chip:hover {
            color: var(--text-color);
            background: rgba(255, 255, 255, 0.06);
        }

        .cat-chip.active {
            background: var(--primary-grad);
            border-color: transparent;
            color: #fff;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        body.light-theme .cat-chip {
            background: rgba(0, 0, 0, 0.03);
            border-color: rgba(0, 0, 0, 0.08);
            color: #4b5563;
        }

        body.light-theme .cat-chip:hover {
            background: rgba(0, 0, 0, 0.06);
            color: #111827;
        }

        body.light-theme .cat-chip.active {
            background: var(--primary-grad);
            color: #ffffff;
        }

        /* Products Grid */
        .products-scroll {
            flex: 1;
            overflow-y: auto;
            padding: 20px 24px;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 16px;
        }

        .product-card {
            background: rgba(255, 255, 255, 0.025);
            border: 1px solid var(--card-border);
            border-radius: 18px;
            padding: 12px;
            display: flex;
            flex-direction: column;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            user-select: none;
        }

        .product-card:hover {
            transform: translateY(-3px);
            border-color: rgba(99, 102, 241, 0.4);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
            background: rgba(255, 255, 255, 0.04);
        }

        .product-card:active {
            transform: scale(0.97);
        }

        body.light-theme .product-card {
            background: #ffffff;
            border-color: rgba(0, 0, 0, 0.08);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        body.light-theme .product-card:hover {
            background: #ffffff;
            border-color: #6366f1;
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.15);
        }

        .product-thumb {
            width: 100%;
            height: 110px;
            border-radius: 12px;
            object-fit: cover;
            background: rgba(0, 0, 0, 0.2);
            margin-bottom: 10px;
        }

        .product-thumb-placeholder {
            width: 100%;
            height: 110px;
            border-radius: 12px;
            background: rgba(99, 102, 241, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            margin-bottom: 10px;
        }

        .product-name {
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 4px;
            line-height: 1.3;
            color: var(--text-color);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-cat {
            font-size: 11px;
            color: var(--text-muted);
            margin-bottom: 8px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .product-price-row {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 6px;
            border-top: 1px dashed var(--card-border);
        }

        .product-price {
            font-weight: 800;
            font-size: 15px;
            color: var(--accent-green);
        }

        .btn-add-mini {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: var(--primary-grad);
            border: none;
            color: white;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            box-shadow: 0 3px 8px rgba(99, 102, 241, 0.3);
        }

        .product-badge-qty {
            position: absolute;
            top: 8px;
            right: 8px;
            background: #ef4444;
            color: white;
            font-size: 11px;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 10px;
            box-shadow: 0 3px 8px rgba(239, 68, 68, 0.4);
            display: none;
        }

        /* Cart Panel (Right) */
        .cart-panel {
            display: flex;
            flex-direction: column;
            background: rgba(11, 15, 25, 0.6);
            backdrop-filter: blur(15px);
            overflow: hidden;
        }

        body.light-theme .cart-panel {
            background: #ffffff;
            box-shadow: -4px 0 20px rgba(0, 0, 0, 0.05);
        }

        .cart-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--card-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-title {
            font-size: 16px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Order Type Selector Tabs */
        .order-type-tabs {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
            padding: 12px 20px;
            border-bottom: 1px solid var(--card-border);
            background: rgba(255, 255, 255, 0.015);
        }

        .type-tab-btn {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--card-border);
            color: var(--text-muted);
            padding: 10px;
            border-radius: 12px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .type-tab-btn.active {
            background: var(--primary-grad);
            border-color: transparent;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        body.light-theme .type-tab-btn {
            background: rgba(0, 0, 0, 0.03);
            border-color: rgba(0, 0, 0, 0.08);
            color: #4b5563;
        }

        body.light-theme .type-tab-btn.active {
            background: var(--primary-grad);
            color: #ffffff;
        }

        .order-meta-box {
            padding: 12px 20px;
            border-bottom: 1px solid var(--card-border);
            display: flex;
            flex-direction: column;
            gap: 8px;
            background: rgba(255, 255, 255, 0.01);
        }

        .meta-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--card-border);
            color: var(--text-color);
            padding: 8px 12px;
            border-radius: 10px;
            font-family: inherit;
            font-size: 13px;
            outline: none;
        }

        .meta-input:focus {
            border-color: #6366f1;
        }

        body.light-theme .meta-input {
            background: rgba(0, 0, 0, 0.02);
            border-color: rgba(0, 0, 0, 0.08);
            color: #111827;
        }

        /* Cart Items List */
        .cart-items-scroll {
            flex: 1;
            overflow-y: auto;
            padding: 14px 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .cart-empty-state {
            margin: auto;
            text-align: center;
            color: var(--text-muted);
            padding: 40px 20px;
        }

        .cart-item-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--card-border);
            border-radius: 14px;
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            transition: all 0.2s;
        }

        body.light-theme .cart-item-card {
            background: rgba(0, 0, 0, 0.02);
            border-color: rgba(0, 0, 0, 0.06);
        }

        .cart-item-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .cart-item-title {
            font-weight: 700;
            font-size: 14px;
            line-height: 1.2;
            color: var(--text-color);
        }

        .cart-item-price {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .cart-item-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 4px;
        }

        .qty-controls {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--card-border);
            border-radius: 10px;
            padding: 3px 6px;
        }

        body.light-theme .qty-controls {
            background: #ffffff;
            border-color: rgba(0, 0, 0, 0.1);
        }

        .qty-btn {
            background: none;
            border: none;
            color: var(--text-color);
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: background 0.2s;
        }

        .qty-btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        body.light-theme .qty-btn:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        .qty-display {
            font-weight: 800;
            font-size: 13px;
            min-width: 20px;
            text-align: center;
        }

        .cart-item-total {
            font-weight: 800;
            font-size: 14px;
            color: var(--accent-green);
        }

        .cart-item-note-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.02);
            border: 1px dashed var(--card-border);
            color: var(--text-color);
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-family: inherit;
            outline: none;
        }

        /* Cart Footer / Totals & Checkout */
        .cart-footer {
            border-top: 1px solid var(--card-border);
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.015);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: var(--text-muted);
        }

        .total-row.grand {
            font-size: 18px;
            font-weight: 800;
            color: var(--text-color);
            border-top: 1px dashed var(--card-border);
            padding-top: 10px;
            margin-top: 4px;
        }

        .btn-checkout-kot {
            width: 100%;
            background: linear-gradient(135deg, #10b981, #059669);
            border: none;
            color: white;
            padding: 14px;
            border-radius: 14px;
            font-family: inherit;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35);
        }

        .btn-checkout-kot:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.45);
        }

        .btn-checkout-kot:active {
            transform: scale(0.98);
        }

        .btn-checkout-pay {
            width: 100%;
            background: var(--primary-grad);
            border: none;
            color: white;
            padding: 12px;
            border-radius: 14px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-checkout-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
        }

        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #111827;
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 25px;
            width: 90%;
            max-width: 460px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
            animation: modalPop 0.2s ease-out;
        }

        body.light-theme .modal-content {
            background: #ffffff;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        @keyframes modalPop {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .pay-methods-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin: 15px 0;
        }

        .pay-btn-tile {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--card-border);
            color: var(--text-color);
            padding: 12px;
            border-radius: 12px;
            font-family: inherit;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .pay-btn-tile:hover {
            border-color: #6366f1;
        }

        .pay-btn-tile.selected {
            background: var(--primary-grad);
            border-color: transparent;
            color: white;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);
        }

        body.light-theme .pay-btn-tile {
            background: rgba(0, 0, 0, 0.03);
            border-color: rgba(0, 0, 0, 0.08);
            color: #1f2937;
        }

        body.light-theme .pay-btn-tile.selected {
            background: var(--primary-grad);
            color: white;
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
            padding: 10px 24px !important;
        }
        .swal2-cancel {
            border-radius: 12px !important;
            font-family: 'Outfit', sans-serif !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            padding: 10px 24px !important;
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
    <header>
        <div class="header-logo">
            <?php if (!empty($settings['logo_path'])): ?>
                <img src="/<?= ltrim(htmlspecialchars($settings['logo_path']), '/') ?>" alt="Logo" style="height: 30px; object-fit: contain; border-radius: 4px;">
            <?php endif; ?>
            <span class="header-title"><?= htmlspecialchars($settings['restaurant_name']) ?> | POS</span>
        </div>
        <div class="header-nav">
            <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                <a href="/admin" class="nav-link">Admin</a>
                <a href="/kot" class="nav-link">KOT Monitor</a>
            <?php endif; ?>
            <a href="/counter/pos" class="nav-link active" style="background: var(--primary-grad); color: white !important; font-weight: 700; border-radius: 10px; padding: 7px 15px; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35); display: inline-flex; align-items: center; gap: 6px;">🛒 New Order (POS)</a>
            <a href="/counter" class="nav-link">💳 Billing Counter</a>
            
            <!-- Delivery Location Received Badge -->
            <button id="pos-deliv-badge-btn" type="button" onclick="openPendingDeliveryRequestsModal()" style="display:none; background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 6px 14px; border-radius: 20px; font-weight: 800; font-size: 13px; cursor: pointer; align-items: center; gap: 6px; box-shadow: 0 4px 14px rgba(16,185,129,0.4); animation: pulseGreen 2s infinite;">
                📍 <span id="pos-deliv-badge-count">0</span> Location Received
            </button>
            
            <!-- QR & Other Sub Menu -->
            <div class="header-dropdown" id="qr-tools-dropdown">
                <button type="button" class="header-dropdown-btn" onclick="toggleQrDropdown(event)">
                    <span>📱 QR & Other</span>
                    <span style="font-size: 10px; transition: transform 0.2s;" id="qr-dropdown-arrow">▼</span>
                </button>
                <div class="header-dropdown-content" id="qr-dropdown-menu">
                    <a href="javascript:void(0)" onclick="showEngagedTablesModal(); closeQrDropdown();" class="dropdown-item">🍽️ Table Status</a>
                    <a href="javascript:void(0)" onclick="showWaiterLoginQr(); closeQrDropdown();" class="dropdown-item">📱 Waiter QR</a>
                    <a href="javascript:void(0)" onclick="showTakeawayQr(); closeQrDropdown();" class="dropdown-item" style="color: #f59e0b;">🛍️ Take Away QR</a>
                    <a href="javascript:void(0)" onclick="showOnlineOrderModal(); closeQrDropdown();" class="dropdown-item" style="color: #10b981;">🌐 Online Order</a>
                </div>
            </div>

            <button type="button" onclick="changeOwnPasswordPrompt()" title="Change Password" style="background: rgba(255,255,255,0.05); border: 1px solid var(--card-border); color: var(--text-color); cursor: pointer; font-size: 15px; width: 34px; height: 34px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; vertical-align: middle;">🔑</button>
            <button type="button" onclick="toggleTheme()" title="Toggle Dark/Light Theme" style="background: rgba(255,255,255,0.05); border: 1px solid var(--card-border); color: var(--text-color); cursor: pointer; font-size: 15px; width: 34px; height: 34px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; vertical-align: middle;">🌓</button>
            <a href="/logout" class="btn-logout" title="Logout" style="width: 34px; height: 34px; border-radius: 50%; padding: 0; display: inline-flex; align-items: center; justify-content: center; font-size: 15px;">🚪</a>
        </div>
    </header>

    <div class="pos-container">
        <!-- Catalog Panel (Left) -->
        <div class="catalog-panel">
            <div class="catalog-topbar">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="pos-search" class="search-input" placeholder="Search menu items by name..." oninput="filterProducts()">
                </div>
                <div class="categories-scroll" id="categories-bar">
                    <button class="cat-chip active" onclick="selectCategory('all', this)">
                        <span>🍽️ All Items</span>
                        <span style="font-size: 11px; opacity: 0.7;">(<?= count($products) ?>)</span>
                    </button>
                    <?php foreach ($categories as $cat): ?>
                        <?php 
                            $count = count(array_filter($products, function($p) use ($cat) {
                                return (int)($p['category_id'] ?? 0) === (int)$cat['id'];
                            }));
                        ?>
                        <button class="cat-chip" onclick="selectCategory('<?= $cat['id'] ?>', this)">
                            <span><?= htmlspecialchars($cat['name']) ?></span>
                            <span style="font-size: 11px; opacity: 0.7;">(<?= $count ?>)</span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="products-scroll">
                <div class="products-grid" id="products-grid">
                    <?php foreach ($products as $prod): ?>
                        <?php 
                            $isAvail = (int)($prod['is_available'] ?? 1) === 1;
                            if (!$isAvail) continue; // Only show available products
                            $img = !empty($prod['image_url']) ? '/' . ltrim(htmlspecialchars($prod['image_url']), '/') : null;
                        ?>
                        <div class="product-card" 
                             data-id="<?= $prod['id'] ?>"
                             data-name="<?= htmlspecialchars(strtolower($prod['name'])) ?>"
                             data-category="<?= $prod['category_id'] ?? 0 ?>"
                             data-base-price="<?= (float)$prod['price'] ?>"
                             onclick="addToCart(<?= htmlspecialchars(json_encode([
                                 'id' => (int)$prod['id'],
                                 'name' => $prod['name'],
                                 'price' => (float)$prod['price'],
                                 'image_url' => $prod['image_url'] ?? null
                             ])) ?>)">
                            <div class="product-badge-qty" id="qty-badge-<?= $prod['id'] ?>">0</div>
                            <?php if ($img): ?>
                                <img src="<?= $img ?>" class="product-thumb" alt="<?= htmlspecialchars($prod['name']) ?>" onerror="this.outerHTML='<div class=\'product-thumb-placeholder\'>🍔</div>'">
                            <?php else: ?>
                                <div class="product-thumb-placeholder">🍔</div>
                            <?php endif; ?>
                            <div class="product-name"><?= htmlspecialchars($prod['name']) ?></div>
                            <div class="product-cat"><?= htmlspecialchars($prod['category_name'] ?? 'Menu Item') ?></div>
                            <div class="product-price-row">
                                <span class="product-price" id="prod-price-<?= $prod['id'] ?>" data-base="<?= (float)$prod['price'] ?>"><?= number_format((float)$prod['price'], 3, '.', '') ?> <?= htmlspecialchars($settings['currency_code']) ?></span>
                                <button type="button" class="btn-add-mini" title="Add to Order">+</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Cart Panel (Right) -->
        <div class="cart-panel">
            <div class="cart-header">
                <div class="cart-title">
                    <span>🧾 Current Order</span>
                    <span id="cart-item-count" style="font-size: 12px; background: rgba(99,102,241,0.15); color: #818cf8; padding: 2px 8px; border-radius: 8px;">0 items</span>
                </div>
                <button type="button" onclick="clearCart()" style="background: none; border: none; color: var(--accent-red); font-size: 12px; font-weight: 700; cursor: pointer;">Clear All</button>
            </div>

            <!-- Order Type Selector -->
            <div class="order-type-tabs">
                <button type="button" class="type-tab-btn active" id="tab-takeaway" onclick="setOrderType('take_away')">
                    <span>🛍️ Take Away</span>
                </button>
                <button type="button" class="type-tab-btn" id="tab-dinein" onclick="setOrderType('dine_in')">
                    <span>🍽️ Dine-In</span>
                </button>
                <button type="button" class="type-tab-btn" id="tab-online" onclick="setOrderType('online')">
                    <span>🌐 Online</span>
                </button>
            </div>

            <!-- Order Meta Fields (Table selector, Online Platform & Customer Info) -->
            <div class="order-meta-box">
                <div id="table-select-container" style="display: none;">
                    <label style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; display: block; margin-bottom: 4px;">Select Table:</label>
                    <select id="pos-table-select" class="meta-input">
                        <option value="">Choose a table...</option>
                        <?php if (!empty($tables)): ?>
                            <?php foreach ($tables as $tbl): ?>
                                <option value="<?= $tbl['table_number'] ?>">
                                    Table T<?= $tbl['table_number'] ?> (<?= ucfirst($tbl['status']) ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php for ($i = 1; $i <= 20; $i++): ?>
                                <option value="<?= $i ?>">Table T<?= $i ?></option>
                            <?php endfor; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div id="online-select-container" style="display: none;">
                    <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                        <div style="flex: 1;">
                            <label style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; display: block; margin-bottom: 4px;">Platform:</label>
                            <select id="pos-platform-select" class="meta-input" onchange="onPlatformChange()">
                                <option value="" data-markup="0">Select Platform...</option>
                                <?php if (!empty($platforms)): ?>
                                    <?php foreach ($platforms as $plat): 
                                        $m = (float)($plat['markup_percent'] ?? 0);
                                        $mLabel = $m > 0 ? ' (+' . number_format($m, 0) . '%)' : '';
                                    ?>
                                        <option value="<?= $plat['id'] ?>" data-markup="<?= $m ?>"><?= htmlspecialchars($plat['name']) . $mLabel ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div style="flex: 1;">
                            <label style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; display: block; margin-bottom: 4px;">Order ID / Ref:</label>
                            <input type="text" id="pos-platform-number" class="meta-input" placeholder="e.g. #7821">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; background: rgba(99,102,241,0.04); border: 1px dashed var(--card-border); padding: 8px; border-radius: 8px;">
                        <div>
                            <label style="font-size: 10.5px; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 3px;" title="Delivery fee charged for this order">🚚 Delivery Fee:</label>
                            <input type="number" id="pos-online-delivery-fee" step="0.001" min="0" value="0.000" class="meta-input" placeholder="0.000" style="padding: 6px 8px; font-size: 12px;" oninput="renderCart()">
                        </div>
                        <div>
                            <label style="font-size: 10.5px; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 3px;" title="Commission or service fee deducted by platform">💼 Service Fee:</label>
                            <input type="number" id="pos-online-service-fee" step="0.001" min="0" value="0.000" class="meta-input" placeholder="0.000" style="padding: 6px 8px; font-size: 12px;" oninput="renderCart()">
                        </div>
                        <div>
                            <label style="font-size: 10.5px; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 3px;" title="Discounts applied to items">🏷️ Discount:</label>
                            <input type="number" id="pos-online-discount" step="0.001" min="0" value="0.000" class="meta-input" placeholder="0.000" style="padding: 6px 8px; font-size: 12px;" oninput="renderCart()">
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 8px;">
                    <div style="flex: 1;">
                        <input type="tel" id="pos-cust-mobile" class="meta-input" placeholder="Mobile (optional)" oninput="lookupPosCustomer()">
                    </div>
                    <div style="flex: 1;">
                        <input type="text" id="pos-cust-name" class="meta-input" placeholder="Customer Name">
                    </div>
                </div>
            </div>

            <!-- Cart Items Container -->
            <div class="cart-items-scroll" id="cart-items-container">
                <div class="cart-empty-state">
                    <div style="font-size: 42px; margin-bottom: 10px; opacity: 0.4;">🛒</div>
                    <div style="font-weight: 700; font-size: 15px; margin-bottom: 4px;">Cart is empty</div>
                    <div style="font-size: 12px;">Click any item on the left to add to this order ticket.</div>
                </div>
            </div>

            <!-- Cart Totals & Actions -->
            <div class="cart-footer">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span id="pos-subtotal">0.000 <?= htmlspecialchars($settings['currency_code']) ?></span>
                </div>
                <div class="total-row" id="pos-row-discount" style="display: none; color: #ef4444;">
                    <span>Discount:</span>
                    <span id="pos-discount-amount">-0.000 <?= htmlspecialchars($settings['currency_code']) ?></span>
                </div>
                <div class="total-row">
                    <span id="pos-tax-label"><?= ($settings['tax_type'] ?? 'VAT') === 'GST' ? 'GST' : 'VAT (' . (float)($settings['vat_percent'] ?? 10) . '%)' ?>:</span>
                    <span id="pos-tax-amount" style="color: var(--accent-orange);">0.000 <?= htmlspecialchars($settings['currency_code']) ?></span>
                </div>
                <div class="total-row" id="pos-row-delivery-fee" style="display: none; color: #38bdf8;">
                    <span>Delivery Fee:</span>
                    <span id="pos-delivery-fee-amount">+0.000 <?= htmlspecialchars($settings['currency_code']) ?></span>
                </div>
                <div class="total-row grand">
                    <span>Total Amount:</span>
                    <span id="pos-grand-total" style="color: var(--accent-green);">0.000 <?= htmlspecialchars($settings['currency_code']) ?></span>
                </div>
                <div class="total-row" id="pos-row-service-fee" style="display: none; font-size: 11.5px; color: #f43f5e; border-top: 1px dashed var(--card-border); padding-top: 4px; margin-top: 4px;">
                    <span>Service Fee (Deduction):</span>
                    <span id="pos-service-fee-amount">-0.000 <?= htmlspecialchars($settings['currency_code']) ?></span>
                </div>
                <div class="total-row" id="pos-row-net-payout" style="display: none; font-size: 13px; font-weight: 700; color: #818cf8;">
                    <span>Net Restaurant Payout:</span>
                    <span id="pos-net-payout-amount">0.000 <?= htmlspecialchars($settings['currency_code']) ?></span>
                </div>

                <button type="button" class="btn-checkout-kot" onclick="submitPosOrder(false)">
                    <span>🔥 Send to KOT (Kitchen)</span>
                </button>
                <button type="button" class="btn-checkout-pay" onclick="submitPosOrder(true)">
                    <span>💳 Send to KOT & Pay Now</span>
                </button>
                <button type="button" onclick="submitPosDeliveryRequest()" style="width: 100%; background: linear-gradient(135deg, #0284c7, #06b6d4); color: white; border: none; padding: 13px; border-radius: 12px; font-weight: 800; font-size: 13.5px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 10px; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35); transition: all 0.2s;">
                    <span>🚴 Delivery (WhatsApp Location Confirmation)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Payment Modal (When Cashier chooses Send to KOT & Pay Now) -->
    <div id="pos-pay-modal" class="modal">
        <div class="modal-content">
            <h3 style="margin-bottom: 6px;">💳 Complete Payment</h3>
            <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 15px;">Order will be dispatched to kitchen and bill cleared.</p>
            
            <div style="text-align: center; font-size: 26px; font-weight: 800; color: var(--accent-green); margin-bottom: 15px;" id="pos-modal-grand-label">
                0.000 BHD
            </div>

            <div class="pay-methods-grid">
                <button type="button" class="pay-btn-tile selected" onclick="selectPosPaymentMethod('cash', this)">
                    <span style="font-size: 22px;">💵</span>
                    <span>Cash</span>
                </button>
                <button type="button" class="pay-btn-tile" onclick="selectPosPaymentMethod('card', this)">
                    <span style="font-size: 22px;">💳</span>
                    <span>Card</span>
                </button>
                <button type="button" class="pay-btn-tile" onclick="selectPosPaymentMethod('qr_pay', this)">
                    <span style="font-size: 22px;">📱</span>
                    <span>QR Pay</span>
                </button>
            </div>

            <div style="margin-bottom: 12px;">
                <label style="font-size: 11px; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 4px;">Discount Percentage (%):</label>
                <input type="number" id="pos-discount-input" min="0" max="100" value="0" class="meta-input" placeholder="e.g. 10" oninput="calculatePosDiscount()">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="font-size: 11px; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 4px;">Gender:</label>
                <select id="pos-cust-gender" class="meta-input">
                    <option value="">Select Gender (optional)</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div style="display: flex; justify-content: space-between; font-size: 16px; font-weight: 800; border-top: 1px dashed var(--card-border); padding-top: 10px; margin-bottom: 20px;">
                <span>Net Payable:</span>
                <span id="pos-net-payable-label" style="color: var(--accent-green);">0.000 BHD</span>
            </div>

            <button type="button" class="btn-checkout-kot" onclick="confirmPosPaymentAndSubmit()" style="background: var(--primary-grad); box-shadow: 0 4px 15px rgba(99,102,241,0.35);">
                Confirm & Dispatch Order
            </button>
            <button type="button" onclick="closePosPayModal()" style="width: 100%; background: none; border: none; color: var(--text-muted); padding: 10px; margin-top: 8px; cursor: pointer; font-size: 13px;">
                Cancel
            </button>
        </div>
    </div>

    <!-- Delivery Confirmed Order Detail Modal -->
    <div id="pos-delivery-order-modal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width: 520px; max-height: 90vh; display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--card-border); padding-bottom: 12px; margin-bottom: 14px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">📍</span>
                    <div>
                        <h3 style="margin: 0; font-size: 18px;" id="deliv-modal-token">Order #---</h3>
                        <span style="font-size: 12px; color: #10b981; font-weight: 700;">Customer Location Received</span>
                    </div>
                </div>
                <button type="button" onclick="closeDeliveryOrderModal()" style="background: none; border: none; font-size: 22px; color: var(--text-muted); cursor: pointer; padding: 0 4px;">&times;</button>
            </div>
            
            <div style="flex: 1; overflow-y: auto; padding-right: 4px; display: flex; flex-direction: column; gap: 14px;">
                <!-- Customer Details Card -->
                <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--card-border); border-radius: 12px; padding: 12px;">
                    <div style="font-size: 11px; text-transform: uppercase; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; letter-spacing: 0.5px;">Customer</div>
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                        <div>
                            <div style="font-weight: 700; font-size: 15px;" id="deliv-modal-cust-name">-</div>
                            <div style="font-size: 13px; color: #38bdf8; margin-top: 2px;" id="deliv-modal-cust-phone">-</div>
                        </div>
                        <div id="deliv-modal-phone-actions" style="display: flex; gap: 6px;"></div>
                    </div>
                </div>

                <!-- Delivery Address & GPS -->
                <div style="background: rgba(16, 185, 129, 0.06); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 12px; padding: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <span style="font-size: 11px; text-transform: uppercase; font-weight: 800; color: #10b981; letter-spacing: 0.5px;">Delivery Destination</span>
                        <div id="deliv-modal-map-link"></div>
                    </div>
                    <div style="font-size: 13.5px; line-height: 1.4; color: var(--text-color);" id="deliv-modal-cust-addr">-</div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 6px; display: none;" id="deliv-modal-notes-wrap">
                        <strong>Notes:</strong> <span id="deliv-modal-notes"></span>
                    </div>
                </div>

                <!-- Order Items & Total -->
                <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--card-border); border-radius: 12px; padding: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 11px; text-transform: uppercase; font-weight: 800; color: var(--text-muted); letter-spacing: 0.5px;">Items Ordered</span>
                        <span id="deliv-modal-pay-badge"></span>
                    </div>
                    <div id="deliv-modal-items-list" style="display: flex; flex-direction: column; gap: 6px; max-height: 160px; overflow-y: auto;">
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed var(--card-border); padding-top: 10px; margin-top: 10px; font-weight: 800; font-size: 16px;">
                        <span>Total Amount:</span>
                        <span id="deliv-modal-total" style="color: var(--accent-green);">0.000 BHD</span>
                    </div>
                </div>
            </div>

            <div style="border-top: 1px solid var(--card-border); padding-top: 14px; margin-top: 14px; display: flex; gap: 10px;">
                <button type="button" onclick="closeDeliveryOrderModal()" style="flex: 1; background: rgba(255,255,255,0.08); border: 1px solid var(--card-border); color: var(--text-muted); padding: 12px; border-radius: 12px; font-weight: 700; cursor: pointer;">
                    Close
                </button>
                <button type="button" id="deliv-modal-send-btn" onclick="sendActiveDeliveryOrderToKot()" style="flex: 2; background: linear-gradient(135deg, #0284c7, #06b6d4); color: white; border: none; padding: 12px; border-radius: 12px; font-weight: 800; font-size: 14px; cursor: pointer; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.4); display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <span>🍳 Send to KOT (Kitchen)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- All Pending Delivery Orders List Modal -->
    <div id="pos-delivery-requests-list-modal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width: 650px; max-height: 85vh; display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--card-border); padding-bottom: 12px; margin-bottom: 14px;">
                <h3 style="margin: 0; font-size: 18px;">📍 Pending Customer Delivery Requests</h3>
                <button type="button" onclick="closePendingDeliveryRequestsModal()" style="background: none; border: none; font-size: 22px; color: var(--text-muted); cursor: pointer; padding: 0 4px;">&times;</button>
            </div>
            <div style="flex: 1; overflow-y: auto;" id="pos-deliv-requests-list-container">
                <!-- Dynamically populated -->
            </div>
        </div>
    </div>

    <!-- Hidden audio chimes for dispatch feedback -->
    <audio id="audio-kot" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>

    <script>
        const currencyCode = '<?= htmlspecialchars($settings['currency_code']) ?>';
        const taxType = '<?= htmlspecialchars($settings['tax_type'] ?? 'VAT') ?>';
        const vatPercent = parseFloat('<?= (float)($settings['vat_percent'] ?? 10) ?>');
        const cgstPercent = parseFloat('<?= (float)($settings['cgst_percent'] ?? 2.5) ?>');
        const sgstPercent = parseFloat('<?= (float)($settings['sgst_percent'] ?? 2.5) ?>');

        let cart = [];
        let currentOrderType = 'take_away';
        let selectedPaymentMethod = 'cash';

        // Dropdown toggle
        function toggleQrDropdown(e) {
            if (e) e.stopPropagation();
            const dropdown = document.getElementById('qr-tools-dropdown');
            if (dropdown) dropdown.classList.toggle('open');
        }

        function closeQrDropdown() {
            const dropdown = document.getElementById('qr-tools-dropdown');
            if (dropdown) dropdown.classList.remove('open');
        }

        window.addEventListener('click', function(e) {
            if (!e.target.closest('#qr-tools-dropdown')) {
                closeQrDropdown();
            }
        });

        // Theme toggle
        function toggleTheme() {
            if (document.body.classList.contains('light-theme')) {
                document.body.classList.remove('light-theme');
                localStorage.setItem('theme', 'dark');
            } else {
                document.body.classList.add('light-theme');
                localStorage.setItem('theme', 'light');
            }
        }
        if (localStorage.getItem('theme') === 'light') {
            document.body.classList.add('light-theme');
        }

        // Change Password Prompt
        function changeOwnPasswordPrompt() {
            Swal.fire({
                title: 'Change Password',
                html: '<input id="swal-curr-pass" class="swal2-input" type="password" placeholder="Current Password">' +
                      '<input id="swal-new-pass" class="swal2-input" type="password" placeholder="New Password">',
                showCancelButton: true,
                confirmButtonText: 'Change Password',
                background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6',
                preConfirm: () => {
                    const c = document.getElementById('swal-curr-pass').value;
                    const n = document.getElementById('swal-new-pass').value;
                    if (!c || !n) { Swal.showValidationMessage('Both fields required'); return false; }
                    return fetch('/user/change-password', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ current_password: c, new_password: n })
                    }).then(r => r.json()).then(res => {
                        if (!res.success) throw new Error(res.error || 'Failed');
                        return res;
                    }).catch(err => Swal.showValidationMessage(err.message));
                }
            }).then(r => {
                if (r.isConfirmed) Swal.fire({ icon: 'success', title: 'Password Changed!', timer: 1500, showConfirmButton: false });
            });
        }

        // Filter products by category or text
        let activeCategoryId = 'all';
        function selectCategory(catId, btn) {
            activeCategoryId = String(catId);
            document.querySelectorAll('.cat-chip').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');
            filterProducts();
        }

        function filterProducts() {
            const query = document.getElementById('pos-search').value.toLowerCase().trim();
            const cards = document.querySelectorAll('.product-card');

            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                const cat = card.getAttribute('data-category');
                
                const matchesCat = (activeCategoryId === 'all' || cat === activeCategoryId);
                const matchesQuery = (!query || name.includes(query));

                if (matchesCat && matchesQuery) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function getActivePlatformMarkup() {
            if (currentOrderType === 'online') {
                const platSelect = document.getElementById('pos-platform-select');
                if (platSelect && platSelect.selectedIndex >= 0) {
                    const opt = platSelect.options[platSelect.selectedIndex];
                    return parseFloat(opt.getAttribute('data-markup') || 0);
                }
            }
            return 0;
        }

        function updateDisplayedMenuPrices() {
            const markup = getActivePlatformMarkup();
            document.querySelectorAll('.product-card').forEach(card => {
                const basePrice = parseFloat(card.getAttribute('data-base-price') || 0);
                const finalPrice = markup > 0 ? (basePrice * (1 + markup / 100)) : basePrice;
                const priceSpan = card.querySelector('.product-price');
                if (priceSpan) {
                    let html = finalPrice.toFixed(window.PRICE_DECIMALS || 3) + ' ' + currencyCode;
                    if (markup > 0) {
                        html += ` <small style="font-size:10px; color:#10b981; font-weight:700; background:rgba(16,185,129,0.15); padding:1px 4px; border-radius:4px;">+${markup}%</small>`;
                    }
                    priceSpan.innerHTML = html;
                }
            });
        }

        function onPlatformChange() {
            updateDisplayedMenuPrices();
            recalculateCartPrices();
        }

        function recalculateCartPrices() {
            const markup = getActivePlatformMarkup();
            cart.forEach(item => {
                const base = (typeof item.base_price !== 'undefined') ? item.base_price : item.price;
                item.base_price = base;
                if (!item.is_custom_price) {
                    item.price = markup > 0 ? (base * (1 + markup / 100)) : base;
                }
                item.markup_percent = markup;
            });
            renderCart();
        }

        function updateItemPrice(productId, newPrice) {
            const idx = cart.findIndex(i => i.id === productId);
            if (idx === -1) return;
            const val = parseFloat(newPrice);
            if (!isNaN(val) && val >= 0) {
                cart[idx].price = val;
                cart[idx].is_custom_price = true;
            }
            renderCart();
        }

        // Order Type Tab selection
        function setOrderType(type) {
            currentOrderType = type;
            document.getElementById('tab-takeaway').classList.toggle('active', type === 'take_away');
            document.getElementById('tab-dinein').classList.toggle('active', type === 'dine_in');
            document.getElementById('tab-online').classList.toggle('active', type === 'online');
            document.getElementById('table-select-container').style.display = (type === 'dine_in') ? 'block' : 'none';
            document.getElementById('online-select-container').style.display = (type === 'online') ? 'block' : 'none';
            updateDisplayedMenuPrices();
            recalculateCartPrices();
        }

        // Cart Management
        function addToCart(product) {
            const markup = getActivePlatformMarkup();
            const basePrice = parseFloat(product.price);
            const finalPrice = markup > 0 ? (basePrice * (1 + markup / 100)) : basePrice;

            const existingIndex = cart.findIndex(item => item.id === product.id);
            if (existingIndex > -1) {
                cart[existingIndex].quantity += 1;
                cart[existingIndex].base_price = basePrice;
                if (!cart[existingIndex].is_custom_price) {
                    cart[existingIndex].price = finalPrice;
                    cart[existingIndex].markup_percent = markup;
                }
            } else {
                cart.push({
                    id: product.id,
                    name: product.name,
                    base_price: basePrice,
                    price: finalPrice,
                    markup_percent: markup,
                    is_custom_price: false,
                    quantity: 1,
                    notes: ''
                });
            }
            renderCart();
        }

        function updateQuantity(productId, delta) {
            const idx = cart.findIndex(i => i.id === productId);
            if (idx === -1) return;

            cart[idx].quantity += delta;
            if (cart[idx].quantity <= 0) {
                cart.splice(idx, 1);
            }
            renderCart();
        }

        function updateItemNote(productId, note) {
            const idx = cart.findIndex(i => i.id === productId);
            if (idx > -1) {
                cart[idx].notes = note;
            }
        }

        function clearCart() {
            cart = [];
            renderCart();
        }

        function renderCart() {
            const container = document.getElementById('cart-items-container');
            const countLabel = document.getElementById('cart-item-count');
            
            let totalQty = 0;
            let subtotal = 0;

            // Reset all card badges
            document.querySelectorAll('.product-badge-qty').forEach(b => {
                b.style.display = 'none';
                b.innerText = '0';
            });

            if (cart.length === 0) {
                container.innerHTML = `
                    <div class="cart-empty-state">
                        <div style="font-size: 42px; margin-bottom: 10px; opacity: 0.4;">🛒</div>
                        <div style="font-weight: 700; font-size: 15px; margin-bottom: 4px;">Cart is empty</div>
                        <div style="font-size: 12px;">Click any item on the left to add to this order ticket.</div>
                    </div>
                `;
            } else {
                let html = '';
                cart.forEach(item => {
                    totalQty += item.quantity;
                    const itemTotal = item.price * item.quantity;
                    subtotal += itemTotal;

                    // Update product card badge
                    const badge = document.getElementById('qty-badge-/' + item.id) || document.getElementById('qty-badge-' + item.id);
                    if (badge) {
                        badge.innerText = item.quantity;
                        badge.style.display = 'block';
                    }

                    html += `
                        <div class="cart-item-card">
                            <div class="cart-item-top">
                                <div style="flex: 1;">
                                    <div class="cart-item-title">${escapeHtml(item.name)}</div>
                                    <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px; flex-wrap: wrap;">
                                        <label style="font-size: 11px; color: var(--text-muted); font-weight: 600;">Unit Price:</label>
                                        <div style="display: inline-flex; align-items: center; gap: 3px;">
                                            <input type="number" step="0.001" min="0" 
                                                   value="${item.price.toFixed(window.PRICE_DECIMALS || 3)}" 
                                                   onchange="updateItemPrice(${item.id}, this.value)" 
                                                   style="width: 76px; padding: 2px 6px; font-size: 11.5px; font-weight: 700; border-radius: 6px; border: 1px solid var(--card-border); background: var(--bg-card); color: var(--text-color); text-align: right;" 
                                                   title="Edit item unit price (e.g. customized platform price)">
                                            <span style="font-size: 11px; color: var(--text-muted);">${currencyCode}</span>
                                        </div>
                                        ${item.is_custom_price ? '<span style="font-size: 9.5px; background: rgba(99,102,241,0.18); color: #818cf8; padding: 1px 5px; border-radius: 4px; font-weight: 700;">Custom</span>' : ''}
                                    </div>
                                </div>
                                <button onclick="updateQuantity(${item.id}, -${item.quantity})" style="background: none; border: none; color: var(--accent-red); cursor: pointer; font-size: 14px; padding: 4px;" title="Remove Item">🗑️</button>
                            </div>
                            <input type="text" class="cart-item-note-input" placeholder="Notes (e.g. No onion)" value="${escapeHtml(item.notes)}" onchange="updateItemNote(${item.id}, this.value)">
                            <div class="cart-item-bottom">
                                <div class="qty-controls">
                                    <button type="button" class="qty-btn" onclick="updateQuantity(${item.id}, -1)">−</button>
                                    <span class="qty-display">${item.quantity}</span>
                                    <button type="button" class="qty-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
                                </div>
                                <div class="cart-item-total">${itemTotal.toFixed(window.PRICE_DECIMALS || 3)} ${currencyCode}</div>
                            </div>
                        </div>
                    `;
                });
                container.innerHTML = html;
            }

            countLabel.innerText = totalQty + (totalQty === 1 ? ' item' : ' items');

            // Online fees & deductions
            let onlineDeliveryFee = 0;
            let onlineServiceFee = 0;
            let onlineDiscount = 0;
            if (currentOrderType === 'online') {
                onlineDeliveryFee = parseFloat(document.getElementById('pos-online-delivery-fee')?.value || 0) || 0;
                onlineServiceFee = parseFloat(document.getElementById('pos-online-service-fee')?.value || 0) || 0;
                onlineDiscount = parseFloat(document.getElementById('pos-online-discount')?.value || 0) || 0;
            }

            const taxableSubtotal = Math.max(0, subtotal - onlineDiscount);

            // Calculate taxes
            let taxAmount = 0;
            if (taxType === 'VAT') {
                taxAmount = taxableSubtotal * (vatPercent / 100.0);
            } else {
                taxAmount = taxableSubtotal * ((cgstPercent + sgstPercent) / 100.0);
            }
            const grandTotal = Math.max(0, taxableSubtotal + taxAmount + onlineDeliveryFee);
            const netPayout = Math.max(0, grandTotal - onlineServiceFee);

            document.getElementById('pos-subtotal').innerText = subtotal.toFixed(window.PRICE_DECIMALS || 3) + ' ' + currencyCode;
            
            const discRow = document.getElementById('pos-row-discount');
            if (discRow) {
                if (onlineDiscount > 0) {
                    discRow.style.display = 'flex';
                    document.getElementById('pos-discount-amount').innerText = '-' + onlineDiscount.toFixed(window.PRICE_DECIMALS || 3) + ' ' + currencyCode;
                } else {
                    discRow.style.display = 'none';
                }
            }

            document.getElementById('pos-tax-amount').innerText = taxAmount.toFixed(window.PRICE_DECIMALS || 3) + ' ' + currencyCode;

            const delRow = document.getElementById('pos-row-delivery-fee');
            if (delRow) {
                if (onlineDeliveryFee > 0) {
                    delRow.style.display = 'flex';
                    document.getElementById('pos-delivery-fee-amount').innerText = '+' + onlineDeliveryFee.toFixed(window.PRICE_DECIMALS || 3) + ' ' + currencyCode;
                } else {
                    delRow.style.display = 'none';
                }
            }

            document.getElementById('pos-grand-total').innerText = grandTotal.toFixed(window.PRICE_DECIMALS || 3) + ' ' + currencyCode;

            const servRow = document.getElementById('pos-row-service-fee');
            const netRow = document.getElementById('pos-row-net-payout');
            if (servRow && netRow) {
                if (currentOrderType === 'online' && onlineServiceFee > 0) {
                    servRow.style.display = 'flex';
                    netRow.style.display = 'flex';
                    document.getElementById('pos-service-fee-amount').innerText = '-' + onlineServiceFee.toFixed(window.PRICE_DECIMALS || 3) + ' ' + currencyCode;
                    document.getElementById('pos-net-payout-amount').innerText = netPayout.toFixed(window.PRICE_DECIMALS || 3) + ' ' + currencyCode;
                } else {
                    servRow.style.display = 'none';
                    netRow.style.display = 'none';
                }
            }
        }

        // Customer Phone Lookup
        function lookupPosCustomer() {
            const mobile = document.getElementById('pos-cust-mobile').value.trim();
            if (mobile.length >= 8) {
                fetch('/counter/customer/lookup?mobile=' + encodeURIComponent(mobile))
                    .then(r => r.json())
                    .then(data => {
                        if (data.success && data.exists && data.customer) {
                            const nameField = document.getElementById('pos-cust-name');
                            if (!nameField.value && data.customer.name) {
                                nameField.value = data.customer.name;
                            }
                        }
                    })
                    .catch(err => console.error(err));
            }
        }

        // Submit POS Order
        function submitPosDeliveryRequest() {
            if (cart.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Cart is Empty',
                    text: 'Please select items to create a delivery order.',
                    confirmButtonColor: '#6366f1'
                });
                return;
            }

            const custMobileInput = document.getElementById('pos-cust-mobile');
            const custNameInput = document.getElementById('pos-cust-name');
            let custMobile = custMobileInput ? custMobileInput.value.trim() : '';
            let custName = custNameInput ? custNameInput.value.trim() : '';

            Swal.fire({
                title: '🚴 Delivery Order (WhatsApp)',
                html: `
                    <div style="text-align:left; font-size:13px;">
                        <p style="color:var(--text-muted); margin-bottom:12px;">
                            We will generate a WhatsApp confirmation link with order items and request the customer's current GPS location.
                        </p>
                        <div style="margin-bottom:12px;">
                            <label style="font-weight:700; display:block; margin-bottom:4px;">Customer Mobile <span style="color:#ef4444;">* (Required)</span></label>
                            <input type="text" id="swal-cust-mobile" class="swal2-input" style="width:100%; margin:0; box-sizing:border-box;" value="${escapeHtml(custMobile)}" placeholder="e.g. 33123456" autofocus>
                        </div>
                        <div style="margin-bottom:12px;">
                            <label style="font-weight:700; display:block; margin-bottom:4px;">Customer Name (Optional)</label>
                            <input type="text" id="swal-cust-name" class="swal2-input" style="width:100%; margin:0; box-sizing:border-box;" value="${escapeHtml(custName)}" placeholder="Customer name">
                        </div>
                        <div style="margin-bottom:12px;">
                            <label style="font-weight:700; display:block; margin-bottom:6px;">Payment Method</label>
                            <div style="display:flex; gap:16px;">
                                <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                                    <input type="radio" name="swal-deliv-pay" value="cod" checked>
                                    <span>Cash on Delivery (COD)</span>
                                </label>
                                <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                                    <input type="radio" name="swal-deliv-pay" value="paid">
                                    <span>Already Paid</span>
                                </label>
                            </div>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '📲 Send WhatsApp Request',
                confirmButtonColor: '#0284c7',
                cancelButtonColor: '#64748b',
                background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6',
                preConfirm: () => {
                    const mobile = document.getElementById('swal-cust-mobile').value.trim();
                    if (!mobile) {
                        Swal.showValidationMessage('Customer Mobile number is required!');
                        return false;
                    }
                    const name = document.getElementById('swal-cust-name').value.trim();
                    const pay = document.querySelector('input[name="swal-deliv-pay"]:checked').value;
                    return { mobile: mobile, name: name, payment_type: pay };
                }
            }).then(result => {
                if (result.isConfirmed && result.value) {
                    const payload = {
                        customer_name: result.value.name,
                        customer_mobile: result.value.mobile,
                        payment_type: result.value.payment_type,
                        items: cart.map(i => ({
                            product_id: i.id,
                            quantity: i.quantity,
                            price: i.price,
                            notes: i.notes || ''
                        }))
                    };

                    Swal.fire({
                        title: 'Generating WhatsApp Request...',
                        text: 'Please wait...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    fetch('/counter/order/create-delivery-request', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    })
                    .then(async r => {
                        const data = await r.json().catch(() => null);
                        if (!r.ok || !data) {
                            throw new Error(data && data.error ? data.error : `Request failed (HTTP ${r.status})`);
                        }
                        return data;
                    })
                    .then(data => {
                        if (data.success) {
                            // Try to open WhatsApp directly
                            try {
                                window.open(data.whatsapp_url, '_blank');
                            } catch (e) {}

                            Swal.fire({
                                icon: 'success',
                                title: `Order #${data.token_number} Created! 🚴`,
                                html: `
                                    <div style="font-size:13px; text-align:left; margin-top:10px;">
                                        <div style="background:rgba(2,132,199,0.1); border:1px solid rgba(2,132,199,0.25); border-radius:10px; padding:10px; margin-bottom:12px;">
                                            <div>WhatsApp confirmation link sent to <strong>${escapeHtml(result.value.mobile)}</strong>.</div>
                                            <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">
                                                Once the customer confirms and shares location, this order will appear in the <strong>Delivery Requests</strong> tab on the Billing Counter.
                                            </div>
                                        </div>
                                        <div style="word-break:break-all; font-size:11px; color:var(--text-muted); margin-bottom:8px;">
                                            Link: <a href="${data.confirm_url}" target="_blank" style="color:#38bdf8;">${data.confirm_url}</a>
                                        </div>
                                    </div>
                                `,
                                showCancelButton: true,
                                confirmButtonText: '📲 Open WhatsApp',
                                cancelButtonText: 'Done / Next Order',
                                confirmButtonColor: '#25D366',
                                cancelButtonColor: '#0284c7'
                            }).then(choice => {
                                if (choice.isConfirmed) {
                                    window.open(data.whatsapp_url, '_blank');
                                }
                            });

                            // Clear cart
                            clearCart();
                            if (custMobileInput) custMobileInput.value = '';
                            if (custNameInput) custNameInput.value = '';
                        } else {
                            Swal.fire('Error', data.error || 'Failed to create delivery request.', 'error');
                        }
                    })
                    .catch(err => Swal.fire('Error', err.message || 'Network request failed.', 'error'));
                }
            });
        }

        function submitPosOrder(payNow) {
            if (cart.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Empty Cart',
                    text: 'Please select items to add to the order before submitting.',
                    background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                    color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
                });
                return;
            }

            if (currentOrderType === 'dine_in') {
                const table = document.getElementById('pos-table-select').value;
                if (!table) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Select Table',
                        text: 'Please choose a table number for Dine-In order.',
                        background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                        color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
                    });
                    return;
                }
            }

            if (currentOrderType === 'online') {
                const platformId = document.getElementById('pos-platform-select').value;
                const platformNum = document.getElementById('pos-platform-number').value.trim();
                if (!platformId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Select Platform',
                        text: 'Please select an Online Platform (e.g. Talabat, Jahez).',
                        background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                        color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
                    });
                    return;
                }
                if (!platformNum) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Order Number Required',
                        text: 'Please enter the Online Order Number or Reference.',
                        background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                        color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
                    });
                    return;
                }
            }

            if (payNow) {
                openPosPayModal();
                return;
            }

            // Normal Send to KOT flow
            executeOrderSubmission({ payNow: false });
        }

        function executeOrderSubmission(options) {
            const tableNum = (currentOrderType === 'dine_in') ? parseInt(document.getElementById('pos-table-select').value) : null;
            const platformId = (currentOrderType === 'online') ? parseInt(document.getElementById('pos-platform-select').value) : null;
            const platformOrderNum = (currentOrderType === 'online') ? document.getElementById('pos-platform-number').value.trim() : null;
            const custName = document.getElementById('pos-cust-name').value.trim();
            const custMobile = document.getElementById('pos-cust-mobile').value.trim();

            let onlineDeliveryFee = 0;
            let onlineServiceFee = 0;
            let onlineDiscount = 0;
            if (currentOrderType === 'online') {
                onlineDeliveryFee = parseFloat(document.getElementById('pos-online-delivery-fee')?.value || 0) || 0;
                onlineServiceFee = parseFloat(document.getElementById('pos-online-service-fee')?.value || 0) || 0;
                onlineDiscount = parseFloat(document.getElementById('pos-online-discount')?.value || 0) || 0;
            }

            const itemsPayload = cart.map(i => ({
                product_id: i.id,
                quantity: i.quantity,
                price: i.price,
                notes: i.notes || null
            }));

            // Get local station billing/counter printer config (e.g. 192.168.8.101)
            const effectiveCounter = (function() {
                let ip = '';
                let port = 0;
                try {
                    const urlParams = new URLSearchParams(window.location.search);
                    ip = urlParams.get('station_printer_ip') || urlParams.get('printer_ip') || '';
                    port = parseInt(urlParams.get('station_printer_port') || urlParams.get('printer_port') || '0') || 0;
                } catch (e) {}
                if (!ip) {
                    try {
                        const savedIp = localStorage.getItem('sands_printer_ip');
                        if (savedIp) {
                            ip = savedIp.trim();
                            port = parseInt(localStorage.getItem('sands_printer_port') || '0') || 0;
                        }
                    } catch (e) {}
                }
                if (!ip) {
                    try {
                        const localCfg = JSON.parse(localStorage.getItem('sands_kot_config') || '{}');
                        if (localCfg && localCfg.printerIp) {
                            ip = String(localCfg.printerIp).trim();
                            port = parseInt(localCfg.printerPort) || 0;
                        }
                    } catch (e) {}
                }
                if (!ip) ip = '<?= htmlspecialchars($settings['billing_printer_ip'] ?? $settings['printer_ip'] ?? '192.168.8.101') ?>';
                if (!port) port = parseInt('<?= $settings['billing_printer_port'] ?? $settings['printer_port'] ?? 9100 ?>') || 9100;
                return { ip, port };
            })();

            const counterPrinterIp = effectiveCounter.ip;
            const counterPrinterPort = effectiveCounter.port;

            // KOT Kitchen Printer Config strictly taken from Settings (default 192.168.8.102)
            const kotPrinterIp = '<?= htmlspecialchars($settings['kot_printer_ip'] ?? '192.168.8.102') ?>';
            const kotPrinterPort = parseInt('<?= $settings['kot_printer_port'] ?? 9100 ?>') || 9100;

            const payload = {
                order_type: currentOrderType,
                table_number: tableNum,
                platform_id: platformId,
                platform_order_number: platformOrderNum,
                customer_name: custName,
                customer_mobile: custMobile,
                items: itemsPayload,
                pay_now: options.payNow,
                payment_method: options.paymentMethod || 'cash',
                discount_percent: options.discountPercent || 0,
                delivery_fee: onlineDeliveryFee,
                service_fee: onlineServiceFee,
                discount_amount: onlineDiscount,
                gender: options.gender || '',
                printer_ip: counterPrinterIp,
                printer_port: counterPrinterPort
            };

            Swal.fire({
                title: 'Sending to Kitchen...',
                text: 'Creating order and dispatching KOT ticket.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            fetch('/counter/order/create-pos', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Play sound
                    try { document.getElementById('audio-kot').play(); } catch (e) {}

                    // Helper to transmit ESC/POS base64 payload to a specific printer IP & port
                    function transmitEscPosToDevice(base64Payload, targetIp, targetPort, jobName) {
                        if (!base64Payload || !targetIp) return;
                        let bridged = false;
                        try {
                            if (typeof window.AndroidPrintBridge !== 'undefined' && window.AndroidPrintBridge && typeof window.AndroidPrintBridge.printTcp === 'function') {
                                window.AndroidPrintBridge.printTcp(targetIp, targetPort, base64Payload);
                                bridged = true;
                            }
                        } catch (e) {}
                        if (!bridged && window.parent && window.parent !== window) {
                            window.parent.postMessage({
                                action: 'sands_print_escpos',
                                base64: base64Payload,
                                printer_ip: targetIp,
                                printer_port: targetPort,
                                job_name: jobName || 'POS'
                            }, '*');
                        }
                    }

                    // 1. KOT Printing: STRICTLY sent to KOT Kitchen Printer (192.168.8.102 from settings)
                    const kotRes = data.kot_print_result || data.print_result;
                    if (kotRes && kotRes.base64) {
                        const targetKotIp = kotRes.printer_ip || kotPrinterIp;
                        const targetKotPort = parseInt(kotRes.printer_port) || kotPrinterPort;
                        // Only relay via client bridge if server did not already print directly over raw socket
                        if (!kotRes.success) {
                            transmitEscPosToDevice(kotRes.base64, targetKotIp, targetKotPort, 'KOT Slip');
                        }
                    }

                    // 2. Bill Printing: ONLY when Pay Now / immediate bill, STRICTLY sent to Counter Printer (192.168.8.101)
                    if (data.bill_print_result && data.bill_print_result.base64) {
                        const targetBillIp = data.bill_print_result.printer_ip || counterPrinterIp;
                        const targetBillPort = parseInt(data.bill_print_result.printer_port) || counterPrinterPort;
                        if (!data.bill_print_result.success) {
                            transmitEscPosToDevice(data.bill_print_result.base64, targetBillIp, targetBillPort, 'Tax Invoice');
                        }
                    }

                    let successMsg = `Order #${data.order_id} dispatched to Kitchen (KOT #${data.kot_id}).`;
                    if (data.token_number) {
                        const tokenPrefix = (currentOrderType === 'online') ? 'Online Token' : 'Takeaway Token';
                        successMsg = `${tokenPrefix}: <strong>#${data.token_number}</strong><br>` + successMsg;
                    }
                    if (data.paid) {
                        successMsg += `<br><span style="color:#10b981; font-weight:bold;">Payment Cleared!</span>`;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'KOT Dispatched!',
                        html: successMsg,
                        confirmButtonText: 'Next Order',
                        confirmButtonColor: '#10b981',
                        background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                        color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
                    });

                    // Reset POS
                    clearCart();
                    document.getElementById('pos-cust-mobile').value = '';
                    document.getElementById('pos-cust-name').value = '';
                    document.getElementById('pos-table-select').value = '';
                    const platSel = document.getElementById('pos-platform-select');
                    if (platSel) platSel.value = '';
                    const platNum = document.getElementById('pos-platform-number');
                    if (platNum) platNum.value = '';
                    if (document.getElementById('pos-online-delivery-fee')) document.getElementById('pos-online-delivery-fee').value = '0.000';
                    if (document.getElementById('pos-online-service-fee')) document.getElementById('pos-online-service-fee').value = '0.000';
                    if (document.getElementById('pos-online-discount')) document.getElementById('pos-online-discount').value = '0.000';
                    setOrderType('take_away');
                    closePosPayModal();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: data.error || 'Failed to dispatch order.',
                        background: document.body.classList.contains('light-theme') ? '#fff' : '#111827',
                        color: document.body.classList.contains('light-theme') ? '#1f2937' : '#f3f4f6'
                    });
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not connect to server.' });
            });
        }

        // Pay Modal Controls
        function openPosPayModal() {
            const grandTotalStr = document.getElementById('pos-grand-total').innerText;
            document.getElementById('pos-modal-grand-label').innerText = grandTotalStr;
            document.getElementById('pos-discount-input').value = '0';
            calculatePosDiscount();
            document.getElementById('pos-pay-modal').style.display = 'flex';
        }

        function closePosPayModal() {
            document.getElementById('pos-pay-modal').style.display = 'none';
        }

        function selectPosPaymentMethod(method, btn) {
            selectedPaymentMethod = method;
            document.querySelectorAll('.pay-btn-tile').forEach(b => b.classList.remove('selected'));
            if (btn) btn.classList.add('selected');
        }

        function calculatePosDiscount() {
            const discountPercent = parseFloat(document.getElementById('pos-discount-input').value) || 0;
            
            let subtotal = 0;
            cart.forEach(i => subtotal += (i.price * i.quantity));
            let tax = (taxType === 'VAT') ? subtotal * (vatPercent / 100.0) : subtotal * ((cgstPercent + sgstPercent) / 100.0);
            let grand = subtotal + tax;

            let discountAmount = grand * (discountPercent / 100.0);
            let net = Math.max(0, grand - discountAmount);

            document.getElementById('pos-net-payable-label').innerText = net.toFixed(window.PRICE_DECIMALS || 3) + ' ' + currencyCode;
        }

        function confirmPosPaymentAndSubmit() {
            const discountPercent = parseFloat(document.getElementById('pos-discount-input').value) || 0;
            const gender = document.getElementById('pos-cust-gender').value;

            executeOrderSubmission({
                payNow: true,
                paymentMethod: selectedPaymentMethod,
                discountPercent: discountPercent,
                gender: gender
            });
        }

        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Utilities from counter_display (QR modals & Table Status)
        function showWaiterLoginQr() {
            const protocol = window.location.protocol;
            const host = window.location.host;
            const basePath = window.location.pathname.endsWith('/') ? window.location.pathname.slice(0, -1) : window.location.pathname;
            const rootPath = basePath.replace(/\/(admin|counter|kot).*$/, '');
            const loginUrl = protocol + '//' + host + rootPath + '/login';
            
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
                        <div style="margin-top: 16px; display: flex; gap: 8px; justify-content: center; flex-wrap: wrap;">
                            <button type="button" onclick="downloadQrDirectly('${loginUrl}', 'Waiter_Login_QR.png', '1000x1000')" style="background: var(--primary-grad); color: white; padding: 10px 18px; border-radius: 10px; font-weight: 700; font-size: 13px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(99,102,241,0.3); transition: transform 0.15s;">
                                <span>📥 Download High-Res QR (PNG)</span>
                            </button>
                            <button type="button" onclick="printQrPoster('${loginUrl}', '📱 Waiter Login', 'Scan with phone camera to log in to system')" style="background: rgba(255,255,255,0.08); border: 1px solid var(--card-border); color: var(--text-color); padding: 10px 16px; border-radius: 10px; font-weight: 600; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: transform 0.15s;">
                                <span>🖨️ Print Standee</span>
                            </button>
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

        function showTakeawayQr() {
            const protocol = window.location.protocol;
            const host = window.location.host;
            const basePath = window.location.pathname.endsWith('/') ? window.location.pathname.slice(0, -1) : window.location.pathname;
            const rootPath = basePath.replace(/\/(admin|counter|kot).*$/, '');
            const takeawayUrl = protocol + '//' + host + rootPath + '/takeaway';
            
            const qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + encodeURIComponent(takeawayUrl);
            
            Swal.fire({
                title: '🛍️ Take Away Menu Link',
                html: `
                    <div style="margin: 15px 0;">
                        <img src="${qrImageUrl}" alt="Take Away QR Code" style="width: 220px; height: 220px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: 4px solid white;">
                        <p style="font-size: 13px; margin-top: 15px; color: var(--text-muted);">Customers can scan this QR code to access the take-away menu and place an order.</p>
                        <div style="background: rgba(0,0,0,0.05); padding: 8px 12px; border-radius: 8px; font-size: 12px; font-family: monospace; word-break: break-all; color: var(--text-color); border: 1px solid var(--card-border); margin-top: 10px;">
                            ${takeawayUrl}
                        </div>
                        <div style="margin-top: 16px; display: flex; gap: 8px; justify-content: center; flex-wrap: wrap;">
                            <button type="button" onclick="downloadQrDirectly('${takeawayUrl}', 'TakeAway_Menu_QR.png', '1000x1000')" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white; padding: 10px 18px; border-radius: 10px; font-weight: 700; font-size: 13px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(245,158,11,0.3); transition: transform 0.15s;">
                                <span>📥 Download High-Res QR (PNG)</span>
                            </button>
                            <button type="button" onclick="printQrPoster('${takeawayUrl}', '🛍️ Take Away Ordering', 'Scan with phone camera to order take away')" style="background: rgba(255,255,255,0.08); border: 1px solid var(--card-border); color: var(--text-color); padding: 10px 16px; border-radius: 10px; font-weight: 600; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: transform 0.15s;">
                                <span>🖨️ Print Standee</span>
                            </button>
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

        function downloadQrDirectly(url, filename, size = '1000x1000') {
            const downloadUrl = '/download_qr.php?data=' + encodeURIComponent(url) + '&size=' + encodeURIComponent(size) + '&filename=' + encodeURIComponent(filename);
            let iframe = document.getElementById('download-qr-iframe');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'download-qr-iframe';
                iframe.style.display = 'none';
                document.body.appendChild(iframe);
            }
            iframe.src = downloadUrl;
        }

        function printQrPoster(url, title, subtitle) {
            const qr1000 = 'https://api.qrserver.com/v1/create-qr-code/?size=1000x1000&format=png&margin=10&data=' + encodeURIComponent(url);
            const restName = '<?= addslashes(htmlspecialchars($settings['restaurant_name'] ?? 'Restaurant')) ?>';
            const logoPath = '<?= !empty($settings['logo_path']) ? '/' . ltrim(addslashes(htmlspecialchars($settings['logo_path'])), '/') : '' ?>';
            const logoHtml = logoPath ? '<img src="' + logoPath + '" class="logo-img">' : '';

            const printWin = window.open('', '_blank', 'width=700,height=900');
            if (!printWin) {
                alert('Please allow popups to print the QR poster.');
                return;
            }

            printWin.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>${title} - ${restName}</title>
                    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
                    <style>
                        @page { size: auto; margin: 12mm; }
                        * { box-sizing: border-box; }
                        body {
                            font-family: 'Outfit', sans-serif;
                            text-align: center;
                            color: #111827;
                            background: #ffffff;
                            margin: 0;
                            padding: 20px;
                        }
                        .poster-card {
                            max-width: 480px;
                            margin: 0 auto;
                            border: 3px solid #111827;
                            border-radius: 28px;
                            padding: 35px 25px;
                            box-shadow: 0 10px 40px rgba(0,0,0,0.06);
                        }
                        .logo-img { max-height: 55px; margin-bottom: 12px; object-fit: contain; }
                        .restaurant-title { font-size: 26px; font-weight: 800; color: #111827; letter-spacing: -0.5px; margin-bottom: 6px; }
                        .poster-badge {
                            display: inline-block;
                            background: #6366f1;
                            color: white;
                            font-size: 15px;
                            font-weight: 700;
                            padding: 7px 20px;
                            border-radius: 20px;
                            margin: 10px 0 20px;
                            letter-spacing: 0.3px;
                        }
                        .qr-frame {
                            background: #ffffff;
                            padding: 16px;
                            display: inline-block;
                            border-radius: 22px;
                            border: 2px solid #e5e7eb;
                            box-shadow: 0 12px 30px rgba(0,0,0,0.08);
                        }
                        .qr-frame img { width: 300px; height: 300px; display: block; border-radius: 8px; }
                        .scan-note { font-size: 16px; font-weight: 700; color: #374151; margin-top: 22px; line-height: 1.4; }
                        .scan-subnote { font-size: 13px; color: #6b7280; margin-top: 6px; }
                        .url-box {
                            margin-top: 16px;
                            background: #f3f4f6;
                            padding: 10px 16px;
                            border-radius: 12px;
                            font-family: monospace;
                            font-size: 13px;
                            color: #4b5563;
                            word-break: break-all;
                            border: 1px solid #e5e7eb;
                        }
                        .no-print-bar {
                            margin-bottom: 20px;
                            display: flex;
                            justify-content: center;
                            gap: 10px;
                        }
                        .btn-print-action {
                            background: #111827;
                            color: white;
                            border: none;
                            padding: 10px 22px;
                            border-radius: 10px;
                            font-family: inherit;
                            font-weight: 700;
                            font-size: 14px;
                            cursor: pointer;
                        }
                        @media print {
                            body { padding: 0; }
                            .poster-card { box-shadow: none; border-color: #000; }
                            .no-print-bar { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <div class="no-print-bar">
                        <button class="btn-print-action" onclick="window.print()">🖨️ Print Now</button>
                    </div>
                    <div class="poster-card">
                        ${logoHtml}
                        <div class="restaurant-title">${restName}</div>
                        <div class="poster-badge">${title}</div>
                        <div>
                            <div class="qr-frame">
                                <img src="${qr1000}" alt="QR Code" onload="setTimeout(() => window.print(), 350)">
                            </div>
                        </div>
                        <div class="scan-note">${subtitle}</div>
                        <div class="scan-subnote">Open your phone camera & scan to start</div>
                        <div class="url-box">${url}</div>
                    </div>
                </body>
                </html>
            `);
            printWin.document.close();
        }

        function showEngagedTablesModal() {
            window.location.href = '/counter';
        }

        function showOnlineOrderModal() {
            setOrderType('online');
            const platSelect = document.getElementById('pos-platform-select');
            if (platSelect) {
                platSelect.focus();
                platSelect.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        /* Real-Time Customer Location & Delivery Order Notifications */
        let activeDeliveryModalOrder = null;
        let lastNotifiedDeliveryIds = new Set();
        let allPendingDeliveryRequests = [];
        let deliveryOrdersById = {};
        let isInitialDeliveryPoll = true;

        function playNotificationChime() {
            try {
                const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                if (!AudioContextClass) return;
                const ctx = new AudioContextClass();
                if (ctx.state === 'suspended') {
                    ctx.resume();
                }
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                
                const now = ctx.currentTime;
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(523.25, now);       // C5
                osc.frequency.setValueAtTime(659.25, now + 0.12); // E5
                osc.frequency.setValueAtTime(783.99, now + 0.24); // G5
                osc.frequency.setValueAtTime(1046.50, now + 0.36);// C6
                
                gain.gain.setValueAtTime(0.4, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.75);
                
                osc.start(now);
                osc.stop(now + 0.75);
            } catch (e) {
                // audio might be blocked if no user interaction yet
            }
        }

        function pollDeliveryRequests() {
            fetch('/counter/delivery/requests?_=' + Date.now(), { cache: 'no-store' })
                .then(r => r.json())
                .then(data => {
                    if (!data || !data.success) return;
                    const requests = data.requests || [];
                    allPendingDeliveryRequests = requests;
                    
                    // Index by id
                    requests.forEach(r => {
                        deliveryOrdersById[r.id] = r;
                    });
                    
                    // Filter for requests that have location received and not yet cooking in kitchen
                    const locationReceivedList = requests.filter(r => r.status === 'location_received');
                    
                    // Update Header Badge
                    const badgeBtn = document.getElementById('pos-deliv-badge-btn');
                    const badgeCount = document.getElementById('pos-deliv-badge-count');
                    if (badgeBtn && badgeCount) {
                        if (locationReceivedList.length > 0) {
                            badgeCount.innerText = locationReceivedList.length;
                            badgeBtn.style.display = 'inline-flex';
                        } else {
                            badgeBtn.style.display = 'none';
                        }
                    }

                    // Detect newly confirmed orders
                    let newlyConfirmed = [];
                    locationReceivedList.forEach(order => {
                        if (!lastNotifiedDeliveryIds.has(order.id)) {
                            newlyConfirmed.push(order);
                        }
                    });

                    // If it's the very first poll, populate the set with existing orders so we don't spam popups on initial page load
                    if (isInitialDeliveryPoll) {
                        isInitialDeliveryPoll = false;
                        locationReceivedList.forEach(order => lastNotifiedDeliveryIds.add(order.id));
                        return;
                    }

                    // If new location confirmation detected!
                    if (newlyConfirmed.length > 0) {
                        newlyConfirmed.forEach(order => lastNotifiedDeliveryIds.add(order.id));
                        playNotificationChime();
                        
                        const latestOrder = newlyConfirmed[0];
                        const custName = latestOrder.customer_name || 'Customer';
                        const token = latestOrder.token_number || latestOrder.order_id;
                        
                        Swal.fire({
                            icon: 'success',
                            title: `📍 Order Confirmed by Customer!`,
                            html: `
                                <div style="font-size:14px; text-align:center; padding:6px 0;">
                                    <div style="font-size:22px; font-weight:800; color:#38bdf8; margin-bottom:6px;">Token #${escapeHtml(token)}</div>
                                    <div style="font-weight:700; font-size:15px; margin-bottom:4px;">${escapeHtml(custName)} (${escapeHtml(latestOrder.customer_mobile)})</div>
                                    <div style="color:var(--text-muted); font-size:12.5px; margin-bottom:12px;">Customer has confirmed the order & shared delivery location.</div>
                                    <div style="background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.25); border-radius:10px; padding:10px; text-align:left; font-size:12.5px; line-height:1.4;">
                                        <strong>📍 Address:</strong> ${escapeHtml(latestOrder.customer_address || 'GPS Coordinates received')}
                                    </div>
                                </div>
                            `,
                            showCancelButton: true,
                            confirmButtonText: '👀 View Order & Send to KOT',
                            cancelButtonText: 'Later',
                            confirmButtonColor: '#0284c7',
                            cancelButtonColor: '#64748b',
                            backdrop: `rgba(0,0,0,0.65)`
                        }).then(result => {
                            if (result.isConfirmed) {
                                openDeliveryOrderModal(latestOrder);
                            }
                        });
                    }
                })
                .catch(err => console.error('Delivery poll error:', err));
        }

        function openDeliveryOrderModalById(id) {
            const order = deliveryOrdersById[id];
            if (order) openDeliveryOrderModal(order);
        }

        function openDeliveryOrderModal(order) {
            activeDeliveryModalOrder = order;
            const token = order.token_number || order.order_id;
            document.getElementById('deliv-modal-token').innerText = 'Order #' + token;
            document.getElementById('deliv-modal-cust-name').innerText = order.customer_name || 'Guest Customer';
            document.getElementById('deliv-modal-cust-phone').innerText = '📱 ' + (order.customer_mobile || '-');
            
            // Phone actions (call & WhatsApp)
            let cleanPhone = (order.customer_mobile || '').replace(/[^0-9]/g, '');
            let waPhone = cleanPhone.length === 8 ? '973' + cleanPhone : cleanPhone;
            document.getElementById('deliv-modal-phone-actions').innerHTML = `
                <a href="tel:${escapeHtml(order.customer_mobile)}" style="background:rgba(2,132,199,0.15); border:1px solid #0284c7; color:#38bdf8; padding:5px 10px; border-radius:8px; font-size:12px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">📞 Call</a>
                <a href="https://wa.me/${waPhone}" target="_blank" style="background:rgba(37,211,102,0.15); border:1px solid #25D366; color:#25D366; padding:5px 10px; border-radius:8px; font-size:12px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">💬 WhatsApp</a>
            `;

            // Map link
            if (order.google_maps_link) {
                document.getElementById('deliv-modal-map-link').innerHTML = `
                    <a href="${escapeHtml(order.google_maps_link)}" target="_blank" style="background:linear-gradient(135deg, #0284c7, #06b6d4); color:white; padding:4px 10px; border-radius:6px; font-size:11.5px; font-weight:800; text-decoration:none; display:inline-flex; align-items:center; gap:4px; box-shadow:0 2px 8px rgba(2,132,199,0.3);">🗺️ Google Maps</a>
                `;
            } else {
                document.getElementById('deliv-modal-map-link').innerHTML = '';
            }

            // Address
            document.getElementById('deliv-modal-cust-addr').innerText = order.customer_address || 'No text address entered (GPS Pin received)';
            
            // Notes
            const notesWrap = document.getElementById('deliv-modal-notes-wrap');
            if (order.delivery_notes) {
                notesWrap.style.display = 'block';
                document.getElementById('deliv-modal-notes').innerText = order.delivery_notes;
            } else {
                notesWrap.style.display = 'none';
            }

            // Payment badge
            const payBadge = document.getElementById('deliv-modal-pay-badge');
            const totalAmount = parseFloat(order.order_total || order.cod_amount || 0).toFixed(window.PRICE_DECIMALS || 3);
            if (order.payment_type === 'cod') {
                payBadge.innerHTML = `<span style="background:rgba(245,158,11,0.15); color:#f59e0b; border:1px solid rgba(245,158,11,0.3); padding:3px 8px; border-radius:6px; font-size:11px; font-weight:800;">💵 Cash on Delivery</span>`;
            } else {
                payBadge.innerHTML = `<span style="background:rgba(16,185,129,0.15); color:#10b981; border:1px solid rgba(16,185,129,0.3); padding:3px 8px; border-radius:6px; font-size:11px; font-weight:800;">✅ Already Paid</span>`;
            }
            document.getElementById('deliv-modal-total').innerText = `${totalAmount} ${currencyCode}`;

            // Items list
            const itemsList = document.getElementById('deliv-modal-items-list');
            let itemsHtml = '';
            (order.items || []).forEach(it => {
                const itemTotal = (parseFloat(it.price || 0) * parseInt(it.quantity || 1)).toFixed(window.PRICE_DECIMALS || 3);
                itemsHtml += `
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:4px 0; border-bottom:1px solid rgba(255,255,255,0.04); font-size:13px;">
                        <div>
                            <span style="font-weight:700; color:#38bdf8;">${it.quantity}x</span> 
                            <span>${escapeHtml(it.product_name)}</span>
                            ${it.notes ? `<div style="font-size:11px; color:var(--text-muted); padding-left:18px;">↳ ${escapeHtml(it.notes)}</div>` : ''}
                        </div>
                        <div style="font-weight:600;">${itemTotal} ${currencyCode}</div>
                    </div>
                `;
            });
            itemsList.innerHTML = itemsHtml || '<div style="color:var(--text-muted); font-size:12px;">No item details</div>';

            const sendBtn = document.getElementById('deliv-modal-send-btn');
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<span>🍳 Send to KOT (Kitchen)</span>';
            }

            // Show modal
            document.getElementById('pos-delivery-order-modal').style.display = 'flex';
        }

        function closeDeliveryOrderModal() {
            document.getElementById('pos-delivery-order-modal').style.display = 'none';
            activeDeliveryModalOrder = null;
        }

        function sendActiveDeliveryOrderToKot() {
            if (!activeDeliveryModalOrder) return;
            const order = activeDeliveryModalOrder;
            const btn = document.getElementById('deliv-modal-send-btn');
            btn.disabled = true;
            btn.innerHTML = '<span>⏳ Sending to KOT...</span>';

            const formData = new FormData();
            formData.append('delivery_id', order.id);

            fetch('/counter/delivery/send-request-kot', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    closeDeliveryOrderModal();
                    Swal.fire({
                        icon: 'success',
                        title: 'Dispatched to Kitchen! 🍳',
                        text: `Order #${order.token_number || order.order_id} sent to KOT. The kitchen will mark it ready.`,
                        timer: 2200,
                        showConfirmButton: false
                    });
                    pollDeliveryRequests();
                } else {
                    Swal.fire('Error', data.error || 'Failed to dispatch to KOT.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<span>🍳 Send to KOT (Kitchen)</span>';
                }
            })
            .catch(err => {
                Swal.fire('Error', 'Network request failed.', 'error');
                btn.disabled = false;
                btn.innerHTML = '<span>🍳 Send to KOT (Kitchen)</span>';
            });
        }

        function openPendingDeliveryRequestsModal() {
            const container = document.getElementById('pos-deliv-requests-list-container');
            const locOrders = allPendingDeliveryRequests.filter(r => r.status === 'location_received');

            if (locOrders.length === 0) {
                container.innerHTML = `<div style="text-align:center; padding:30px; color:var(--text-muted);">No pending customer location confirmations.</div>`;
            } else {
                let html = '<div style="display:flex; flex-direction:column; gap:12px;">';
                locOrders.forEach(o => {
                    const token = o.token_number || o.order_id;
                    const items = (o.items || []).map(i => `${i.quantity}x ${escapeHtml(i.product_name)}`).join(', ');
                    const total = parseFloat(o.order_total || o.cod_amount || 0).toFixed(window.PRICE_DECIMALS || 3);
                    
                    html += `
                        <div style="background:rgba(255,255,255,0.03); border:1px solid var(--card-border); border-radius:14px; padding:14px; display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                            <div style="flex:1; min-width:220px;">
                                <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                                    <span style="font-size:16px; font-weight:800; color:#38bdf8;">Token #${escapeHtml(token)}</span>
                                    <span style="background:rgba(16,185,129,0.15); color:#10b981; border:1px solid rgba(16,185,129,0.3); padding:2px 7px; border-radius:5px; font-size:11px; font-weight:700;">📍 Location Ready</span>
                                </div>
                                <div style="font-weight:700; font-size:13.5px;">${escapeHtml(o.customer_name || 'Customer')} - <a href="tel:${escapeHtml(o.customer_mobile)}" style="color:#38bdf8; text-decoration:none;">${escapeHtml(o.customer_mobile)}</a></div>
                                <div style="font-size:12px; color:var(--text-muted); margin-top:3px; max-width:320px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">🏠 ${escapeHtml(o.customer_address || 'GPS Coordinates')}</div>
                                <div style="font-size:12px; color:var(--accent-green); font-weight:700; margin-top:2px;">${total} ${currencyCode} • ${items}</div>
                            </div>
                            <div style="display:flex; gap:8px;">
                                <button type="button" onclick="closePendingDeliveryRequestsModal(); openDeliveryOrderModalById(${o.id});" style="background:rgba(255,255,255,0.08); border:1px solid var(--card-border); color:var(--text-color); padding:8px 12px; border-radius:8px; font-size:12px; font-weight:700; cursor:pointer;">
                                    Details
                                </button>
                                <button type="button" onclick="sendDeliveryKotDirect(${o.id}, '${escapeHtml(token)}')" style="background:linear-gradient(135deg, #0284c7, #06b6d4); color:white; border:none; padding:8px 14px; border-radius:8px; font-size:12.5px; font-weight:800; cursor:pointer; box-shadow:0 3px 10px rgba(2,132,199,0.35);">
                                    🍳 Send to KOT
                                </button>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
            }

            document.getElementById('pos-delivery-requests-list-modal').style.display = 'flex';
        }

        function closePendingDeliveryRequestsModal() {
            document.getElementById('pos-delivery-requests-list-modal').style.display = 'none';
        }

        function sendDeliveryKotDirect(deliveryId, tokenNumber) {
            const formData = new FormData();
            formData.append('delivery_id', deliveryId);

            fetch('/counter/delivery/send-request-kot', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Dispatched to Kitchen! 🍳',
                        text: `Order #${tokenNumber} sent to KOT.`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    closePendingDeliveryRequestsModal();
                    pollDeliveryRequests();
                } else {
                    Swal.fire('Error', data.error || 'Failed to dispatch to KOT.', 'error');
                }
            })
            .catch(() => Swal.fire('Error', 'Network request failed.', 'error'));
        }

        // Start background delivery requests polling
        pollDeliveryRequests();
        setInterval(pollDeliveryRequests, 4000);
    </script>
</body>
</html>
