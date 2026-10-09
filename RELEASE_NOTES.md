# SaNDS KOT & POS Billing System - Release Notes

## 🏷️ Current Release: v5.1 (Ready for Publish)
- **Tag**: \5.1\ / \5.1.0\
- **Locked Git Commit**: \5bc2010131b6e6f648cb7ba82e4279d5af407082\
- **Branch**: \5\
- **Release Status**: ✅ Locked & Ready for Production

---

## 🚀 Key Modules & Feature Status

### 1. SaNDS KOT Printer Driver App (v5.1)
- **Exit App Action**: Replaced redundant fullscreen button with a dedicated door exit button.
- **Glassmorphic Exit Confirmation Modal**: Displays a styled confirmation card with glowing pulse indicator and safety notice.
- **Android Native Process Termination**: Connected JavaScript bridge to native Android \PrinterBridge.exitApp()\ (\ctivity.finishAffinity()\).
- **APK Package Signing**: Fixed Android package verification by signing with valid Android signatures in Gradle.
- **Waiter Isolation**: Blocked waiter logins through the Printer Driver app; waiters use dedicated web URL or QR scanning.

### 2. Waiter Station App (\/waiter-app\)
- **Clean Icon Controls**: Converted header actions to icon-only buttons with tooltips (\🔑\ Change Password, \🌓\ Theme, \🚪\ Logout).
- **Vite & Legacy Polyfills**: Bundled with \@vitejs/plugin-legacy\ to support older Android POS tablets and browsers.
- **Live Dispatch Alerts**: Real-time kitchen dispatch notifications with auto-refresh and audio chimes.

### 3. Web POS & Admin Consoles
- **Icon-Only Header Actions**:
  - **Billing Counter (\/counter\)**: \🔑\ Change Password, \🌓\ Theme toggle, and \🚪\ Logout icon.
  - **Admin Console (\/admin\)**: \🔑\ Change Password, \🌓\ Theme toggle, and \🚪\ Logout icon.
  - **KOT Monitor (\/kot\)**: \🔑\ Change Password, \🌓\ Theme toggle, and \🚪\ Logout icon.
  - **Admin App (\/admin-app\)**: \🔑\ Password update prompt and \🚪\ modal logout confirmation.
- **Shift Settlement**: Supports zero-amount shift logout and positive-balance admin verification workflows.
- **QR Codes**: Dedicated popups for Waiter Login QR (\/waiter-app\), Takeaway Ordering QR, and Table Dine-in QR codes.

---

## 🛠️ How to Deploy / Pull on Server

### To checkout this exact release tag:
\\\ash
git fetch --tags
git checkout v5.1
\\\

### To continue tracking branch updates:
\\\ash
git checkout v5
git pull origin v5
\\\

---

## 📦 Distribution Packages
- **SaNDS KOT Printer Driver APK**: \downloads/SaNDS-KOT-Printer-Driver.apk\
- **Waiter Web App**: \waiter-app-dist/index.html\ (or route \/waiter-app\)
