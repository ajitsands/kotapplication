<?php
$shiftCfg = getShopShiftConfig($settings);
$shiftWin = getShopShiftWindow('today', $settings);
$curCode = $settings['currency_code'] ?? 'BHD';
$threeDec = in_array(strtoupper(trim($curCode)), ['BHD', 'KWD', 'OMR', 'IQD', 'JOD', 'TND', 'LYD']);
$decimals = $threeDec ? 3 : 2;
$stepVal = $threeDec ? '0.001' : '0.01';
$nowLocal = date('Y-m-d\TH:i');
$userRole = $_SESSION['user_role'] ?? $_SESSION['role'] ?? 'counter';
$userName = $_SESSION['name'] ?? $_SESSION['username'] ?? 'Staff';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petty Cash Book | <?= htmlspecialchars($settings['restaurant_name'] ?? 'POS') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --bg: #0b1120;
            --card-bg: #111827;
            --card-border: rgba(255, 255, 255, 0.08);
            --text-color: #f3f4f6;
            --text-muted: #9ca3af;
            --input-bg: rgba(255, 255, 255, 0.04);
            --primary: #6366f1;
        }
        body.light-theme {
            --bg: #f1f5f9;
            --card-bg: #ffffff;
            --card-border: #e2e8f0;
            --text-color: #0f172a;
            --text-muted: #64748b;
            --input-bg: #f8fafc;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background: var(--bg); color: var(--text-color); min-height: 100vh; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .topbar {
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;
            background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 16px;
            padding: 14px 20px; margin-bottom: 20px;
        }
        .title-wrap { display: flex; align-items: center; gap: 12px; }
        .title-wrap h1 { font-size: 20px; font-weight: 800; }
        .badge-role {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            padding: 4px 10px; border-radius: 20px; background: rgba(99,102,241,0.16); color: #818cf8;
            border: 1px solid rgba(99,102,241,0.35);
        }
        .grid-layout { display: grid; grid-template-columns: 380px 1fr; gap: 20px; align-items: start; }
        @media (max-width: 960px) { .grid-layout { grid-template-columns: 1fr; } }
        .panel-card {
            background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 16px; padding: 20px;
        }
        .type-toggle { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 16px; }
        .type-btn {
            padding: 12px; border-radius: 12px; border: 2px solid var(--card-border);
            background: var(--input-bg); color: var(--text-muted); font-weight: 800; font-size: 14px;
            cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .type-btn.active-expense {
            border-color: #ef4444; background: rgba(239, 68, 68, 0.14); color: #ef4444;
        }
        .type-btn.active-income {
            border-color: #10b981; background: rgba(16, 185, 129, 0.14); color: #10b981;
        }
        .form-group { margin-bottom: 14px; }
        .form-label { display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.4px; }
        .form-input, .form-select, .form-textarea {
            width: 100%; padding: 10px 12px; border-radius: 10px; border: 1px solid var(--card-border);
            background: var(--input-bg); color: var(--text-color); font-size: 14px; outline: none;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus { border-color: var(--primary); }
        .btn-submit {
            width: 100%; padding: 12px 16px; border-radius: 12px; border: none;
            font-size: 14px; font-weight: 800; color: white; cursor: pointer;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
        }
        .kpi-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 16px; }
        @media (max-width: 600px) { .kpi-row { grid-template-columns: 1fr; } }
        .kpi-card {
            padding: 14px 16px; border-radius: 14px; border: 1px solid var(--card-border);
            background: rgba(255,255,255,0.02);
        }
        .kpi-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 4px; }
        .kpi-val { font-size: 20px; font-weight: 900; }
        .filter-bar {
            display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 14px;
            padding: 12px; border-radius: 12px; border: 1px solid var(--card-border); background: rgba(255,255,255,0.02);
        }
        .preset-btn {
            padding: 6px 11px; border-radius: 8px; border: 1px solid var(--card-border);
            background: transparent; color: var(--text-muted); font-size: 12px; font-weight: 700; cursor: pointer;
        }
        .preset-btn.active { border-color: #6366f1; background: rgba(99,102,241,0.16); color: #818cf8; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { padding: 10px 12px; border-bottom: 1px solid var(--card-border); text-align: left; }
        th { font-size: 11px; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.4px; }
        .pill {
            display: inline-block; padding: 3px 9px; border-radius: 20px; font-size: 11px; font-weight: 800; text-transform: uppercase;
        }
        .pill-expense { background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); }
        .pill-income { background: rgba(16,185,129,0.15); color: #10b981; border: 1px solid rgba(16,185,129,0.3); }
        .btn-del {
            background: rgba(239,68,68,0.12); color: #ef4444; border: 1px solid rgba(239,68,68,0.3);
            padding: 5px 10px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer;
        }
        .btn-top {
            padding: 8px 14px; border-radius: 10px; border: 1px solid var(--card-border);
            background: var(--input-bg); color: var(--text-color); font-size: 13px; font-weight: 700; cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="topbar">
            <div class="title-wrap">
                <span style="font-size: 26px;">💵</span>
                <div>
                    <h1>Petty Cash Book (Expense &amp; Income)</h1>
                    <div style="font-size: 12px; color: var(--text-muted);">
                        Logged in as <strong><?= htmlspecialchars($userName) ?></strong>
                        <span class="badge-role" style="margin-left: 6px;"><?= htmlspecialchars($userRole) ?></span>
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <button type="button" class="btn-top" onclick="toggleTheme()">🌓 Theme</button>
                <button type="button" class="btn-top" onclick="window.close()">✖ Close Window</button>
            </div>
        </div>

        <div class="grid-layout">
            <!-- Booking Form -->
            <div class="panel-card">
                <h2 style="font-size: 16px; font-weight: 800; margin-bottom: 14px;">📝 Book Petty Cash Entry</h2>
                <form id="petty-cash-form" onsubmit="submitPettyCash(event)">
                    <div class="type-toggle">
                        <button type="button" id="btn-type-expense" class="type-btn active-expense" onclick="setEntryType('expense')">
                            📤 Expense (Out)
                        </button>
                        <button type="button" id="btn-type-income" class="type-btn" onclick="setEntryType('income')">
                            📥 Income (In)
                        </button>
                    </div>
                    <input type="hidden" id="pc-type" value="expense">

                    <div class="form-group">
                        <label class="form-label">Particulars / Title *</label>
                        <input type="text" id="pc-title" class="form-input" placeholder="e.g. Milk & Tea, Ice, Opening Float..." required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select id="pc-category" class="form-select">
                            <option value="Supplies">Supplies / Groceries</option>
                            <option value="Utilities">Utilities / Gas / Water</option>
                            <option value="Transport">Transport / Delivery</option>
                            <option value="Staff">Staff Welfare / Advance</option>
                            <option value="Maintenance">Maintenance / Repairs</option>
                            <option value="Float / Cash In">Float / Cash In</option>
                            <option value="Other Income">Other Income</option>
                            <option value="General">General</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Amount (<?= htmlspecialchars($curCode) ?>) *</label>
                        <input type="number" id="pc-amount" class="form-input" step="<?= $stepVal ?>" min="<?= $stepVal ?>" placeholder="0.000" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Date &amp; Time</label>
                        <input type="datetime-local" id="pc-datetime" class="form-input" value="<?= $nowLocal ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notes / Remarks</label>
                        <textarea id="pc-notes" class="form-textarea" rows="2" placeholder="Optional bill ref, paid to, etc."></textarea>
                    </div>

                    <button type="submit" id="pc-submit-btn" class="btn-submit">📤 Book Petty Cash Expense</button>
                </form>
            </div>

            <!-- Entries List & Shift Filter -->
            <div class="panel-card">
                <div class="kpi-row">
                    <div class="kpi-card" style="border-color: rgba(16,185,129,0.3);">
                        <div class="kpi-label">Petty Cash Income (+)</div>
                        <div class="kpi-val" style="color: #10b981;" id="kpi-pc-income">0.000 <?= htmlspecialchars($curCode) ?></div>
                    </div>
                    <div class="kpi-card" style="border-color: rgba(239,68,68,0.3);">
                        <div class="kpi-label">Petty Cash Expense (-)</div>
                        <div class="kpi-val" style="color: #ef4444;" id="kpi-pc-expense">0.000 <?= htmlspecialchars($curCode) ?></div>
                    </div>
                    <div class="kpi-card" style="border-color: rgba(99,102,241,0.35);">
                        <div class="kpi-label">Net Petty Cash (=)</div>
                        <div class="kpi-val" style="color: #818cf8;" id="kpi-pc-net">0.000 <?= htmlspecialchars($curCode) ?></div>
                    </div>
                </div>

                <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 10px; align-items: center;">
                    <span style="font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Quick Shifts:</span>
                    <button type="button" class="preset-btn active" id="pc-preset-current" onclick="setPcPreset('current')">🌙 Shift (<?= htmlspecialchars($shiftCfg['label']) ?>)</button>
                    <button type="button" class="preset-btn" id="pc-preset-yesterday" onclick="setPcPreset('yesterday_shift')">🌙 Yesterday Shift</button>
                    <button type="button" class="preset-btn" id="pc-preset-today" onclick="setPcPreset('today')">📅 Today</button>
                </div>

                <div class="filter-bar">
                    <div>
                        <label class="form-label" style="margin-bottom: 3px;">From Date</label>
                        <input type="date" id="pc-from-date" class="form-input" style="padding: 7px 10px; width: 145px;" value="<?= htmlspecialchars($shiftWin['start_date']) ?>">
                    </div>
                    <div>
                        <label class="form-label" style="margin-bottom: 3px;">From Time</label>
                        <input type="time" id="pc-from-time" class="form-input" style="padding: 7px 10px; width: 125px;" value="<?= htmlspecialchars($shiftCfg['start_time']) ?>">
                    </div>
                    <div>
                        <label class="form-label" style="margin-bottom: 3px;">To Date</label>
                        <input type="date" id="pc-to-date" class="form-input" style="padding: 7px 10px; width: 145px;" value="<?= htmlspecialchars($shiftWin['end_date']) ?>">
                    </div>
                    <div>
                        <label class="form-label" style="margin-bottom: 3px;">To Time</label>
                        <input type="time" id="pc-to-time" class="form-input" style="padding: 7px 10px; width: 125px;" value="<?= htmlspecialchars($shiftCfg['end_time']) ?>">
                    </div>
                    <button type="button" onclick="loadPettyCashList()" style="padding: 9px 16px; border-radius: 10px; border: none; background: #6366f1; color: white; font-weight: 700; cursor: pointer;">Filter</button>
                </div>

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Particulars</th>
                                <th>Category</th>
                                <th>Date &amp; Time</th>
                                <th>Booked By</th>
                                <th style="text-align: right;">Amount</th>
                                <th>Notes</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="pc-table-body">
                            <tr><td colspan="8" style="text-align: center; padding: 24px; color: var(--text-muted);">Loading petty cash entries...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.SHOP_SHIFT = <?= json_encode($shiftCfg) ?>;
        const CUR_CODE = <?= json_encode($curCode) ?>;
        const DECIMALS = <?= (int)$decimals ?>;
        const ROOT_PATH = (() => {
            let p = window.location.pathname;
            p = p.replace(/\/(admin|counter)\/petty-cash\/?$/, '').replace(/\/petty-cash\/?$/, '');
            return p.endsWith('/') ? p.slice(0, -1) : p;
        })();

        if (localStorage.getItem('theme') === 'light') {
            document.body.classList.add('light-theme');
        }
        function toggleTheme() {
            document.body.classList.toggle('light-theme');
            localStorage.setItem('theme', document.body.classList.contains('light-theme') ? 'light' : 'dark');
        }

        function esc(s) {
            return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function setEntryType(type) {
            document.getElementById('pc-type').value = type;
            const btnExp = document.getElementById('btn-type-expense');
            const btnInc = document.getElementById('btn-type-income');
            const submitBtn = document.getElementById('pc-submit-btn');
            const catSel = document.getElementById('pc-category');
            if (type === 'income') {
                btnExp.className = 'type-btn';
                btnInc.className = 'type-btn active-income';
                submitBtn.textContent = '📥 Book Petty Cash Income';
                submitBtn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                if (catSel.value === 'Supplies') catSel.value = 'Float / Cash In';
            } else {
                btnExp.className = 'type-btn active-expense';
                btnInc.className = 'type-btn';
                submitBtn.textContent = '📤 Book Petty Cash Expense';
                submitBtn.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
                if (catSel.value === 'Float / Cash In' || catSel.value === 'Other Income') catSel.value = 'Supplies';
            }
        }

        function setPcPreset(preset) {
            document.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('active'));
            const btnId = preset === 'yesterday_shift' ? 'pc-preset-yesterday' : (preset === 'today' ? 'pc-preset-today' : 'pc-preset-current');
            document.getElementById(btnId)?.classList.add('active');

            const now = new Date();
            const pad = n => String(n).padStart(2, '0');
            const fmt = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
            const cfg = window.SHOP_SHIFT || { start_time: '15:00', end_time: '04:00', start_minutes: 900, is_overnight: true };
            const curMin = now.getHours() * 60 + now.getMinutes();

            let sDate = fmt(now), eDate = fmt(now), sTime = cfg.start_time, eTime = cfg.end_time;
            if (preset === 'today') {
                sTime = '00:00'; eTime = '23:59';
            } else {
                const sd = new Date(now), ed = new Date(now);
                if (cfg.is_overnight) {
                    if (curMin >= cfg.start_minutes) ed.setDate(ed.getDate() + 1);
                    else sd.setDate(sd.getDate() - 1);
                }
                if (preset === 'yesterday_shift') {
                    sd.setDate(sd.getDate() - 1);
                    ed.setDate(ed.getDate() - 1);
                }
                sDate = fmt(sd); eDate = fmt(ed);
            }
            document.getElementById('pc-from-date').value = sDate;
            document.getElementById('pc-from-time').value = sTime;
            document.getElementById('pc-to-date').value = eDate;
            document.getElementById('pc-to-time').value = eTime;
            loadPettyCashList();
        }

        function loadPettyCashList() {
            const fd = document.getElementById('pc-from-date').value;
            const ft = document.getElementById('pc-from-time').value || '00:00';
            const td = document.getElementById('pc-to-date').value;
            const tt = document.getElementById('pc-to-time').value || '23:59';
            const startDT = `${fd} ${ft}:00`;
            const endDT = `${td} ${tt}:59`;

            fetch(`${ROOT_PATH}/petty-cash/list?startDateTime=${encodeURIComponent(startDT)}&endDateTime=${encodeURIComponent(endDT)}&_=${Date.now()}`, { cache: 'no-store' })
                .then(r => r.json())
                .then(res => {
                    if (res.status !== 'success') return;
                    const d = res.data;
                    document.getElementById('kpi-pc-income').textContent = `+${parseFloat(d.income_total || 0).toFixed(DECIMALS)} ${CUR_CODE}`;
                    document.getElementById('kpi-pc-expense').textContent = `-${parseFloat(d.expense_total || 0).toFixed(DECIMALS)} ${CUR_CODE}`;
                    const net = parseFloat(d.net_total || 0);
                    const netEl = document.getElementById('kpi-pc-net');
                    netEl.textContent = `${net >= 0 ? '+' : ''}${net.toFixed(DECIMALS)} ${CUR_CODE}`;
                    netEl.style.color = net >= 0 ? '#10b981' : '#ef4444';

                    const tbody = document.getElementById('pc-table-body');
                    if (!d.items || d.items.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 24px; color: var(--text-muted);">No petty cash entries in this shift window.</td></tr>';
                        return;
                    }
                    tbody.innerHTML = d.items.map(it => {
                        const isInc = it.entry_type === 'income';
                        return `
                            <tr>
                                <td><span class="pill ${isInc ? 'pill-income' : 'pill-expense'}">${isInc ? 'INCOME' : 'EXPENSE'}</span></td>
                                <td style="font-weight: 700;">${esc(it.title)}</td>
                                <td style="color: var(--text-muted);">${esc(it.category)}</td>
                                <td style="color: var(--text-muted); font-size: 12px;">${esc(it.entry_datetime_formatted)}</td>
                                <td style="color: var(--text-muted); font-size: 12px;">${esc(it.user_name)}</td>
                                <td style="text-align: right; font-weight: 800; color: ${isInc ? '#10b981' : '#ef4444'};">
                                    ${isInc ? '+' : '-'}${parseFloat(it.amount).toFixed(DECIMALS)} ${esc(CUR_CODE)}
                                </td>
                                <td style="color: var(--text-muted); font-size: 12px;">${esc(it.notes || '-')}</td>
                                <td style="text-align: right;">
                                    <button type="button" class="btn-del" onclick="deletePettyCash(${it.id})">Delete</button>
                                </td>
                            </tr>
                        `;
                    }).join('');
                })
                .catch(err => console.error(err));
        }

        function submitPettyCash(e) {
            e.preventDefault();
            const payload = {
                entry_type: document.getElementById('pc-type').value,
                title: document.getElementById('pc-title').value.trim(),
                category: document.getElementById('pc-category').value,
                amount: parseFloat(document.getElementById('pc-amount').value || 0),
                entry_datetime: document.getElementById('pc-datetime').value,
                notes: document.getElementById('pc-notes').value.trim()
            };
            fetch(`${ROOT_PATH}/petty-cash/store`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    document.getElementById('pc-title').value = '';
                    document.getElementById('pc-amount').value = '';
                    document.getElementById('pc-notes').value = '';
                    loadPettyCashList();
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 2000 });
                } else {
                    Swal.fire('Error', res.error || 'Failed to save entry', 'error');
                }
            })
            .catch(() => Swal.fire('Error', 'Network error', 'error'));
        }

        function deletePettyCash(id) {
            Swal.fire({
                title: 'Delete Petty Cash Entry?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Yes, Delete'
            }).then(r => {
                if (!r.isConfirmed) return;
                fetch(`${ROOT_PATH}/petty-cash/delete/${id}`, { method: 'POST' })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) loadPettyCashList();
                        else Swal.fire('Error', res.error || 'Could not delete', 'error');
                    });
            });
        }

        setEntryType('expense');
        loadPettyCashList();
    </script>
</body>
</html>
