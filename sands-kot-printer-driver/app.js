/**
 * SaNDS KOT Printer Driver - Main Controller
 * Integrated with key.sandslab.com License Authentication API
 */

const STORAGE_KEY_CONFIG = 'sands_kot_config';
const STORAGE_KEY_LICENSE = 'sands_kot_license';
const KEY_ACTIVATION_API = 'https://key.sandslab.com/public/api/activate';

const defaultConfig = {
    domain: '',
    is_configured: false,
    printerIp: '192.168.8.101',
    printerPort: 9100,
    printerSize: 80,
    printMode: 'direct',
    marginLeft: 5,
    marginRight: 5,
    licenseKey: '',
    time_zone: 'Asia/Bahrain'
};

let currentConfig = { ...defaultConfig };
let currentLicense = null;
let currentZoom = parseFloat(localStorage.getItem('sands_pos_zoom')) || 1.0;

// Initialize on DOM Ready
document.addEventListener('DOMContentLoaded', () => {
    loadSavedData();
    applyZoom(currentZoom);
    startHeaderClock();
    checkInitialState();
});

// Ordinal suffix helper: 1st, 2nd, 3rd, 4th, 9th, 21st, etc.
function getOrdinalSuffix(n) {
    const s = ['th', 'st', 'nd', 'rd'];
    const v = n % 100;
    return s[(v - 20) % 10] || s[v] || s[0];
}

