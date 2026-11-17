<?php
$pcShiftCfg = getShopShiftConfig($settings);
$pcShiftWin = getShopShiftWindow('today', $settings);
$pcCurCode = $settings['currency_code'] ?? 'BHD';
$pcThreeDec = in_array(strtoupper(trim($pcCurCode)), ['BHD', 'KWD', 'OMR', 'IQD', 'JOD', 'TND', 'LYD']);
$pcDecimals = $pcThreeDec ? 3 : 2;
$pcStepVal = $pcThreeDec ? '0.001' : '0.01';
$pcNowLocal = date('Y-m-d\TH:i');
?>
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 18px;">
    <div>
        <h2 class="panel-title" style="margin-bottom: 4px;">💵 Petty Cash Book (Expense &amp; Income)</h2>
        <div style="color: var(--text-muted); font-size: 13px;">Record petty cash expenses and income for the active shift. Entries automatically appear in the Total Collection Report.</div>
    </div>
</div>

<style>
    .ipc-grid {
        display: grid;
        grid-template-columns: minmax(300px, 4fr) minmax(0, 8fr);
        gap: 20px;
        align-items: start;
    }
    @media (max-width: 960px) {
        .ipc-grid {
            grid-template-columns: 1fr;
        }
    }
    .ipc-form-card .form-input,
    .ipc-form-card select.form-input {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        display: block;
    }
</style>

