<?php
/**
 * Master Demo Data Generator & Seed Script
 * Specifically built for B1 BURGER (b1.restoflow.us)
 * Extracted from b1Menu/B1_Burger_Menu_With_Food_Pictures.pdf
 * 
 * Usage:
 * CLI: php seed_demo_data.php
 * Web: Accessed via /admin/generate-demo-data (Superadmin only)
 */

if (php_sapi_name() !== 'cli') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (($_SESSION['username'] ?? '') !== 'superadmin') {
        http_response_code(403);
        die("<h1>403 Forbidden</h1><p>Superadmin access required to run this script.</p>");
    }
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo (php_sapi_name() === 'cli') ? "--- Starting B1 Burger Master Menu Seed ---\n" : "<div style='font-family: monospace; background: #0b0f19; color: #10b981; padding: 20px; border-radius: 12px;'><h3>🌱 Generating B1 Burger Master Data...</h3><pre>";

    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // 1. Verify Schema Tables
    $schemaTables = [
        "CREATE TABLE IF NOT EXISTS `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(50) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `name` VARCHAR(100) NOT NULL,
            `role` ENUM('admin', 'waiter', 'kot', 'counter') NOT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `is_logged_in` TINYINT(1) NOT NULL DEFAULT 0,
            `last_login` DATETIME DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        "CREATE TABLE IF NOT EXISTS `settings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `restaurant_name` VARCHAR(100) NOT NULL DEFAULT 'B1 Burger',
            `currency_code` VARCHAR(10) NOT NULL DEFAULT 'BHD',
            `time_zone` VARCHAR(50) NOT NULL DEFAULT 'Asia/Bahrain',
            `custom_units` VARCHAR(255) DEFAULT 'Nos, Meal, Portion, Box, Packet, Gram, KG, Litre, ML, Cup, Can',
            `tax_type` ENUM('VAT', 'GST') NOT NULL DEFAULT 'VAT',
            `vat_percent` DECIMAL(5,2) NOT NULL DEFAULT 10.00,
            `cgst_percent` DECIMAL(5,2) NOT NULL DEFAULT 2.50,
            `sgst_percent` DECIMAL(5,2) NOT NULL DEFAULT 2.50,
            `printer_size` INT NOT NULL DEFAULT 80,
            `logo_path` VARCHAR(255) DEFAULT 'uploads/b1_logo.png',
            `software_expiry_date` DATE DEFAULT '2027-12-31',
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        "CREATE TABLE IF NOT EXISTS `categories` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL UNIQUE,
            `image_url` VARCHAR(255) DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        "CREATE TABLE IF NOT EXISTS `products` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `category_id` INT NOT NULL,
            `name` VARCHAR(100) NOT NULL,
            `description` TEXT,
            `price` DECIMAL(10,3) NOT NULL,
            `image_url` VARCHAR(255) DEFAULT NULL,
            `is_available` TINYINT(1) DEFAULT 1,
            `is_counter_item` TINYINT(1) DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        "CREATE TABLE IF NOT EXISTS `dining_tables` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `table_number` INT NOT NULL UNIQUE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        "CREATE TABLE IF NOT EXISTS `orders` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `table_number` INT DEFAULT NULL,
            `status` ENUM('active', 'closed', 'completed', 'cancelled') DEFAULT 'active',
            `order_type` ENUM('dine_in', 'take_away', 'online') NOT NULL DEFAULT 'dine_in',
            `platform_id` INT DEFAULT NULL,
            `platform_order_number` VARCHAR(100) DEFAULT NULL,
            `customer_name` VARCHAR(100) DEFAULT NULL,
            `customer_mobile` VARCHAR(20) DEFAULT NULL,
            `token_number` VARCHAR(10) DEFAULT NULL,
            `waiter_id` INT DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`waiter_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        "CREATE TABLE IF NOT EXISTS `kots` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `order_id` INT NOT NULL,
            `waiter_id` INT DEFAULT NULL,
            `kot_number` VARCHAR(50) NOT NULL UNIQUE,
            `status` ENUM('pending', 'preparing', 'ready', 'dispatched', 'cancelled') DEFAULT 'pending',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`waiter_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        "CREATE TABLE IF NOT EXISTS `kot_items` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `kot_id` INT NOT NULL,
            `product_id` INT NOT NULL,
            `quantity` INT NOT NULL,
            `status` ENUM('pending', 'preparing', 'ready', 'dispatched', 'cancelled') DEFAULT 'pending',
            `refund_status` ENUM('pending', 'refunded') DEFAULT 'pending',
            `notes` VARCHAR(255) DEFAULT NULL,
            FOREIGN KEY (`kot_id`) REFERENCES `kots`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        "CREATE TABLE IF NOT EXISTS `customers` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `mobile` VARCHAR(20) NOT NULL UNIQUE,
            `name` VARCHAR(100) NOT NULL,
            `gender` VARCHAR(20) DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        "CREATE TABLE IF NOT EXISTS `bills` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        "CREATE TABLE IF NOT EXISTS `counter_sessions` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        "CREATE TABLE IF NOT EXISTS `suppliers` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `contact_person` VARCHAR(100) DEFAULT NULL,
            `phone` VARCHAR(20) DEFAULT NULL,
            `email` VARCHAR(100) DEFAULT NULL,
            `address` TEXT DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        "CREATE TABLE IF NOT EXISTS `inventory_items` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL UNIQUE,
            `unit` VARCHAR(50) NOT NULL DEFAULT 'Nos',
            `current_stock` DECIMAL(10,3) NOT NULL DEFAULT 0.000,
            `min_stock_level` DECIMAL(10,3) NOT NULL DEFAULT 0.000,
            `buying_price_per_unit` DECIMAL(10,3) NOT NULL DEFAULT 0.000,
            `selling_price` DECIMAL(10,3) NOT NULL DEFAULT 0.000,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        "CREATE TABLE IF NOT EXISTS `product_recipes` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `product_id` INT NOT NULL,
            `inventory_item_id` INT NOT NULL,
            `quantity_required` DECIMAL(10,3) NOT NULL,
            FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        "CREATE TABLE IF NOT EXISTS `inventory_transactions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `inventory_item_id` INT NOT NULL,
            `transaction_type` ENUM('add_stock', 'consume_kot', 'adjustment', 'damage') NOT NULL,
            `quantity` DECIMAL(10,3) NOT NULL,
            `unit_price` DECIMAL(10,3) DEFAULT NULL,
            `supplier_id` INT DEFAULT NULL,
            `chef_id` INT DEFAULT NULL,
            `reference_id` VARCHAR(50) DEFAULT NULL,
            `notes` TEXT DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE SET NULL,
            FOREIGN KEY (`chef_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        "CREATE TABLE IF NOT EXISTS `online_platforms` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `status` ENUM('active','inactive') DEFAULT 'active',
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
    ];

    foreach ($schemaTables as $sql) {
        $db->exec($sql);
    }

    // Normalizing table charsets
    $tableNames = ['users', 'settings', 'categories', 'products', 'dining_tables', 'orders', 'kots', 'kot_items', 'customers', 'bills', 'counter_sessions', 'suppliers', 'inventory_items', 'product_recipes', 'inventory_transactions', 'online_platforms'];
    foreach ($tableNames as $tbl) {
        try {
            $db->exec("ALTER TABLE `$tbl` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (Exception $e) {}
    }
    echo "✓ Database tables verified.\n";

    // 2. Wipe Previous Data for Clean B1 Menu Deployment
    $db->exec("TRUNCATE TABLE `kot_items`;");
    $db->exec("TRUNCATE TABLE `kots`;");
    $db->exec("TRUNCATE TABLE `bills`;");
    $db->exec("TRUNCATE TABLE `orders`;");
    $db->exec("TRUNCATE TABLE `counter_sessions`;");
    $db->exec("TRUNCATE TABLE `inventory_transactions`;");
    $db->exec("TRUNCATE TABLE `product_recipes`;");
    $db->exec("TRUNCATE TABLE `products`;");
    $db->exec("TRUNCATE TABLE `categories`;");
    $db->exec("TRUNCATE TABLE `inventory_items`;");
    $db->exec("TRUNCATE TABLE `suppliers`;");
    $db->exec("TRUNCATE TABLE `customers`;");

    echo "✓ Cleaned previous test tables.\n";

    // 3. Settings Setup for B1 Burger
    $db->exec("INSERT INTO `settings` (`id`, `restaurant_name`, `currency_code`, `time_zone`, `custom_units`, `tax_type`, `vat_percent`, `cgst_percent`, `sgst_percent`, `printer_size`, `logo_path`, `software_expiry_date`) 
               VALUES (1, 'B1 Burger', 'BHD', 'Asia/Bahrain', 'Nos, Meal, Portion, Box, Packet, Gram, KG, Litre, ML, Cup, Can', 'VAT', 10.00, 2.50, 2.50, 80, 'uploads/b1_logo.png', '2027-12-31')
               ON DUPLICATE KEY UPDATE 
               `restaurant_name` = 'B1 Burger',
               `currency_code` = 'BHD',
               `tax_type` = 'VAT',
               `vat_percent` = 10.00,
               `logo_path` = 'uploads/b1_logo.png';");
    echo "✓ Settings initialized (B1 Burger, BHD, 10% VAT, Logo: uploads/b1_logo.png).\n";

    // 4. Default Users
    $defaultUsers = [
        [1, 'admin', '$2y$10$eKJ6GL3MMiONVOGB.YY92.EUbDW1xJn72.K7OYbxwN6oczfwpgk2e', 'System Administrator', 'admin'],
        [2, 'waiter1', '$2y$10$Zm8osWJRVu6LWa9MH/wZ4.tZxFD.2yivpg0QRGSr2azhal5DgXd5C', 'Waiter John', 'waiter'],
        [3, 'waiter2', '$2y$10$Zm8osWJRVu6LWa9MH/wZ4.tZxFD.2yivpg0QRGSr2azhal5DgXd5C', 'Waiter Sarah', 'waiter'],
        [4, 'chef1', '$2y$10$taBABla6.ATOxuS7pY10uu8z4T3d7GNa/bVKiW8ZuoSaXKVWqj0zi', 'Head Chef Mario', 'kot'],
        [5, 'counter1', '$2y$10$rC2bzZxCggfJT0FUHUAKnOdFdHJ3eVNMSdWfj8lm9muu9abOZPtK.', 'Cashier Sam', 'counter'],
        [6, 'superadmin', '$2y$10$GIlyTrYJ3QAvz5vzgYjh2.QZV5HJYep7yvez8ay5dgyYs5HXoa3Nq', 'SaNDS Lab Super Admin', 'admin']
    ];

    $stmtUser = $db->prepare("INSERT INTO `users` (`id`, `username`, `password`, `name`, `role`, `is_active`) VALUES (?, ?, ?, ?, ?, 1)
                              ON DUPLICATE KEY UPDATE `password` = VALUES(`password`), `name` = VALUES(`name`), `role` = VALUES(`role`), `is_active` = 1");
    foreach ($defaultUsers as $u) {
        $stmtUser->execute($u);
    }
    echo "✓ Users configured.\n";

    // 5. Dining Tables (1 to 20)
    for ($t = 1; $t <= 20; $t++) {
        $db->exec("INSERT IGNORE INTO `dining_tables` (`table_number`) VALUES ($t)");
    }
    echo "✓ 20 Dining Tables initialized.\n";

    // 6. Online Delivery Platforms
    $platforms = ['Talabat', 'Jahez', 'Hungerstation', 'Deliveroo', 'Ahlan', 'UberEats'];
    $stmtPlat = $db->prepare("INSERT IGNORE INTO `online_platforms` (`id`, `name`, `status`) VALUES (?, ?, 'active')");
    foreach ($platforms as $idx => $pname) {
        $stmtPlat->execute([$idx + 1, $pname]);
    }
    echo "✓ Delivery Platforms seeded.\n";

    // 7. Suppliers
    $suppliers = [
        ['Bahrain Prime Meats & Poultry', 'Ahmed Al-Doseri', '+973 33112233', 'orders@b1prime.bh', 'Manama Central Market'],
        ['Gulf Bakery & Sauce Traders', 'Khalid Hassan', '+973 39887766', 'sales@gulfbakery.com', 'Salmabad Industrial Area'],
        ['Fresh Beverage & Dairy Supplies', 'Ali Mansoor', '+973 36554433', 'beverages@freshbh.com', 'Tubli Commercial Zone']
    ];
    $stmtSupp = $db->prepare("INSERT INTO `suppliers` (`name`, `contact_person`, `phone`, `email`, `address`) VALUES (?, ?, ?, ?, ?)");
    foreach ($suppliers as $s) {
        $stmtSupp->execute($s);
    }
    echo "✓ Suppliers seeded.\n";

    // 8. Raw Material Inventory Items
    $inventoryItems = [
        ['Fresh Angus Beef Patty Mix', 'KG', 120.000, 20.000, 2.200, 3.400],
        ['Fresh Chicken Fillet & Strips', 'KG', 90.000, 15.000, 1.600, 2.600],
        ['Artisan Brioche Burger Buns', 'Nos', 350.000, 50.000, 0.120, 0.250],
        ['B1 Signature House Sauce', 'Litre', 40.000, 10.000, 1.800, 3.000],
        ['Melted Cheddar Cheese Sauce', 'Litre', 35.000, 8.000, 2.200, 3.500],
        ['Premium Skin-On Fries (Frozen)', 'KG', 100.000, 25.000, 0.650, 1.200],
        ['Fresh Iceberg Lettuce & Veggies', 'KG', 30.000, 5.000, 0.400, 0.800],
        ['Mojito Puree & Sparkling Mix', 'Litre', 45.000, 10.000, 1.200, 2.200],
        ['Milkshake Ice Cream Base & Milk', 'Litre', 50.000, 12.000, 0.900, 1.800],
        ['Assorted Dip Sauces', 'Litre', 30.000, 5.000, 1.500, 2.500]
    ];
    $stmtInv = $db->prepare("INSERT INTO `inventory_items` (`name`, `unit`, `current_stock`, `min_stock_level`, `buying_price_per_unit`, `selling_price`) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($inventoryItems as $item) {
        $stmtInv->execute($item);
    }
    echo "✓ Raw Material Inventory items configured.\n";

    // 9. Exact B1 Burger Menu Categories & Products with Pictures
    $b1Categories = [
        [
            'name' => 'Burgers (Smashed to perfection)',
            'image_url' => 'uploads/cat_burgers.jpg',
            'products' => [
                ['B1 Classic Burger', 'Smashed beef patty, B1 sauce', 1.000, 'uploads/b1_classic_burger.jpg', 0],
                ['B1 Classic Burger (Meal)', 'Smashed beef patty, B1 sauce (Includes Fries & Drink)', 2.000, 'uploads/b1_classic_burger.jpg', 0],
                ['B1 Double Burger', 'Smashed double patties, B1 sauce, lettuce, onion, tomato', 1.500, 'uploads/b1_double_burger.jpg', 0],
                ['B1 Double Burger (Meal)', 'Smashed double patties, B1 sauce, lettuce, onion, tomato (Includes Fries & Drink)', 2.500, 'uploads/b1_double_burger.jpg', 0],
                ['B1 Signature Burger', 'Smashed patty, B1 sauce, lettuce, onion rings, tomato, pickles toppings', 2.000, 'uploads/b1_signature_burger.jpg', 0],
                ['B1 Signature Burger (Meal)', 'Smashed patty, B1 sauce, lettuce, onion rings, tomato, pickles toppings (Includes Fries & Drink)', 3.000, 'uploads/b1_signature_burger.jpg', 0],
                ['B1 Chicken Sando Burger', 'Chicken deep fry, B1 sauce', 1.000, 'uploads/b1_chicken_sando.jpg', 0],
                ['B1 Chicken Sando Burger (Meal)', 'Chicken deep fry, B1 sauce (Includes Fries & Drink)', 2.000, 'uploads/b1_chicken_sando.jpg', 0],
                ['B1 Chicken Nashville Burger', 'Chicken, B1 spicy sauce, lettuce, onion, tomato', 1.500, 'uploads/b1_chicken_nashville.jpg', 0],
                ['B1 Chicken Nashville Burger (Meal)', 'Chicken, B1 spicy sauce, lettuce, onion, tomato (Includes Fries & Drink)', 2.500, 'uploads/b1_chicken_nashville.jpg', 0],
                ['B1 Chicken Signature', 'B1 special chicken, B1 sauce, lettuce, tomato, pickles, cheese', 2.000, 'uploads/b1_chicken_signature.jpg', 0],
                ['B1 Chicken Signature (Meal)', 'B1 special chicken, B1 sauce, lettuce, tomato, pickles, cheese (Includes Fries & Drink)', 3.000, 'uploads/b1_chicken_signature.jpg', 0]
            ]
        ],
        [
            'name' => 'Kids Meals',
            'image_url' => 'uploads/cat_kids_meals.jpg',
            'products' => [
                ['Kids Meal - 4 Nuggets', '4 Crispy Chicken Nuggets, Fries, Drink', 1.300, 'uploads/kids_meal_nuggets.jpg', 0],
                ['Kids Meal - 4 Chicken Strips', '4 Tender Chicken Strips, Fries, Drink', 1.500, 'uploads/kids_meal_strips.jpg', 0]
            ]
        ],
        [
            'name' => 'Sides',
            'image_url' => 'uploads/cat_sides.jpg',
            'products' => [
                ['B1 Crispy Fries', 'Golden seasoned crispy potato fries', 0.600, 'uploads/b1_fries.jpg', 0],
                ['B1 Loaded Fries', 'Chicken, Fries, B1 Special Sauce, Cheese drizzle', 1.500, 'uploads/b1_loaded_fries.jpg', 0]
            ]
        ],
        [
            'name' => 'Wraps',
            'image_url' => 'uploads/cat_wraps.jpg',
            'products' => [
                ['B1 Lettuce Chicken Wrap', 'Crisp lettuce wrapped, grilled chicken bites, B1 special sauce', 1.600, 'uploads/b1_wrap.jpg', 0]
            ]
        ],
        [
            'name' => 'Drinks & Shakes',
            'image_url' => 'uploads/cat_drinks_shakes.jpg',
            'products' => [
                ['Mojito - Passion Fruit', 'Refreshing passion fruit mojito with fresh mint and lime', 1.000, 'uploads/b1_mojitos.jpg', 0],
                ['Mojito - Watermelon', 'Fresh sweet watermelon mojito with mint and lime', 1.000, 'uploads/b1_mojitos.jpg', 0],
                ['Mojito - Strawberry', 'Berry delicious strawberry mojito with fresh mint', 1.000, 'uploads/b1_mojitos.jpg', 0],
                ['Mojito - Pineapple', 'Tropical pineapple mojito with fresh mint and lime', 1.000, 'uploads/b1_mojitos.jpg', 0],
                ['Mojito - Lemon Mint', 'Classic zesty lemon & fresh crushed mint mojito', 1.000, 'uploads/b1_mojitos.jpg', 0],
                ['Milkshake - Vanilla', 'Thick creamy vanilla bean shake topped with whipped cream & chocolate drizzle', 1.500, 'uploads/b1_milkshakes.jpg', 0],
                ['Milkshake - Strawberry', 'Rich creamy strawberry milkshake with whipped cream', 1.500, 'uploads/b1_milkshakes.jpg', 0]
            ]
        ],
        [
            'name' => 'Add Ons & Sauces',
            'image_url' => 'uploads/cat_addons.jpg',
            'products' => [
                ['B1 House Sauce', 'Signature B1 burger sauce cup', 0.250, 'uploads/b1_sauce.jpg', 1],
                ['Garlic Mayo Sauce', 'Rich creamy garlic mayonnaise dipping sauce', 0.250, 'uploads/garlic_mayo_sauce.jpg', 1],
                ['Warm Cheese Sauce', 'Melted golden cheddar cheese sauce cup', 0.250, 'uploads/cheese_sauce.jpg', 1],
                ['B1 Spicy Chili Sauce', 'Extra spicy B1 chili dipping sauce', 0.250, 'uploads/b1_spicy_sauce.jpg', 1],
                ['Extra Smashed Beef Patty', 'Fresh smashed grilled beef patty add-on', 0.700, 'uploads/beef_patty.jpg', 0],
                ['Extra Crispy Chicken Patty', 'Crispy fried chicken breast patty add-on', 0.650, 'uploads/chicken_patty.jpg', 0]
            ]
        ]
    ];

    $productIds = [];
    $stmtCat = $db->prepare("INSERT INTO `categories` (`name`, `image_url`) VALUES (?, ?)");
    $stmtProd = $db->prepare("INSERT INTO `products` (`category_id`, `name`, `description`, `price`, `image_url`, `is_available`, `is_counter_item`) VALUES (?, ?, ?, ?, ?, 1, ?)");

    foreach ($b1Categories as $cat) {
        $stmtCat->execute([$cat['name'], $cat['image_url']]);
        $catId = $db->lastInsertId();

        foreach ($cat['products'] as $prod) {
            $stmtProd->execute([$catId, $prod[0], $prod[1], $prod[2], $prod[3], $prod[4]]);
            $productIds[] = [
                'id' => $db->lastInsertId(),
                'name' => $prod[0],
                'price' => $prod[2]
            ];
        }
    }
    echo "✓ " . count($b1Categories) . " B1 Categories and " . count($productIds) . " B1 Products with exact PDF food pictures seeded.\n";

    // 10. Sample Customers
    $customers = [
        ['+973 39123456', 'Ali Mansoor', 'male'],
        ['+973 36987654', 'Fatima Ebrahim', 'female'],
        ['+973 33456789', 'David Miller', 'male'],
        ['+973 38112244', 'Noor Al-Hassan', 'female'],
        ['+973 37554433', 'Zaid Tariq', 'male'],
        ['+973 34221199', 'Sara Al-Ghatam', 'female']
    ];
    $stmtCust = $db->prepare("INSERT INTO `customers` (`mobile`, `name`, `gender`) VALUES (?, ?, ?)");
    $custIds = [];
    foreach ($customers as $c) {
        $stmtCust->execute($c);
        $custIds[] = $db->lastInsertId();
    }
    echo "✓ Sample Customers seeded.\n";

    // 11. Generate Realistic Historical Paid Transactions for B1 Burger (Last 7 Days)
    echo "🌱 Generating historical paid bills and KOTs for reports & dashboard...\n";

    $waiterIds = [2, 3];
    $cashierId = 5;
    $paymentMethods = ['cash', 'card', 'qr_pay', 'card', 'cash'];

    $orderInsert = $db->prepare("INSERT INTO `orders` (`table_number`, `status`, `order_type`, `platform_id`, `platform_order_number`, `customer_name`, `customer_mobile`, `token_number`, `waiter_id`, `created_at`, `updated_at`) VALUES (?, 'closed', ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $kotInsert = $db->prepare("INSERT INTO `kots` (`order_id`, `waiter_id`, `kot_number`, `status`, `created_at`) VALUES (?, ?, ?, 'dispatched', ?)");
    $kotItemInsert = $db->prepare("INSERT INTO `kot_items` (`kot_id`, `product_id`, `quantity`, `status`, `notes`) VALUES (?, ?, ?, 'dispatched', ?)");
    $billInsert = $db->prepare("INSERT INTO `bills` (`order_id`, `subtotal`, `tax_amount`, `discount_percent`, `discount_amount`, `grand_total`, `payment_method`, `status`, `cashier_id`, `customer_id`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, ?, 'paid', ?, ?, ?)");

    $totalHistoricalOrders = 36;
    $orderCounter = 100;

    for ($i = 0; $i < $totalHistoricalOrders; $i++) {
        $daysAgo = rand(0, 6);
        $hour = rand(11, 23);
        $minute = rand(0, 59);
        $second = rand(0, 59);
        $timeStr = date('Y-m-d H:i:s', strtotime("-$daysAgo days $hour:$minute:$second"));

        $orderTypeRand = rand(1, 10);
        $orderType = 'dine_in';
        $tableNum = rand(1, 20);
        $platformId = null;
        $platformOrderNo = null;
        $tokenNum = null;

        if ($orderTypeRand <= 6) {
            $orderType = 'dine_in';
        } elseif ($orderTypeRand <= 8) {
            $orderType = 'take_away';
            $tableNum = null;
            $tokenNum = 'TK-' . str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT);
        } else {
            $orderType = 'online';
            $tableNum = null;
            $platformId = rand(1, 4);
            $platformOrderNo = 'TAL-' . strtoupper(substr(uniqid(), -5));
            $tokenNum = 'OL-' . str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT);
        }

        $wId = $waiterIds[array_rand($waiterIds)];
        $cId = $custIds[array_rand($custIds)];
        $cName = $customers[array_rand($customers)][1];
        $cMobile = $customers[array_rand($customers)][0];

        $orderInsert->execute([$tableNum, $orderType, $platformId, $platformOrderNo, $cName, $cMobile, $tokenNum, $wId, $timeStr, $timeStr]);
        $orderId = $db->lastInsertId();

        $kotNum = 'KOT-' . date('Ymd', strtotime($timeStr)) . '-' . str_pad($orderCounter++, 4, '0', STR_PAD_LEFT);
        $kotInsert->execute([$orderId, $wId, $kotNum, $timeStr]);
        $kotId = $db->lastInsertId();

        // 2 to 4 random B1 items
        $itemCount = rand(2, 4);
        $subtotal = 0.0;
        $selectedKeys = (array)array_rand($productIds, $itemCount);

        foreach ($selectedKeys as $k) {
            $p = $productIds[$k];
            $qty = rand(1, 2);
            $itemSubtotal = $p['price'] * $qty;
            $subtotal += $itemSubtotal;
            $kotItemInsert->execute([$kotId, $p['id'], $qty, (rand(0, 1) ? 'Extra sauce' : null)]);
        }

        $vatPercent = 10.00;
        $taxAmount = round($subtotal * ($vatPercent / 100), 3);
        $discountPercent = (rand(1, 6) === 1) ? 10.00 : 0.00;
        $discountAmount = round(($subtotal + $taxAmount) * ($discountPercent / 100), 3);
        $grandTotal = round(($subtotal + $taxAmount) - $discountAmount, 3);
        $pm = $paymentMethods[array_rand($paymentMethods)];

        $billInsert->execute([$orderId, $subtotal, $taxAmount, $discountPercent, $discountAmount, $grandTotal, $pm, $cashierId, $cId, $timeStr]);
    }
    echo "✓ $totalHistoricalOrders Paid B1 Burger orders & bills generated across the last 7 days.\n";

    // 12. Create Live Active Orders for Testing
    echo "🌱 Creating active live B1 orders for testing...\n";

    // Table 2: Active Dine-In order
    $db->exec("INSERT INTO `orders` (`table_number`, `status`, `order_type`, `waiter_id`, `customer_name`, `created_at`) 
               VALUES (2, 'active', 'dine_in', 2, 'Table 2 Guests', NOW())");
    $actOrder1 = $db->lastInsertId();
    $db->exec("INSERT INTO `kots` (`order_id`, `waiter_id`, `kot_number`, `status`, `created_at`) 
               VALUES ($actOrder1, 2, 'KOT-" . date('Ymd') . "-B101', 'preparing', NOW())");
    $actKot1 = $db->lastInsertId();
    $db->exec("INSERT INTO `kot_items` (`kot_id`, `product_id`, `quantity`, `status`, `notes`) VALUES 
               ($actKot1, {$productIds[4]['id']}, 2, 'preparing', 'B1 Signature Burger - No onion'),
               ($actKot1, {$productIds[13]['id']}, 1, 'preparing', 'Loaded Fries - Extra Cheese'),
               ($actKot1, {$productIds[16]['id']}, 2, 'ready', 'Passion Fruit Mojito')");

    // Table 5: Active Dine-In order (Ready in kitchen)
    $db->exec("INSERT INTO `orders` (`table_number`, `status`, `order_type`, `waiter_id`, `customer_name`, `created_at`) 
               VALUES (5, 'active', 'dine_in', 3, 'VIP Booth', NOW())");
    $actOrder2 = $db->lastInsertId();
    $db->exec("INSERT INTO `kots` (`order_id`, `waiter_id`, `kot_number`, `status`, `created_at`) 
               VALUES ($actOrder2, 3, 'KOT-" . date('Ymd') . "-B102', 'ready', NOW())");
    $actKot2 = $db->lastInsertId();
    $db->exec("INSERT INTO `kot_items` (`kot_id`, `product_id`, `quantity`, `status`, `notes`) VALUES 
               ($actKot2, {$productIds[2]['id']}, 2, 'ready', 'B1 Double Burger'),
               ($actKot2, {$productIds[8]['id']}, 1, 'ready', 'B1 Chicken Nashville (Meal)'),
               ($actKot2, {$productIds[21]['id']}, 2, 'ready', 'Vanilla Milkshake')");

    // Talabat Online order
    $db->exec("INSERT INTO `orders` (`status`, `order_type`, `platform_id`, `platform_order_number`, `customer_name`, `customer_mobile`, `token_number`, `created_at`) 
               VALUES ('active', 'online', 1, 'TAL-58210', 'Hamad Al-Khalifa', '+973 39887711', 'OL-08', NOW())");
    $actOrder3 = $db->lastInsertId();
    $db->exec("INSERT INTO `kots` (`order_id`, `waiter_id`, `kot_number`, `status`, `created_at`) 
               VALUES ($actOrder3, 2, 'KOT-" . date('Ymd') . "-TAL88', 'preparing', NOW())");
    $actKot3 = $db->lastInsertId();
    $db->exec("INSERT INTO `kot_items` (`kot_id`, `product_id`, `quantity`, `status`, `notes`) VALUES 
               ($actKot3, {$productIds[5]['id']}, 1, 'preparing', 'B1 Signature Meal'),
               ($actKot3, {$productIds[17]['id']}, 1, 'preparing', 'Watermelon Mojito')");

    echo "✓ Active live orders configured on Table 2, Table 5, and Talabat Online Delivery.\n";

    // 13. Counter Sessions
    $db->exec("INSERT INTO `counter_sessions` 
        (`cashier_id`, `opened_at`, `closed_at`, `cash_total`, `card_total`, `qr_total`, `system_total`, `collected_cash`, `collected_card`, `collected_qr`, `collected_total`, `cashier_notes`, `status`, `approved_by`) 
        VALUES 
        (5, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 18 HOUR), 35.000, 58.500, 12.500, 106.000, 35.000, 58.500, 12.500, 106.000, 'B1 Burger day close. Shift balanced.', 'closed', 1)");

    $db->exec("INSERT INTO `counter_sessions` 
        (`cashier_id`, `opened_at`, `cash_total`, `card_total`, `qr_total`, `system_total`, `status`) 
        VALUES 
        (5, NOW(), 18.000, 32.500, 8.500, 59.000, 'open')");

    echo "✓ Cash drawer sessions seeded.\n";

    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");

    echo "\n🎉 SUCCESS! B1 Burger Master Menu & Food Pictures seeded successfully.\n";
    echo "--------------------------------------------------------\n";
    echo "📋 B1 Burger System Credentials:\n";
    echo "  • Superadmin: superadmin / (existing)\n";
    echo "  • Admin:      admin / admin123\n";
    echo "  • Waiter:     waiter1 / waiter123\n";
    echo "  • Waiter:     waiter2 / waiter123\n";
    echo "  • Chef:       chef1 / chef123\n";
    echo "  • Cashier:    counter1 / counter123\n";
    echo "--------------------------------------------------------\n";

    if (php_sapi_name() !== 'cli') {
        echo "</pre><br><a href='/admin' style='display:inline-block; background:#6366f1; color:white; padding:12px 24px; border-radius:8px; text-decoration:none; font-weight:600;'>Go to Admin Dashboard</a></div>";
    }

} catch (Exception $e) {
    if (isset($db)) {
        $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }
    echo "❌ Error during demo data generation: " . $e->getMessage() . "\n";
    throw $e;
}
