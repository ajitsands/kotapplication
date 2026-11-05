<?php
// Customer Mobile Delivery Confirmation & GPS Location Sharing View
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Confirm Your Delivery | <?= htmlspecialchars($settings['restaurant_name'] ?? 'Restaurant') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #0284c7;
            --primary-grad: linear-gradient(135deg, #0284c7, #06b6d4);
            --bg-color: #0f172a;
            --card-bg: rgba(255, 255, 255, 0.04);
            --card-border: rgba(255, 255, 255, 0.1);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --accent-green: #10b981;
            --accent-orange: #f59e0b;
        }

        body {
            margin: 0;
            padding: 16px;
            font-family: 'Outfit', sans-serif;
            background: var(--bg-color);
            color: var(--text-main);
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            box-sizing: border-box;
        }

        .container {
            width: 100%;
            max-width: 460px;
            background: #1e293b;
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            box-sizing: border-box;
        }

        .header {
            text-align: center;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--card-border);
            margin-bottom: 16px;
        }

        .logo {
            max-height: 48px;
            margin-bottom: 8px;
        }

        .restaurant-name {
            font-size: 18px;
            font-weight: 800;
            color: var(--text-main);
            margin: 0;
        }

        .order-badge {
            display: inline-block;
            background: rgba(2, 132, 199, 0.2);
            color: #38bdf8;
            border: 1px solid rgba(2, 132, 199, 0.3);
            font-weight: 800;
            font-size: 13px;
            padding: 4px 12px;
            border-radius: 20px;
            margin-top: 6px;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 14px;
            padding: 14px;
            margin-bottom: 14px;
        }

        .card-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13.5px;
            padding: 6px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .item-row:last-child {
            border-bottom: none;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 16px;
            font-weight: 800;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid var(--card-border);
            color: var(--accent-green);
        }

        .pay-pill {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            margin-top: 6px;
        }

        .pay-cod {
            background: rgba(245, 158, 11, 0.15);
            color: var(--accent-orange);
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .pay-paid {
            background: rgba(16, 185, 129, 0.15);
            color: var(--accent-green);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .btn-gps {
            width: 100%;
            background: var(--primary-grad);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(2, 132, 199, 0.35);
            transition: all 0.2s;
        }

        .btn-gps:active {
            transform: scale(0.98);
        }

        .form-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid var(--card-border);
            color: var(--text-main);
            border-radius: 10px;
            padding: 10px 12px;
            font-family: inherit;
            font-size: 13.5px;
            box-sizing: border-box;
            margin-bottom: 10px;
        }

        .form-input:focus {
            outline: none;
            border-color: #38bdf8;
            background: rgba(255, 255, 255, 0.09);
        }

        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .btn-submit {
            width: 100%;
            background: #10b981;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-family: inherit;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35);
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .success-box {
            text-align: center;
            padding: 30px 10px;
        }

        .check-icon {
            font-size: 50px;
            margin-bottom: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <?php if (!empty($settings['logo_path'])): ?>
                <img src="/<?= ltrim(htmlspecialchars($settings['logo_path']), '/') ?>" alt="Logo" class="logo">
            <?php endif; ?>
            <h1 class="restaurant-name"><?= htmlspecialchars($settings['restaurant_name'] ?? 'Gourmet Express') ?></h1>
            <div class="order-badge">Token #<?= htmlspecialchars($delivery['token_number'] ?? $delivery['order_id']) ?></div>
        </header>

        <?php if ($delivery['status'] === 'location_received' || $delivery['status'] === 'preparing_in_kitchen' || $delivery['status'] === 'pending_pickup' || $delivery['status'] === 'out_for_delivery' || $delivery['status'] === 'delivered'): ?>
            <!-- Already Confirmed Screen -->
            <div class="success-box">
                <div class="check-icon">🎉</div>
                <h2 style="font-size:20px; margin-bottom:8px; color:var(--accent-green);">Location Already Confirmed!</h2>
                <p style="color:var(--text-muted); font-size:13.5px; line-height:1.5;">
                    Thank you! We have received your delivery location and instructions.
                    <br>Our kitchen and delivery team are handling your order.
                </p>
                <?php if (!empty($delivery['google_maps_link'])): ?>
                    <div style="margin-top:16px;">
                        <a href="<?= htmlspecialchars($delivery['google_maps_link']) ?>" target="_blank" style="color:#38bdf8; text-decoration:none; font-size:13px; font-weight:700;">
                            🗺️ View Confirmed Location on Map
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Order Summary Card -->
            <div class="card">
                <div class="card-title">🛒 Order Summary</div>
                <?php if (!empty($delivery['items'])): ?>
                    <?php foreach ($delivery['items'] as $it): ?>
                        <?php 
                            $unitPrice = (float)($it['price'] ?? $it['unit_price'] ?? 0);
                            $qty = (int)($it['quantity'] ?? 1);
                        ?>
                        <div class="item-row">
                            <span><?= $qty ?>x <?= htmlspecialchars($it['product_name'] ?? 'Item') ?></span>
                            <span style="font-weight:600;"><?= number_format($unitPrice * $qty, 3) ?> <?= htmlspecialchars($settings['currency_code'] ?? 'BHD') ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="total-row">
                    <span>Total:</span>
                    <span><?= number_format((float)($delivery['order_total'] ?? 0), 3) ?> <?= htmlspecialchars($settings['currency_code'] ?? 'BHD') ?></span>
                </div>

                <div style="text-align:center; margin-top:8px;">
                    <?php if ($delivery['payment_type'] === 'cod'): ?>
                        <span class="pay-pill pay-cod">💵 Pay on Delivery: <?= number_format((float)($delivery['cod_amount'] ?? $delivery['order_total']), 3) ?> <?= htmlspecialchars($settings['currency_code'] ?? 'BHD') ?></span>
                    <?php else: ?>
                        <span class="pay-pill pay-paid">✅ Already Paid</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($delivery['payment_type'] === 'cod' && !empty($settings['benefit_pay_qr'])): ?>
                <div class="card" style="text-align: center; background: rgba(16, 185, 129, 0.06); border: 1.5px dashed rgba(16, 185, 129, 0.35);">
                    <div style="font-weight: 800; color: #10b981; font-size: 14px; margin-bottom: 4px;">
                        📱 Pay via BenefitPay QR
                    </div>
                    <div style="font-size: 11.5px; color: var(--text-muted); margin-bottom: 10px;">
                        You can also pay via BenefitPay upon delivery by scanning this QR:
                    </div>
                    <div style="background: white; padding: 8px; border-radius: 12px; display: inline-block; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                        <img src="/<?= ltrim($settings['benefit_pay_qr'], '/') ?>" alt="BenefitPay QR" style="width: 150px; height: 150px; object-fit: contain; display: block;">
                    </div>
                    <div style="margin-top: 6px; font-weight: 700; color: #10b981; font-size: 13.5px;">
                        Amount: <?= number_format((float)($delivery['cod_amount'] ?? $delivery['order_total']), 3) ?> <?= htmlspecialchars($settings['currency_code'] ?? 'BHD') ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Location & Address Form -->
            <form id="confirm-form" onsubmit="submitConfirmation(event)">
                <div class="card">
                    <div class="card-title">📍 Share Your Delivery Location</div>
                    
                    <button type="button" class="btn-gps" id="btn-share-gps" onclick="captureGpsLocation()">
                        <span style="font-size:18px;">📍</span>
                        <span>Tap to Share Current Location</span>
                    </button>

                    <div id="gps-status-box" style="display:none; background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.25); border-radius:10px; padding:10px; margin-top:10px; text-align:center;">
                    </div>
                    <input type="hidden" id="maps-link-input" name="google_maps_link" value="<?= htmlspecialchars($delivery['google_maps_link'] ?? '') ?>">

                    <div style="margin-top:14px;">
                        <label class="form-label">Delivery Address (Building, Road, Block, Area)</label>
                        <textarea class="form-input" id="address-input" rows="2" placeholder="e.g. Building 123, Road 45, Block 678, Area"><?= htmlspecialchars($delivery['customer_address'] ?? '') ?></textarea>
                    </div>

                    <div>
                        <label class="form-label">Special Delivery Instructions (Optional)</label>
                        <input type="text" class="form-input" id="notes-input" placeholder="e.g. Ring bell, Leave at reception, Call on arrival" value="<?= htmlspecialchars($delivery['delivery_notes'] ?? '') ?>">
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="btn-confirm-submit">
                    <span>✅</span> Confirm & Send Location
                </button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        function captureGpsLocation() {
            const btn = document.getElementById('btn-share-gps');
            const statusBox = document.getElementById('gps-status-box');
            
            if (!navigator.geolocation) {
                Swal.fire('GPS Error', 'Geolocation is not supported by your browser. Please type your address below.', 'warning');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span>⏳</span> Locating your GPS position...';

            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    const mapsUrl = `https://www.google.com/maps?q=${lat},${lng}`;
                    
                    document.getElementById('maps-link-input').value = mapsUrl;
                    
                    statusBox.style.display = 'block';
                    statusBox.innerHTML = `
                        <div style="color:var(--accent-green); font-weight:800; font-size:13px; margin-bottom:4px;">
                            ✅ GPS Location Captured!
                        </div>
                        <a href="${mapsUrl}" target="_blank" style="color:#38bdf8; text-decoration:underline; font-size:12px; font-weight:600;">
                            🗺️ Preview on Google Maps
                        </a>
                    `;

                    btn.disabled = false;
                    btn.style.background = '#10b981';
                    btn.innerHTML = '<span>✅</span> Location Captured (Tap to re-detect)';
                },
                function(err) {
                    btn.disabled = false;
                    btn.innerHTML = '<span>📍</span> Tap to Share Current Location';
                    
                    let msg = 'Could not detect your GPS location automatically. Please enter your address manually below.';
                    if (err.code === 1) {
                        msg = 'Location permission was denied. Please allow location access in your browser settings, or enter your address below.';
                    }
                    Swal.fire({
                        icon: 'info',
                        title: 'Location Notice',
                        text: msg,
                        confirmButtonColor: '#0284c7'
                    });
                },
                { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
            );
        }

        function submitConfirmation(e) {
            e.preventDefault();
            const mapsLink = document.getElementById('maps-link-input').value.trim();
            const address = document.getElementById('address-input').value.trim();
            const notes = document.getElementById('notes-input').value.trim();

            if (!mapsLink && !address) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Location Required',
                    text: 'Please tap "Share Current Location" or type your delivery address so our driver can find you.',
                    confirmButtonColor: '#0284c7'
                });
                return;
            }

            const btn = document.getElementById('btn-confirm-submit');
            btn.disabled = true;
            btn.innerHTML = '<span>⏳</span> Confirming...';

            const formData = new FormData();
            formData.append('google_maps_link', mapsLink);
            formData.append('customer_address', address);
            formData.append('delivery_notes', notes);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Confirmed! 🎉',
                        text: 'Your delivery location has been received by the restaurant.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    btn.disabled = false;
                    btn.innerHTML = '<span>✅</span> Confirm & Send Location';
                    Swal.fire('Error', data.error || 'Failed to submit confirmation.', 'error');
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = '<span>✅</span> Confirm & Send Location';
                Swal.fire('Error', 'Network error. Please try again.', 'error');
            });
        }
    </script>
</body>
</html>