<div class="ipc-grid">
    <!-- Booking Form (4 cols) -->
    <div class="ipc-form-card" style="background: rgba(255,255,255,0.02); border: 1px solid var(--card-border); border-radius: 14px; padding: 18px;">
        <h3 style="font-size: 15px; font-weight: 800; margin: 0 0 10px 0;">📝 Book Petty Cash Entry</h3>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; border-radius: 10px; border: 1px solid rgba(245,158,11,0.3); background: rgba(245,158,11,0.08); margin-bottom: 12px; font-size: 12px;">
            <span style="color: var(--text-muted); font-weight: 700;">💵 Cash Collection Available:</span>
            <strong style="color: #f59e0b; font-size: 13px;" id="ipc-avail-cash">0.000 <?= htmlspecialchars($pcCurCode) ?></strong>
        </div>
        <form id="inline-petty-cash-form" novalidate onsubmit="submitInlinePettyCash(event)">
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 6px; margin-bottom: 14px;">
                <button type="button" id="ipc-btn-expense" onclick="setInlinePcType('expense')" style="padding: 9px 6px; border-radius: 10px; border: 2px solid #ef4444; background: rgba(239,68,68,0.14); color: #ef4444; font-weight: 800; font-size: 12px; cursor: pointer;">
                    📤 Expense
                </button>
                <button type="button" id="ipc-btn-income" onclick="setInlinePcType('income')" style="padding: 9px 6px; border-radius: 10px; border: 2px solid var(--card-border); background: transparent; color: var(--text-muted); font-weight: 800; font-size: 12px; cursor: pointer;">
                    📥 Income
                </button>
                <button type="button" id="ipc-btn-cash_transfer" onclick="setInlinePcType('cash_transfer')" style="padding: 9px 6px; border-radius: 10px; border: 2px solid var(--card-border); background: transparent; color: var(--text-muted); font-weight: 800; font-size: 12px; cursor: pointer;">
                    💵 From Cash
                </button>
            </div>
            <input type="hidden" id="ipc-type" value="expense">

            <div style="margin-bottom: 12px;">
                <label class="form-label" style="font-size: 11px; display: block; margin-bottom: 4px;">Particulars / Title *</label>
                <input type="text" id="ipc-title" class="form-input" style="margin-bottom: 0;" placeholder="e.g. Milk &amp; Tea, Ice, Opening Float..." required>
            </div>

            <div style="margin-bottom: 12px;">
                <label class="form-label" id="ipc-party-label" style="font-size: 11px; display: block; margin-bottom: 4px;">Paid To (Whom Paid)</label>
                <input type="text" id="ipc-party" class="form-input" style="margin-bottom: 0;" placeholder="e.g. Supplier, Driver, Staff Name...">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                <div style="min-width: 0;">
                    <label class="form-label" style="font-size: 11px; display: block; margin-bottom: 4px;">Category</label>
                    <select id="ipc-category" class="form-input" style="margin-bottom: 0;">
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
                <div style="min-width: 0;">
                    <label class="form-label" style="font-size: 11px; display: block; margin-bottom: 4px;">Amount (<?= htmlspecialchars($pcCurCode) ?>) *</label>
                    <input type="number" id="ipc-amount" class="form-input" style="margin-bottom: 0;" step="any" min="0" placeholder="0.000">
                </div>
            </div>

            <div style="margin-bottom: 12px;">
                <label class="form-label" style="font-size: 11px; display: block; margin-bottom: 4px;">Date &amp; Time</label>
                <input type="datetime-local" id="ipc-datetime" class="form-input" style="margin-bottom: 0;" value="<?= $pcNowLocal ?>">
            </div>

            <div style="margin-bottom: 12px;">
                <label class="form-label" style="font-size: 11px; display: block; margin-bottom: 4px;">Notes / Remarks</label>
                <input type="text" id="ipc-notes" class="form-input" style="margin-bottom: 0;" placeholder="Optional bill ref, paid to, etc.">
            </div>

            <div id="ipc-deduct-cash-wrap" style="margin-bottom: 14px; padding: 9px 12px; border-radius: 10px; border: 1px solid rgba(245,158,11,0.3); background: rgba(245,158,11,0.06);">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 12px; font-weight: 700; color: var(--text-color);">
                    <input type="checkbox" id="ipc-deduct-cash" style="width: 16px; height: 16px; accent-color: #f59e0b; cursor: pointer;">
                    <span>Deduct directly from Cash Collection (1-step)</span>
                </label>
            </div>

            <button type="button" id="ipc-submit-btn" onclick="submitInlinePettyCash(event)" style="width: 100%; padding: 11px 16px; border-radius: 10px; border: none; font-size: 14px; font-weight: 800; color: white; cursor: pointer; background: linear-gradient(135deg, #ef4444, #dc2626);">
                📤 Book Petty Cash Expense
            </button>
        </form>
    </div>

    <!-- Summary & Entries Table -->
    <div style="min-width: 0;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin-bottom: 14px;">
            <div style="padding: 12px 14px; border-radius: 12px; border: 1px solid rgba(16,185,129,0.3); background: rgba(16,185,129,0.06);">
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-muted);">Income (+)</div>
                <div style="font-size: 18px; font-weight: 900; color: #10b981;" id="ipc-kpi-income">0.000 <?= htmlspecialchars($pcCurCode) ?></div>
            </div>
            <div style="padding: 12px 14px; border-radius: 12px; border: 1px solid rgba(239,68,68,0.3); background: rgba(239,68,68,0.06);">
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-muted);">Expense (-)</div>
                <div style="font-size: 18px; font-weight: 900; color: #ef4444;" id="ipc-kpi-expense">0.000 <?= htmlspecialchars($pcCurCode) ?></div>
            </div>
            <div style="padding: 12px 14px; border-radius: 12px; border: 1px solid rgba(99,102,241,0.35); background: rgba(99,102,241,0.06);">
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-muted);">Net Petty Cash (=)</div>
                <div style="font-size: 18px; font-weight: 900; color: #818cf8;" id="ipc-kpi-net">0.000 <?= htmlspecialchars($pcCurCode) ?></div>
            </div>
        </div>

        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 10px;">
            <span style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Quick Shifts:</span>
            <button type="button" class="ipc-preset-btn active" id="ipc-preset-current" onclick="setInlinePcPreset('current')" style="padding: 5px 10px; border-radius: 8px; border: 1px solid #6366f1; background: rgba(99,102,241,0.16); color: #818cf8; font-size: 12px; font-weight: 700; cursor: pointer;">🌙 Shift (<?= htmlspecialchars($pcShiftCfg['label']) ?>)</button>
            <button type="button" class="ipc-preset-btn" id="ipc-preset-yesterday_shift" onclick="setInlinePcPreset('yesterday_shift')" style="padding: 5px 10px; border-radius: 8px; border: 1px solid var(--card-border); background: transparent; color: var(--text-muted); font-size: 12px; font-weight: 700; cursor: pointer;">🌙 Yesterday Shift</button>
            <button type="button" class="ipc-preset-btn" id="ipc-preset-today" onclick="setInlinePcPreset('today')" style="padding: 5px 10px; border-radius: 8px; border: 1px solid var(--card-border); background: transparent; color: var(--text-muted); font-size: 12px; font-weight: 700; cursor: pointer;">📅 Today</button>
        </div>

        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 14px; padding: 10px 12px; border-radius: 12px; border: 1px solid var(--card-border); background: rgba(255,255,255,0.02);">
            <div>
                <label class="form-label" style="font-size: 10px; display: block; margin-bottom: 3px;">From Date</label>
                <input type="date" id="ipc-from-date" class="form-input" style="padding: 6px 8px; font-size: 12px; width: 135px; margin-bottom: 0;" value="<?= htmlspecialchars($pcShiftWin['start_date']) ?>">
            </div>
            <div>
                <label class="form-label" style="font-size: 10px; display: block; margin-bottom: 3px;">From Time</label>
                <input type="time" id="ipc-from-time" class="form-input" style="padding: 6px 8px; font-size: 12px; width: 118px; margin-bottom: 0;" value="<?= htmlspecialchars($pcShiftCfg['start_time']) ?>">
            </div>
            <div>
                <label class="form-label" style="font-size: 10px; display: block; margin-bottom: 3px;">To Date</label>
                <input type="date" id="ipc-to-date" class="form-input" style="padding: 6px 8px; font-size: 12px; width: 135px; margin-bottom: 0;" value="<?= htmlspecialchars($pcShiftWin['end_date']) ?>">
            </div>
            <div>
                <label class="form-label" style="font-size: 10px; display: block; margin-bottom: 3px;">To Time</label>
                <input type="time" id="ipc-to-time" class="form-input" style="padding: 6px 8px; font-size: 12px; width: 118px; margin-bottom: 0;" value="<?= htmlspecialchars($pcShiftCfg['end_time']) ?>">
            </div>
            <button type="button" onclick="loadInlinePettyCash()" class="btn btn-primary" style="padding: 7px 14px; font-size: 12px; height: 34px;">Filter</button>
        </div>

        <div style="overflow-x: auto; border: 1px solid var(--card-border); border-radius: 12px;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="background: rgba(255,255,255,0.02); border-bottom: 1px solid var(--card-border);">
                        <th style="padding: 10px; text-align: left;">Type</th>
                        <th style="padding: 10px; text-align: left;">Particulars</th>
                        <th style="padding: 10px; text-align: left;">Paid To / Received From</th>
                        <th style="padding: 10px; text-align: left;">Category</th>
                        <th style="padding: 10px; text-align: left;">Date &amp; Time</th>
                        <th style="padding: 10px; text-align: left;">By</th>
                        <th style="padding: 10px; text-align: right;">Amount</th>
                        <th style="padding: 10px; text-align: left;">Notes</th>
                        <th style="padding: 10px; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody id="ipc-table-body">
                    <tr><td colspan="9" style="text-align: center; padding: 20px; color: var(--text-muted);">Loading petty cash entries...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    (function() {
        const IPC_CUR = <?= json_encode($pcCurCode) ?>;
        const IPC_DEC = <?= (int)$pcDecimals ?>;
        const getRootPath = () => {
            let p = window.location.pathname.endsWith('/') ? window.location.pathname.slice(0, -1) : window.location.pathname;
            return p.replace(/\/(admin|counter)$/, '');
        };
        const escHtml = (s) => String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

        window.setInlinePcType = function(type) {
            document.getElementById('ipc-type').value = type;
            const btnExp = document.getElementById('ipc-btn-expense');
            const btnInc = document.getElementById('ipc-btn-income');
            const btnCash = document.getElementById('ipc-btn-cash_transfer');
            const subBtn = document.getElementById('ipc-submit-btn');
            const catSel = document.getElementById('ipc-category');
            const partyLbl = document.getElementById('ipc-party-label');
            const partyInp = document.getElementById('ipc-party');
            const titleInp = document.getElementById('ipc-title');
            const deductWrap = document.getElementById('ipc-deduct-cash-wrap');
            const deductChk = document.getElementById('ipc-deduct-cash');

            [btnExp, btnInc, btnCash].forEach(b => {
                if (!b) return;
                b.style.borderColor = 'var(--card-border)';
                b.style.background = 'transparent';
                b.style.color = 'var(--text-muted)';
            });

            if (type === 'income') {
                btnInc.style.borderColor = '#10b981'; btnInc.style.background = 'rgba(16,185,129,0.14)'; btnInc.style.color = '#10b981';
                subBtn.textContent = '📥 Book Petty Cash Income';
                subBtn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                if (partyLbl) partyLbl.textContent = 'Received From (Whom Received)';
                if (partyInp) partyInp.placeholder = 'e.g. Owner, Manager, Customer...';
                if (titleInp) titleInp.placeholder = 'e.g. Opening Float, Cash Added...';
                if (deductWrap) deductWrap.style.display = 'none';
                if (deductChk) deductChk.checked = false;
                if (catSel.value === 'Supplies') catSel.value = 'Float / Cash In';
            } else if (type === 'cash_transfer') {
                btnCash.style.borderColor = '#f59e0b'; btnCash.style.background = 'rgba(245,158,11,0.14)'; btnCash.style.color = '#f59e0b';
                subBtn.textContent = '💵 Transfer from Cash Collection';
                subBtn.style.background = 'linear-gradient(135deg, #f59e0b, #d97706)';
                if (partyLbl) partyLbl.textContent = 'Source';
                if (partyInp) { partyInp.placeholder = 'Cash Drawer'; }
                if (titleInp) titleInp.placeholder = 'e.g. Petty Cash Float from Cash Collection';
                if (deductWrap) deductWrap.style.display = 'none';
                if (deductChk) deductChk.checked = false;
                catSel.value = 'Float / Cash In';
            } else {
                btnExp.style.borderColor = '#ef4444'; btnExp.style.background = 'rgba(239,68,68,0.14)'; btnExp.style.color = '#ef4444';
                subBtn.textContent = '📤 Book Petty Cash Expense';
                subBtn.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
                if (partyLbl) partyLbl.textContent = 'Paid To (Whom Paid)';
                if (partyInp) partyInp.placeholder = 'e.g. Supplier, Driver, Staff Name...';
                if (titleInp) titleInp.placeholder = 'e.g. Milk & Tea, Ice, Water Company...';
                if (deductWrap) deductWrap.style.display = 'block';
                if (catSel.value === 'Float / Cash In' || catSel.value === 'Other Income') catSel.value = 'Supplies';
            }
        };

        window.setInlinePcPreset = function(preset) {
            document.querySelectorAll('.ipc-preset-btn').forEach(b => {
                b.style.borderColor = 'var(--card-border)';
                b.style.background = 'transparent';
                b.style.color = 'var(--text-muted)';
            });
            const active = document.getElementById('ipc-preset-' + preset);
            if (active) {
                active.style.borderColor = '#6366f1';
                active.style.background = 'rgba(99,102,241,0.16)';
                active.style.color = '#818cf8';
            }
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
            document.getElementById('ipc-from-date').value = sDate;
            document.getElementById('ipc-from-time').value = sTime;
            document.getElementById('ipc-to-date').value = eDate;
            document.getElementById('ipc-to-time').value = eTime;
            loadInlinePettyCash();
        };

        window.loadInlinePettyCash = function() {
            const fd = document.getElementById('ipc-from-date')?.value;
            const ft = document.getElementById('ipc-from-time')?.value || '00:00';
            const td = document.getElementById('ipc-to-date')?.value;
            const tt = document.getElementById('ipc-to-time')?.value || '23:59';
            if (!fd || !td) return;

            fetch(`${getRootPath()}/petty-cash/list?startDateTime=${encodeURIComponent(fd + ' ' + ft + ':00')}&endDateTime=${encodeURIComponent(td + ' ' + tt + ':59')}&_=${Date.now()}`, { cache: 'no-store' })
                .then(r => r.json())
                .then(res => {
                    if (res.status !== 'success') return;
                    const d = res.data;
                    const cs = res.cash_summary || {};
                    const availEl = document.getElementById('ipc-avail-cash');
                    if (availEl) {
                        availEl.textContent = `${parseFloat(cs.available_cash || 0).toFixed(IPC_DEC)} ${IPC_CUR}`;
                    }
                    document.getElementById('ipc-kpi-income').textContent = `+${parseFloat(d.income_total || 0).toFixed(IPC_DEC)} ${IPC_CUR}`;
                    document.getElementById('ipc-kpi-expense').textContent = `-${parseFloat(d.expense_total || 0).toFixed(IPC_DEC)} ${IPC_CUR}`;
                    const net = parseFloat(d.net_total || 0);
                    const netEl = document.getElementById('ipc-kpi-net');
                    netEl.textContent = `${net >= 0 ? '+' : ''}${net.toFixed(IPC_DEC)} ${IPC_CUR}`;
                    netEl.style.color = net >= 0 ? '#10b981' : '#ef4444';

                    const tbody = document.getElementById('ipc-table-body');
                    if (!d.items || d.items.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 20px; color: var(--text-muted);">No petty cash entries in this shift window.</td></tr>';
                        return;
                    }
                    tbody.innerHTML = d.items.map(it => {
                        const isInc = it.entry_type === 'income';
                        const isFromCash = it.source === 'cash_collection';
                        const badgeText = isFromCash ? 'FROM CASH' : (isInc ? 'INCOME' : 'EXPENSE');
                        const badgeStyle = isFromCash
                            ? 'background: rgba(245,158,11,0.16); color: #f59e0b;'
                            : (isInc ? 'background: rgba(16,185,129,0.15); color: #10b981;' : 'background: rgba(239,68,68,0.15); color: #ef4444;');
                        return `
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                <td style="padding: 10px;"><span style="padding: 3px 8px; border-radius: 20px; font-size: 11px; font-weight: 800; ${badgeStyle}">${badgeText}</span></td>
                                <td style="padding: 10px; font-weight: 700;">${escHtml(it.title)}</td>
                                <td style="padding: 10px; font-weight: 600;">${escHtml(it.party_name || '-')}</td>
                                <td style="padding: 10px; color: var(--text-muted);">${escHtml(it.category)}</td>
                                <td style="padding: 10px; color: var(--text-muted); font-size: 12px;">${escHtml(it.entry_datetime_formatted)}</td>
                                <td style="padding: 10px; color: var(--text-muted); font-size: 12px;">${escHtml(it.user_name)}</td>
                                <td style="padding: 10px; text-align: right; font-weight: 800; color: ${isInc ? '#10b981' : '#ef4444'};">${isInc ? '+' : '-'}${parseFloat(it.amount).toFixed(IPC_DEC)} ${escHtml(IPC_CUR)}</td>
                                <td style="padding: 10px; color: var(--text-muted); font-size: 12px;">${escHtml(it.notes || '-')}</td>
                                <td style="padding: 10px; text-align: right;">
                                    <button type="button" onclick="deleteInlinePettyCash(${it.id})" style="background: rgba(239,68,68,0.12); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); padding: 4px 9px; border-radius: 7px; font-size: 11px; font-weight: 700; cursor: pointer;">Delete</button>
                                </td>
                            </tr>
                        `;
                    }).join('');
                })
                .catch(err => console.error(err));
        };

        window.submitInlinePettyCash = function(e) {
            if (e && e.preventDefault) e.preventDefault();
            const entryType = document.getElementById('ipc-type').value || 'expense';
            const titleEl = document.getElementById('ipc-title');
            const amtEl = document.getElementById('ipc-amount');
            const title = (titleEl ? titleEl.value : '').trim();
            const amount = parseFloat(amtEl ? amtEl.value : '0') || 0;

            const showErr = (msg) => {
                if (typeof Swal !== 'undefined') Swal.fire('Notice', msg, 'warning');
                else alert(msg);
            };

            if (entryType !== 'cash_transfer' && !title) {
                showErr('Please enter Particulars / Title.');
                if (titleEl) titleEl.focus();
                return;
            }
            if (amount <= 0) {
                showErr('Please enter a valid Amount greater than 0.');
                if (amtEl) amtEl.focus();
                return;
            }

            const payload = {
                entry_type: entryType,
                title: title,
                party_name: (document.getElementById('ipc-party')?.value || '').trim(),
                category: document.getElementById('ipc-category')?.value || 'General',
                amount: amount,
                entry_datetime: document.getElementById('ipc-datetime')?.value || '',
                notes: (document.getElementById('ipc-notes')?.value || '').trim(),
                deduct_from_cash: !!document.getElementById('ipc-deduct-cash')?.checked
            };

            const subBtn = document.getElementById('ipc-submit-btn');
            const origText = subBtn ? subBtn.textContent : '';
            if (subBtn) { subBtn.disabled = true; subBtn.textContent = 'Saving...'; }

            fetch(`${getRootPath()}/petty-cash/store`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(async r => {
                const txt = await r.text();
                try { return JSON.parse(txt); } catch (_) { return { success: false, error: txt || ('HTTP ' + r.status) }; }
            })
            .then(res => {
                if (res && res.success) {
                    if (titleEl) titleEl.value = '';
                    if (document.getElementById('ipc-party')) document.getElementById('ipc-party').value = '';
                    if (amtEl) amtEl.value = '';
                    if (document.getElementById('ipc-notes')) document.getElementById('ipc-notes').value = '';
                    if (document.getElementById('ipc-deduct-cash')) document.getElementById('ipc-deduct-cash').checked = false;
                    loadInlinePettyCash();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 2000 });
                    }
                } else {
                    showErr((res && res.error) ? res.error : 'Failed to save entry');
                }
            })
            .catch(err => showErr('Network error: ' + (err && err.message ? err.message : 'Please try again')))
            .finally(() => {
                if (subBtn) { subBtn.disabled = false; subBtn.textContent = origText; }
            });
        };

        window.deleteInlinePettyCash = function(id) {
            const runDel = () => {
                fetch(`${getRootPath()}/petty-cash/delete/${id}`, { method: 'POST' })
                    .then(r => r.json())
                    .then(res => { if (res.success) loadInlinePettyCash(); });
            };
            if (typeof Swal !== 'undefined') {
                Swal.fire({ title: 'Delete Petty Cash Entry?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Yes, Delete' })
                    .then(r => { if (r.isConfirmed) runDel(); });
            } else if (confirm('Delete Petty Cash Entry?')) {
                runDel();
            }
        };
    })();
</script>
