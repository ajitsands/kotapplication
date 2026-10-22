# SaNDS KOT & POS Billing System - Release Notes

## 🔒 Production Milestone: Multi-Device Driver Printing & Station Routing
- **Target Server**: `https://b1.restoflow.us`
- **Milestone Status**: 🟢 **Locked, Production Ready & Verified**
- **Branch**: `v5`
- **Release Version**: `v5.6` (Cross-Origin Print Bridge & Dynamic Station IP Sync)
- **Tag**: `v5.6` / `v5.6.0`

---

## 🏷️ Release Version: v5.6 (Cross-Origin Print Bridge & Dynamic Station IP Sync)
- **Branch**: `v5`
- **Release Status**: 🔒 **Locked & Verified by User**

### 🌟 New in v5.6
1. **Cross-Origin Safe Printer Communication**:
   - Resolved browser Same-Origin Policy boundary between Driver native assets (`file:///android_asset/`) and remote POS iframe (`https://b1.restoflow.us`).
   - Replaced raw `window.parent.AndroidPrintBridge` calls with cross-origin safe `getAndroidBridge()` and bi-directional `window.parent.postMessage` print relay.
2. **Station-Specific Printer IP Synchronization**:
   - Driver App embeds `station_printer_ip` in iframe query string and broadcasts `sands_printer_config_update` events on settings save.
   - Dynamic priority: Station IP &rarr; Local Storage &rarr; Central Default Fallback (`192.168.8.101`).
3. **Seamless KOT & Counter POS Thermal Printing**:
   - Single-click direct printing for kitchen tickets and tax invoices across `/kot`, `/counter/pos`, `/counter`, and preview modals.
4. **Compiled Release APK**:
   - `SaNDS-KOT-Printer-Driver.apk` built with Gradle 8.6 (2,570,471 bytes).

---

## 🏷️ Release Version: v5.5 (Online Platform Commission Markup & Server Launcher)
- **Branch**: `v5`
- **Release Status**: 🟢 **Verified & Production Ready**

### 🌟 New in v5.5
1. **Online Delivery Platform Surcharge / Commission Markup (%)**:
   - Added percentage markup configuration (`markup_percent`) per platform in **Admin -> Online Platforms** (e.g., Talabat +30%, Jahez +20%).
   - Dynamic real-time menu card price adjustment on POS when an online platform is selected.
   - Dynamic Cart price recalculation when switching between Dine-In, Takeaway, and different Online platforms.
   - Snapshotting unit prices into `kot_items.price` for historical accuracy and reporting consistency.
   - Accurate Subtotal, Tax/VAT, Grand Total, and Thermal Receipt printing with marked-up prices.
2. **Database Migration Script**:
   - `migrate_platform_markup.php` for zero-downtime live server schema update (`markup_percent` on `online_platforms` and `price` on `kot_items`).
3. **One-Click Local Launcher**:
   - `start_server.bat` for simple Windows startup on port 3031 with auto PHP detection and browser launch.

---

## 🏷️ Release Version: v5.4 (Counter POS Order Taking, QR Standee & Header Submenu)
- **Branch**: `v5`
- **Release Status**: 🟢 **Verified & Production Ready**

### 🌟 New in v5.4
1. **Cashier POS Order Taking Screen (`/counter/pos`)**:
   - Modern full-screen product catalog with live search and category chips filter.
   - 3-Way Order Types: **Take Away**, **Dine-In** (with Table picker), and **Online Orders** (with platform selection & order reference ID).
   - Instant dispatch to Kitchen Display System (KDS) & thermal printer with optional immediate checkout (**Pay Now**).
2. **Streamlined Header Navigation (`QR & Other ▾`)**:
   - Consolidates rarely clicked tools (*Table Status, Waiter QR, Take Away QR, Online Order*) into a clean dropdown.
   - Added prominent top navigation button: **`🛒 New Order (POS)`**.
3. **High-Resolution QR Downloads & Standee Printing**:
   - Direct download of crisp **1000 &times; 1000 px PNG QR codes** for professional print shops and stickers.
   - Built-in **Print Standee** generator that creates a formatted poster card with restaurant branding and instructions for placing outside.
4. **Kitchen Display System (KDS) Integration**:
   - Staff-initiated takeaway and online orders immediately appear in the kitchen monitor ticket stream.

---

## 🏷️ Release Version: v5.3 (Multi-Device Printing & Dynamic Station Routing)
- **Branch**: `v5`
- **Release Status**: 🔒 **Locked & Verified**

---

## 🚀 Key Modules & Feature Highlights (v5.3)

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

