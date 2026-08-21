<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://sandslab.com/get_our_latest_products.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$output = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode == 200 && $output) {
    echo $output;
} else {
    echo json_encode(['status' => false, 'message' => 'Failed to fetch data']);
}
