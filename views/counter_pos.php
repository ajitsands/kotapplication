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
                    <div style="display: flex; gap: 8px;">
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
                <div class="total-row">
                    <span id="pos-tax-label"><?= ($settings['tax_type'] ?? 'VAT') === 'GST' ? 'GST' : 'VAT (' . (float)($settings['vat_percent'] ?? 10) . '%)' ?>:</span>
                    <span id="pos-tax-amount" style="color: var(--accent-orange);">0.000 <?= htmlspecialchars($settings['currency_code']) ?></span>
                </div>
                <div class="total-row grand">
                    <span>Total Amount:</span>
                    <span id="pos-grand-total" style="color: var(--accent-green);">0.000 <?= htmlspecialchars($settings['currency_code']) ?></span>
                </div>

                <button type="button" class="btn-checkout-kot" onclick="submitPosOrder(false)">
                    <span>🔥 Send to KOT (Kitchen)</span>
                </button>
                <button type="button" class="btn-checkout-pay" onclick="submitPosOrder(true)">
                    <span>💳 Send to KOT & Pay Now</span>
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
                item.price = markup > 0 ? (base * (1 + markup / 100)) : base;
                item.markup_percent = markup;
            });
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
                cart[existingIndex].price = finalPrice;
                cart[existingIndex].markup_percent = markup;
            } else {
                cart.push({
                    id: product.id,
                    name: product.name,
                    base_price: basePrice,
                    price: finalPrice,
                    markup_percent: markup,
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
                                <div>
                                    <div class="cart-item-title">${escapeHtml(item.name)}</div>
                                    <div class="cart-item-price">${item.price.toFixed(window.PRICE_DECIMALS || 3)} ${currencyCode}</div>
                                </div>
                                <button onclick="updateQuantity(${item.id}, -${item.quantity})" style="background: none; border: none; color: var(--accent-red); cursor: pointer; font-size: 14px;" title="Remove Item">🗑️</button>
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

            // Calculate taxes
            let taxAmount = 0;
            if (taxType === 'VAT') {
                taxAmount = subtotal * (vatPercent / 100.0);
            } else {
                taxAmount = subtotal * ((cgstPercent + sgstPercent) / 100.0);
            }
            const grandTotal = subtotal + taxAmount;

            document.getElementById('pos-subtotal').innerText = subtotal.toFixed(window.PRICE_DECIMALS || 3) + ' ' + currencyCode;
            document.getElementById('pos-tax-amount').innerText = taxAmount.toFixed(window.PRICE_DECIMALS || 3) + ' ' + currencyCode;
            document.getElementById('pos-grand-total').innerText = grandTotal.toFixed(window.PRICE_DECIMALS || 3) + ' ' + currencyCode;
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

            const itemsPayload = cart.map(i => ({
                product_id: i.id,
                quantity: i.quantity,
                price: i.price,
                notes: i.notes || null
            }));

            // Get local station printer config
            const effective = (typeof getEffectivePrinterConfig === 'function') 
                ? getEffectivePrinterConfig() 
                : (function() {
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
                    if (!ip) ip = '<?= $settings['printer_ip'] ?? '192.168.8.101' ?>';
                    if (!port) port = parseInt('<?= $settings['printer_port'] ?? 9100 ?>') || 9100;
                    return { ip, port };
                })();

            const printerIp = effective.ip;
            const printerPort = effective.port;

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
                gender: options.gender || '',
                printer_ip: printerIp,
                printer_port: printerPort
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

                    // Relay KOT print job to driver app or native bridge if running on client
                    if (data.print_result && data.print_result.base64) {
                        let bridged = false;
                        try {
                            if (typeof window.AndroidPrintBridge !== 'undefined' && window.AndroidPrintBridge && typeof window.AndroidPrintBridge.printTcp === 'function') {
                                window.AndroidPrintBridge.printTcp(printerIp, printerPort, data.print_result.base64);
                                bridged = true;
                            }
                        } catch (e) {}
                        if (!bridged && window.parent && window.parent !== window) {
                            window.parent.postMessage({
                                action: 'sands_print_escpos',
                                base64: data.print_result.base64,
                                printer_ip: printerIp,
                                printer_port: printerPort
                            }, '*');
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
    </script>
</body>
</html>
