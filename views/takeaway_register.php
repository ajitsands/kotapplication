<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Away | <?= htmlspecialchars($settings['restaurant_name'] ?? 'Restaurant') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0b0f19;
            --card-bg: rgba(255, 255, 255, 0.05);
            --card-border: rgba(255, 255, 255, 0.1);
            --text-color: #fff;
            --text-muted: #9ca3af;
            --input-bg: rgba(0, 0, 0, 0.2);
            --input-focus: rgba(0, 0, 0, 0.4);
        }

        body.light-theme {
            --bg-color: #f3f4f6;
            --card-bg: rgba(255, 255, 255, 0.85);
            --card-border: rgba(0, 0, 0, 0.08);
            --text-color: #1f2937;
            --text-muted: #6b7280;
            --input-bg: rgba(0, 0, 0, 0.03);
            --input-focus: rgba(0, 0, 0, 0.05);
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
            background: var(--bg-color);
            color: var(--text-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 40px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            position: relative;
        }
        h1 {
            margin-top: 0;
            font-size: 28px;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        p {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 500;
        }
        input {
            width: 100%;
            padding: 12px 15px;
            background: var(--input-bg);
            border: 1px solid var(--card-border);
            border-radius: 10px;
            color: var(--text-color);
            font-size: 16px;
            box-sizing: border-box;
            outline: none;
            transition: all 0.3s;
        }
        input:focus {
            border-color: #a855f7;
            background: var(--input-focus);
        }
        button.btn-primary {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: opacity 0.3s;
        }
        button.btn-primary:hover {
            opacity: 0.9;
        }
        .theme-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(128,128,128,0.1);
            border: 1px solid var(--card-border);
            color: var(--text-color);
            cursor: pointer;
            font-size: 15px;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error {
            color: #ef4444;
            font-size: 14px;
            margin-bottom: 15px;
            display: none;
        }
        .loading {
            display: none;
            color: var(--text-muted);
            margin-top: 15px;
        }
    </style>
</head>
<body class="light-theme">

<div class="container">
    <button class="theme-toggle" onclick="document.body.classList.toggle('light-theme')">🌓</button>
    <h1>Take Away</h1>
    <p>Please enter your details to order or check status.</p>
    
    <div id="error-msg" class="error"></div>
    
    <form id="takeaway-form">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" required placeholder="Enter your name">
        </div>
        
        <div class="form-group">
            <label for="mobile">Mobile Number</label>
            <input type="tel" id="mobile" required placeholder="Enter your mobile number">
        </div>
        
        <button type="submit" class="btn-primary">Continue to Menu</button>
        <div id="loading" class="loading">Checking existing orders...</div>
    </form>
</div>

<script>
document.getElementById('takeaway-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const name = document.getElementById('name').value.trim();
    const mobile = document.getElementById('mobile').value.trim();
    const errorMsg = document.getElementById('error-msg');
    const loading = document.getElementById('loading');
    const btn = document.querySelector('button.btn-primary');
    
    if (!name || !mobile) {
        errorMsg.textContent = 'Please fill in all fields';
        errorMsg.style.display = 'block';
        return;
    }
    
    errorMsg.style.display = 'none';
    loading.style.display = 'block';
    btn.disabled = true;
    
    try {
        // Check if there is an active order for this mobile
        const response = await fetch('/api/orders/mobile/' + encodeURIComponent(mobile));
        const data = await response.json();
        
        // Save details in sessionStorage for the menu page
        sessionStorage.setItem('takeaway_name', name);
        sessionStorage.setItem('takeaway_mobile', mobile);
        
        if (data.active) {
            sessionStorage.setItem('takeaway_active_order_id', data.order_id);
            sessionStorage.setItem('takeaway_token', data.token_number);
        } else {
            sessionStorage.removeItem('takeaway_active_order_id');
            sessionStorage.removeItem('takeaway_token');
        }
        
        // Redirect to menu page
        window.location.href = '/customer/0?takeaway=1';
        
    } catch (err) {
        errorMsg.textContent = 'Network error. Please try again.';
        errorMsg.style.display = 'block';
        loading.style.display = 'none';
        btn.disabled = false;
    }
});

// Auto-fill if already in session
window.addEventListener('DOMContentLoaded', () => {
    const savedName = sessionStorage.getItem('takeaway_name');
    const savedMobile = sessionStorage.getItem('takeaway_mobile');
    if (savedName) document.getElementById('name').value = savedName;
    if (savedMobile) document.getElementById('mobile').value = savedMobile;
});
</script>

</body>
</html>
