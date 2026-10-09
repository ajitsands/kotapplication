<?php
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/User.php';

class AdminController extends Controller {
    public function __construct() {
        $this->requireAuth('admin');
    }

    public function index() {
        $settingsModel = new Setting();
        $settings = $settingsModel->getSettings();

        $categoryModel = new Category();
        $categories = $categoryModel->getAll();

        $productModel = new Product();
        $products = $productModel->getAll();

        $orderModel = new Order();
        $tables = $orderModel->getTablesState();

        $userModel = new User();
        $users = $userModel->getAll();
        
        $platformModel = new OnlinePlatform();
        $platforms = $platformModel->getAll();

        $this->render('admin', [
            'settings' => $settings,
            'categories' => $categories,
            'products' => $products,
            'tables' => $tables,
            'users' => $users,
            'platforms' => $platforms
        ]);
    }

    public function saveSettings() {
        $settingsModel = new Setting();
        $currentSettings = $settingsModel->getSettings();
        
        $logoPath = $currentSettings['logo_path'];
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $fileName = 'logo_' . time() . '.' . $ext;
            $uploadFile = 'uploads/' . $fileName;

            if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadFile)) {
                $logoPath = 'uploads/' . $fileName;
            }
        }

        $settingsModel->updateSettings($_POST, $logoPath);
        $this->redirect('/admin');
    }

    public function testPrinter() {
        require_once __DIR__ . '/../services/PrinterService.php';
        $ip = $_POST['printer_ip'] ?? $_GET['printer_ip'] ?? null;
        $port = $_POST['printer_port'] ?? $_GET['printer_port'] ?? null;

        $result = PrinterService::testPrint($ip, $port);
        $this->json($result);
    }

    public function saveCategory() {
        $id = $_POST['id'] ?? null;
        $name = $_POST['name'] ?? '';
        $imageUrl = null;

        // Handle cropped category banner upload
        $croppedImage = $_POST['cropped_image_category'] ?? '';
        if (!empty($croppedImage) && strpos($croppedImage, 'data:image/') === 0) {
            $parts = explode(',', $croppedImage);
            if (count($parts) === 2) {
                $decodedData = base64_decode($parts[1]);
                if ($decodedData !== false) {
                    $fileName = 'cat_' . time() . '.jpg';
                    $uploadFile = 'uploads/' . $fileName;
                    if (file_put_contents($uploadFile, $decodedData)) {
                        $imageUrl = 'uploads/' . $fileName;
                    }
                }
            }
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = 'cat_' . time() . '.' . $ext;
            $uploadFile = 'uploads/' . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                $imageUrl = 'uploads/' . $fileName;
            }
        }

        $categoryModel = new Category();
        if (!empty($id)) {
            $categoryModel->update((int)$id, $name, $imageUrl);
        } else {
            $categoryModel->add($name, $imageUrl);
        }
        $this->redirect('/admin');
    }

    public function deleteCategory($params) {
        $id = (int)($params['id'] ?? 0);
        if ($id > 0) {
            $categoryModel = new Category();
            $categoryModel->delete($id);
        }
        $this->redirect('/admin');
    }

    public function saveProduct() {
        $productModel = new Product();
        $data = [
            'id' => $_POST['id'] ?? null,
            'category_id' => $_POST['category_id'] ?? 0,
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'price' => $_POST['price'] ?? 0.0,
            'is_available' => isset($_POST['is_available']) ? 1 : 0,
            'is_counter_item' => isset($_POST['is_counter_item']) ? 1 : 0
        ];

        // Handle cropped image upload
        $croppedImage = $_POST['cropped_image'] ?? '';
        if (!empty($croppedImage) && strpos($croppedImage, 'data:image/') === 0) {
            $parts = explode(',', $croppedImage);
            if (count($parts) === 2) {
                $decodedData = base64_decode($parts[1]);
                if ($decodedData !== false) {
                    $fileName = 'prod_' . time() . '.jpg';
                    $uploadFile = 'uploads/' . $fileName;
                    if (file_put_contents($uploadFile, $decodedData)) {
                        $data['image_url'] = 'uploads/' . $fileName;
                    }
                }
            }
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = 'prod_' . time() . '.' . $ext;
            $uploadFile = 'uploads/' . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                $data['image_url'] = 'uploads/' . $fileName;
            }
        }

        $productModel->save($data);
        $this->redirect('/admin');
    }

    public function deleteProduct($params) {
        $id = (int)($params['id'] ?? 0);
        if ($id > 0) {
            $productModel = new Product();
            $productModel->delete($id);
        }
        $this->redirect('/admin');
    }

    public function addTable() {
        $tableNumber = (int)($_POST['table_number'] ?? 0);
        if ($tableNumber > 0) {
            $orderModel = new Order();
            try {
                $orderModel->addTable($tableNumber);
            } catch (Exception $e) {
                // Table already exists or error
            }
        }
        $this->redirect('/admin');
    }

    public function deleteTable($params) {
        $tableNumber = (int)($params['id'] ?? 0);
        if ($tableNumber > 0) {
            $orderModel = new Order();
            $orderModel->deleteTable($tableNumber);
        }
        $this->redirect('/admin');
    }

    public function addUser() {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $name = $_POST['name'] ?? '';
        $role = $_POST['role'] ?? 'waiter';

        if ($username !== '' && $password !== '' && $name !== '') {
            $userModel = new User();
            $userModel->add($username, $password, $name, $role);
        }
        $this->redirect('/admin');
    }

    public function deleteUser($params) {
        $id = (int)($params['id'] ?? 0);
        if ($id > 0) {
            $userModel = new User();
            $userModel->delete($id);
        }
        $this->redirect('/admin');
    }

    public function toggleUserStatus($params) {
        $id = (int)($params['id'] ?? 0);
        $status = (int)($_POST['is_active'] ?? 1);
        if ($id > 0) {
            $userModel = new User();
            $userModel->toggleStatus($id, $status);
        }
        $this->redirect('/admin');
    }

    public function resetUserPassword($params) {
        $id = (int)($params['id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';
        if ($id > 0 && $newPassword !== '') {
            $userModel = new User();
            $userModel->resetPassword($id, $newPassword);
            $this->json(['success' => true]);
        } else {
            $this->json(['success' => false, 'error' => 'Invalid parameters'], 400);
        }
    }

    public function productsListJson() {
        $categoryId = $_GET['category_id'] ?? 'all';
        $productModel = new Product();
        if ($categoryId === 'all' || $categoryId === '') {
            $products = $productModel->getAll();
        } else {
            $products = $productModel->getByCategoryForAdmin((int)$categoryId);
        }
        $this->json(['products' => $products]);
    }

    public function taxReportJson() {
        $startDate = $_GET['start_date'] ?? date('Y-m-d');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        require_once __DIR__ . '/../models/Bill.php';
        $billModel = new Bill();
        $report = $billModel->getTaxReport($startDate, $endDate);

        $this->json(['success' => true, 'report' => $report]);
    }

    public function analyticsJson() {
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        require_once __DIR__ . '/../models/Bill.php';
        $billModel = new Bill();
        $report = $billModel->getProductSalesAnalytics($startDate, $endDate);

        $this->json(['success' => true, 'report' => $report]);
    }

    public function waiterPerformanceJson() {
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        require_once __DIR__ . '/../models/Bill.php';
        $billModel = new Bill();
        $report = $billModel->getWaiterPerformance($startDate, $endDate);

        $this->json(['success' => true, 'report' => $report]);
    }

    public function savePlatform() {
        $name = $_POST['name'] ?? '';
        if (!empty($name)) {
            $platformModel = new OnlinePlatform();
            $platformModel->add($name);
        }
        $this->redirect('/admin#platforms');
    }

    public function deletePlatform($params) {
        $id = (int)($params['id'] ?? 0);
        if ($id > 0) {
            $platformModel = new OnlinePlatform();
            $platformModel->delete($id);
        }
        $this->redirect('/admin#platforms');
    }

    public function togglePlatformStatus($params) {
        $id = (int)($params['id'] ?? 0);
        $newStatus = null;
        if ($id > 0) {
            $platformModel = new OnlinePlatform();
            $platformModel->toggleStatus($id);
            // Fetch updated to return new status
            $p = $platformModel->getAll();
            foreach($p as $pl) { if($pl['id'] == $id) $newStatus = $pl['status']; }
        }
        
        $wantsJson = isset($_GET['ajax']) && $_GET['ajax'] == 1;
        if ($wantsJson) {
            $this->json(['success' => true, 'new_status' => $newStatus]);
            return;
        }
        $this->redirect('/admin#platforms');
    }

    /**
     * Clear all transaction data (Orders, KOTs, Bills, Counter Sessions, Stock Logs)
     * Preserves Users, Categories, Products, Dining Tables, Settings, and Suppliers
     * Superadmin Only
     */
    public function clearTransactions() {
        if (($_SESSION['username'] ?? '') !== 'superadmin') {
            $this->json(['success' => false, 'error' => 'Unauthorized. Superadmin privilege required.'], 403);
            return;
        }

        try {
            $db = Database::getInstance()->getConnection();
            $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
            
            // Wipe transaction-specific tables
            $db->exec("TRUNCATE TABLE `kot_items`;");
            $db->exec("TRUNCATE TABLE `kots`;");
            $db->exec("TRUNCATE TABLE `bills`;");
            $db->exec("TRUNCATE TABLE `orders`;");
            $db->exec("TRUNCATE TABLE `counter_sessions`;");
            $db->exec("TRUNCATE TABLE `inventory_transactions`;");

            // Reset AUTO_INCREMENT on transaction tables
            $db->exec("ALTER TABLE `orders` AUTO_INCREMENT = 1;");
            $db->exec("ALTER TABLE `kots` AUTO_INCREMENT = 1;");
            $db->exec("ALTER TABLE `kot_items` AUTO_INCREMENT = 1;");
            $db->exec("ALTER TABLE `bills` AUTO_INCREMENT = 1;");
            $db->exec("ALTER TABLE `counter_sessions` AUTO_INCREMENT = 1;");
            $db->exec("ALTER TABLE `inventory_transactions` AUTO_INCREMENT = 1;");

            $db->exec("SET FOREIGN_KEY_CHECKS = 1;");

            $this->json([
                'success' => true,
                'message' => 'All transaction data has been successfully cleared! All tables are now clean and ready for your client.'
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate Master Demo Data for full system testing
     * Superadmin Only
     */
    public function generateDemoData() {
        if (($_SESSION['username'] ?? '') !== 'superadmin') {
            $this->json(['success' => false, 'error' => 'Unauthorized. Superadmin privilege required.'], 403);
            return;
        }

        ob_start();
        require_once __DIR__ . '/../seed_demo_data.php';
        $output = ob_get_clean();

        $this->json([
            'success' => true,
            'message' => 'Complete master demo data (products, categories, inventory, recipes, sample orders & KOTs) has been generated successfully!',
            'log' => $output
        ]);
    }
}
