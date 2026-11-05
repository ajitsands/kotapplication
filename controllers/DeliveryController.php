<?php
require_once __DIR__ . '/../models/Delivery.php';
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../models/User.php';

class DeliveryController extends Controller {

    private function getLoggedInDriverId() {
        if (!empty($_SESSION['user_id']) && in_array(($_SESSION['user_role'] ?? ''), ['delivery', 'admin', 'superadmin'])) {
            return (int)$_SESSION['user_id'];
        }
        return null;
    }

    public function index() {
        $driverId = $this->getLoggedInDriverId();
        if (!$driverId) {
            $this->redirect('/delivery/login');
            return;
        }

        $settingsModel = new Setting();
        $settings = $settingsModel->getSettings();

        $commRate = (float)($settings['delivery_commission_percent'] ?? 0);
        $commissionEnabled = ($commRate > 0);

        $this->render('delivery_app', [
            'settings' => $settings,
            'driver_id' => $driverId,
            'driver_name' => $_SESSION['user_name'] ?? $_SESSION['name'] ?? 'Driver',
            'commission_enabled' => $commissionEnabled,
            'commission_rate' => $commRate
        ]);
    }

    public function loginView() {
        $driverId = $this->getLoggedInDriverId();
        if ($driverId) {
            $this->redirect('/delivery');
            return;
        }

        $settingsModel = new Setting();
        $settings = $settingsModel->getSettings();
        $this->render('delivery_login', ['settings' => $settings]);
    }

