<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Delivery Driver Login | <?= htmlspecialchars($settings['restaurant_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --bg-color: #0b0f19;
            --card-bg: #111827;
            --card-border: rgba(255, 255, 255, 0.08);
            --text-color: #f3f4f6;
            --text-muted: #9ca3af;
            --primary-grad: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            --accent-cyan: #06b6d4;
            --accent-green: #10b981;
            --accent-orange: #f59e0b;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(2, 132, 199, 0.12) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(6, 182, 212, 0.08) 0%, transparent 40%);
        }

        .login-box {
            width: 90%;
            max-width: 400px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 32px 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            box-sizing: border-box;
            text-align: center;
        }

        .brand-icon {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            background: rgba(2, 132, 199, 0.15);
            border: 1px solid rgba(2, 132, 199, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            margin: 0 auto 18px;
        }

        .title {
            font-size: 22px;
            font-weight: 800;
            margin: 0 0 6px;
            letter-spacing: -0.5px;
        }

        .subtitle {
            font-size: 13px;
            color: var(--text-muted);
            margin: 0 0 24px;
        }

        .error-badge {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.25);
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group {
            margin-bottom: 18px;
            text-align: left;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--card-border);
            border-radius: 14px;
            color: var(--text-color);
            font-size: 15px;
            box-sizing: border-box;
            outline: none;
            transition: all 0.2s;
        }

        .form-input:focus {
            border-color: #0284c7;
            background: rgba(2, 132, 199, 0.05);
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.2);
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: var(--primary-grad);
            border: none;
            border-radius: 14px;
            color: #ffffff;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 10px;
            box-shadow: 0 8px 20px rgba(2, 132, 199, 0.35);
            transition: transform 0.15s, box-shadow 0.15s;
        }

        .btn-login:active {
            transform: scale(0.98);
        }

        .footer-note {
            margin-top: 24px;
            font-size: 12px;
            color: var(--text-muted);
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="brand-icon">🚴</div>
        <h1 class="title"><?= htmlspecialchars($settings['restaurant_name']) ?></h1>
        <p class="subtitle">Delivery Driver Terminal</p>

        <?php if (!empty($error)): ?>
            <div class="error-badge">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="<?= rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') ?>/delivery/login" method="POST" autocomplete="off">
            <div class="form-group">
                <label class="form-label">Driver Username</label>
                <input type="text" name="username" class="form-input" placeholder="e.g. driver1" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-login">Start Shift / Log In</button>
        </form>

        <div class="footer-note">
            Scan restaurant Delivery QR code to access this terminal anytime.
        </div>
    </div>
</body>
</html>
