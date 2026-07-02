<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Order.php';

class HomeController extends Controller {
    public function index() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
            $this->redirect('/login');
        }

        $role = $_SESSION['user_role'];
        switch ($role) {
            case 'admin':
                $this->redirect('/admin');
                break;
            case 'kot':
                $this->redirect('/kot');
                break;
            case 'counter':
                $this->redirect('/counter');
                break;
            case 'waiter':
                // Redirect to waiter app (served out of public/waiter or React route)
                $this->redirect('/waiter-app/dist/index.html');
                break;
            default:
                $this->redirect('/login');
        }
    }

    public function loginView() {
        if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
            $settingsModel = new Setting();
            $settings = $settingsModel->getSettings();
            $expiryDate = $settings['software_expiry_date'] ?? null;
            $isExpired = $expiryDate && date('Y-m-d') > $expiryDate;
            $isSuperAdmin = isset($_SESSION['username']) && $_SESSION['username'] === 'superadmin';

            if (!$isExpired || $isSuperAdmin) {
                $validRoles = ['admin', 'waiter', 'kot', 'counter'];
                if (in_array($_SESSION['user_role'], $validRoles)) {
                    $this->redirect('/');
                }
            } else {
                // Clear session so user can log in as superadmin
                session_destroy();
                $_SESSION = [];
            }
        }
        $settingsModel = new Setting();
        $settings = $settingsModel->getSettings();
        $this->render('login', ['settings' => $settings]);
    }

    public function loginSubmit() {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $userModel = new User();
        $user = $userModel->authenticate($username, $password);

        if ($user === 'deactivated') {
            $settingsModel = new Setting();
            $settings = $settingsModel->getSettings();
            $this->render('login', [
                'error' => 'Account is deactivated. Please contact the administrator.',
                'settings' => $settings
            ]);
        } elseif ($user) {
            // Check if software has expired and this is not the superadmin user
            $settingsModel = new Setting();
            $settings = $settingsModel->getSettings();
            $expiryDate = $settings['software_expiry_date'] ?? null;
            if ($expiryDate && date('Y-m-d') > $expiryDate && $user['username'] !== 'superadmin') {
                $this->render('login', [
                    'error' => 'License expired. Please contact your vendor.',
                    'settings' => $settings
                ]);
                return;
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            
            // Clean waiter prefix from user name
            $name = $user['name'];
            $name = preg_replace('/^waiter\s+/i', '', $name);
            $_SESSION['user_name'] = $name;

            $this->redirect('/');
        } else {
            $settingsModel = new Setting();
            $settings = $settingsModel->getSettings();
            $this->render('login', [
                'error' => 'Invalid username or password',
                'settings' => $settings
            ]);
        }
    }

    public function logout() {
        session_destroy();
        $this->redirect('/login');
    }

    public function customerView($params) {
        $tableNumber = (int)($params['table'] ?? 0);
        if ($tableNumber <= 0 || $tableNumber > 20) {
            echo "<h1>Invalid Table</h1>";
            exit;
        }

        $settingsModel = new Setting();
        $settings = $settingsModel->getSettings();

        $categoryModel = new Category();
        $categories = $categoryModel->getAll();

        $productModel = new Product();
        $products = $productModel->getAll();

        // Organize products by category
        $productsByCategory = [];
        foreach ($products as $product) {
            $productsByCategory[$product['category_id']][] = $product;
        }

        $this->render('customer_menu', [
            'tableNumber' => $tableNumber,
            'settings' => $settings,
            'categories' => $categories,
            'productsByCategory' => $productsByCategory,
            'products' => $products
        ]);
    }

    public function changePassword() {
        if (!isset($_SESSION['user_id'])) {
            $this->json(['success' => false, 'error' => 'Unauthorized'], 401);
            return;
        }

        $userId = $_SESSION['user_id'];
        $data = $this->getJsonInput();
        
        $currentPassword = $data['current_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword)) {
            $this->json(['success' => false, 'error' => 'All fields are required.'], 400);
            return;
        }

        $userModel = new User();
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $hash = $stmt->fetchColumn();

        if ($hash && password_verify($currentPassword, $hash)) {
            $userModel->resetPassword($userId, $newPassword);
            $this->json(['success' => true]);
        } else {
            $this->json(['success' => false, 'error' => 'Current password is incorrect.'], 400);
        }
    }
}
