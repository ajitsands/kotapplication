<?php
/**
 * webhook.php - Git Automated Deploy Webhook
 * 
 * Instructions to set up on GitHub:
 * 1. Go to your repository on GitHub.
 * 2. Click Settings -> Webhooks -> Add webhook.
 * 3. Payload URL: http://<your-server-domain>/webhook.php
 * 4. Content type: application/json
 * 5. Secret: (Define a secret below, e.g. 'my_secure_deploy_token_123', and enter it on GitHub)
 * 6. Which events? Select "Just the push event".
 * 7. Click Add webhook.
 */

// Define your secure secret token here (must match the secret entered in GitHub settings).
// To disable secret verification (not recommended for production), set to null.
define('GITHUB_WEBHOOK_SECRET', 'my_secure_deploy_token_123'); 

$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

if (GITHUB_WEBHOOK_SECRET !== null) {
    if (empty($signature)) {
        header('HTTP/1.1 403 Forbidden - Signature missing');
        echo json_encode(['error' => 'Signature missing.']);
        exit;
    }
    
    $hash = 'sha256=' . hash_hmac('sha256', $payload, GITHUB_WEBHOOK_SECRET);
    if (!hash_equals($hash, $signature)) {
        header('HTTP/1.1 403 Forbidden - Invalid signature');
        echo json_encode(['error' => 'Signature verification failed.']);
        exit;
    }
}

// Log deployment start
$logFile = 'deploy.log';
$timestamp = date('Y-m-d H:i:s');
file_put_contents($logFile, "[$timestamp] Starting deployment...\n", FILE_APPEND);

// Execute deployment commands
$output = [];
$returnCode = 0;

// 1. Fetch latest changes from remote main branch
exec('git fetch origin main 2>&1', $output, $returnCode);

if ($returnCode === 0) {
    // 2. Hard reset local tracking files to match origin main
    exec('git reset --hard origin/main 2>&1', $output, $returnCode);
}

// Write outputs to deploy.log
$outputStr = implode("\n", $output);
file_put_contents($logFile, "[$timestamp] Git Pull Output:\n$outputStr\nStatus Code: $returnCode\n\n", FILE_APPEND);

header('Content-Type: application/json');
echo json_encode([
    'success' => $returnCode === 0,
    'output' => $output,
    'status' => $returnCode
]);
