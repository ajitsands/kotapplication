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

        $this->render('admin', [
            'settings' => $settings,
            'categories' => $categories,
            'products' => $products,
            'tables' => $tables,
            'users' => $users
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

    public function addCategory() {
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
        $categoryModel->add($name, $imageUrl);
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
            'is_available' => isset($_POST['is_available']) ? 1 : 0
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
}
