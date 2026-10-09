<?php
/**
 * Master Demo Data Generator & Seed Script
 * For Gourmet Express / SaNDS KOT POS & Billing System
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

    echo (php_sapi_name() === 'cli') ? "--- Starting Demo Data Generation ---\n" : "<div style='font-family: monospace; background: #0b0f19; color: #10b981; padding: 20px; border-radius: 12px;'><h3>🌱 Generating Master Demo Data...</h3><pre>";

    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // 1. Ensure Schema and Missing Columns
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
            `restaurant_name` VARCHAR(100) NOT NULL DEFAULT 'Gourmet Express',
            `currency_code` VARCHAR(10) NOT NULL DEFAULT 'BHD',
            `time_zone` VARCHAR(50) NOT NULL DEFAULT 'Asia/Bahrain',
            `custom_units` VARCHAR(255) DEFAULT 'Nos, Portion, Box, Packet, Gram, KG, Litre, ML, Can, Glass',
            `tax_type` ENUM('VAT', 'GST') NOT NULL DEFAULT 'VAT',
            `vat_percent` DECIMAL(5,2) NOT NULL DEFAULT 10.00,
            `cgst_percent` DECIMAL(5,2) NOT NULL DEFAULT 2.50,
            `sgst_percent` DECIMAL(5,2) NOT NULL DEFAULT 2.50,
            `printer_size` INT NOT NULL DEFAULT 80,
            `logo_path` VARCHAR(255) DEFAULT NULL,
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
    echo "✓ Database tables verified.\n";

    // 2. Clear old transactions to ensure a fresh, consistent seed
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

    echo "✓ Previous transaction and product tables cleared.\n";

    // 3. Settings Setup
    $db->exec("INSERT INTO `settings` (`id`, `restaurant_name`, `currency_code`, `time_zone`, `custom_units`, `tax_type`, `vat_percent`, `cgst_percent`, `sgst_percent`, `printer_size`, `logo_path`, `software_expiry_date`) 
               VALUES (1, 'Gourmet Express - Restaurant & Cafe', 'BHD', 'Asia/Bahrain', 'Nos, Portion, Box, Packet, Gram, KG, Litre, ML, Can, Glass', 'VAT', 10.00, 2.50, 2.50, 80, NULL, '2027-12-31')
               ON DUPLICATE KEY UPDATE 
               `restaurant_name` = 'Gourmet Express - Restaurant & Cafe',
               `currency_code` = 'BHD',
               `tax_type` = 'VAT',
               `vat_percent` = 10.00;");
    echo "✓ Settings initialized (Gourmet Express, BHD, 10% VAT).\n";

    // 4. Default Users
    $defaultUsers = [
        [1, 'admin', '$2y$10$eKJ6GL3MMiONVOGB.YY92.EUbDW1xJn72.K7OYbxwN6oczfwpgk2e', 'System Administrator', 'admin'],
        [2, 'waiter1', '$2y$10$Zm8osWJRVu6LWa9MH/wZ4.tZxFD.2yivpg0QRGSr2azhal5DgXd5C', 'Waiter John', 'waiter'],
        [3, 'waiter2', '$2y$10$Zm8osWJRVu6LWa9MH/wZ4.tZxFD.2yivpg0QRGSr2azhal5DgXd5C', 'Waiter Sarah', 'waiter'],
        [4, 'chef1', '$2y$10$taBABla6.ATOxuS7pY10uu8z4T3d7GNa/bVKiW8ZuoSaXKVWqj0zi', 'Head Chef Mario', 'kot'],
        [5, 'counter1', '$2y$10$rC2bzZxCggfJT0FUHUAKnOdFdHJ3eVNMSdWfj8lm9muu9abOZPtK.', 'Cashier Sam', 'counter'],
        [6, 'superadmin', '$2y$10$GIlyTrYJ3QAvz5vzgYjh2.QZV5HJYep7yvez8ay5dgyYs5HXoa3Nq', 'SaNDS Lab Super Admin', 'admin'],
        [7, 'waiter3', '$2y$10$Zm8osWJRVu6LWa9MH/wZ4.tZxFD.2yivpg0QRGSr2azhal5DgXd5C', 'Waiter Alex', 'waiter'],
        [8, 'chef2', '$2y$10$taBABla6.ATOxuS7pY10uu8z4T3d7GNa/bVKiW8ZuoSaXKVWqj0zi', 'Chef Luigi', 'kot']
    ];

    $stmtUser = $db->prepare("INSERT INTO `users` (`id`, `username`, `password`, `name`, `role`, `is_active`) VALUES (?, ?, ?, ?, ?, 1)
                              ON DUPLICATE KEY UPDATE `password` = VALUES(`password`), `name` = VALUES(`name`), `role` = VALUES(`role`), `is_active` = 1");
    foreach ($defaultUsers as $u) {
        $stmtUser->execute($u);
    }
    echo "✓ Users seeded (admin, superadmin, waiter1, waiter2, waiter3, chef1, chef2, counter1).\n";

    // 5. Dining Tables (1 to 20)
    for ($t = 1; $t <= 20; $t++) {
        $db->exec("INSERT IGNORE INTO `dining_tables` (`table_number`) VALUES ($t)");
    }
    echo "✓ 20 Dining Tables initialized.\n";

    // 6. Online Platforms
    $platforms = ['Talabat', 'Jahez', 'Hungerstation', 'Deliveroo', 'Ahlan', 'UberEats'];
    $stmtPlat = $db->prepare("INSERT IGNORE INTO `online_platforms` (`id`, `name`, `status`) VALUES (?, ?, 'active')");
    foreach ($platforms as $idx => $pname) {
        $stmtPlat->execute([$idx + 1, $pname]);
    }
    echo "✓ Online Platforms seeded.\n";

    // 7. Suppliers
    $suppliers = [
        ['Bahrain Fresh Poultry & Meats', 'Ahmed Al-Khalifa', '+973 33112233', 'poultry@bahrainfresh.bh', 'Manama Central Market'],
        ['Gulf Dairy & Spices Traders', 'Mohammed Hassan', '+973 39887766', 'orders@gulfspices.com', 'Salmabad Industrial Area'],
        ['Golden Harvest Produce & Bakery', 'Suresh Kumar', '+973 36554433', 'sales@goldenharvest.bh', 'Tubli Commercial Zone']
    ];
    $stmtSupp = $db->prepare("INSERT INTO `suppliers` (`name`, `contact_person`, `phone`, `email`, `address`) VALUES (?, ?, ?, ?, ?)");
    foreach ($suppliers as $s) {
        $stmtSupp->execute($s);
    }
    echo "✓ Suppliers seeded.\n";

    // 8. Inventory Items (Raw Materials)
    $inventoryItems = [
        ['Fresh Chicken Breast', 'KG', 85.000, 15.000, 1.800, 2.500],
        ['Prime Mutton Cuts', 'KG', 45.000, 10.000, 3.600, 4.800],
        ['Basmati Royal Rice', 'KG', 150.000, 30.000, 0.750, 1.200],
        ['Mozzarella & Cheddar Blend', 'KG', 35.000, 8.000, 2.400, 3.500],
        ['Cooking Oil & Pure Ghee', 'Litre', 90.000, 20.000, 0.950, 1.400],
        ['Specialty Espresso Beans', 'KG', 25.000, 5.000, 6.500, 9.000],
        ['Fresh Full Cream Milk', 'Litre', 60.000, 15.000, 0.450, 0.700],
        ['Pizza Dough & Flour', 'KG', 70.000, 20.000, 0.400, 0.800],
        ['French Fries (Frozen)', 'KG', 60.000, 15.000, 0.800, 1.400],
        ['Assorted Spices & Herbs', 'KG', 20.000, 4.000, 4.200, 6.000]
    ];
    $stmtInv = $db->prepare("INSERT INTO `inventory_items` (`name`, `unit`, `current_stock`, `min_stock_level`, `buying_price_per_unit`, `selling_price`) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($inventoryItems as $item) {
        $stmtInv->execute($item);
    }
    echo "✓ Raw Material Inventory items seeded.\n";

    // 9. Categories & Products
    $categoriesData = [
        ['name' => '🍲 Starters & Appetizers', 'products' => [
            ['Crispy Calamari Rings', 'Golden fried calamari served with tartare sauce and lemon wedge', 2.200, 0],
            ['Garlic Parmesan Chicken Wings', 'Crispy tossed wings coated with garlic butter and aged parmesan', 2.500, 0],
            ['Truffle Parmesan Fries', 'Hand-cut potato fries drizzled with aromatic white truffle oil', 1.800, 0],
            ['Dynamite Shrimp Cups', 'Tempura fried shrimp coated in spicy creamy dynamite glaze', 2.800, 0],
            ['Hummus with Warm Pita', 'Creamy chickpea puree with tahini, extra virgin olive oil and warm pita', 1.500, 0]
        ]],
        ['name' => '🍔 Burgers & Sandwiches', 'products' => [
            ['Wagyu Classic Cheeseburger', 'Juicy 180g Wagyu beef patty, cheddar, lettuce, tomato and secret sauce', 3.500, 0],
            ['Crispy Buttermilk Chicken Burger', 'Fried chicken fillet with spicy coleslaw, pickles and brioche bun', 2.800, 0],
            ['Truffle Swiss Mushroom Burger', 'Grilled beef patty topped with sautéed portobello mushrooms and swiss cheese', 3.200, 0],
            ['Smoky BBQ Beef Bacon Burger', 'Angus patty with beef bacon, smoked cheddar and hickory BBQ sauce', 3.600, 0]
        ]],
        ['name' => '🍕 Artisan Pizzas', 'products' => [
            ['Classic Margherita Pizza', 'San Marzano tomato base, fresh buffalo mozzarella, fresh basil and olive oil', 3.200, 0],
            ['Pepperoni Supreme Pizza', 'Double beef pepperoni, rich tomato sauce, mozzarella and oregano', 3.800, 0],
            ['Wild Truffle & Mushroom Pizza', 'Creamy white base, wild mushrooms, mozzarella, truffle glaze', 4.200, 0],
            ['BBQ Smoked Chicken Pizza', 'Grilled chicken breast, red onions, sweet corn, cilantro and smoky BBQ', 3.900, 0]
        ]],
        ['name' => '🍛 Biryani & Main Course', 'products' => [
            ['Royal Chicken Dum Biryani', 'Slow-cooked aromatic basmati rice with tender chicken, fried onions & saffron', 3.600, 0],
            ['Hyderabadi Mutton Biryani', 'Fragrant basmati rice layered with spiced baby mutton and fresh mint', 4.500, 0],
            ['Butter Chicken with Garlic Naan', 'Tender chicken tikka simmered in creamy makhani gravy with freshly baked naan', 3.800, 0],
            ['Grilled Atlantic Salmon Fillet', 'Herb-crusted salmon with creamy mashed potato and lemon butter caper sauce', 5.200, 0],
            ['Penne Creamy Alfredo Chicken', 'Al dente penne pasta tossed in rich parmesan cream sauce with grilled chicken', 3.400, 0]
        ]],
        ['name' => '☕ Hot & Cold Beverages', 'products' => [
            ['Signature Spanish Latte', 'Rich espresso layered with sweetened condensed milk and silky foam', 1.800, 0],
            ['Iced Caramel Frappuccino', 'Blended iced coffee with creamy caramel swirl and whipped cream', 2.200, 0],
            ['Passionfruit Mint Mojito', 'Crushed lime, fresh mint leaves, passionfruit pulp and sparkling soda', 1.600, 0],
            ['Fresh Orange Juice', '100% pure freshly squeezed Valencia oranges (no added sugar)', 1.400, 0],
            ['Mineral Water 500ml', 'Chilled premium mineral drinking water bottle', 0.500, 1] // Counter item
        ]],
        ['name' => '🍰 Gourmet Desserts', 'products' => [
            ['Lotus Biscoff Cheesecake', 'Creamy baked cheesecake infused with Lotus spread on speculoos biscuit crust', 2.400, 0],
            ['Warm Molten Chocolate Lava', 'Decadent chocolate cake with warm gooey center, served with vanilla bean ice cream', 2.600, 0],
            ['Pistachio Saffron Milk Cake', 'Spongy tres leches cake soaked in rich pistachio saffron infused milk', 2.800, 0],
            ['Traditional Kunafa with Gelato', 'Crispy golden shredded phyllo dough layered with sweet cheese and rose syrup', 3.000, 0]
        ]]
    ];

    $productIds = [];
    $stmtCat = $db->prepare("INSERT INTO `categories` (`name`) VALUES (?)");
    $stmtProd = $db->prepare("INSERT INTO `products` (`category_id`, `name`, `description`, `price`, `is_available`, `is_counter_item`) VALUES (?, ?, ?, ?, 1, ?)");

    foreach ($categoriesData as $cat) {
        $stmtCat->execute([$cat['name']]);
        $catId = $db->lastInsertId();

        foreach ($cat['products'] as $prod) {
            $stmtProd->execute([$catId, $prod[0], $prod[1], $prod[2], $prod[3]]);
            $productIds[] = [
                'id' => $db->lastInsertId(),
                'name' => $prod[0],
                'price' => $prod[2]
            ];
        }
    }
    echo "✓ 6 Menu Categories and " . count($productIds) . " Gourmet Products seeded.\n";

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

    // 11. Generate Realistic Historical Paid Transactions (Last 7 Days)
    echo "🌱 Generating historical paid bills and KOTs for reports & dashboard...\n";

    $waiterIds = [2, 3, 7];
    $chefIds = [4, 8];
    $cashierId = 5;
    $paymentMethods = ['cash', 'card', 'qr_pay', 'card', 'cash'];

    $orderInsert = $db->prepare("INSERT INTO `orders` (`table_number`, `status`, `order_type`, `platform_id`, `platform_order_number`, `customer_name`, `customer_mobile`, `token_number`, `waiter_id`, `created_at`, `updated_at`) VALUES (?, 'closed', ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $kotInsert = $db->prepare("INSERT INTO `kots` (`order_id`, `waiter_id`, `kot_number`, `status`, `created_at`) VALUES (?, ?, ?, 'dispatched', ?)");
    $kotItemInsert = $db->prepare("INSERT INTO `kot_items` (`kot_id`, `product_id`, `quantity`, `status`, `notes`) VALUES (?, ?, ?, 'dispatched', ?)");
    $billInsert = $db->prepare("INSERT INTO `bills` (`order_id`, `subtotal`, `tax_amount`, `discount_percent`, `discount_amount`, `grand_total`, `payment_method`, `status`, `cashier_id`, `customer_id`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, ?, 'paid', ?, ?, ?)");

    $totalHistoricalOrders = 35;
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
            $platformOrderNo = 'ORD-' . strtoupper(substr(uniqid(), -6));
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

        // Add 2 to 5 random items
        $itemCount = rand(2, 5);
        $subtotal = 0.0;
        $selectedKeys = (array)array_rand($productIds, $itemCount);

        foreach ($selectedKeys as $k) {
            $p = $productIds[$k];
            $qty = rand(1, 3);
            $itemSubtotal = $p['price'] * $qty;
            $subtotal += $itemSubtotal;
            $kotItemInsert->execute([$kotId, $p['id'], $qty, (rand(0, 1) ? 'Less spicy' : null)]);
        }

        // Tax & Discount calculation
        $vatPercent = 10.00;
        $taxAmount = round($subtotal * ($vatPercent / 100), 3);
        $discountPercent = (rand(1, 5) === 1) ? 10.00 : 0.00;
        $discountAmount = round(($subtotal + $taxAmount) * ($discountPercent / 100), 3);
        $grandTotal = round(($subtotal + $taxAmount) - $discountAmount, 3);
        $pm = $paymentMethods[array_rand($paymentMethods)];

        $billInsert->execute([$orderId, $subtotal, $taxAmount, $discountPercent, $discountAmount, $grandTotal, $pm, $cashierId, $cId, $timeStr]);
    }
    echo "✓ $totalHistoricalOrders Paid orders & bills generated across the last 7 days.\n";

    // 12. Create 3 Active Live Orders for Testing
    echo "🌱 Creating active live orders on tables & delivery for instant testing...\n";

    // Active Order 1: Dine-in on Table 3 (Preparing state in Kitchen)
    $db->exec("INSERT INTO `orders` (`table_number`, `status`, `order_type`, `waiter_id`, `customer_name`, `created_at`) 
               VALUES (3, 'active', 'dine_in', 2, 'Family Table', NOW())");
    $actOrder1 = $db->lastInsertId();
    $db->exec("INSERT INTO `kots` (`order_id`, `waiter_id`, `kot_number`, `status`, `created_at`) 
               VALUES ($actOrder1, 2, 'KOT-" . date('Ymd') . "-LIVE01', 'preparing', NOW())");
    $actKot1 = $db->lastInsertId();
    $db->exec("INSERT INTO `kot_items` (`kot_id`, `product_id`, `quantity`, `status`, `notes`) VALUES 
               ($actKot1, {$productIds[5]['id']}, 2, 'preparing', 'Medium Well'),
               ($actKot1, {$productIds[2]['id']}, 1, 'preparing', 'Extra dip'),
               ($actKot1, {$productIds[18]['id']}, 2, 'ready', 'Less ice')");

    // Active Order 2: Dine-in on Table 7 (Ready state in Kitchen for Waiter Dispatch)
    $db->exec("INSERT INTO `orders` (`table_number`, `status`, `order_type`, `waiter_id`, `customer_name`, `created_at`) 
               VALUES (7, 'active', 'dine_in', 3, 'VIP Guests', NOW())");
    $actOrder2 = $db->lastInsertId();
    $db->exec("INSERT INTO `kots` (`order_id`, `waiter_id`, `kot_number`, `status`, `created_at`) 
               VALUES ($actOrder2, 3, 'KOT-" . date('Ymd') . "-LIVE02', 'ready', NOW())");
    $actKot2 = $db->lastInsertId();
    $db->exec("INSERT INTO `kot_items` (`kot_id`, `product_id`, `quantity`, `status`, `notes`) VALUES 
               ($actKot2, {$productIds[13]['id']}, 2, 'ready', 'Extra raita'),
               ($actKot2, {$productIds[14]['id']}, 1, 'ready', 'Spicy'),
               ($actKot2, {$productIds[24]['id']}, 2, 'ready', 'Serve warm')");

    // Active Order 3: Talabat Online Order (Pending kitchen pickup)
    $db->exec("INSERT INTO `orders` (`status`, `order_type`, `platform_id`, `platform_order_number`, `customer_name`, `customer_mobile`, `token_number`, `created_at`) 
               VALUES ('active', 'online', 1, 'TAL-98231', 'Rashid Al-Doseri', '+973 39998811', 'OL-05', NOW())");
    $actOrder3 = $db->lastInsertId();
    $db->exec("INSERT INTO `kots` (`order_id`, `waiter_id`, `kot_number`, `status`, `created_at`) 
               VALUES ($actOrder3, 2, 'KOT-" . date('Ymd') . "-TAL01', 'preparing', NOW())");
    $actKot3 = $db->lastInsertId();
    $db->exec("INSERT INTO `kot_items` (`kot_id`, `product_id`, `quantity`, `status`, `notes`) VALUES 
               ($actKot3, {$productIds[9]['id']}, 1, 'preparing', 'Extra cheese'),
               ($actKot3, {$productIds[20]['id']}, 2, 'preparing', 'Packed separately')");

    echo "✓ Active live test orders created for Table 3, Table 7, and Talabat Delivery.\n";

    // 13. Counter Sessions (Cash Drawer Reconciliation)
    // Yesterday's closed session
    $db->exec("INSERT INTO `counter_sessions` 
        (`cashier_id`, `opened_at`, `closed_at`, `cash_total`, `card_total`, `qr_total`, `system_total`, `collected_cash`, `collected_card`, `collected_qr`, `collected_total`, `cashier_notes`, `status`, `approved_by`) 
        VALUES 
        (5, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 18 HOUR), 48.500, 72.800, 15.400, 136.700, 48.500, 72.800, 15.400, 136.700, 'Shift closed with zero variance. All cash counted.', 'closed', 1)");

    // Today's open session
    $db->exec("INSERT INTO `counter_sessions` 
        (`cashier_id`, `opened_at`, `cash_total`, `card_total`, `qr_total`, `system_total`, `status`) 
        VALUES 
        (5, NOW(), 22.400, 38.600, 12.000, 73.000, 'open')");

    echo "✓ Cash drawer and counter shift sessions seeded.\n";

    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");

    echo "\n🎉 SUCCESS! Complete Demo Data successfully seeded.\n";
    echo "--------------------------------------------------------\n";
    echo "📋 Test Credentials:\n";
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
}
