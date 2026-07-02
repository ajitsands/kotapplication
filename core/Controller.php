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
        header("Content-Type: application/json");
        http_response_code($statusCode);
        echo json_encode($data);
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
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
            $this->redirect('/login');
        }
        if ($role && $_SESSION['user_role'] !== $role && $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/login'); // unauthorized redirect
        }
    }

    // Verify API Token/Session auth for React client
    protected function requireApiAuth() {
        if (!isset($_SESSION['user_id'])) {
            $this->json(['error' => 'Unauthorized'], 401);
        }
    }
}
