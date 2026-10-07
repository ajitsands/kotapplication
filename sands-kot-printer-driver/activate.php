<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$licenseKey = trim($input['license_key'] ?? '');
$domain = trim($input['domain_name'] ?? '');
$ip = trim($input['ip_address'] ?? '');

if (empty($licenseKey)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'License key is required.']);
    exit;
}

if (empty($domain)) {
    $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
}

$cleanDomain = preg_replace('#^https?://#', '', $domain);
$cleanDomain = explode('/', $cleanDomain)[0];
$cleanDomain = explode(':', $cleanDomain)[0];

if (empty($ip)) {
    $ip = gethostbyname($cleanDomain);
    if ($ip === $cleanDomain || $ip === '127.0.0.1') {
        $ip = @file_get_contents('https://api.ipify.org') ?: ($_SERVER['SERVER_ADDR'] ?? '');
    }
}

$payload = [
    'license_key' => $licenseKey,
    'domain_name' => $cleanDomain,
    'ip_address'  => trim($ip)
];

$ch = curl_init('https://key.sandslab.com/public/api/activate');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 15
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to reach key server: ' . $curlError]);
    exit;
}

$data = json_decode($response, true);
if (!$data) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Invalid response from key server: ' . $response]);
    exit;
}

http_response_code($httpCode);
echo json_encode($data);
