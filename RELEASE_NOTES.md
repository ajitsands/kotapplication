# SaNDS KOT & POS Billing System - Release Notes

## 🧪 Active Milestone: B1 Burger Server Testing Started
- **Target Server**: `https://b1.restoflow.us`
- **Milestone Status**: 🟡 **Server Testing Initiated — Full Test Scheduled in ~8 Hours**
- **Branch**: `v5`
- **Testing Scope & Ready Modules**:
  1. **B1 Burger Master Menu**: 30 products across 6 categories with food pictures extracted directly from the official PDF.
  2. **Table Status Management**: Resolved billing flags so available tables (T1-T22) are clean and ready for seating.
  3. **Waiter Terminal App**: Added item food picture thumbnails, info button, and comprehensive **Ingredients & Details Modal** with direct ordering and quantity selectors.
  4. **KOT Kitchen Live Display**: Real-time order dispatch notifications with audio alerts.
  5. **Cashier / POS Billing**: Counter drawer sessions, BHD 3-decimal calculations, and shift closures.
  6. **Superadmin Transaction Wiper**: Superadmin "Clear All Transaction Data" feature ready to wipe testing orders before final handover.

---

## 🏷️ Release Version: v5.2 (B1 Burger Edition)
- **Tag**: `v5.2` / `v5.2.0-b1-testing`
- **Branch**: `v5`
- **Release Status**: ✅ Ready for Functional & Load Testing

---

## 🚀 Key Modules & Feature Status

### 1. SaNDS KOT Printer Driver App (v5.1)
- **Exit App Action**: Replaced redundant fullscreen button with a dedicated door exit button.
- **Glassmorphic Exit Confirmation Modal**: Displays a styled confirmation card with glowing pulse indicator and safety notice.
- **Android Native Process Termination**: Connected JavaScript bridge to native Android `PrinterBridge.exitApp()` (`Activity.finishAffinity()`).
- **APK Package Signing**: Fixed Android package verification by signing with valid Android signatures in Gradle.
- **Waiter Isolation**: Blocked waiter logins through the Printer Driver app; waiters use dedicated web URL or QR scanning.

### 2. Waiter Station App (`/waiter-app`)
- **Food Picture Thumbnails & Info Badge**: Every menu item displays its food image with a 1-tap `ℹ️` badge.
- **Ingredients & Details Modal**: Displays full recipe ingredients, descriptions, counter vs kitchen tags, and order customization box.
- **Clean Icon Controls**: Header actions with tooltips (🔑 Change Password, 🌓 Theme, 🚪 Logout).
- **Vite & Legacy Polyfills**: Bundled with `@vitejs/plugin-legacy` to support older Android POS tablets and browsers.
- **Live Dispatch Alerts**: Real-time kitchen dispatch notifications with auto-refresh and audio chimes.

### 3. Web POS & Admin Consoles
- **Icon-Only Header Actions**:
  - **Billing Counter (`/counter`)**: 🔑 Change Password, 🌓 Theme toggle, and 🚪 Logout icon.
  - **Admin Console (`/admin`)**: 🔑 Change Password, 🌓 Theme toggle, and 🚪 Logout icon.
  - **KOT Monitor (`/kot`)**: 🔑 Change Password, 🌓 Theme toggle, and 🚪 Logout icon.
  - **Admin App (`/admin-app`)**: 🔑 Password update prompt and 🚪 modal logout confirmation.
- **Shift Settlement**: Supports zero-amount shift logout and positive-balance admin verification workflows.
- **QR Codes**: Dedicated popups for Waiter Login QR (`/waiter-app`), Takeaway Ordering QR, and Table Dine-in QR codes.

---

## 🛠️ How to Deploy / Pull on Server

### To pull latest test build on server:
```bash
git checkout v5
git pull origin v5
php seed_demo_data.php
```

---

## 📦 Distribution Packages
- **SaNDS KOT Printer Driver APK**: `downloads/SaNDS-KOT-Printer-Driver.apk`
- **Waiter Web App**: `waiter-app-dist/index.html` (or route `/waiter-app`)
