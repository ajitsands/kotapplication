# Restaurant KOT & Billing System - Progress Summary

This document summarizes the accomplishments and configurations of the development session to help resume immediately.

---

## 🛠️ Accomplishments

1. **MySQL Database Configuration & Seed**:
   - Updated database target to `kot_billing`.
   - Initialized and seeded all tables (users, settings, orders, products, categories, KOT tickets, KOT items, and invoices).
   - Generated and seeded verified BCrypt password hashes for all staff logins.

2. **Custom PHP MVC Core**:
   - Programmed the pattern-matching **Routing Table** mapping virtual URLs.
   - Built a **Singleton Database connection** and base model/controller helper classes.
   - Added support to automatically strip `/index.php` for environments with rewrite rules disabled.

3. **Vibrant Glassmorphic Operations Panels**:
   - **Admin Console**: Menu catalog CRUDs, GCC settings, printer roll size toggles (58mm/80mm), single VAT/split India GST percentages, and Table QR code generator downloads.
   - **Kitchen Display (KOT)**: Live checklist monitoring, status updates, 58mm/80mm print popups, and pulsing timer warnings for items pending > 10 minutes.
   - **Billing Counter**: Cashier checkout dashboard with Cash/Card/QR Pay collections.
   - **Monospaced Print Layouts**: Receipts optimized for thermal roll prints with automatic print prompts.
   - **Customer QR Order Page**: Mobile-first categorised menus for tables (`/customer/:table`) allowing direct self-ordering.

4. **Waiter React Application**:
   - Scaffolded, built, and compiled the mobile-first Vite React SPA inside the workspace.
   - Implemented order-taking drawers, categories search, KOT basket checkout with kitchen notes, and real-time polling alert notifications when items are marked "Ready".

5. **Dark & Light Theme Option**:
   - Integrated full dark/light theme toggles `🌓` across all PHP views and the React Waiter App, persisting the chosen layout in `localStorage`.

6. **Global Public Mobile Access**:
   - Established a global public tunnel to bypass firewall blocks and let you run the application on your mobile phone:
     - **Active Global URL**: `https://orange-trees-teach.loca.lt`
     - **Tunnel Security Bypass Password (IP)**: `46.184.209.225`

---

## 🔑 Login Credentials

| Role                | Username   | Password     | Purpose & Redirect View                                                           |
| :------------------ | :--------- | :----------- | :-------------------------------------------------------------------------------- |
| **System Admin**    | `admin`    | `admin123`   | Configure categories, products, taxes, and settings (Redirects to `/admin`).      |
| **Waiter Console**  | `waiter1`  | `waiter123`  | Mobile terminal to take table orders, view KOT basket, and dispatch items.        |
| **Kitchen Chef**    | `chef1`    | `kot123`     | Queue screen, print tickets, mark items ready (Redirects to `/kot`).              |
| **Counter Cashier** | `counter1` | `counter123` | Print customer bills, select payment methods, checkout (Redirects to `/counter`). |

---

## 🔗 How to Connect Tomorrow

1. **Verify local PHP Server is running on Port 5050**.
2. **Access local urls**:
   - **Staff Sign-in**: `http://localhost:5050/login`
   - **Waiter Mobile console**: `http://localhost:5050/waiter-app/dist/index.html`
   - **Customer Self-Order (Table 5)**: `http://localhost:5050/customer/5`
3. **Establish a new public tunnel** if accessing from a phone outside your home network:
   ```bash
   npx localtunnel --port 5050
   ```

---

## 🚀 Recommended Goals for Tomorrow

- Upload your actual restaurant logo inside **Admin -> Settings** to verify how it propagates to application icons and thermal prints.
- Create mock products under different categories (Drinks, Starters, Main Course) and test the full end-to-end flow from ordering to kitchen notification, dispatch, and final billing payment.
