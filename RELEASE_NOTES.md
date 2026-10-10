# SaNDS KOT & POS Billing System - Release Notes

## 🔒 Production Milestone: Multi-Device Station Printing & Version Locked
- **Target Server**: `https://b1.restoflow.us`
- **Milestone Status**: 🟢 **Version Locked & Ready for Production Deployment**
- **Branch**: `v5`
- **Release Version**: `v5.3` (Multi-Device Thermal Printing & Dynamic Station Routing Edition)
- **Tag**: `v5.3` / `v5.3.0-locked`

---

## 🏷️ Release Version: v5.3 (Multi-Device Printing & Dynamic Station Routing)
- **Branch**: `v5`
- **Release Status**: 🔒 **Locked & Verified**

---

## 🚀 Key Modules & Feature Highlights

### 1. Dynamic Multi-Device Printer IP Routing (v5.3 New)
- **Device-Level Priority (`getEffectivePrinterConfig()`)**:
  - Each individual POS tablet / terminal running the SaNDS Printer Driver App now routes print jobs to its own locally configured thermal printer (e.g., Tab 1 &rarr; `192.168.8.101`, Tab 2 &rarr; `192.168.8.102`).
- **Seamless Central Fallback**:
  - Unconfigured tablets, mobile devices, and standard browsers automatically fallback to the master central IP (`settings.printer_ip` = `192.168.8.101`).
- **Dual-Layer & Multi-Channel Transmission**:
  - Server-side raw ESC/POS direct TCP sockets (`fsockopen` to port 9100).
  - Client-side Android Native TCP Socket Bridge (`AndroidPrintBridge.printTcp()`).
  - Driver iframe postMessage relay (`sands_print_escpos`).
- **Universal Coverage Across POS Views**:
  - **Kitchen Display (`/kot`)**: KOT tickets dispatched with device-specific IP.
  - **Billing Counter (`/counter`)**: Tax Invoices and Order Receipts dispatched with device-specific IP.
  - **Print Preview Windows (`/kot/print` & `/counter/print`)**: Thermal triggers pass device-specific target printer coordinates.

### 2. SaNDS KOT Printer Driver App (v5.1 - v5.3)
- **Local Station Configuration**: Allows independent station-level IP and port configuration per tablet.
- **Exit App Action**: Replaced redundant fullscreen button with a dedicated door exit button.
- **Glassmorphic Exit Confirmation Modal**: Displays a styled confirmation card with glowing pulse indicator and safety notice.
- **Android Native Process Termination**: Connected JavaScript bridge to native Android `PrinterBridge.exitApp()` (`Activity.finishAffinity()`).
- **APK Package Signing**: Fixed Android package verification by signing with valid Android signatures in Gradle.
- **Waiter Isolation**: Blocked waiter logins through the Printer Driver app; waiters use dedicated web URL or QR scanning.

### 3. Waiter Station App (`/waiter-app`)
- **Food Picture Thumbnails & Info Badge**: Every menu item displays its food image with a 1-tap `ℹ️` badge.
- **Ingredients & Details Modal**: Displays full recipe ingredients, descriptions, counter vs kitchen tags, and order customization box.
- **Clean Icon Controls**: Header actions with tooltips (🔑 Change Password, 🌓 Theme, 🚪 Logout).
- **Vite & Legacy Polyfills**: Bundled with `@vitejs/plugin-legacy` to support older Android POS tablets and browsers.
- **Live Dispatch Alerts**: Real-time kitchen dispatch notifications with auto-refresh and audio chimes.

### 4. Web POS & Admin Consoles
- **Icon-Only Header Actions**:
  - **Billing Counter (`/counter`)**: 🔑 Change Password, 🌓 Theme toggle, and 🚪 Logout icon.
  - **Admin Console (`/admin`)**: 🔑 Change Password, 🌓 Theme toggle, and 🚪 Logout icon.
  - **KOT Monitor (`/kot`)**: 🔑 Change Password, 🌓 Theme toggle, and 🚪 Logout icon.
  - **Admin App (`/admin-app`)**: 🔑 Password update prompt and 🚪 modal logout confirmation.
- **Shift Settlement**: Supports zero-amount shift logout and positive-balance admin verification workflows.
- **QR Codes**: Dedicated popups for Waiter Login QR (`/waiter-app`), Takeaway Ordering QR, and Table Dine-in QR codes.

---

## 🛠️ How to Deploy / Pull on Server

### To pull the locked v5.3 build on server:
```bash
git checkout v5
git pull origin v5
```

---

## 📦 Distribution Packages
- **SaNDS KOT Printer Driver APK**: `downloads/SaNDS-KOT-Printer-Driver.apk`
- **Waiter Web App**: `waiter-app-dist/index.html` (or route `/waiter-app`)

