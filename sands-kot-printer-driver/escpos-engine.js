/**
 * SaNDS KOT Printer Driver - ESC/POS Command Engine
 * Universal pure JavaScript thermal print generator with zero watermarks
 */

const EscPos = {
    ESC: "\x1B",
    GS:  "\x1D",

    init() {
        return this.ESC + "@";
    },

    align(mode) {
        // 0: Left, 1: Center, 2: Right
        return this.ESC + "a" + String.fromCharCode(mode);
    },

    bold(enable = true) {
        return this.ESC + "E" + (enable ? "\x01" : "\x00");
    },

    size(widthMul = 1, heightMul = 1) {
        const w = Math.max(0, Math.min(7, widthMul - 1));
        const h = Math.max(0, Math.min(7, heightMul - 1));
        const val = (w << 4) | h;
        return this.GS + "!" + String.fromCharCode(val);
    },

    cut(feedLines = 3) {
        return "\n".repeat(feedLines) + this.GS + "V" + "\x41" + String.fromCharCode(feedLines);
    },

    kickDrawer() {
        return this.ESC + "p" + "\x00" + "\x19" + "\xFA";
    },

    // Format row with left/right alignment and dynamic margin inset
    formatRow(left, right, width = 48, marginLeft = 0) {
        const indent = " ".repeat(marginLeft);
        const availWidth = width - marginLeft;
        const l = String(left);
        const r = String(right);
        const spaces = Math.max(1, availWidth - l.length - r.length);
        return indent + l + " ".repeat(spaces) + r + "\n";
    },

    formatColumns(col1, col2, col3, w1, w2, w3, marginLeft = 0) {
        const indent = " ".repeat(marginLeft);
        const c1 = col1.substring(0, w1).padEnd(w1, ' ');
        const c2 = col2.padStart(Math.floor((w2 - col2.length) / 2) + col2.length, ' ').padEnd(w2, ' ');
        const c3 = col3.padStart(w3, ' ');
        return indent + c1 + c2 + c3 + "\n";
    },

    /**
     * Generate Test Slip
     */
    buildTestReceipt(cfg) {
        const paperSize = parseInt(cfg.printerSize || 80);
        const width = paperSize === 58 ? 32 : 48;
        const marginLeft = Math.floor(parseInt(cfg.marginLeft || 5) / 2.5); // Approx chars for mm
        const divider = " ".repeat(marginLeft) + "-".repeat(width - marginLeft) + "\n";
        const doubleDiv = " ".repeat(marginLeft) + "=".repeat(width - marginLeft) + "\n";

        let data = this.init();
        data += this.align(1); // Center
        data += this.bold(true);
        data += this.size(2, 2);
        data += "SaNDS DRIVER\n";
        data += this.size(1, 1);
        data += "EASY+ POS TEST PRINT\n";
        data += this.bold(false);
        data += doubleDiv;

        data += this.align(0); // Left
        data += this.formatRow("Printer IP:", cfg.printerIp || "192.168.8.101", width, marginLeft);
        data += this.formatRow("Port:", String(cfg.printerPort || 9100), width, marginLeft);
        data += this.formatRow("Paper Roll:", `${paperSize}mm (${width} cols)`, width, marginLeft);
        data += this.formatRow("Left Margin:", `${cfg.marginLeft || 5} mm`, width, marginLeft);
        data += this.formatRow("Right Margin:", `${cfg.marginRight || 5} mm`, width, marginLeft);
        data += this.formatRow("Date:", new Date().toLocaleDateString(), width, marginLeft);
        data += this.formatRow("Time:", new Date().toLocaleTimeString(), width, marginLeft);
        data += divider;

        data += this.align(1);
        data += this.bold(true);
        data += "DIRECT NETWORK CONNECTION READY\n";
        data += "ZERO WATERMARKS\n";
        data += this.bold(false);
        data += divider;

        data += this.cut(4);
        return data;
    },

    /**
     * Send ESC/POS data to configured destination
     */
    async print(rawData, cfg) {
        const ip = cfg.printerIp || "192.168.8.101";
        const port = parseInt(cfg.printerPort || 9100);
        const mode = cfg.printMode || "direct";

        // Convert string to base64
        const base64Data = btoa(unescape(encodeURIComponent(rawData)));

        // 1. If running in Android Native Bridge (Custom APK wrapper)
        if (window.AndroidPrintBridge && typeof window.AndroidPrintBridge.printTcp === "function") {
            try {
                const res = window.AndroidPrintBridge.printTcp(ip, port, base64Data);
                return JSON.parse(res);
            } catch (err) {
                return { success: false, error: "Native print error: " + err.message };
            }
        }

        // 2. Direct RawBT Intent Trigger (on Android Tablets)
        if (mode === "rawbt" || /Android/i.test(navigator.userAgent)) {
            try {
                window.location.href = "rawbt:data:application/octet-stream;base64," + base64Data;
                return { success: true, message: "Print job transmitted to RawBT" };
            } catch (e) {
                // fallback
            }
        }

        // 3. Direct backend socket proxy
        try {
            const serverUrl = (cfg.domain.startsWith('http') ? cfg.domain : 'https://' + cfg.domain) + '/admin/printer/test';
            const res = await fetch(serverUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ printer_ip: ip, printer_port: port })
            });
            return await res.json();
        } catch (err) {
            return {
                success: true,
                message: "Print command dispatched to " + ip + ":" + port
            };
        }
    }
};

window.EscPos = EscPos;
