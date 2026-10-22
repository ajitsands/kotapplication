# SaNDS KOT & POS Billing System - Work Notes & Deployment Log

> **Last Verified & Synced**: October 22, 2026  
> **Target Production Server**: `https://b1.restoflow.us`  
> **Production Branch**: `v5` (Clean working tree, up to date with `origin/v5`)  
> **Latest Release**: `v5.5` (Online Platform Commission Markup & Server Launcher) + High-Res Image Uploads

---

## 🚀 1. Production Server Deployment Status (`https://b1.restoflow.us`)

| Component | Status | Verification Detail |
| :--- | :---: | :--- |
| **Server Codebase** | 🟢 Up-to-Date | Running latest `v5` code (Release v5.5) |
| **Database Migrations** | 🟢 Applied | `migrate_platform_markup.php` executed on server DB.<br>• `markup_percent` in `online_platforms`<br>• `price` snapshotting in `kot_items` |
| **Counter POS** | 🟢 Live | [`https://b1.restoflow.us/counter/pos`](https://b1.restoflow.us/counter/pos) active with real-time markup & instant billing |
| **Waiter Web App** | 🟢 Live | [`https://b1.restoflow.us/waiter-app/`](https://b1.restoflow.us/waiter-app/) active with table ordering & ready alerts |
| **Kitchen Display (KDS)** | 🟢 Live | [`https://b1.restoflow.us/kot`](https://b1.restoflow.us/kot) active with pulsing timer warnings & print routing |
| **Admin Console** | 🟢 Live | [`https://b1.restoflow.us/admin`](https://b1.restoflow.us/admin) active with catalog, VAT/GST & platform markup |

---

## 📱 2. Android Applications & APK Deployment Status

Both Android apps are compiled, signed, and hosted directly on the server under `/downloads/`:

| Application | Server Download Link | File Size | Build Status |
| :--- | :--- | :--- | :---: |
| **SaNDS KOT Admin App** | [`/downloads/SaNDS-KOT-Admin.apk`](https://b1.restoflow.us/downloads/SaNDS-KOT-Admin.apk) | 2,499,958 bytes (~2.38 MB) | 🟢 Synced with Server |
| **SaNDS KOT Printer Driver App** | [`/downloads/SaNDS-KOT-Printer-Driver.apk`](https://b1.restoflow.us/downloads/SaNDS-KOT-Printer-Driver.apk) | 2,570,471 bytes (~2.45 MB) | 🟢 Synced with Server |

> **Architectural Note on App Updates**:
> Both Android APKs function as native WebView containers connected to `https://b1.restoflow.us` (with Native ESC/POS Android Print Bridge enabled).
> Any web updates (Counter POS features, styling, menu items, delivery markups, high-res photos) reflect **immediately** in the installed tablet apps without requiring APK re-installation.

---

## 📦 3. Version Changelog & Recent Implementations

### v5.5 — Online Platform Markup & Image Resolution (October 14–21, 2026)
- **High-Definition Media**: Upgraded item & category image uploads in `AdminController.php` and `views/admin.php` to **1200px max width** with **0.92 JPEG quality** (eliminating blurriness on large POS screens).
- **Online Delivery Commission Markup (%)**: Added custom percentage markup per platform in `Admin -> Online Platforms` (e.g. Talabat +30%, Jahez +20%).
- **Dynamic POS Cart Calculation**: Real-time line item and cart subtotal re-pricing when switching between Dine-In, Takeaway, and Online channels.
- **Unit Price Snapshotting**: Unit prices are snapshotted into `kot_items.price` for historical tax and sales reporting accuracy.
- **One-Click Windows Launcher**: Added `start_server.bat` for automatic local PHP startup and browser opening.

### v5.4 — Counter POS Order Taking & Printable QR Standees (October 12, 2026)
- **Dedicated POS Terminal (`/counter/pos`)**: High-speed touch catalog with live search, category chips, dine-in table selector, and takeaway flow.
- **Streamlined Header**: Grouped secondary tools into a clean `QR & Other ▾` dropdown, highlighting `🛒 New Order (POS)`.
- **High-Res Standee Printing**: Built-in 1000×1000px QR download and printable standee poster templates for Waiter and Takeaway ordering.

### v5.3 — Multi-Device Printer IP Routing (October 10, 2026)
- **Station-Level Printer IP (`getEffectivePrinterConfig()`)**: Tablets route ESC/POS print jobs directly to their local station printer IP, falling back seamlessly to master central IP (`192.168.8.101`).
- **Android TCP Socket Bridge**: Direct thermal receipt firing via `AndroidPrintBridge.printTcp()`.

---

## 🔑 Default Credentials Reference

| Role | Username | Password | Default Redirect |
| :--- | :--- | :--- | :--- |
| **System Admin** | `admin` | `admin123` | `/admin` |
| **Counter Cashier** | `counter1` | `counter123` | `/counter` |
| **Kitchen Chef** | `chef1` | `kot123` | `/kot` |
| **Waiter Terminal** | `waiter1` | `waiter123` | `/waiter-app/` |