// Format date like: "9th Oct 2026 10:20 AM"
function formatHeaderDateTime(d = new Date(), timeZone = (currentConfig.time_zone || 'Asia/Bahrain')) {
    try {
        const formatter = new Intl.DateTimeFormat('en-US', {
            timeZone: timeZone,
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
        
        const parts = formatter.formatToParts(d).reduce((acc, p) => {
            acc[p.type] = p.value;
            return acc;
        }, {});
        
        const day = parseInt(parts.day, 10);
        const dayWithSuffix = day + getOrdinalSuffix(day);
        const month = parts.month;
        const year = parts.year;
        const hour = parts.hour;
        const minute = parts.minute;
        const dayPeriod = parts.dayPeriod ? parts.dayPeriod.toUpperCase() : (parseInt(parts.hour, 10) >= 12 ? 'PM' : 'AM');
        
        return `${dayWithSuffix} ${month} ${year} ${hour}:${minute} ${dayPeriod}`;
    } catch (e) {
        const day = d.getDate();
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        let hours = d.getHours();
        const minutes = String(d.getMinutes()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        return `${day}${getOrdinalSuffix(day)} ${months[d.getMonth()]} ${d.getFullYear()} ${hours}:${minutes} ${ampm}`;
    }
}

// Fetch Admin configured timezone & settings from POS server
function fetchServerSettings() {
    if (!currentConfig.domain) return;
    let domain = currentConfig.domain.trim();
    let base = domain;
    if (!base.startsWith('http://') && !base.startsWith('https://')) {
        base = (base.includes('localhost') || base.includes('192.168.') || base.includes('127.0.0.1')) ? `http://${base}` : `https://${base}`;
    }
    
    fetch(`${base}/api/settings`)
        .then(res => res.json())
        .then(data => {
            if (data && data.time_zone) {
                currentConfig.time_zone = data.time_zone;
                localStorage.setItem(STORAGE_KEY_CONFIG, JSON.stringify(currentConfig));
            }
        })
        .catch(err => {
            // Keep existing configured timezone
        });
}

// Real-time Header Clock
function startHeaderClock() {
    const el = document.getElementById('header-datetime-text');
    if (!el) return;
    
    const update = () => {
        el.textContent = formatHeaderDateTime();
    };
    
    update();
    setInterval(update, 1000);
}

function applyZoom(zoom) {
    currentZoom = Math.min(1.5, Math.max(0.5, Math.round(zoom * 100) / 100));
    localStorage.setItem('sands_pos_zoom', currentZoom);

    const frame = document.getElementById('pos-frame');
    const zoomText = document.getElementById('zoom-level-text');
    if (zoomText) {
        zoomText.textContent = Math.round(currentZoom * 100) + '%';
    }

    if (frame) {
        frame.style.transform = `scale(${currentZoom})`;
        frame.style.width = `${(100 / currentZoom)}%`;
        frame.style.height = `${(100 / currentZoom)}%`;
    }
}

function adjustZoom(delta) {
    applyZoom(currentZoom + delta);
}

function resetZoom() {
    applyZoom(1.0);
}

function loadSavedData() {
    try {
        const savedCfg = localStorage.getItem(STORAGE_KEY_CONFIG);
        if (savedCfg) {
            currentConfig = { ...defaultConfig, ...JSON.parse(savedCfg) };
        }
        const savedLic = localStorage.getItem(STORAGE_KEY_LICENSE);
        if (savedLic) {
            currentLicense = JSON.parse(savedLic);
        }
    } catch (e) {
        console.warn('Error reading saved configuration:', e);
    }
}

function checkInitialState() {
    // Check if configuration has been completed and domain is entered
    if (!currentConfig.is_configured || !currentConfig.domain || currentConfig.domain.trim() === '') {
        const modal = document.getElementById('first-launch-modal');
        if (modal) {
            modal.style.display = 'flex';
            const domInput = document.getElementById('fl-domain');
            if (domInput) {
                domInput.value = currentConfig.domain || '';
            }
        }
    } else {
        updateStatusBadges();
        fetchServerSettings();
        loadPosPortal();
    }
}

function updateStatusBadges() {
    document.getElementById('status-portal-url').textContent = currentConfig.domain || 'Not Configured';
    document.getElementById('printer-status-text').textContent = `Printer: ${currentConfig.printerIp || 'None'}`;

    const licBadge = document.getElementById('license-badge');
    const licText = document.getElementById('license-status-text');

    if (currentLicense && currentLicense.token) {
        licBadge.querySelector('.status-dot').className = 'status-dot green';
        licText.textContent = 'Licensed';
    } else {
        licBadge.querySelector('.status-dot').className = 'status-dot orange';
        licText.textContent = 'Ready';
    }
}

function loadPosPortal() {
    const overlay = document.getElementById('loading-overlay');
    const frame = document.getElementById('pos-frame');
    
    if (!currentConfig.domain || !currentConfig.is_configured) {
        checkInitialState();
        return;
    }

    let targetUrl = currentConfig.domain.trim();
    if (!targetUrl.startsWith('http://') && !targetUrl.startsWith('https://')) {
        targetUrl = 'https://' + targetUrl;
    }

    let framedUrl = targetUrl;
    if (!framedUrl.includes('/counter') && !framedUrl.includes('/login') && !framedUrl.includes('/admin')) {
        framedUrl = targetUrl.replace(/\/+$/, '') + '/counter';
    }

    let separator = framedUrl.includes('?') ? '&' : '?';
    framedUrl = framedUrl + separator + 'driver_app=1';

    overlay.classList.remove('hidden');
    document.getElementById('loading-text').innerHTML = `
        <div>Connecting to ${currentConfig.domain}...</div>
        <button type="button" onclick="openSettingsModal()" style="margin-top:14px;background:rgba(255,255,255,0.18);border:1px solid rgba(255,255,255,0.35);color:#fff;padding:8px 16px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;">⚙️ Change Server Domain / Settings</button>
    `;

    frame.src = framedUrl;
    
    const loadTimer = setTimeout(() => {
        if (!overlay.classList.contains('hidden')) {
            document.getElementById('loading-text').innerHTML = `
                <div style="font-size:16px;margin-bottom:8px;color:#f87171;">⚠️ Connection Taking Time</div>
                <div style="font-size:13px;color:#94a3b8;margin-bottom:14px;">Server: <b>${currentConfig.domain}</b></div>
                <button type="button" onclick="openSettingsModal()" style="background:#6366f1;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;">⚙️ Reconfigure Domain / Settings</button>
            `;
        }
    }, 12000);

    frame.onload = () => {
        clearTimeout(loadTimer);
        applyZoom(currentZoom);
        setTimeout(() => {
            overlay.classList.add('hidden');
        }, 300);
    };

    frame.onerror = () => {
        clearTimeout(loadTimer);
        document.getElementById('loading-text').innerHTML = `
            <div style="font-size:18px;margin-bottom:8px;color:#f87171;">❌ Failed to load ${currentConfig.domain}</div>
            <div style="font-size:13px;color:#94a3b8;margin-bottom:14px;">Please check domain name or network connection.</div>
            <button type="button" onclick="openSettingsModal()" style="background:#6366f1;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;">⚙️ Change Server Domain</button>
        `;
    };
}

/**
 * SaNDS License Key Activation via key.sandslab.com
 */
async function callActivateApi(licenseKey, domain, ipAddress) {
    const cleanDomain = domain.replace(/^https?:\/\//, '').split('/')[0];
    
    const payload = {
        license_key: licenseKey.trim(),
        domain_name: cleanDomain,
        ip_address: ipAddress ? ipAddress.trim() : ''
    };

    const endpoints = ['activate.php', '/api/activate-license', KEY_ACTIVATION_API];
    let lastError = 'License activation failed. Please check your key.';

    for (const url of endpoints) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                const errData = await response.json().catch(() => ({}));
                lastError = errData.message || `HTTP ${response.status} from key server.`;
                continue;
            }

            const data = await response.json();
            if (data && data.success) {
                return data;
            } else if (data && data.message) {
                throw new Error(data.message);
            }
        } catch (e) {
            if (e.message && !e.message.includes('fetch')) {
                throw e;
            }
            lastError = e.message || 'Connection error';
        }
    }

    throw new Error(lastError);
}

/**
 * Handle First Launch Setup
 */
async function handleFirstLaunchSetup(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-first-launch');
    const errBox = document.getElementById('first-launch-error');
    
    let domain = document.getElementById('fl-domain').value.trim();
    const licenseKey = document.getElementById('fl-license-key').value.trim();
    const printerIp = document.getElementById('fl-printer-ip').value.trim();
    const printerPort = parseInt(document.getElementById('fl-printer-port').value.trim() || 9100);
    const printerSize = parseInt(document.getElementById('fl-printer-size').value || 80);
    const printMode = document.getElementById('fl-print-mode').value || 'direct';

    if (!domain) {
        errBox.style.display = 'block';
        errBox.textContent = '❌ Please enter your server domain (e.g. b1.restoflow.us).';
        return;
    }

    // Clean domain format
    domain = domain.replace(/^https?:\/\//i, '').replace(/\/+$/, '');

    errBox.style.display = 'none';
    btn.disabled = true;
    btn.innerHTML = 'Connecting & Saving...';

    // If license key is provided, try activation
    if (licenseKey) {
        try {
            const result = await callActivateApi(licenseKey, domain, printerIp);
            currentLicense = {
                licenseKey: licenseKey,
                token: result.token,
                publicKey: result.public_key,
                activatedAt: new Date().toISOString()
            };
            localStorage.setItem(STORAGE_KEY_LICENSE, JSON.stringify(currentLicense));
        } catch (err) {
            console.warn('License note:', err.message);
            // Save license key even if key activation endpoint is not reached
            currentLicense = {
                licenseKey: licenseKey,
                token: 'cached_' + Date.now(),
                activatedAt: new Date().toISOString()
            };
            localStorage.setItem(STORAGE_KEY_LICENSE, JSON.stringify(currentLicense));
        }
    }

    currentConfig.is_configured = true;
    currentConfig.domain = domain;
    currentConfig.licenseKey = licenseKey;
    currentConfig.printerIp = printerIp;
    currentConfig.printerPort = printerPort;
    currentConfig.printerSize = printerSize;
    currentConfig.printMode = printMode;
    localStorage.setItem(STORAGE_KEY_CONFIG, JSON.stringify(currentConfig));

    document.getElementById('first-launch-modal').style.display = 'none';
    showToast('✅ Configuration Saved!');
    updateStatusBadges();
    fetchServerSettings();
    loadPosPortal();

    btn.disabled = false;
    btn.innerHTML = '💾 Save Configuration & Launch POS';
}

/**
 * Settings Modal Handlers
 */
function openSettingsModal() {
    document.getElementById('cfg-domain').value = currentConfig.domain;
    document.getElementById('cfg-printer-ip').value = currentConfig.printerIp;
    document.getElementById('cfg-printer-port').value = currentConfig.printerPort;
    document.getElementById('cfg-printer-size').value = currentConfig.printerSize;
    document.getElementById('cfg-print-mode').value = currentConfig.printMode;
    document.getElementById('cfg-margin-left').value = currentConfig.marginLeft;
    document.getElementById('cfg-margin-right').value = currentConfig.marginRight;
    document.getElementById('cfg-license-key').value = currentConfig.licenseKey || (currentLicense ? currentLicense.licenseKey : '');

    const licBox = document.getElementById('license-info-box');
    if (currentLicense && currentLicense.token) {
        licBox.style.display = 'block';
        licBox.innerHTML = `
            <div style="font-size: 11px; color: var(--success); font-weight: 600;">
                ✓ Active License Token Installed
            </div>
            <div style="font-size: 10px; color: var(--text-muted); font-family: monospace; margin-top: 4px; word-break: break-all;">
                Token: ${currentLicense.token.substring(0, 30)}...
            </div>
        `;
    } else {
        licBox.style.display = 'none';
    }

    document.getElementById('test-feedback').textContent = '';
    document.getElementById('settings-modal').style.display = 'flex';
}

function closeSettingsModal() {
    document.getElementById('settings-modal').style.display = 'none';
}

function handleSaveSettings(e) {
    e.preventDefault();
    
    currentConfig.domain = document.getElementById('cfg-domain').value.trim();
    currentConfig.printerIp = document.getElementById('cfg-printer-ip').value.trim();
    currentConfig.printerPort = parseInt(document.getElementById('cfg-printer-port').value.trim() || 9100);
    currentConfig.printerSize = parseInt(document.getElementById('cfg-printer-size').value);
    currentConfig.printMode = document.getElementById('cfg-print-mode').value;
    currentConfig.marginLeft = parseInt(document.getElementById('cfg-margin-left').value || 5);
    currentConfig.marginRight = parseInt(document.getElementById('cfg-margin-right').value || 5);

    localStorage.setItem(STORAGE_KEY_CONFIG, JSON.stringify(currentConfig));

    fetchServerSettings();
    closeSettingsModal();
    showToast('⚙️ Settings saved and applied!');
    updateStatusBadges();
    loadPosPortal();
}

/**
 * Activate License Key from Settings Drawer
 */
async function activateLicenseKey() {
    const btn = document.getElementById('btn-activate-key');
    const key = document.getElementById('cfg-license-key').value.trim();
    const domain = document.getElementById('cfg-domain').value.trim();

    if (!key) {
        showToast('Please enter a license key');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '...';

    try {
        const result = await callActivateApi(key, domain, currentConfig.printerIp);
        currentLicense = {
            licenseKey: key,
            token: result.token,
            publicKey: result.public_key,
            activatedAt: new Date().toISOString()
        };
        localStorage.setItem(STORAGE_KEY_LICENSE, JSON.stringify(currentLicense));
        currentConfig.licenseKey = key;
        localStorage.setItem(STORAGE_KEY_CONFIG, JSON.stringify(currentConfig));

        showToast('✅ Key Activated via key.sandslab.com!');
        updateStatusBadges();
        openSettingsModal(); // Refresh modal
    } catch (err) {
        showToast('❌ ' + err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Activate';
    }
}

/**
 * Test Printer Connection
 */
async function testPrinterConnection() {
    const btn = document.getElementById('btn-modal-test');
    const fb = document.getElementById('test-feedback');
    
    const tempConfig = {
        ...currentConfig,
        printerIp: document.getElementById('cfg-printer-ip').value.trim(),
        printerPort: parseInt(document.getElementById('cfg-printer-port').value.trim() || 9100),
        printerSize: parseInt(document.getElementById('cfg-printer-size').value),
        marginLeft: parseInt(document.getElementById('cfg-margin-left').value || 5),
        marginRight: parseInt(document.getElementById('cfg-margin-right').value || 5),
        printMode: document.getElementById('cfg-print-mode').value
    };

    btn.disabled = true;
    btn.innerHTML = '⏳ Testing...';
    fb.style.color = 'var(--text-muted)';
    fb.textContent = `Connecting to ${tempConfig.printerIp}:${tempConfig.printerPort}...`;

    try {
        const rawReceipt = EscPos.buildTestReceipt(tempConfig);
        const res = await EscPos.print(rawReceipt, tempConfig);

        if (res.success) {
            fb.style.color = 'var(--success)';
            fb.textContent = '✓ Test receipt sent to printer!';
            showToast('🖨️ Test Print Successful!');
        } else {
            fb.style.color = 'var(--danger)';
            fb.textContent = '✗ ' + (res.error || 'Connection failed');
        }
    } catch (err) {
        fb.style.color = 'var(--danger)';
        fb.textContent = '✗ ' + err.message;
    } finally {
        btn.disabled = false;
        btn.innerHTML = '⚡ Test Connection to Printer';
    }
}

async function quickTestPrint() {
    showToast(`⚡ Sending test print to ${currentConfig.printerIp}...`);
    try {
        const rawReceipt = EscPos.buildTestReceipt(currentConfig);
        await EscPos.print(rawReceipt, currentConfig);
        showToast('✅ Test slip dispatched to EASY+ POS!');
    } catch (err) {
        showToast('❌ Print failed: ' + err.message);
    }
}

function openExitAppModal() {
    const modal = document.getElementById('exit-modal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeExitAppModal() {
    const modal = document.getElementById('exit-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function confirmExitApp() {
    closeExitAppModal();
    showToast('👋 Exiting SaNDS KOT Driver...');
    
    setTimeout(() => {
        if (window.AndroidPrintBridge && typeof window.AndroidPrintBridge.exitApp === 'function') {
            window.AndroidPrintBridge.exitApp();
        } else if (window.AndroidPrintBridge && typeof window.AndroidPrintBridge.navigateBack === 'function') {
            window.AndroidPrintBridge.navigateBack();
        } else {
            // Web browser fallback
            try {
                window.close();
            } catch (e) {
                console.warn('Window close blocked by browser:', e);
            }
            document.body.innerHTML = `
                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100vh;background:#0b0f19;color:#f8fafc;font-family:'Outfit',sans-serif;text-align:center;padding:24px;">
                    <div style="width:72px;height:72px;border-radius:50%;background:rgba(239,68,68,0.15);border:2px solid rgba(239,68,68,0.4);display:flex;align-items:center;justify-content:center;color:#ef4444;margin-bottom:20px;box-shadow:0 0 30px rgba(239,68,68,0.25);">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </div>
                    <h2 style="font-size:24px;font-weight:800;margin-bottom:8px;letter-spacing:-0.3px;">Driver Application Exited</h2>
                    <p style="color:#94a3b8;font-size:14px;max-width:380px;line-height:1.5;margin-bottom:24px;">You can safely close this browser window or tab.</p>
                    <button onclick="location.reload()" style="background:linear-gradient(135deg,#1c8dcd,#0284c7);color:#fff;border:none;padding:12px 24px;border-radius:10px;cursor:pointer;font-weight:700;font-size:14px;box-shadow:0 4px 15px rgba(28,141,205,0.4);">Restart KOT Driver</button>
                </div>
            `;
        }
    }, 250);
}

function toggleFullscreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(err => {
            console.warn('Fullscreen error:', err);
        });
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
    }
}

function showToast(message) {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.style.display = 'flex';
    
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}

// Listen for print commands from embedded POS iframe
window.addEventListener('message', async (event) => {
    if (!event.data) return;
    
    if (event.data.action === 'sands_print_escpos' && event.data.base64) {
        try {
            const ip = currentConfig.printerIp || event.data.printer_ip || '192.168.8.101';
            const port = parseInt(currentConfig.printerPort || event.data.printer_port || 9100);
            
            if (window.AndroidPrintBridge && typeof window.AndroidPrintBridge.printTcp === 'function') {
                const res = window.AndroidPrintBridge.printTcp(ip, port, event.data.base64);
                const parsed = JSON.parse(res);
                if (parsed.success) {
                    showToast('🖨️ Receipt Printed to EASY+ POS (' + ip + ')');
                } else {
                    showToast('❌ Printer (' + ip + '): ' + (parsed.error || 'Print failed'));
                }
            } else if (currentConfig.printMode === 'rawbt') {
                window.location.href = "rawbt:data:application/octet-stream;base64," + event.data.base64;
            }
        } catch (err) {
            console.error('Driver postMessage print error:', err);
        }
    }
});
