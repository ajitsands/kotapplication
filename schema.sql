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
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Settings Table
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `restaurant_name` VARCHAR(100) NOT NULL DEFAULT 'Gourmet Restaurant',
    `currency_code` VARCHAR(10) NOT NULL DEFAULT 'BHD',
    `time_zone` VARCHAR(50) NOT NULL DEFAULT 'Asia/Bahrain',
    `tax_type` ENUM('VAT', 'GST') NOT NULL DEFAULT 'VAT',
    `vat_percent` DECIMAL(5,2) NOT NULL DEFAULT 10.00,
    `cgst_percent` DECIMAL(5,2) NOT NULL DEFAULT 2.50,
    `sgst_percent` DECIMAL(5,2) NOT NULL DEFAULT 2.50,
    `printer_size` INT NOT NULL DEFAULT 80, -- 58 or 80
    `logo_path` VARCHAR(255) DEFAULT NULL,
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
    `table_number` INT NOT NULL,
    `status` ENUM('active', 'closed', 'completed') DEFAULT 'active',
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
    `status` ENUM('pending', 'preparing', 'ready', 'dispatched') DEFAULT 'pending',
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
    `status` ENUM('pending', 'preparing', 'ready', 'dispatched') DEFAULT 'pending',
    `notes` VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (`kot_id`) REFERENCES `kots`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
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
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Default Data Insertions
INSERT INTO `users` (`id`, `username`, `password`, `name`, `role`) VALUES
(1, 'admin', '$2y$10$eKJ6GL3MMiONVOGB.YY92.EUbDW1xJn72.K7OYbxwN6oczfwpgk2e', 'System Administrator', 'admin'),
(2, 'waiter1', '$2y$10$Zm8osWJRVu6LWa9MH/wZ4.tZxFD.2yivpg0QRGSr2azhal5DgXd5C', 'Waiter John', 'waiter'),
(3, 'waiter2', '$2y$10$Zm8osWJRVu6LWa9MH/wZ4.tZxFD.2yivpg0QRGSr2azhal5DgXd5C', 'Waiter Sarah', 'waiter'),
(4, 'chef1', '$2y$10$taBABla6.ATOxuS7pY10uu8z4T3d7GNa/bVKiW8ZuoSaXKVWqj0zi', 'Head Chef Mario', 'kot'),
(5, 'counter1', '$2y$10$rC2bzZxCggfJT0FUHUAKnOdFdHJ3eVNMSdWfj8lm9muu9abOZPtK.', 'Cashier Sam', 'counter');

-- Default Setting
INSERT INTO `settings` (`id`, `restaurant_name`, `currency_code`, `time_zone`, `tax_type`, `vat_percent`, `cgst_percent`, `sgst_percent`, `printer_size`, `logo_path`) VALUES
(1, 'Gourmet Express', 'BHD', 'Asia/Bahrain', 'VAT', 10.00, 2.50, 2.50, 80, NULL);

-- Default Dining Tables (1 to 20)
INSERT INTO `dining_tables` (`table_number`) VALUES
(1), (2), (3), (4), (5), (6), (7), (8), (9), (10),
(11), (12), (13), (14), (15), (16), (17), (18), (19), (20);
