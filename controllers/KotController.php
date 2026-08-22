<?php
require_once __DIR__ . '/../models/Kot.php';
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';

class KotController extends Controller {
    public function __construct() {
        // Allow authenticated staff with roles 'kot' or 'admin'
        if (strpos($_SERVER['REQUEST_URI'], '/kot/print/') !== 0) {
            $this->requireAuth('kot');
        }
    }

    public function index() {
        $settingsModel = new Setting();
        $settings = $settingsModel->getSettings();
        $this->render('kot_display', ['settings' => $settings]);
    }

    // Ajax endpoint to fetch current active KOTs list
    public function itemsList() {
        $kotModel = new Kot();
        $activeKots = $kotModel->getActiveKots();
        $this->json(['kots' => $activeKots]);
    }

    // Ajax endpoint to fetch completed/dispatched KOTs list
    public function completedList() {
        $limit = $_GET['limit'] ?? 20;
        $date = $_GET['date'] ?? date('Y-m-d');
        $kotModel = new Kot();
        $completedKots = $kotModel->getCompletedKots($limit, $date);
        $this->json(['kots' => $completedKots]);
    }

    public function markItemReady($params) {
        $itemId = (int)($params['id'] ?? 0);
        $kotModel = new Kot();
        $success = $kotModel->markItemReady($itemId);
        $this->json(['success' => $success]);
    }

    public function markKotReady($params) {
        $kotId = (int)($params['id'] ?? 0);
        $kotModel = new Kot();
        $success = $kotModel->markKotReady($kotId);
        $this->json(['success' => $success]);
    }

    // Renders the print view of KOT
    public function printKot($params) {
        $kotId = (int)($params['id'] ?? 0);
        $kotModel = new Kot();
        $kot = $kotModel->getKotDetails($kotId);

        if (!$kot) {
            echo "<h1>KOT Not Found</h1>";
            exit;
        }

        $settingsModel = new Setting();
        $settings = $settingsModel->getSettings();

        $this->render('print_kot', [
            'kot' => $kot,
            'settings' => $settings
        ]);
    }

    public function deleteItem($params) {
        $itemId = (int)($params['id'] ?? 0);
        file_put_contents(__DIR__ . '/../debug_log.txt', date('Y-m-d H:i:s') . " - KotController::deleteItem called for ID: $itemId\n", FILE_APPEND);
        error_log("KotController::deleteItem called for ID: $itemId");
        $kotModel = new Kot();
        $success = $kotModel->deleteKotItem($itemId);
        file_put_contents(__DIR__ . '/../debug_log.txt', date('Y-m-d H:i:s') . " - KotController::deleteItem success: " . var_export($success, true) . "\n", FILE_APPEND);
        error_log("KotController::deleteItem success: " . var_export($success, true));
        $this->json(['success' => $success]);
    }

    public function deleteKot($params) {
        $kotId = (int)($params['id'] ?? 0);
        file_put_contents(__DIR__ . '/../debug_log.txt', date('Y-m-d H:i:s') . " - KotController::deleteKot called for ID: $kotId\n", FILE_APPEND);
        error_log("KotController::deleteKot called for ID: $kotId");
        $kotModel = new Kot();
        $success = $kotModel->deleteKot($kotId);
        file_put_contents(__DIR__ . '/../debug_log.txt', date('Y-m-d H:i:s') . " - KotController::deleteKot success: " . var_export($success, true) . "\n", FILE_APPEND);
        error_log("KotController::deleteKot success: " . var_export($success, true));
        $this->json(['success' => $success]);
    }

    public function productsListJson() {
        $productModel = new Product();
        $products = $productModel->getAll();
        
        $categoryModel = new Category();
        $categories = $categoryModel->getAll();

        $this->json(['products' => $products, 'categories' => $categories]);
    }

    public function toggleProductAvailability($params) {
        $productId = (int)($params['id'] ?? 0);
        $data = $this->getJsonInput();
        $isAvailable = isset($data['is_available']) ? (int)$data['is_available'] : 0;

        $productModel = new Product();
        $success = $productModel->updateAvailability($productId, $isAvailable);

        $this->json(['success' => $success]);
    }
}
