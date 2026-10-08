/**
 * SaNDS KOT Printer Driver - Main Controller
 * Integrated with key.sandslab.com License Authentication API
 */

const STORAGE_KEY_CONFIG = 'sands_kot_config';
const STORAGE_KEY_LICENSE = 'sands_kot_license';
const KEY_ACTIVATION_API = 'https://key.sandslab.com/public/api/activate';

const defaultConfig = {
    domain: 'kot.sandslab.com',
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
    fetchServerSettings();
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
    let domain = currentConfig.domain ? currentConfig.domain.trim() : 'kot.sandslab.com';
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
    // Check if license exists and is valid
    if (!currentLicense || !currentLicense.token) {
        document.getElementById('first-launch-modal').style.display = 'flex';
    } else {
        updateStatusBadges();
        loadPosPortal();
    }
}

function updateStatusBadges() {
    document.getElementById('status-portal-url').textContent = currentConfig.domain;
    document.getElementById('printer-status-text').textContent = `Printer: ${currentConfig.printerIp}`;

    const licBadge = document.getElementById('license-badge');
    const licText = document.getElementById('license-status-text');

    if (currentLicense && currentLicense.token) {
        licBadge.querySelector('.status-dot').className = 'status-dot green';
        licText.textContent = 'Licensed';
    } else {
        licBadge.querySelector('.status-dot').className = 'status-dot orange';
        licText.textContent = 'Unlicensed';
    }
}

function loadPosPortal() {
    const overlay = document.getElementById('loading-overlay');
    const frame = document.getElementById('pos-frame');
    
    let targetUrl = currentConfig.domain.trim();
    if (!targetUrl.startsWith('http://') && !targetUrl.startsWith('https://')) {
        targetUrl = 'https://' + targetUrl;
    }

    overlay.classList.remove('hidden');
    document.getElementById('loading-text').textContent = `Loading ${currentConfig.domain}...`;

    frame.src = targetUrl;
    frame.onload = () => {
        applyZoom(currentZoom);
        setTimeout(() => {
            overlay.classList.add('hidden');
        }, 300);
    };

    frame.onerror = () => {
        document.getElementById('loading-text').textContent = `Failed to load ${currentConfig.domain}. Please check domain in settings.`;
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
    
    const licenseKey = document.getElementById('fl-license-key').value.trim();
    const domain = document.getElementById('fl-domain').value.trim();
    const printerIp = document.getElementById('fl-printer-ip').value.trim();
    const printerPort = parseInt(document.getElementById('fl-printer-port').value.trim() || 9100);

    errBox.style.display = 'none';
    btn.disabled = true;
    btn.innerHTML = 'Activating License...';

    try {
        const result = await callActivateApi(licenseKey, domain, printerIp);
        
        currentLicense = {
            licenseKey: licenseKey,
            token: result.token,
            publicKey: result.public_key,
            activatedAt: new Date().toISOString()
        };
        localStorage.setItem(STORAGE_KEY_LICENSE, JSON.stringify(currentLicense));

        currentConfig.licenseKey = licenseKey;
        currentConfig.domain = domain;
        currentConfig.printerIp = printerIp;
        currentConfig.printerPort = printerPort;
        localStorage.setItem(STORAGE_KEY_CONFIG, JSON.stringify(currentConfig));

        document.getElementById('first-launch-modal').style.display = 'none';
        showToast('✅ License Activated Successfully!');
        updateStatusBadges();
        loadPosPortal();

    } catch (err) {
        errBox.style.display = 'block';
        errBox.textContent = '❌ ' + err.message;
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Activate & Launch POS';
    }
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
            const ip = currentConfig.printerIp || '192.168.8.101';
            const port = parseInt(currentConfig.printerPort || 9100);
            
            if (window.AndroidPrintBridge && typeof window.AndroidPrintBridge.printTcp === 'function') {
                const res = window.AndroidPrintBridge.printTcp(ip, port, event.data.base64);
                const parsed = JSON.parse(res);
                if (parsed.success) {
                    showToast('🖨️ Receipt Printed to EASY+ POS!');
                } else {
                    showToast('❌ Printer: ' + (parsed.error || 'Print failed'));
                }
            } else if (currentConfig.printMode === 'rawbt') {
                window.location.href = "rawbt:data:application/octet-stream;base64," + event.data.base64;
            }
        } catch (err) {
            console.error('Driver postMessage print error:', err);
        }
    }
});
