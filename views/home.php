<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestoFlow | Restaurant KOT & Billing System</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #070a13;
            --surface-color: rgba(255, 255, 255, 0.02);
            --surface-hover: rgba(255, 255, 255, 0.05);
            --border-color: rgba(255, 255, 255, 0.08);
            --border-hover: rgba(255, 255, 255, 0.16);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --primary: #6366f1;
            --primary-light: #818cf8;
            --primary-grad: linear-gradient(135deg, #6366f1, #a855f7);
            --primary-grad-hover: linear-gradient(135deg, #4f46e5, #9333ea);
            --accent: #f43f5e;
            --success: #10b981;
            --warning: #f59e0b;
            --info: #06b6d4;
            --card-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
            --glass-bg: rgba(15, 23, 42, 0.6);
            --glow-color: rgba(99, 102, 241, 0.15);
        }

        body.light-theme {
            --bg-color: #f8fafc;
            --surface-color: rgba(255, 255, 255, 0.7);
            --surface-hover: rgba(255, 255, 255, 0.9);
            --border-color: rgba(15, 23, 42, 0.06);
            --border-hover: rgba(15, 23, 42, 0.12);
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #64748b;
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --primary-grad: linear-gradient(135deg, #4f46e5, #9333ea);
            --primary-grad-hover: linear-gradient(135deg, #3730a3, #7e22ce);
            --accent: #e11d48;
            --success: #059669;
            --warning: #d97706;
            --info: #0891b2;
            --card-shadow: 0 15px 30px rgba(15, 23, 42, 0.05);
            --glass-bg: rgba(255, 255, 255, 0.4);
            --glow-color: rgba(79, 70, 229, 0.08);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
            overflow-x: hidden;
            line-height: 1.6;
            background-image: 
                radial-gradient(at 10% 10%, var(--glow-color) 0px, transparent 40%),
                radial-gradient(at 90% 90%, var(--glow-color) 0px, transparent 40%);
            background-attachment: fixed;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Scrollbar styles */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: var(--bg-color);
        }
        ::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--text-muted);
        }

        /* Container & Grid layout utilities */
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Header Styles */
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        header.scrolled {
            padding: 8px 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .nav-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 72px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--text-primary);
            font-family: 'Space Grotesk', sans-serif;
            font-size: 22px;
            font-weight: 700;
        }

        .logo-symbol {
            width: 38px;
            height: 38px;
            background: var(--primary-grad);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            box-shadow: 0 8px 16px rgba(99, 102, 241, 0.3);
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 32px;
            list-style: none;
        }

        .nav-link {
            text-decoration: none;
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 15px;
            transition: color 0.2s ease;
            position: relative;
            padding: 6px 0;
        }

        .nav-link:hover {
            color: var(--text-primary);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: width 0.2s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .theme-btn {
            background: var(--surface-color);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .theme-btn:hover {
            background: var(--surface-hover);
            border-color: var(--border-hover);
            transform: scale(1.05);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 12px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary-grad);
            border: none;
            color: white;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            background: var(--primary-grad-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }

        .btn-secondary {
            background: var(--surface-color);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .btn-secondary:hover {
            background: var(--surface-hover);
            border-color: var(--border-hover);
            transform: translateY(-2px);
        }

        .btn-lg {
            padding: 14px 28px;
            font-size: 16px;
            border-radius: 14px;
        }

        /* Mobile Menu Toggle */
        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text-primary);
            font-size: 24px;
            cursor: pointer;
        }

        /* Hero Section Styles */
        .hero {
            padding: 160px 0 100px;
            text-align: center;
            position: relative;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.2);
            color: var(--primary-light);
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 24px;
            animation: fadeInDown 0.8s ease;
        }

        .hero h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 56px;
            font-weight: 700;
            line-height: 1.15;
            max-width: 900px;
            margin: 0 auto 24px;
            letter-spacing: -1px;
            animation: fadeInUp 0.8s ease;
        }

        .hero h1 span {
            background: var(--primary-grad);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            font-size: 18px;
            color: var(--text-secondary);
            max-width: 680px;
            margin: 0 auto 40px;
            animation: fadeInUp 1s ease;
        }

        .hero-ctas {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            margin-bottom: 60px;
            animation: fadeInUp 1.2s ease;
        }

        /* Showcase Banner/Visual illustration */
        .hero-illustration {
            max-width: 1000px;
            margin: 0 auto;
            background: var(--surface-color);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 12px;
            box-shadow: var(--card-shadow);
            position: relative;
            overflow: hidden;
            animation: scaleIn 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .hero-banner-inner {
            border-radius: 16px;
            background: #0d1222;
            overflow: hidden;
            aspect-ratio: 16/9;
            position: relative;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Mockup Grid Inside Banner */
        .grid-mockup {
            display: grid;
            grid-template-columns: 2.5fr 1fr;
            grid-template-rows: 1fr 1fr;
            gap: 12px;
            padding: 16px;
            height: 100%;
            width: 100%;
            background: linear-gradient(135deg, #090c15 0%, #151b30 100%);
        }

        /* Mockup items */
        .mock-panel {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 14px;
            position: relative;
            overflow: hidden;
            color: #fff;
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            backdrop-filter: blur(10px);
        }

        .mock-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            padding-bottom: 8px;
        }

        .mock-title {
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #a5b4fc;
        }

        .mock-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--success);
        }

        .mock-body {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Layout blocks */
        .mock-row {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
        }

        .mock-bar {
            height: 8px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 4px;
            flex-grow: 1;
        }

        .mock-bar.short { width: 40%; flex-grow: 0; }
        .mock-bar.medium { width: 60%; flex-grow: 0; }
        .mock-bar.accent { background: var(--primary); }
        .mock-bar.success { background: var(--success); }
        .mock-bar.warning { background: var(--warning); }

        .mock-circle {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
        }

        /* Specific Mock panels in hero mockup */
        .mock-admin {
            grid-column: 1 / 2;
            grid-row: 1 / 3;
            background: rgba(15, 23, 42, 0.5);
            border-color: rgba(99, 102, 241, 0.15);
        }

        .mock-analytics-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 16px;
        }

        .mock-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.05);
            padding: 10px;
            border-radius: 8px;
            text-align: left;
        }

        .mock-card-val {
            font-size: 16px;
            font-weight: 700;
            margin-top: 4px;
            color: #fff;
        }

        .mock-chart-container {
            flex-grow: 1;
            display: flex;
            align-items: flex-end;
            gap: 8px;
            height: 100px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .mock-chart-bar {
            width: 100%;
            background: linear-gradient(to top, var(--primary), #c084fc);
            border-radius: 4px 4px 0 0;
            animation: pulseHeight 3s infinite ease-in-out;
        }

        .mock-kitchen {
            grid-column: 2 / 3;
            grid-row: 1 / 2;
            border-color: rgba(244, 63, 94, 0.15);
        }

        .mock-ticket {
            background: rgba(244, 63, 94, 0.05);
            border: 1px dashed rgba(244, 63, 94, 0.2);
            border-radius: 6px;
            padding: 8px;
            font-size: 11px;
        }

        .mock-waiter {
            grid-column: 2 / 3;
            grid-row: 2 / 3;
            border-color: rgba(6, 182, 212, 0.15);
        }

        .mock-list-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            font-size: 11px;
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        @keyframes pulseHeight {
            0%, 100% { height: 40%; }
            50% { height: 85%; }
        }

        /* Stats Grid Section */
        .stats-section {
            padding: 60px 0;
            background: rgba(99, 102, 241, 0.02);
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }

        .stat-card {
            background: var(--surface-color);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 24px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-light);
            background: var(--surface-hover);
        }

        .stat-num {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 40px;
            font-weight: 700;
            color: var(--primary-light);
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Core Features Grid Section */
        .features-section {
            padding: 100px 0;
        }

        .section-header {
            text-align: center;
            max-width: 600px;
            margin: 0 auto 60px;
        }

        .section-header h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 38px;
            font-weight: 700;
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }

        .section-header p {
            color: var(--text-secondary);
            font-size: 16px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .feature-card {
            background: var(--surface-color);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 32px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--primary-grad);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .feature-card:hover {
            background: var(--surface-hover);
            border-color: var(--border-hover);
            transform: translateY(-6px);
            box-shadow: var(--card-shadow);
        }

        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-icon-wrapper {
            width: 48px;
            height: 48px;
            background: rgba(99, 102, 241, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-light);
            margin-bottom: 24px;
        }

        .feature-card h3 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--text-primary);
        }

        .feature-card p {
            font-size: 15px;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        /* Interactive Roles Showcase (Tabs) */
        .roles-section {
            padding: 100px 0;
            background: rgba(15, 23, 42, 0.15);
            border-top: 1px solid var(--border-color);
        }

        .tabs-nav {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 48px;
            flex-wrap: wrap;
        }

        .tab-trigger {
            background: var(--surface-color);
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            padding: 12px 24px;
            border-radius: 12px;
            font-family: inherit;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-trigger:hover {
            color: var(--text-primary);
            border-color: var(--border-hover);
            background: var(--surface-hover);
        }

        .tab-trigger.active {
            color: white;
            background: var(--primary);
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.4s ease forwards;
        }

        .tab-content.active {
            display: block;
        }

        .tour-display {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 48px;
            align-items: center;
        }

        .tour-info h3 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .tour-info p {
            color: var(--text-secondary);
            font-size: 16px;
            margin-bottom: 24px;
        }

        .tour-features {
            list-style: none;
            margin-bottom: 32px;
        }

        .tour-features li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 15px;
            color: var(--text-primary);
            margin-bottom: 12px;
        }

        .tour-features li svg {
            color: var(--success);
            flex-shrink: 0;
            margin-top: 3px;
        }

        /* High Fidelity CSS Mockups */
        .tour-visual {
            background: var(--surface-color);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 16px;
            box-shadow: var(--card-shadow);
            position: relative;
            min-height: 380px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Active Mockup Screen styles */
        .mock-window {
            flex-grow: 1;
            background: #0a0e1a;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.06);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 12px;
        }

        body.light-theme .mock-window {
            background: #f1f5f9;
            color: #0f172a;
            border-color: rgba(0,0,0,0.08);
        }

        .mock-win-header {
            background: #111827;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        body.light-theme .mock-win-header {
            background: #e2e8f0;
            border-bottom: 1px solid rgba(0,0,0,0.08);
        }

        .mock-win-dots {
            display: flex;
            gap: 6px;
        }

        .mock-win-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #ef4444;
        }
        .mock-win-dot:nth-child(2) { background: #f59e0b; }
        .mock-win-dot:nth-child(3) { background: #10b981; }

        .mock-win-title {
            color: #9ca3af;
            font-size: 11px;
            font-weight: 500;
        }

        body.light-theme .mock-win-title {
            color: #475569;
        }

        .mock-win-body {
            padding: 16px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        /* 1. Admin Mockup */
        .mock-admin-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 16px;
        }
        .mock-admin-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            padding: 12px;
            border-radius: 8px;
        }
        body.light-theme .mock-admin-card {
            background: #ffffff;
            border-color: rgba(0,0,0,0.05);
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }
        .mock-admin-card-title {
            color: #9ca3af;
            font-size: 10px;
            text-transform: uppercase;
        }
        body.light-theme .mock-admin-card-title {
            color: #64748b;
        }
        .mock-admin-card-val {
            font-size: 16px;
            font-weight: 700;
            margin-top: 4px;
            color: var(--primary-light);
        }
        .mock-admin-list {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 8px;
            padding: 8px;
        }
        body.light-theme .mock-admin-list {
            background: #ffffff;
            border-color: rgba(0,0,0,0.05);
        }
        .mock-admin-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px;
            border-bottom: 1px solid rgba(255,255,255,0.03);
        }
        body.light-theme .mock-admin-row {
            border-bottom-color: rgba(0,0,0,0.03);
        }
        .mock-admin-row:last-child {
            border-bottom: none;
        }

        /* 2. Waiter Tablet Mockup */
        .mock-waiter-layout {
            display: grid;
            grid-template-columns: 1fr 140px;
            gap: 12px;
            height: 100%;
            flex-grow: 1;
        }
        .mock-waiter-menu {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            align-content: start;
        }
        .mock-waiter-item {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 8px;
            padding: 10px;
            cursor: pointer;
            text-align: left;
        }
        body.light-theme .mock-waiter-item {
            background: #ffffff;
            border-color: rgba(0,0,0,0.05);
        }
        .mock-waiter-item-title {
            font-weight: 600;
        }
        .mock-waiter-item-price {
            color: #a5b4fc;
            margin-top: 4px;
        }
        body.light-theme .mock-waiter-item-price {
            color: var(--primary);
        }
        .mock-waiter-cart {
            background: rgba(255,255,255,0.02);
            border-left: 1px solid rgba(255,255,255,0.06);
            padding: 10px;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        body.light-theme .mock-waiter-cart {
            background: #ffffff;
            border-left: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }
        .mock-waiter-cart-title {
            font-weight: 600;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        body.light-theme .mock-waiter-cart-title {
            border-bottom-color: rgba(0,0,0,0.08);
        }
        .mock-waiter-cart-list {
            flex-grow: 1;
            overflow-y: auto;
        }
        .mock-waiter-cart-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 11px;
        }
        .mock-waiter-cart-btn {
            background: var(--success);
            border: none;
            color: white;
            padding: 8px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            margin-top: 10px;
        }

        /* 3. Kitchen Board Mockup */
        .mock-kitchen-board {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            height: 100%;
        }
        .mock-kitchen-col {
            background: rgba(255,255,255,0.01);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 8px;
            padding: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        body.light-theme .mock-kitchen-col {
            background: #ffffff;
            border-color: rgba(0,0,0,0.05);
        }
        .mock-kitchen-ticket {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 6px;
            padding: 10px;
            position: relative;
        }
        .mock-kitchen-ticket.warning {
            border-color: var(--warning);
            background: rgba(245, 158, 11, 0.04);
            animation: pulseBorder 2s infinite;
        }
        body.light-theme .mock-kitchen-ticket {
            background: #f8fafc;
            border-color: rgba(0,0,0,0.06);
        }
        body.light-theme .mock-kitchen-ticket.warning {
            background: rgba(245, 158, 11, 0.04);
        }
        .mock-kitchen-ticket-hdr {
            display: flex;
            justify-content: space-between;
            font-weight: 600;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        body.light-theme .mock-kitchen-ticket-hdr {
            border-bottom-color: rgba(0,0,0,0.06);
        }
        .mock-kitchen-timer {
            color: var(--warning);
            font-weight: 600;
        }
        .mock-kitchen-btn {
            background: var(--primary);
            border: none;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 6px;
        }
        @keyframes pulseBorder {
            0%, 100% { border-color: rgba(245, 158, 11, 0.4); }
            50% { border-color: rgba(245, 158, 11, 1); }
        }

        /* 4. Counter / Cashier Mockup */
        .mock-counter-layout {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 12px;
            height: 100%;
        }
        .mock-counter-tables {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            align-content: start;
        }
        .mock-counter-tbl {
            height: 48px;
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-weight: 600;
        }
        .mock-counter-tbl.occupied {
            background: rgba(99, 102, 241, 0.1);
            border-color: var(--primary);
            color: #fff;
        }
        body.light-theme .mock-counter-tbl {
            background: #ffffff;
            border-color: rgba(0,0,0,0.05);
        }
        body.light-theme .mock-counter-tbl.occupied {
            background: rgba(79, 70, 229, 0.1);
            border-color: var(--primary);
            color: var(--primary);
        }
        .mock-counter-bill {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 8px;
            padding: 10px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        body.light-theme .mock-counter-bill {
            background: #ffffff;
            border-color: rgba(0,0,0,0.05);
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }
        .mock-counter-bill-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .mock-counter-bill-total {
            font-weight: 700;
            border-top: 1px solid rgba(255,255,255,0.06);
            padding-top: 6px;
            margin-top: 6px;
            color: var(--success);
        }
        body.light-theme .mock-counter-bill-total {
            border-top-color: rgba(0,0,0,0.06);
        }
        .mock-counter-pay-btns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
            margin-top: 10px;
        }
        .mock-counter-pay-btn {
            padding: 6px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            font-size: 10px;
            cursor: pointer;
            text-align: center;
        }
        .mock-counter-pay-btn.secondary {
            background: rgba(255,255,255,0.08);
            color: var(--text-primary);
        }
        body.light-theme .mock-counter-pay-btn.secondary {
            background: rgba(0,0,0,0.05);
        }

        /* 5. Customer Self-Order QR Mockup */
        .mock-customer-layout {
            max-width: 240px;
            margin: 0 auto;
            border: 4px solid #334155;
            border-radius: 20px;
            background: #080c14;
            padding: 10px;
            height: 100%;
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 20px rgba(0,0,0,0.4);
        }
        body.light-theme .mock-customer-layout {
            background: #f8fafc;
            border-color: #cbd5e1;
        }
        .mock-cust-header {
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        body.light-theme .mock-cust-header {
            border-bottom-color: rgba(0,0,0,0.06);
        }
        .mock-cust-title {
            font-size: 11px;
            font-weight: 700;
            color: var(--primary-light);
        }
        .mock-cust-sub {
            font-size: 8px;
            color: #94a3b8;
        }
        .mock-cust-categories {
            display: flex;
            gap: 4px;
            margin-bottom: 10px;
            overflow-x: auto;
        }
        .mock-cust-cat {
            background: rgba(255,255,255,0.04);
            padding: 3px 6px;
            border-radius: 10px;
            font-size: 8px;
            white-space: nowrap;
        }
        .mock-cust-cat.active {
            background: var(--primary);
            color: white;
        }
        body.light-theme .mock-cust-cat {
            background: rgba(0,0,0,0.04);
        }
        .mock-cust-list {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .mock-cust-dish {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 6px;
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        body.light-theme .mock-cust-dish {
            background: #ffffff;
            border-color: rgba(0,0,0,0.05);
        }
        .mock-cust-dish-info {
            text-align: left;
        }
        .mock-cust-dish-name {
            font-weight: 600;
            font-size: 10px;
        }
        .mock-cust-dish-price {
            font-size: 8px;
            color: #a5b4fc;
        }
        body.light-theme .mock-cust-dish-price {
            color: var(--primary);
        }
        .mock-cust-add-btn {
            background: var(--primary-grad);
            border: none;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 600;
            cursor: pointer;
        }

        /* Order Workflow Section */
        .workflow-section {
            padding: 100px 0;
            background: rgba(99, 102, 241, 0.01);
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
        }

        .workflow-timeline {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            position: relative;
            margin-top: 60px;
        }

        .workflow-timeline::before {
            content: '';
            position: absolute;
            top: 24px;
            left: 5%;
            right: 5%;
            height: 2px;
            background: var(--border-color);
            z-index: 1;
        }

        .workflow-step {
            width: 18%;
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .workflow-step-num {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--surface-color);
            border: 2px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            color: var(--text-secondary);
            transition: all 0.3s ease;
        }

        .workflow-step:hover .workflow-step-num {
            border-color: var(--primary-light);
            background: var(--primary);
            color: white;
            transform: scale(1.1);
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.4);
        }

        .workflow-step h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .workflow-step p {
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        /* Demo Sign In Credentials Section */
        .demo-section {
            padding: 100px 0;
        }

        .demo-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }

        .demo-card {
            background: var(--surface-color);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 24px;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.3s ease;
        }

        .demo-card:hover {
            background: var(--surface-hover);
            border-color: var(--primary-light);
            transform: translateY(-4px);
            box-shadow: var(--card-shadow);
        }

        .demo-role-badge {
            display: inline-block;
            align-self: flex-start;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 16px;
        }

        .demo-card.admin .demo-role-badge { background: rgba(168, 85, 247, 0.1); color: #c084fc; }
        .demo-card.waiter .demo-role-badge { background: rgba(6, 182, 212, 0.1); color: #22d3ee; }
        .demo-card.chef .demo-role-badge { background: rgba(244, 63, 94, 0.1); color: #fb7185; }
        .demo-card.cashier .demo-role-badge { background: rgba(16, 185, 129, 0.1); color: #34d399; }

        .demo-card h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .demo-card p {
            font-size: 13px;
            color: var(--text-secondary);
            margin-bottom: 16px;
            flex-grow: 1;
        }

        .demo-credentials {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 12px;
            font-size: 13px;
            margin-bottom: 16px;
        }

        body.light-theme .demo-credentials {
            background: rgba(255, 255, 255, 0.8);
        }

        .demo-cred-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .demo-cred-row:last-child { margin-bottom: 0; }
        .demo-cred-label { color: var(--text-muted); }
        .demo-cred-val { font-family: monospace; font-weight: 600; color: var(--text-primary); }

        .demo-btn-link {
            text-align: center;
            padding: 8px;
            background: var(--surface-hover);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 8px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .demo-btn-link:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        /* Footer Styles */
        footer {
            border-top: 1px solid var(--border-color);
            background: rgba(15, 23, 42, 0.4);
            padding: 60px 0 30px;
            font-size: 14px;
            color: var(--text-secondary);
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 48px;
            margin-bottom: 40px;
        }

        .footer-logo-desc p {
            margin-top: 16px;
            max-width: 320px;
            line-height: 1.6;
        }

        .footer-col h4 {
            font-family: 'Space Grotesk', sans-serif;
            color: var(--text-primary);
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .footer-links a:hover {
            color: var(--text-primary);
        }

        .footer-bottom {
            border-top: 1px solid var(--border-color);
            padding-top: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive Styles */
        @media (max-width: 1024px) {
            .hero h1 { font-size: 44px; }
            .features-grid { grid-template-columns: repeat(2, 1fr); }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .tour-display { grid-template-columns: 1fr; gap: 32px; }
            .demo-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .mobile-toggle { display: block; }
            .nav-menu {
                display: none;
                position: absolute;
                top: 72px;
                left: 0;
                right: 0;
                background: var(--bg-color);
                border-bottom: 1px solid var(--border-color);
                flex-direction: column;
                padding: 24px;
                gap: 16px;
                box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            }
            .nav-menu.active { display: flex; }
            .hero h1 { font-size: 36px; }
            .hero p { font-size: 15px; }
            .workflow-timeline { flex-direction: column; gap: 32px; }
            .workflow-timeline::before { display: none; }
            .workflow-step { width: 100%; display: flex; text-align: left; gap: 16px; align-items: center; }
            .workflow-step-num { margin: 0; flex-shrink: 0; }
            .footer-grid { grid-template-columns: 1fr; gap: 32px; }
            .footer-bottom { flex-direction: column; gap: 16px; text-align: center; }
        }

        @media (max-width: 480px) {
            .hero-ctas { flex-direction: column; width: 100%; }
            .hero-ctas .btn { width: 100%; }
            .features-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: 1fr; }
            .demo-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <header id="header">
        <div class="container">
            <div class="nav-wrapper">
                <a href="#home" class="logo">
                    <div class="logo-symbol">R</div>
                    <span>RestoFlow</span>
                </a>
                
                <button class="mobile-toggle" onclick="toggleMobileMenu()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
                </button>

                <ul class="nav-menu" id="navMenu">
                    <li><a href="#features" class="nav-link" onclick="closeMobileMenu()">Features</a></li>
                    <li><a href="#tour" class="nav-link" onclick="closeMobileMenu()">System Tour</a></li>
                    <li><a href="#workflow" class="nav-link" onclick="closeMobileMenu()">Order Flow</a></li>
                    <li><a href="#demo" class="nav-link" onclick="closeMobileMenu()">Demo Credentials</a></li>
                </ul>

                <div class="nav-actions">
                    <button class="theme-btn" onclick="toggleTheme()" aria-label="Toggle Theme">
                        <svg width="18" height="18" class="theme-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                    </button>
                    <?php if ($isLoggedIn): ?>
                        <a href="/admin" class="btn btn-primary">Go to Dashboard</a>
                    <?php else: ?>
                        <a href="/login" class="btn btn-primary">Sign In</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="container">
            <div class="badge">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                v1.2 Operations Hub Released
            </div>
            <h1>Streamline Operations from <span>Order to Invoice</span></h1>
            <p>A high-performance, glassmorphic KOT & Billing platform crafted for restaurants. Powering waiter tablets, real-time kitchen screens, self-order customer displays, and cashier desks in one unified local environment.</p>
            
            <div class="hero-ctas">
                <?php if ($isLoggedIn): ?>
                    <a href="/admin" class="btn btn-primary btn-lg">Go to Dashboard</a>
                <?php else: ?>
                    <a href="/login" class="btn btn-primary btn-lg">Launch App Portal</a>
                <?php endif; ?>
                <a href="#tour" class="btn btn-secondary btn-lg">Explore Features</a>
            </div>

            <!-- Dashboard illustration -->
            <div class="hero-illustration">
                <div class="hero-banner-inner">
                    <div class="grid-mockup">
                        <!-- Admin Mini Panel Mockup -->
                        <div class="mock-panel mock-admin">
                            <div class="mock-header">
                                <div class="mock-title">
                                    <div class="mock-dot"></div>
                                    Admin Dashboard Overview
                                </div>
                                <span style="font-size: 10px; color: var(--text-muted);">Real-time Live</span>
                            </div>
                            <div class="mock-analytics-grid">
                                <div class="mock-card">
                                    <div class="mock-admin-card-title">Daily Sales</div>
                                    <div class="mock-card-val"><?= htmlspecialchars($settings['currency_code']) ?> 234.850</div>
                                </div>
                                <div class="mock-card">
                                    <div class="mock-admin-card-title">Orders Processed</div>
                                    <div class="mock-card-val"><?= $stats['total_orders'] + 42 ?></div>
                                </div>
                                <div class="mock-card">
                                    <div class="mock-admin-card-title">Active Tables</div>
                                    <div class="mock-card-val">5 / <?= $stats['total_tables'] ?></div>
                                </div>
                            </div>
                            <div style="font-size: 11px; text-align: left; margin-bottom: 6px; font-weight: 600; color: #a5b4fc;">Weekly Sales Trend</div>
                            <div class="mock-chart-container">
                                <div class="mock-chart-bar" style="height: 35%;"></div>
                                <div class="mock-chart-bar" style="height: 48%;"></div>
                                <div class="mock-chart-bar" style="height: 60%;"></div>
                                <div class="mock-chart-bar" style="height: 40%;"></div>
                                <div class="mock-chart-bar" style="height: 80%;"></div>
                                <div class="mock-chart-bar" style="height: 95%;"></div>
                                <div class="mock-chart-bar" style="height: 70%;"></div>
                            </div>
                        </div>

                        <!-- Kitchen Mini Panel Mockup -->
                        <div class="mock-panel mock-kitchen">
                            <div class="mock-header">
                                <div class="mock-title" style="color: #fda4af;">
                                    <div class="mock-dot" style="background: var(--accent);"></div>
                                    Kitchen Display (KOT)
                                </div>
                            </div>
                            <div class="mock-body" style="justify-content: flex-start; gap: 8px;">
                                <div class="mock-ticket">
                                    <div style="display:flex; justify-content:space-between; font-weight:600; margin-bottom: 4px;">
                                        <span>Table 4</span>
                                        <span style="color:var(--accent);">Pending (3m)</span>
                                    </div>
                                    <div class="mock-row"><div class="mock-bar accent"></div></div>
                                    <div class="mock-row"><div class="mock-bar medium"></div></div>
                                </div>
                            </div>
                        </div>

                        <!-- Waiter Mini Panel Mockup -->
                        <div class="mock-panel mock-waiter">
                            <div class="mock-header">
                                <div class="mock-title" style="color: #67e8f9;">
                                    <div class="mock-dot" style="background: var(--info);"></div>
                                    Waiter Terminal
                                </div>
                            </div>
                            <div class="mock-body" style="justify-content: flex-start; gap: 4px;">
                                <div class="mock-list-item">
                                    <span>Grilled Salmon</span>
                                    <span style="color:var(--success);">Ready</span>
                                </div>
                                <div class="mock-list-item">
                                    <span>Chocolate Mousse</span>
                                    <span style="color:var(--warning);">Preparing</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-num"><?= $stats['total_products'] ?></div>
                    <div class="stat-label">Dishes on Menu</div>
                </div>
                <div class="stat-card">
                    <div class="stat-num"><?= $stats['total_categories'] ?></div>
                    <div class="stat-label">Product Categories</div>
                </div>
                <div class="stat-card">
                    <div class="stat-num"><?= $stats['total_tables'] ?></div>
                    <div class="stat-label">Active Tables Supported</div>
                </div>
                <div class="stat-card">
                    <div class="stat-num"><?= $stats['total_orders'] ?>+</div>
                    <div class="stat-label">Total Orders Tracked</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Product Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="section-header">
                <h2>Engineered for High-Pressure Kitchens</h2>
                <p>Equipped with a rich stack of operational tools configured for low latency, smooth animation, and dark mode compliance.</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon-wrapper">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    </div>
                    <h3>Kitchen Order Tickets (KOT)</h3>
                    <p>Live order queue displays instantly sorted by timestamp with pulse timing alerts warning chefs about orders delayed over 10 minutes.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon-wrapper">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    </div>
                    <h3>Vite React Waiter App</h3>
                    <p>A mobile-optimized React client allows waiters to add dishes, insert cooking notes, check out KOTs, and receive ready-to-serve notifications.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon-wrapper">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                    <h3>Flexible Taxation & Billing</h3>
                    <p>Configure tax rates dynamically (Split India GST / Single GCC VAT), merge table invoices, and print bills on standard thermal receipt sizes (58mm/80mm).</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon-wrapper">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <h3>Customer Self-Ordering QR</h3>
                    <p>Tables contain downloadable, automatic QR codes. Customers scan to access direct mobile-friendly menus, select options, and self-order.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon-wrapper">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                    <h3>Integrated Cashier Counter</h3>
                    <p>Cashiers track checkout transactions, select multiple payment methods (Cash, Card, QR Link Pay), print monospaced thermal bills, and manage daily sales sessions.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon-wrapper">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    </div>
                    <h3>Instant Notification Sync</h3>
                    <p>Low latency long-polling infrastructure alerts servers and waiters of ready orders without requiring manual page refreshes or external WebSockets.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Interactive Roles Showcase (Tabs) -->
    <section class="roles-section" id="tour">
        <div class="container">
            <div class="section-header">
                <h2>Explore the System Terminals</h2>
                <p>Click on any operational profile to view an interactive demo mockup of their dashboard workflow.</p>
            </div>

            <!-- Tab triggers -->
            <div class="tabs-nav">
                <button class="tab-trigger active" onclick="switchTab(event, 'tab-admin')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                    System Admin
                </button>
                <button class="tab-trigger" onclick="switchTab(event, 'tab-waiter')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    Waiter Mobile App
                </button>
                <button class="tab-trigger" onclick="switchTab(event, 'tab-kitchen')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    Kitchen Chef
                </button>
                <button class="tab-trigger" onclick="switchTab(event, 'tab-cashier')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2" ry="2"/><line x1="12" y1="4" x2="12" y2="20"/><line x1="2" y1="12" x2="22" y2="12"/></svg>
                    Cashier Counter
                </button>
                <button class="tab-trigger" onclick="switchTab(event, 'tab-customer')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                    Customer Self-Order
                </button>
            </div>

            <!-- Tab content views -->

            <!-- 1. Admin -->
            <div id="tab-admin" class="tab-content active">
                <div class="tour-display">
                    <div class="tour-info">
                        <h3>Administrator Control Console</h3>
                        <p>Complete control over catalog management, taxation schemas, system parameters, and sales metrics reports.</p>
                        <ul class="tour-features">
                            <li>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Create and delete menu categories with dynamic image uploads.
                            </li>
                            <li>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Configure GCC single VAT or dual India CGST+SGST percentages.
                            </li>
                            <li>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Generate and download dedicated table QR codes (PDF format).
                            </li>
                            <li>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Monitor cumulative tax summaries and analytical item sales graphs.
                            </li>
                        </ul>
                        <a href="/login" class="btn btn-primary">Try Admin Console</a>
                    </div>
                    <div class="tour-visual">
                        <div class="mock-window">
                            <div class="mock-win-header">
                                <div class="mock-win-dots">
                                    <div class="mock-win-dot"></div>
                                    <div class="mock-win-dot"></div>
                                    <div class="mock-win-dot"></div>
                                </div>
                                <div class="mock-win-title">Admin | Catalog & Settings</div>
                                <div style="width: 30px;"></div>
                            </div>
                            <div class="mock-win-body">
                                <div class="mock-admin-grid">
                                    <div class="mock-admin-card">
                                        <div class="mock-admin-card-title">Total Dishes</div>
                                        <div class="mock-admin-card-val"><?= $stats['total_products'] ?> items</div>
                                    </div>
                                    <div class="mock-admin-card">
                                        <div class="mock-admin-card-title">Active Tables</div>
                                        <div class="mock-admin-card-val"><?= $stats['total_tables'] ?> tables</div>
                                    </div>
                                    <div class="mock-admin-card">
                                        <div class="mock-admin-card-title">Tax Type</div>
                                        <div class="mock-admin-card-val"><?= htmlspecialchars($settings['tax_type']) ?> (<?= htmlspecialchars($settings['vat_percent'] ?? $settings['cgst_percent']+$settings['sgst_percent']) ?>%)</div>
                                    </div>
                                </div>
                                <div class="mock-admin-list">
                                    <div class="mock-admin-row">
                                        <span style="font-weight:600;">Product Name</span>
                                        <span style="font-weight:600;">Category</span>
                                        <span style="font-weight:600;">Price</span>
                                    </div>
                                    <div class="mock-admin-row">
                                        <span>Butter Chicken</span>
                                        <span>Main Course</span>
                                        <span><?= htmlspecialchars($settings['currency_code']) ?> 4.500</span>
                                    </div>
                                    <div class="mock-admin-row">
                                        <span>Virgin Mojito</span>
                                        <span>Drinks</span>
                                        <span><?= htmlspecialchars($settings['currency_code']) ?> 1.800</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Waiter -->
            <div id="tab-waiter" class="tab-content">
                <div class="tour-display">
                    <div class="tour-info">
                        <h3>Vite React Mobile Waiter SPA</h3>
                        <p>Mobile-first order-taking interface served with instant loading speed and real-time order tracking.</p>
                        <ul class="tour-features">
                            <li>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Category quick filters (Starters, Main Course, Drinks, Dessert).
                            </li>
                            <li>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Order checkout drawer with special kitchen instructions/notes.
                            </li>
                            <li>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Real-time long-polling alert when KOT items are marked "Ready" by the kitchen.
                            </li>
                        </ul>
                        <a href="/waiter-app/dist/index.html" class="btn btn-primary">Open Waiter App</a>
                    </div>
                    <div class="tour-visual">
                        <div class="mock-window">
                            <div class="mock-win-header">
                                <div class="mock-win-dots">
                                    <div class="mock-win-dot"></div>
                                    <div class="mock-win-dot"></div>
                                    <div class="mock-win-dot"></div>
                                </div>
                                <div class="mock-win-title">Waiter App Tablet Console</div>
                                <div style="width: 30px;"></div>
                            </div>
                            <div class="mock-win-body">
                                <div class="mock-waiter-layout">
                                    <div class="mock-waiter-menu">
                                        <div class="mock-waiter-item">
                                            <div class="mock-waiter-item-title">Chicken Biryani</div>
                                            <div class="mock-waiter-item-price"><?= htmlspecialchars($settings['currency_code']) ?> 3.900</div>
                                        </div>
                                        <div class="mock-waiter-item" style="border-color: var(--primary);">
                                            <div class="mock-waiter-item-title">Hummus Plat.</div>
                                            <div class="mock-waiter-item-price"><?= htmlspecialchars($settings['currency_code']) ?> 2.200</div>
                                        </div>
                                        <div class="mock-waiter-item">
                                            <div class="mock-waiter-item-title">Garlic Naan</div>
                                            <div class="mock-waiter-item-price"><?= htmlspecialchars($settings['currency_code']) ?> 0.500</div>
                                        </div>
                                    </div>
                                    <div class="mock-waiter-cart">
                                        <div>
                                            <div class="mock-waiter-cart-title">Table 5 KOT</div>
                                            <div class="mock-waiter-cart-list">
                                                <div class="mock-waiter-cart-item">
                                                    <span>Hummus x1</span>
                                                    <span>2.200</span>
                                                </div>
                                                <div class="mock-waiter-cart-item">
                                                    <span>Biryani x1</span>
                                                    <span>3.900</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <div style="display:flex; justify-content:space-between; font-weight:700; margin-bottom:8px; font-size:11px;">
                                                <span>Total:</span>
                                                <span>6.100</span>
                                            </div>
                                            <button class="mock-waiter-cart-btn" style="width:100%;">Send KOT</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Kitchen -->
            <div id="tab-kitchen" class="tab-content">
                <div class="tour-display">
                    <div class="tour-info">
                        <h3>Kitchen Order Display (KDS) Screen</h3>
                        <p>A full-width, auto-updating dashboard designed for kitchen monitors to manage preparation queues.</p>
                        <ul class="tour-features">
                            <li>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Grouped tickets sorting cooking items by tables.
                            </li>
                            <li>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Pulses amber/red warning outline when cooking exceeds 10 minutes.
                            </li>
                            <li>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Single-click "Mark Ready" notifies servers instantly.
                            </li>
                        </ul>
                        <a href="/login" class="btn btn-primary">Open Kitchen Board</a>
                    </div>
                    <div class="tour-visual">
                        <div class="mock-window">
                            <div class="mock-win-header">
                                <div class="mock-win-dots">
                                    <div class="mock-win-dot"></div>
                                    <div class="mock-win-dot"></div>
                                    <div class="mock-win-dot"></div>
                                </div>
                                <div class="mock-win-title">KOT Kitchen Monitor Panel</div>
                                <div style="width: 30px;"></div>
                            </div>
                            <div class="mock-win-body">
                                <div class="mock-kitchen-board">
                                    <div class="mock-kitchen-col">
                                        <div class="mock-kitchen-ticket">
                                            <div class="mock-kitchen-ticket-hdr">
                                                <span>Table 8 (KOT-091)</span>
                                                <span style="color:var(--success);">3m ago</span>
                                            </div>
                                            <div style="margin-bottom: 4px;">- 1x Butter Chicken</div>
                                            <div>- 2x Garlic Naan</div>
                                            <button class="mock-kitchen-btn">Mark KOT Ready</button>
                                        </div>
                                    </div>
                                    <div class="mock-kitchen-col">
                                        <div class="mock-kitchen-ticket warning">
                                            <div class="mock-kitchen-ticket-hdr">
                                                <span>Table 3 (KOT-087)</span>
                                                <span class="mock-kitchen-timer">12m ago</span>
                                            </div>
                                            <div style="margin-bottom: 4px;">- 1x Grilled Ribeye</div>
                                            <div style="color: #fda4af; font-size:10px;">* Note: Medium Rare</div>
                                            <button class="mock-kitchen-btn" style="background:var(--warning);">Complete Steak</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Cashier -->
            <div id="tab-cashier" class="tab-content">
                <div class="tour-display">
                    <div class="tour-info">
                        <h3>Cashier Counter & Invoicing Terminal</h3>
                        <p>Complete checkout point mapping occupied dining tables, managing payments, and printing thermal roll layout bills.</p>
                        <ul class="tour-features">
                            <li>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Check table layout to review cumulative pending receipts.
                            </li>
                            <li>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Split receipt preview including VAT/GST breakdown automatically calculated.
                            </li>
                            <li>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Single-click collection of Card, Cash, or QR Pay transfers.
                            </li>
                        </ul>
                        <a href="/login" class="btn btn-primary">Open Cashier Terminal</a>
                    </div>
                    <div class="tour-visual">
                        <div class="mock-window">
                            <div class="mock-win-header">
                                <div class="mock-win-dots">
                                    <div class="mock-win-dot"></div>
                                    <div class="mock-win-dot"></div>
                                    <div class="mock-win-dot"></div>
                                </div>
                                <div class="mock-win-title">Cashier Billing Dashboard</div>
                                <div style="width: 30px;"></div>
                            </div>
                            <div class="mock-win-body">
                                <div class="mock-counter-layout">
                                    <div class="mock-counter-tables">
                                        <div class="mock-counter-tbl occupied">Table 1</div>
                                        <div class="mock-counter-tbl">Table 2</div>
                                        <div class="mock-counter-tbl occupied">Table 3</div>
                                        <div class="mock-counter-tbl occupied">Table 4</div>
                                        <div class="mock-counter-tbl">Table 5</div>
                                        <div class="mock-counter-tbl">Table 6</div>
                                    </div>
                                    <div class="mock-counter-bill">
                                        <div>
                                            <div style="font-weight:700; font-size:11px; margin-bottom:6px;">Invoice: Table 3</div>
                                            <div class="mock-counter-bill-row">
                                                <span>Subtotal:</span>
                                                <span>12.000</span>
                                            </div>
                                            <div class="mock-counter-bill-row">
                                                <span>VAT (10%):</span>
                                                <span>1.200</span>
                                            </div>
                                            <div class="mock-counter-bill-row mock-counter-bill-total">
                                                <span>Total Bill:</span>
                                                <span>13.200</span>
                                            </div>
                                        </div>
                                        <div class="mock-counter-pay-btns">
                                            <button class="mock-counter-pay-btn">Cash Pay</button>
                                            <button class="mock-counter-pay-btn secondary">Card/QR</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. Customer -->
            <div id="tab-customer" class="tab-content">
                <div class="tour-display">
                    <div class="tour-info">
                        <h3>Self-Service Digital Menu</h3>
                        <p>Customer mobile interface accessed instantly via scanning a table's QR code. No downloads or sign-ups required.</p>
                        <ul class="tour-features">
                            <li>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Automatically binds the order to the correct dining table from the URL.
                            </li>
                            <li>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                High-definition categories filter mapping photos and prices.
                            </li>
                            <li>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Sends orders straight to kitchen monitors without staff intervention.
                            </li>
                        </ul>
                        <a href="/customer/5" class="btn btn-primary">View Customer Menu (Table 5)</a>
                    </div>
                    <div class="tour-visual" style="justify-content: center; align-items: center; padding: 24px 0;">
                        <div class="mock-customer-layout">
                            <div class="mock-cust-header">
                                <div class="mock-cust-title"><?= htmlspecialchars($settings['restaurant_name']) ?></div>
                                <div class="mock-cust-sub">Table 5 • Digital self-order menu</div>
                            </div>
                            <div class="mock-cust-categories">
                                <div class="mock-cust-cat active">Starters</div>
                                <div class="mock-cust-cat">Dessert</div>
                                <div class="mock-cust-cat">Drinks</div>
                            </div>
                            <div class="mock-cust-list">
                                <div class="mock-cust-dish">
                                    <div class="mock-cust-dish-info">
                                        <div class="mock-cust-dish-name">Truffle Fries</div>
                                        <div class="mock-cust-dish-price"><?= htmlspecialchars($settings['currency_code']) ?> 2.200</div>
                                    </div>
                                    <button class="mock-cust-add-btn">+ Add</button>
                                </div>
                                <div class="mock-cust-dish">
                                    <div class="mock-cust-dish-info">
                                        <div class="mock-cust-dish-name">Spring Rolls</div>
                                        <div class="mock-cust-dish-price interstitial"><?= htmlspecialchars($settings['currency_code']) ?> 1.800</div>
                                    </div>
                                    <button class="mock-cust-add-btn">+ Add</button>
                                </div>
                            </div>
                            <button class="mock-waiter-cart-btn" style="padding: 6px; font-size:10px; margin-top:12px;">Place Order</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Order Workflow Flowchart Section -->
    <section class="workflow-section" id="workflow">
        <div class="container">
            <div class="section-header">
                <h2>The Lifecycle of an Order</h2>
                <p>How RestoFlow synchronizes operations seamlessly across different panels in your restaurant.</p>
            </div>
            
            <div class="workflow-timeline">
                <div class="workflow-step">
                    <div class="workflow-step-num">1</div>
                    <h4>Order Input</h4>
                    <p>Waiters select dishes on their tablet app OR customers scan QR codes to order.</p>
                </div>
                <div class="workflow-step">
                    <div class="workflow-step-num">2</div>
                    <h4>KOT Generation</h4>
                    <p>Tickets route automatically to KOT files and trigger on kitchen displays.</p>
                </div>
                <div class="workflow-step">
                    <div class="workflow-step-num">3</div>
                    <h4>Kitchen Prep</h4>
                    <p>Chefs prepares meals. If preparation takes > 10m, a warning border pulses on KDS.</p>
                </div>
                <div class="workflow-step">
                    <div class="workflow-step-num">4</div>
                    <h4>Ready & Alert</h4>
                    <p>Chef marks meal ready. Waiters receive instant browser/tablet alerts to pick up.</p>
                </div>
                <div class="workflow-step">
                    <div class="workflow-step-num">5</div>
                    <h4>Receipt Checkout</h4>
                    <p>Cashier checks out the table, collects cash/card, and prints thermal receipts.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Demo Role Logins Grid Section -->
    <section class="demo-section" id="demo">
        <div class="container">
            <div class="section-header">
                <h2>Launch Interactive Terminals</h2>
                <p>Log in with any operational role credential below to explore different sides of the system.</p>
            </div>

            <div class="demo-grid">
                <!-- Admin Card -->
                <div class="demo-card admin">
                    <div>
                        <div class="demo-role-badge">Admin</div>
                        <h3>System Admin</h3>
                        <p>Manage product catalogues, adjust split taxation schemas, and download table QRs.</p>
                    </div>
                    <div>
                        <div class="demo-credentials">
                            <div class="demo-cred-row">
                                <span class="demo-cred-label">User:</span>
                                <span class="demo-cred-val">admin</span>
                            </div>
                            <div class="demo-cred-row">
                                <span class="demo-cred-label">Pass:</span>
                                <span class="demo-cred-val">admin123</span>
                            </div>
                        </div>
                        <a href="/login" class="demo-btn-link">Open Dashboard</a>
                    </div>
                </div>

                <!-- Waiter Card -->
                <div class="demo-card waiter">
                    <div>
                        <div class="demo-role-badge">Waiter</div>
                        <h3>Waiter SPA</h3>
                        <p>Take dining table orders, customize cook notes, and view active order baskets.</p>
                    </div>
                    <div>
                        <div class="demo-credentials">
                            <div class="demo-cred-row">
                                <span class="demo-cred-label">User:</span>
                                <span class="demo-cred-val">waiter1</span>
                            </div>
                            <div class="demo-cred-row">
                                <span class="demo-cred-label">Pass:</span>
                                <span class="demo-cred-val">waiter123</span>
                            </div>
                        </div>
                        <a href="/waiter-app/dist/index.html" class="demo-btn-link">Launch SPA</a>
                    </div>
                </div>

                <!-- Kitchen Card -->
                <div class="demo-card chef">
                    <div>
                        <div class="demo-role-badge">Kitchen</div>
                        <h3>Kitchen KOT</h3>
                        <p>Receive tickets instantly, prepare dishes, and alert waiters when ready to serve.</p>
                    </div>
                    <div>
                        <div class="demo-credentials">
                            <div class="demo-cred-row">
                                <span class="demo-cred-label">User:</span>
                                <span class="demo-cred-val">chef1</span>
                            </div>
                            <div class="demo-cred-row">
                                <span class="demo-cred-label">Pass:</span>
                                <span class="demo-cred-val">kot123</span>
                            </div>
                        </div>
                        <a href="/login" class="demo-btn-link">Open Kitchen Board</a>
                    </div>
                </div>

                <!-- Cashier Card -->
                <div class="demo-card cashier">
                    <div>
                        <div class="demo-role-badge">Counter</div>
                        <h3>Cashier Desk</h3>
                        <p>Print thermal bill rolls, manage cashier sessions, and settle tables invoices.</p>
                    </div>
                    <div>
                        <div class="demo-credentials">
                            <div class="demo-cred-row">
                                <span class="demo-cred-label">User:</span>
                                <span class="demo-cred-val">counter1</span>
                            </div>
                            <div class="demo-cred-row">
                                <span class="demo-cred-label">Pass:</span>
                                <span class="demo-cred-val">counter123</span>
                            </div>
                        </div>
                        <a href="/login" class="demo-btn-link">Open Cashier Portal</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-logo-desc">
                    <a href="#home" class="logo">
                        <div class="logo-symbol">R</div>
                        <span>RestoFlow</span>
                    </a>
                    <p>A local high-availability kitchen order routing and POS system designed to run on low-latency hardware with dynamic responsive interfaces.</p>
                </div>
                <div class="footer-col">
                    <h4>Modules</h4>
                    <ul class="footer-links">
                        <li><a href="#tour">Admin Portal</a></li>
                        <li><a href="#tour">Waiter App</a></li>
                        <li><a href="#tour">Kitchen Board</a></li>
                        <li><a href="#tour">Cashier Desk</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="/login">Portal Login</a></li>
                        <li><a href="/customer/5">Demo Table 5 Menu</a></li>
                        <li><a href="https://wa.me/97335078079" target="_blank">Contact Support</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <div>© 2026 RestoFlow Operations. Powered by <a href="javascript:void(0)" onclick="openSandsModal()" style="color: var(--primary-light); text-decoration: none; font-weight: 600;">SaNDS Lab</a>.</div>
                <div>All rights reserved. Made for high-frequency dining.</div>
            </div>
        </div>
    </footer>

    <!-- SaNDS Lab Popup Modal -->
    <div id="sands-modal" class="modal-sands" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(11, 15, 25, 0.85); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); z-index: 2000; align-items: center; justify-content: center;">
        <div class="modal-sands-content" style="background: #ffffff; border: 1px solid rgba(0, 0, 0, 0.08); padding: 35px 25px; border-radius: 24px; text-align: center; max-width: 340px; width: 90%; box-shadow: 0 20px 50px rgba(0,0,0,0.2); position: relative; color: #1f2937; font-family: system-ui, -apple-system, sans-serif;">
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
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="margin-right: 6px;"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.513 2.262 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.503-5.739-1.45L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.42 9.864-9.864.002-2.637-1.03-5.118-2.91-6.999-1.88-1.882-4.36-2.914-7.001-2.915-5.442 0-9.867 4.42-9.871 9.866-.002 2.015.528 3.985 1.536 5.736l-.991 3.616 3.7-.977zm11.452-6.52c-.29-.145-1.716-.847-1.982-.944-.265-.098-.458-.146-.65.145-.193.292-.748.944-.917 1.138-.17.19-.338.213-.628.068-.29-.145-1.226-.452-2.336-1.443-.864-.77-1.447-1.722-1.616-2.012-.17-.29-.018-.447.127-.59.13-.13.29-.338.435-.508.145-.17.193-.29.29-.483.097-.19.048-.36-.024-.505-.072-.145-.65-1.568-.89-2.146-.233-.56-.47-.483-.65-.492-.168-.008-.362-.01-.555-.01-.193 0-.507.072-.77.36-.266.29-1.014.992-1.014 2.42 0 1.427 1.038 2.805 1.182 3 .145.195 2.043 3.12 4.95 4.377.69.298 1.23.477 1.65.61.693.22 1.325.19 1.822.115.555-.083 1.716-.7 1.96-1.375.242-.676.242-1.256.17-1.376-.073-.12-.266-.194-.556-.34z"/></svg>
                    Contact Now
                </a>
            </div>
        </div>
    </div>

    <script>
        // Track Scroll for Header styling
        window.addEventListener('scroll', function() {
            const header = document.getElementById('header');
            if (window.scrollY > 20) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Toggle Mobile Menu
        function toggleMobileMenu() {
            const menu = document.getElementById('navMenu');
            menu.classList.toggle('active');
        }

        function closeMobileMenu() {
            const menu = document.getElementById('navMenu');
            menu.classList.remove('active');
        }

        // Apply theme on load
        if (localStorage.getItem('theme') === 'light') {
            document.body.classList.add('light-theme');
            updateThemeIcon('light');
        }

        function toggleTheme() {
            if (document.body.classList.contains('light-theme')) {
                document.body.classList.remove('light-theme');
                localStorage.setItem('theme', 'dark');
                updateThemeIcon('dark');
            } else {
                document.body.classList.add('light-theme');
                localStorage.setItem('theme', 'light');
                updateThemeIcon('light');
            }
        }

        function updateThemeIcon(theme) {
            const btn = document.querySelector('.theme-btn');
            if (theme === 'light') {
                btn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>`;
            } else {
                btn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>`;
            }
        }

        // Tour Tabs Switch
        function switchTab(evt, tabId) {
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => content.classList.remove('active'));

            const triggers = document.querySelectorAll('.tab-trigger');
            triggers.forEach(trigger => trigger.classList.remove('active'));

            document.getElementById(tabId).classList.add('active');
            evt.currentTarget.classList.add('active');
        }

        function openSandsModal() {
            document.getElementById('sands-modal').style.display = 'flex';
        }

        function closeSandsModal() {
            document.getElementById('sands-modal').style.display = 'none';
        }
    </script>
</body>
</html>
