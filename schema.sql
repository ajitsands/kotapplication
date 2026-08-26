-- Database Schema for KOT & Billing System
CREATE DATABASE IF NOT EXISTS `kot_billing` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `kot_billing`;

-- Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `role` ENUM('admin', 'waiter', 'kot', 'counter') NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Settings Table
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `restaurant_name` VARCHAR(100) NOT NULL DEFAULT 'Gourmet Restaurant',
    `currency_code` VARCHAR(10) NOT NULL DEFAULT 'BHD',
    `time_zone` VARCHAR(50) NOT NULL DEFAULT 'Asia/Bahrain',
    `custom_units` VARCHAR(255) DEFAULT 'Nos, Box, Packet, Gram, KG, Litre, ML',
    `tax_type` ENUM('VAT', 'GST') NOT NULL DEFAULT 'VAT',
    `vat_percent` DECIMAL(5,2) NOT NULL DEFAULT 10.00,
    `cgst_percent` DECIMAL(5,2) NOT NULL DEFAULT 2.50,
    `sgst_percent` DECIMAL(5,2) NOT NULL DEFAULT 2.50,
    `printer_size` INT NOT NULL DEFAULT 80, -- 58 or 80
    `logo_path` VARCHAR(255) DEFAULT NULL,
    `software_expiry_date` DATE DEFAULT '2027-12-31',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `image_url` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Products Table
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(10,3) NOT NULL, -- Supporting 3 decimals for BHD
    `image_url` VARCHAR(255) DEFAULT NULL,
    `is_available` TINYINT(1) DEFAULT 1,
    `is_counter_item` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Dining Tables Table
CREATE TABLE IF NOT EXISTS `dining_tables` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `table_number` INT NOT NULL UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Orders Table
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `table_number` INT DEFAULT NULL,
    `status` ENUM('active', 'closed', 'completed', 'cancelled') DEFAULT 'active',
    `order_type` ENUM('dine_in', 'take_away') DEFAULT 'dine_in',
    `customer_name` VARCHAR(100) DEFAULT NULL,
    `customer_mobile` VARCHAR(20) DEFAULT NULL,
    `token_number` VARCHAR(10) DEFAULT NULL,
    `waiter_id` INT DEFAULT NULL, -- Logged in waiter who started order
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`waiter_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- KOTs Table (Kitchen Order Tickets)
CREATE TABLE IF NOT EXISTS `kots` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `waiter_id` INT DEFAULT NULL, -- Waiter who created this specific KOT
    `kot_number` VARCHAR(50) NOT NULL UNIQUE, -- e.g. KOT-20260630-001
    `status` ENUM('pending', 'preparing', 'ready', 'dispatched', 'cancelled') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`waiter_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- KOT Items Table
CREATE TABLE IF NOT EXISTS `kot_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `kot_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL,
    `status` ENUM('pending', 'preparing', 'ready', 'dispatched', 'cancelled') DEFAULT 'pending',
    `refund_status` ENUM('pending', 'refunded') DEFAULT 'pending',
    `notes` VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (`kot_id`) REFERENCES `kots`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Customers Table
