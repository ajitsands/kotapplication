<?php
require_once __DIR__ . '/../models/Kot.php';
require_once __DIR__ . '/../models/Setting.php';

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
        $kotModel = new Kot();
        $success = $kotModel->deleteKotItem($itemId);
        $this->json(['success' => $success]);
    }

    public function deleteKot($params) {
        $kotId = (int)($params['id'] ?? 0);
        $kotModel = new Kot();
        $success = $kotModel->deleteKot($kotId);
        $this->json(['success' => $success]);
    }
}
