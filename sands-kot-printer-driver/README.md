# SaNDS KOT Printer Driver & POS Terminal

A dedicated, watermark-free POS & Network Thermal Printer Driver Client built for Tablets (Android/iOS) and Windows Desktop POS.

---

## 🚀 Features

- **🔐 SaNDS Lab License Key Activation**:
  - Seamlessly integrates with [https://key.sandslab.com/public/api/activate](https://key.sandslab.com/public/api/activate).
  - Validates cryptographic token and enforces domain & IP binding.
- **🖨️ Universal ESC/POS Network Printer Engine**:
  - Connects to ANY Wi-Fi / Ethernet Thermal Receipt Printer (`192.168.8.101:9100`).
  - Native ESC/POS command stream: header, item tables, VAT/GST breakdown, cash drawer kick, auto-cutter.
  - Configurable **Left Margin** and **Right Margin** (in mm).
  - 100% Free with **Zero Third-Party Watermarks**.
- **🌐 Embedded KOT & POS Portal**:
  - Loads your server domain (`kot.sandslab.com` or custom URL) in high-performance kiosk mode.
  - Quick Test Print button for instant connection verification.

---

## 🛠️ Configuration Parameters

| Setting | Default Value | Description |
| :--- | :--- | :--- |
| **Server Domain** | `kot.sandslab.com` | URL of your KOT / POS portal |
| **Printer IP** | `192.168.8.101` | Local Wi-Fi IP assigned to your thermal printer |
| **Printer Port** | `9100` | Standard RAW ESC/POS port |
| **Paper Roll** | `80 MM` (or `58 MM`) | Thermal roll width |
| **Left Margin** | `5 mm` | Distance from left roll edge |
| **Right Margin** | `5 mm` | Distance from right roll edge |
| **License Key** | `INV-XXXXXX-...` | Key issued from `key.sandslab.com` |

---

## 📱 How to Run on Tablet (Android / iOS):
1. Open Chrome/Safari on your Tablet &rarr; navigate to `https://kot.sandslab.com/sands-kot-printer-driver/` (or run locally).
2. Tap the browser menu &rarr; **"Add to Home Screen" / "Install App"**.
3. It launches as a fullscreen native POS app with direct printer controls!