    public function loginSubmit() {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        $userModel = new User();
        $user = $userModel->authenticate($username, $password);

        if ($user && $user !== 'deactivated') {
            // Check if user has role delivery, admin, or superadmin
            if (!in_array($user['role'], ['delivery', 'admin', 'superadmin'])) {
                $settingsModel = new Setting();
                $settings = $settingsModel->getSettings();
                $this->render('delivery_login', [
                    'error' => 'This account does not have delivery driver access permissions.',
                    'settings' => $settings
                ]);
                return;
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['name'] = $user['name'];

            $this->redirect('/delivery');
            return;
        }

        $settingsModel = new Setting();
        $settings = $settingsModel->getSettings();
        $this->render('delivery_login', [
            'error' => ($user === 'deactivated') ? 'Account is deactivated. Contact admin.' : 'Invalid driver username or password.',
            'settings' => $settings
        ]);
    }

    public function logout() {
        if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['delivery', 'admin', 'superadmin'])) {
            unset($_SESSION['user_id']);
            unset($_SESSION['username']);
            unset($_SESSION['user_role']);
            unset($_SESSION['role']);
            unset($_SESSION['user_name']);
            unset($_SESSION['name']);
        }
        $this->redirect('/delivery/login');
    }

    public function dashboardData() {
        $driverId = $this->getLoggedInDriverId();
        if (!$driverId) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $deliveryModel = new Delivery();
        $stats = $deliveryModel->getDriverStats($driverId);
        $activeDelivery = $deliveryModel->getActiveDeliveryForDriver($driverId);
        
        // If driver has an active delivery, lock available list (or return empty list)
        $availableDeliveries = [];
        if (!$activeDelivery) {
            $availableDeliveries = $deliveryModel->getAvailableDeliveries();
        }

        $settingsModel = new Setting();
        $settings = $settingsModel->getSettings();
        $commRate = (float)($settings['delivery_commission_percent'] ?? 0);
        $commissionEnabled = ($commRate > 0);

        $this->json([
            'success' => true,
            'driver' => [
                'id' => $driverId,
                'name' => $_SESSION['user_name'] ?? $_SESSION['name'] ?? 'Driver',
                'username' => $_SESSION['username'] ?? ''
            ],
            'commission_enabled' => $commissionEnabled,
            'commission_rate' => $commRate,
            'stats' => $stats,
            'active_delivery' => $activeDelivery,
            'available_deliveries' => $availableDeliveries
        ]);
    }

    public function claimDelivery($params) {
        $driverId = $this->getLoggedInDriverId();
        if (!$driverId) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $deliveryId = (int)($params['id'] ?? $_POST['delivery_id'] ?? 0);
        if ($deliveryId <= 0) {
            $this->json(['success' => false, 'error' => 'Invalid delivery ID.']);
            return;
        }

        $deliveryModel = new Delivery();
        $res = $deliveryModel->claimDelivery($deliveryId, $driverId);
        $this->json($res);
    }

    public function markDelivered($params) {
        $driverId = $this->getLoggedInDriverId();
        if (!$driverId) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $deliveryId = (int)($params['id'] ?? $_POST['delivery_id'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');

        if ($deliveryId <= 0) {
            $this->json(['success' => false, 'error' => 'Invalid delivery ID.']);
            return;
        }

        $deliveryModel = new Delivery();
        $res = $deliveryModel->markDelivered($deliveryId, $driverId, $notes);
        $this->json($res);
    }

    public function historyData() {
        $driverId = $this->getLoggedInDriverId();
        if (!$driverId) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $today = date('Y-m-d');
        $startDate = !empty($_GET['start']) ? trim($_GET['start']) : $today;
        $endDate = !empty($_GET['end']) ? trim($_GET['end']) : $today;
        $limit = max(1, min(200, (int)($_GET['limit'] ?? 100)));

        $deliveryModel = new Delivery();
        $history = $deliveryModel->getDriverDeliveriesHistory($driverId, $startDate, $endDate, $limit);

        $this->json([
            'success' => true,
            'deliveries' => $history,
            'start' => $startDate,
            'end' => $endDate
        ]);
    }

    public function ledgerData() {
        $driverId = $this->getLoggedInDriverId();
        if (!$driverId) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $today = date('Y-m-d');
        $startDate = !empty($_GET['start']) ? trim($_GET['start']) : $today;
        $endDate = !empty($_GET['end']) ? trim($_GET['end']) : $today;

        $deliveryModel = new Delivery();
        $ledger = $deliveryModel->getDriverLedger($driverId, $startDate, $endDate);
        $stats = $deliveryModel->getDriverStats($driverId);

        $this->json([
            'success' => true,
            'stats' => $stats,
            'ledger' => $ledger,
            'start' => $startDate,
            'end' => $endDate
        ]);
    }

    public function customerConfirmView($params) {
        $token = trim($params['token'] ?? '');
        if (empty($token)) {
            echo "<h1>Invalid Delivery Link</h1>";
            exit;
        }

        require_once __DIR__ . '/../models/Delivery.php';
        $deliveryModel = new Delivery();
        $delivery = $deliveryModel->getDeliveryByConfirmToken($token);

        if (!$delivery) {
            echo "<div style='font-family:sans-serif; text-align:center; padding:50px 20px;'>
                    <h2>⚠️ Order Not Found</h2>
                    <p>This confirmation link is invalid or has expired.</p>
                  </div>";
            exit;
        }

        $settingsModel = new Setting();
        $settings = $settingsModel->getSettings();

        $this->render('customer_delivery_confirm', [
            'delivery' => $delivery,
            'settings' => $settings
        ]);
    }

    public function customerConfirmSubmit($params) {
        $token = trim($params['token'] ?? '');
        if (empty($token)) {
            $this->json(['success' => false, 'error' => 'Missing confirmation token.'], 400);
            return;
        }

        $googleMapsLink = trim($_POST['google_maps_link'] ?? '');
        $address = trim($_POST['customer_address'] ?? '');
        $notes = trim($_POST['delivery_notes'] ?? '');

        require_once __DIR__ . '/../models/Delivery.php';
        $deliveryModel = new Delivery();
        $res = $deliveryModel->customerConfirmLocation($token, $googleMapsLink, $address, $notes);
        $this->json($res);
    }
}

