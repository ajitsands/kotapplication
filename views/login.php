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

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(at 10% 20%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 90% 80%, rgba(168, 85, 247, 0.15) 0px, transparent 50%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-color);
            overflow: hidden;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 40px 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            text-align: center;
            position: relative;
            overflow: hidden;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-area {
            margin-bottom: 30px;
        }

        .logo-img {
            max-width: 80px;
            height: auto;
            border-radius: 16px;
            margin-bottom: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .restaurant-title {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: var(--primary-grad);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            font-size: 14px;
            color: var(--text-muted);
            margin-top: 5px;
        }

        .form-group {
            margin-bottom: 20px;
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
    </style>
</head>
<body>
    <button id="theme-toggle" onclick="toggleTheme()" style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.05); border: 1px solid var(--card-border); color: var(--text-color); width: 42px; height: 42px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 20px; z-index: 1000; transition: all 0.3s; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">🌓</button>

    <div class="login-container">
        <div class="login-card">
            <div class="logo-area">
                <?php if (!empty($settings['logo_path'])): ?>
                    <img src="<?= htmlspecialchars($settings['logo_path']) ?>" class="logo-img" alt="Logo">
                <?php else: ?>
                    <div style="width: 60px; height: 60px; border-radius: 14px; background: var(--primary-grad); margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 24px;">G</div>
                <?php endif; ?>
                <h1 class="restaurant-title"><?= htmlspecialchars($settings['restaurant_name']) ?></h1>
                <p class="subtitle">Enter credentials to access dashboard</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="login" method="POST">
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

    <script>
        // Apply theme on load
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