CREATE TABLE IF NOT EXISTS `customers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `mobile` VARCHAR(20) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `gender` VARCHAR(20) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Counter Sessions Table
CREATE TABLE IF NOT EXISTS `counter_sessions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cashier_id` INT NOT NULL,
    `opened_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `close_requested_at` TIMESTAMP NULL DEFAULT NULL,
    `closed_at` TIMESTAMP NULL DEFAULT NULL,
    `cash_total` DECIMAL(10,3) DEFAULT 0.000,
    `card_total` DECIMAL(10,3) DEFAULT 0.000,
    `qr_total` DECIMAL(10,3) DEFAULT 0.000,
    `system_total` DECIMAL(10,3) DEFAULT 0.000,
    `collected_cash` DECIMAL(10,3) DEFAULT 0.000,
    `collected_card` DECIMAL(10,3) DEFAULT 0.000,
    `collected_qr` DECIMAL(10,3) DEFAULT 0.000,
    `collected_total` DECIMAL(10,3) DEFAULT 0.000,
    `cashier_notes` TEXT DEFAULT NULL,
    `status` ENUM('open', 'close_requested', 'closed') DEFAULT 'open',
    `approved_by` INT DEFAULT NULL,
    FOREIGN KEY (`cashier_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Bills Table
CREATE TABLE IF NOT EXISTS `bills` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `subtotal` DECIMAL(10,3) NOT NULL,
    `tax_amount` DECIMAL(10,3) NOT NULL,
    `discount_percent` DECIMAL(5,2) DEFAULT 0.00,
    `discount_amount` DECIMAL(10,3) DEFAULT 0.000,
    `grand_total` DECIMAL(10,3) NOT NULL,
    `payment_method` ENUM('cash', 'card', 'qr_pay') DEFAULT NULL,
    `status` ENUM('pending', 'paid') DEFAULT 'pending',
    `cashier_id` INT DEFAULT NULL,
    `customer_id` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`cashier_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Default Data Insertions
INSERT IGNORE INTO `users` (`id`, `username`, `password`, `name`, `role`) VALUES
(1, 'admin', '$2y$10$eKJ6GL3MMiONVOGB.YY92.EUbDW1xJn72.K7OYbxwN6oczfwpgk2e', 'System Administrator', 'admin'),
(2, 'waiter1', '$2y$10$Zm8osWJRVu6LWa9MH/wZ4.tZxFD.2yivpg0QRGSr2azhal5DgXd5C', 'Waiter John', 'waiter'),
(3, 'waiter2', '$2y$10$Zm8osWJRVu6LWa9MH/wZ4.tZxFD.2yivpg0QRGSr2azhal5DgXd5C', 'Waiter Sarah', 'waiter'),
(4, 'chef1', '$2y$10$taBABla6.ATOxuS7pY10uu8z4T3d7GNa/bVKiW8ZuoSaXKVWqj0zi', 'Head Chef Mario', 'kot'),
(5, 'counter1', '$2y$10$rC2bzZxCggfJT0FUHUAKnOdFdHJ3eVNMSdWfj8lm9muu9abOZPtK.', 'Cashier Sam', 'counter'),
(6, 'superadmin', '$2y$10$GIlyTrYJ3QAvz5vzgYjh2.QZV5HJYep7yvez8ay5dgyYs5HXoa3Nq', 'SaNDS Lab Super Admin', 'admin');

-- Default Setting
INSERT IGNORE INTO `settings` (`id`, `restaurant_name`, `currency_code`, `time_zone`, `custom_units`, `tax_type`, `vat_percent`, `cgst_percent`, `sgst_percent`, `printer_size`, `logo_path`, `software_expiry_date`) VALUES
(1, 'Gourmet Express', 'BHD', 'Asia/Bahrain', 'Nos, Box, Packet, Gram, KG, Litre, ML', 'VAT', 10.00, 2.50, 2.50, 80, NULL, '2027-12-31');

-- Default Dining Tables (1 to 20)
INSERT IGNORE INTO `dining_tables` (`table_number`) VALUES
(1), (2), (3), (4), (5), (6), (7), (8), (9), (10),
(11), (12), (13), (14), (15), (16), (17), (18), (19), (20);

-- Suppliers Table (For Inventory Purchases)
CREATE TABLE IF NOT EXISTS `suppliers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `contact_person` VARCHAR(100) DEFAULT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Inventory Items Table (Raw Materials)
CREATE TABLE IF NOT EXISTS `inventory_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `unit` VARCHAR(50) NOT NULL DEFAULT 'Nos',
    `current_stock` DECIMAL(10,3) NOT NULL DEFAULT 0.000,
    `min_stock_level` DECIMAL(10,3) NOT NULL DEFAULT 0.000,
    `buying_price_per_unit` DECIMAL(10,3) NOT NULL DEFAULT 0.000,
    `selling_price` DECIMAL(10,3) NOT NULL DEFAULT 0.000,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Product Recipes Table (BOM)
CREATE TABLE IF NOT EXISTS `product_recipes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `inventory_item_id` INT NOT NULL,
    `quantity_required` DECIMAL(10,3) NOT NULL,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Inventory Transactions Table
CREATE TABLE IF NOT EXISTS `inventory_transactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `inventory_item_id` INT NOT NULL,
    `transaction_type` ENUM('add_stock', 'consume_kot', 'adjustment', 'damage') NOT NULL,
    `quantity` DECIMAL(10,3) NOT NULL, -- Positive for add, negative for consume/damage
    `unit_price` DECIMAL(10,3) DEFAULT NULL, -- Captured at time of transaction
    `supplier_id` INT DEFAULT NULL, -- If added from a supplier
    `chef_id` INT DEFAULT NULL, -- Chef who marked KOT as ready (caused consumption)
    `reference_id` VARCHAR(50) DEFAULT NULL, -- e.g., KOT Number or Invoice Number
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`chef_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

