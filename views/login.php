<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | <?= htmlspecialchars($settings['restaurant_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Ladda/1.0.6/ladda-themeless.min.css">
    <style>
        :root {
            --bg-color: #0b0f19;
            --card-bg: rgba(255, 255, 255, 0.03);
            --card-border: rgba(255, 255, 255, 0.08);
            --primary-grad: linear-gradient(135deg, #6366f1, #a855f7);
            --text-color: #f3f4f6;
            --text-muted: #9ca3af;
        }

        body.light-theme {
            --bg-color: #f3f4f6;
            --card-bg: rgba(255, 255, 255, 0.7);
            --card-border: rgba(0, 0, 0, 0.08);
            --text-color: #1f2937;
            --text-muted: #6b7280;
        }

        body.light-theme .form-input {
            background: rgba(0, 0, 0, 0.03);
            color: #1f2937;
            border-color: rgba(0, 0, 0, 0.08);
        }

        body.light-theme .login-card {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            min-height: 100%;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(at 10% 20%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 90% 80%, rgba(168, 85, 247, 0.15) 0px, transparent 50%);
            background-attachment: fixed;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-color);
            overflow-x: hidden;
            overflow-y: auto;
            padding: 24px 16px;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            margin: auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 32px 26px 26px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            text-align: center;
            position: relative;
            overflow: hidden;
            width: 100%;
            animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-area {
            margin-bottom: 20px;
        }

        .logo-img {
            max-width: 68px;
            height: auto;
            border-radius: 14px;
            margin-bottom: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .restaurant-title {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: var(--primary-grad);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 3px;
        }

        .form-group {
            margin-bottom: 16px;
            text-align: left;
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 8px;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--card-border);
            padding: 14px 18px;
            border-radius: 14px;
            font-family: inherit;
            color: var(--text-color);
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #a855f7;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 4px rgba(168, 85, 247, 0.15);
        }

        .btn-submit {
            width: 100%;
            background: var(--primary-grad);
            border: none;
            padding: 14px;
            border-radius: 14px;
            font-family: inherit;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(168, 85, 247, 0.4);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(168, 85, 247, 0.6);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
            padding: 12px;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Segmented Auth Tabs */
        .auth-tabs {
            display: flex;
            background: rgba(0, 0, 0, 0.05);
            border: 1px solid var(--card-border);
            border-radius: 14px;
            padding: 4px;
            margin-bottom: 22px;
            gap: 4px;
        }

        body.light-theme .auth-tabs {
            background: rgba(0, 0, 0, 0.04);
            border-color: rgba(0, 0, 0, 0.08);
        }

        .auth-tab-btn {
            flex: 1;
            padding: 10px 12px;
            border-radius: 11px;
            border: none;
            background: transparent;
            color: var(--text-muted);
            font-family: inherit;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        body.light-theme .auth-tab-btn.active {
            background: #ffffff;
            color: #1f2937;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        body:not(.light-theme) .auth-tab-btn.active {
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }

        .auth-tab-badge {
            font-size: 9.5px;
            font-weight: 800;
            background: #22c55e;
            color: #ffffff;
            padding: 2px 6px;
            border-radius: 6px;
        }

        .auth-pane {
            display: none;
            animation: fadeInTab 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .auth-pane.active {
            display: block;
        }

        @keyframes fadeInTab {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* SaNDS Lab KOT Printer Driver & Admin App Download Banners */
        .driver-download-card {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.3);
            border-radius: 18px;
            padding: 15px;
            margin-bottom: 12px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            text-align: left;
            position: relative;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            transition: all 0.3s ease;
            box-shadow: 0 4px 18px rgba(34, 197, 94, 0.08);
        }

        body.light-theme .driver-download-card {
            background: linear-gradient(145deg, #f0fdf4, #e8fbf0);
            border: 1px solid rgba(34, 197, 94, 0.35);
            box-shadow: 0 6px 18px rgba(34, 197, 94, 0.1);
        }

        .driver-download-card:hover {
            border-color: rgba(34, 197, 94, 0.55);
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.18);
        }

        .driver-card-top {
            display: flex;
            align-items: center;
            gap: 14px;
            width: 100%;
        }

        .sands-app-icon {
            width: 54px;
            height: 54px;
            min-width: 54px;
            border-radius: 15px;
            background: radial-gradient(circle at 35% 25%, #a3e635 0%, #4ade80 40%, #22c55e 75%, #15803d 100%);
            box-shadow: 0 6px 18px rgba(34, 197, 94, 0.45), inset 0 2px 3px rgba(255, 255, 255, 0.6);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            border: 1.5px solid rgba(255, 255, 255, 0.45);
            color: #ffffff;
            flex-shrink: 0;
            padding: 3px 2px 2px;
            transition: transform 0.3s ease;
        }

        .driver-download-card:hover .sands-app-icon {
            transform: scale(1.05) rotate(-2deg);
        }

        .sands-app-icon .printer-icon-svg {
            width: 22px;
            height: 22px;
            stroke: #ffffff;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
        }

        .sands-app-icon .sands-icon-text {
            font-family: 'Outfit', sans-serif;
            font-weight: 900;
            font-size: 10px;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: #ffffff;
            line-height: 1;
            margin-top: 2px;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.45);
        }

        .driver-info {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .driver-badge-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
        }

        .driver-title {
            font-size: 14px;
            font-weight: 800;
            color: var(--text-color);
            letter-spacing: -0.2px;
            line-height: 1.2;
        }

        body.light-theme .driver-title {
            color: #14532d;
        }

        .driver-badge {
            font-size: 9px;
            font-weight: 800;
            background: #22c55e;
            color: #ffffff;
            padding: 2px 6px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            flex-shrink: 0;
        }

        .driver-subtitle {
            font-size: 11.5px;
            color: var(--text-muted);
            line-height: 1.35;
            margin: 0;
        }

        body.light-theme .driver-subtitle {
            color: #166534;
        }

        .btn-apk-dl {
            width: 100%;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #ffffff !important;
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 14px rgba(34, 197, 94, 0.35);
            transition: all 0.25s ease;
            box-sizing: border-box;
        }

        .btn-apk-dl:hover {
            background: linear-gradient(135deg, #4ade80, #22c55e);
            box-shadow: 0 6px 20px rgba(34, 197, 94, 0.55);
            transform: translateY(-1px);
        }

        .btn-apk-dl:active {
            transform: translateY(0);
        }

        #theme-toggle {
            position: fixed;
            top: 16px;
            right: 16px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid var(--card-border);
            color: var(--text-color);
            width: 38px;
            height: 38px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            z-index: 1000;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        body.light-theme #theme-toggle {
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        @media (max-width: 480px) {
            body {
                padding: 16px 12px;
            }
            .login-card {
                padding: 24px 18px 20px;
                border-radius: 20px;
            }
            .restaurant-title {
                font-size: 20px;
            }
            .subtitle {
                font-size: 12px;
            }
            .auth-tabs {
                margin-bottom: 16px;
            }
            .form-input {
                padding: 12px 14px;
                font-size: 14px;
            }
            .btn-submit {
                padding: 12px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body class="light-theme">
    <button id="theme-toggle" onclick="toggleTheme()" title="Toggle Dark/Light Mode">🌓</button>

    <div class="login-container">
        <div class="login-card">
            <div class="logo-area">
                <?php if (!empty($settings['logo_path'])): ?>
                    <img src="<?= htmlspecialchars($settings['logo_path']) ?>" class="logo-img" alt="Logo">
                <?php else: ?>
                    <div style="width: 60px; height: 60px; border-radius: 14px; background: var(--primary-grad); margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 24px;">G</div>
                <?php endif; ?>
                <h1 class="restaurant-title"><?= htmlspecialchars($settings['restaurant_name']) ?></h1>
                <p class="subtitle">Access POS &amp; Management Dashboard</p>
            </div>

            <!-- Segmented Switcher Tabs (Login vs Downloads) -->
            <div class="auth-tabs">
                <button type="button" class="auth-tab-btn active" id="tab-btn-login" onclick="switchAuthTab('login')">
                    <span>🔐 Sign In</span>
                </button>
                <button type="button" class="auth-tab-btn" id="tab-btn-downloads" onclick="switchAuthTab('downloads')">
                    <span>📱 Downloads</span>
                    <span class="auth-tab-badge">2 APKs</span>
                </button>
            </div>

            <!-- TAB 1: SIGN IN PANE (SELECTED BY DEFAULT) -->
            <div id="auth-pane-login" class="auth-pane active">
                <?php if (isset($error)): ?>
                    <div class="error-message">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="login" method="POST">
                    <input type="hidden" name="is_printer_driver" id="is_printer_driver" value="0">
                    <div class="form-group">
                        <label class="form-label" for="username">Username</label>
                        <input class="form-input" type="text" id="username" name="username" placeholder="e.g. admin" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input class="form-input" type="password" id="password" name="password" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn-submit">Sign In</button>
                </form>

                <div style="margin-top: 18px; text-align: center;">
                    <button type="button" onclick="switchAuthTab('downloads')" style="background: none; border: none; color: #16a34a; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; font-family: inherit;">
                        <span>📱 Need Android Apps? Download APKs</span> &rarr;
                    </button>
                </div>
            </div>

            <!-- TAB 2: DOWNLOADS PANE (SHOWS BOTH APKS) -->
            <div id="auth-pane-downloads" class="auth-pane">
                <div style="margin-bottom: 14px; text-align: left;">
                    <div style="font-size: 14.5px; font-weight: 800; color: var(--text-color);">Native Android Apps</div>
                    <p style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">Download APKs for your Android phones, tablets, and POS devices.</p>
                </div>

                <!-- 1. KOT Admin App Card -->
                <div class="driver-download-card">
                    <div class="driver-card-top">
                        <div class="sands-app-icon" title="KOT Admin App for Android">
                            <!-- Crown / Admin SVG -->
                            <svg class="printer-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14"></path>
                            </svg>
                            <!-- Under Icon Write KOT ADMIN -->
                            <span class="sands-icon-text" style="font-size: 8.5px; letter-spacing: 0.5px;">KOT ADMIN</span>
                        </div>
                        <div class="driver-info">
                            <div class="driver-badge-row">
                                <span class="driver-title">KOT Admin App</span>
                                <span class="driver-badge">Admin APK</span>
                            </div>
                            <p class="driver-subtitle">Live Operations, Collections &amp; Shift Closings</p>
                        </div>
                    </div>
                    <a href="/download/admin-apk" class="btn-apk-dl" download="SaNDS-KOT-Admin.apk" title="Download KOT Admin App APK">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <span>Download KOT Admin App (APK)</span>
                    </a>
                </div>

                <!-- 2. SaNDS Lab KOT Printer Driver App -->
                <div class="driver-download-card" style="margin-top: 8px;">
                    <div class="driver-card-top">
                        <div class="sands-app-icon" title="SaNDS Lab KOT Printer Driver App">
                            <!-- Printer Icon -->
                            <svg class="printer-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 9V2h12v7"></path>
                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                <path d="M6 14h12v8H6z"></path>
                            </svg>
                            <!-- Under Printer ICON Write SaNDS -->
                            <span class="sands-icon-text">SaNDS</span>
                        </div>
                        <div class="driver-info">
                            <div class="driver-badge-row">
                                <span class="driver-title">SaNDS KOT Driver</span>
                                <span class="driver-badge">Driver APK</span>
                            </div>
                            <p class="driver-subtitle">Thermal Network POS &amp; Receipt Printing</p>
                        </div>
                    </div>
                    <a href="/download/driver-apk" class="btn-apk-dl" download="SaNDS-KOT-Printer-Driver.apk" title="Download SaNDS KOT Driver APK">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <span>Download Driver App (APK)</span>
                    </a>
                </div>

                <div style="margin-top: 18px; text-align: center;">
                    <button type="button" onclick="switchAuthTab('login')" style="background: none; border: none; color: #6366f1; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; font-family: inherit;">
                        &larr; <span>Back to Sign In</span>
                    </button>
                </div>
            </div>

            <div class="login-footer" style="margin-top: 30px; font-size: 12px; color: var(--text-muted);">
                Powered By <a href="javascript:void(0)" onclick="openSandsModal()" style="color: #818cf8; text-decoration: none; font-weight: 600;">SaNDS Lab</a>. All rights reserved to <?= htmlspecialchars($settings['restaurant_name']) ?>
            </div>
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
                <button onclick="openSandsProductsModal()" style="display: block; width: 100%; border: none; cursor: pointer; background: linear-gradient(135deg, #3b82f6, #06b6d4); padding: 12px; border-radius: 12px; color: white; font-size: 14px; font-weight: 600; box-shadow: 0 4px 12px rgba(59,130,246,0.3); text-align: center; margin-top: 12px;">🚀 Our Latest Products</button>
            </div>
        </div>
    </div>

    <!-- SaNDS Lab Products Modal -->
    <div id="sands-products-modal" class="modal-sands" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(11, 15, 25, 0.85); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); z-index: 2005; align-items: center; justify-content: center;">
        <div class="modal-sands-content" style="background: #ffffff; border: 1px solid rgba(0,0,0,0.08); padding: 30px 20px; border-radius: 24px; text-align: left; max-width: 600px; width: 95%; max-height: 80vh; display: flex; flex-direction: column; box-shadow: 0 20px 50px rgba(0,0,0,0.2); position: relative; color: #1f2937;">
            <button onclick="closeSandsProductsModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; color: #6b7280; font-size: 24px; cursor: pointer; line-height: 1; z-index: 10;">&times;</button>
            <h3 style="font-size: 20px; font-weight: 800; color: #1f2937; margin-bottom: 5px; text-align: center;">Our Latest Products</h3>
            <p style="font-size: 12px; color: #6b7280; text-align: center; margin-bottom: 20px;">Discover the innovative solutions by SaNDS Lab</p>
            
            <div id="sands-products-list" style="overflow-y: auto; padding-right: 5px; display: flex; flex-direction: column; gap: 15px; flex: 1;">
                <div style="text-align: center; color: #6b7280; padding: 20px;">Loading products...</div>
            </div>
        </div>
    </div>

    <script>
        // Tab Switcher between Login and Downloads
        function switchAuthTab(tab) {
            const loginBtn = document.getElementById('tab-btn-login');
            const dlBtn = document.getElementById('tab-btn-downloads');
            const loginPane = document.getElementById('auth-pane-login');
            const dlPane = document.getElementById('auth-pane-downloads');

            if (!loginBtn || !dlBtn || !loginPane || !dlPane) return;

            if (tab === 'downloads') {
                loginBtn.classList.remove('active');
                dlBtn.classList.add('active');
                loginPane.classList.remove('active');
                dlPane.classList.add('active');
            } else {
                dlBtn.classList.remove('active');
                loginBtn.classList.add('active');
                dlPane.classList.remove('active');
                loginPane.classList.add('active');
                const u = document.getElementById('username');
                if (u && typeof u.focus === 'function') {
                    try { u.focus({ preventScroll: true }); } catch (e) { u.focus(); }
                }
            }
        }

        // Check hash or query param on page load & detect Printer Driver client
        window.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (window.location.hash === '#downloads' || urlParams.get('tab') === 'downloads') {
                switchAuthTab('downloads');
            }

            // Detect if running inside SaNDS KOT Printer Driver App or iframe
            var isDriver = false;
            if (urlParams.get('driver_app') === '1' || urlParams.get('driver_client') === '1' || urlParams.get('is_printer_driver') === '1') {
                isDriver = true;
            } else if (window.self !== window.top) {
                isDriver = true;
            } else if (window.AndroidPrintBridge || (window.parent && window.parent.AndroidPrintBridge)) {
                isDriver = true;
            } else if (navigator.userAgent && navigator.userAgent.indexOf('SaNDS-KOT-Printer-Driver') !== -1) {
                isDriver = true;
            }

            var driverInput = document.getElementById('is_printer_driver');
            if (isDriver) {
                if (driverInput) driverInput.value = '1';
                document.cookie = "is_printer_driver=1; path=/; SameSite=Lax";
            } else {
                if (driverInput) driverInput.value = '0';
                document.cookie = "is_printer_driver=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC; SameSite=Lax";
            }
        });

        function openSandsProductsModal() {
            closeSandsModal();
            document.getElementById('sands-products-modal').style.display = 'flex';
            const listContainer = document.getElementById('sands-products-list');
            listContainer.innerHTML = '<div style="text-align: center; color: #6b7280; padding: 20px;">Loading products...</div>';
            
            fetch('/get_latest_products.php')
                .then(res => res.json())
                .then(data => {
                    if (data.status && data.data) {
                        let html = '';
                        data.data.forEach(p => {
                            html += `
                                <div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 15px; background: #f9fafb; transition: all 0.2s;">
                                    <h4 style="margin: 0 0 5px 0; font-size: 16px; color: #111827;">${p.product_name}</h4>
                                    <p style="margin: 0 0 10px 0; font-size: 12px; color: #4b5563; line-height: 1.4;">${p.product_discription}</p>
                                    <a href="${p.web_link}" target="_blank" style="display: inline-block; background: #6366f1; color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; text-decoration: none; font-weight: 600;">Explore Product</a>
                                </div>
                            `;
                        });
                        listContainer.innerHTML = html;
                    } else {
                        listContainer.innerHTML = '<div style="text-align: center; color: #ef4444; padding: 20px;">Failed to load products.</div>';
                    }
                })
                .catch(err => {
                    listContainer.innerHTML = '<div style="text-align: center; color: #ef4444; padding: 20px;">Error loading products.</div>';
                });
        }
        function closeSandsProductsModal() {
            document.getElementById('sands-products-modal').style.display = 'none';
            openSandsModal();
        }
    </script>


    <script>
        // Apply theme on load
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.remove('light-theme');
        } else {
            // Default to light theme
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
