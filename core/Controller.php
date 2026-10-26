<?php

class Controller {
    // Render a view file with optional data variables
    protected function render($view, $data = []) {
        // Extract variables to local scope
        extract($data);

        // Include layout or view
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            echo "View '{$view}' not found.";
        }
    }

    // Standard API response in JSON format
    protected function json($data, $statusCode = 200) {
        while (ob_get_level()) {
            ob_end_clean();
        }
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("Content-Type: application/json; charset=utf-8");
        http_response_code($statusCode);
        echo $json;
        exit;
    }

    // Redirect to another local path
    protected function redirect($url) {
        // Handle subdirectories and normalize backslashes on Windows
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $prefix = ($scriptDir === '/') ? '' : $scriptDir;
        header("Location: " . $prefix . $url);
        exit;
    }

    // Get raw JSON input from requests
    protected function getJsonInput() {
        $json = file_get_contents('php://input');
        return json_decode($json, true) ?? [];
    }

    // Verify session auth and redirect if missing
    protected function requireAuth($role = null) {
        $userRole = $_SESSION['user_role'] ?? $_SESSION['role'] ?? null;
        $username = $_SESSION['username'] ?? '';
        $isLoggedIn = isset($_SESSION['user_id']) && $userRole !== null;
        
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
            || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
            || (strpos($_SERVER['REQUEST_URI'] ?? '', '/json') !== false)
            || (strpos($_SERVER['REQUEST_URI'] ?? '', '/cancel/') !== false)
            || (strpos($_SERVER['REQUEST_URI'] ?? '', '/delete/') !== false)
            || (strpos($_SERVER['REQUEST_URI'] ?? '', '/pay/') !== false);

        if (!$isLoggedIn) {
            if ($isAjax) {
                $this->json(['success' => false, 'error' => 'Session expired. Please log in again.'], 401);
            }
            $this->redirect('/login');
        }

        if ($username === 'superadmin') {
            return;
        }

        if ($role) {
            $allowed = is_array($role) ? $role : [$role, 'admin'];
            if (!in_array($userRole, $allowed)) {
                if ($isAjax) {
                    $this->json(['success' => false, 'error' => 'Unauthorized access for role ' . htmlspecialchars($userRole)], 403);
                }
                $this->redirect('/login');
            }
        }
    }

    // Verify API Token/Session auth for React client
    protected function requireApiAuth() {
        if (!isset($_SESSION['user_id'])) {
            $this->json(['error' => 'Unauthorized'], 401);
        }
    }
}
