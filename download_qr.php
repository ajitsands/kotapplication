<?php
if (!isset($_GET['data'])) {
    die("No data provided");
}

$data = urlencode($_GET['data']);
$size = isset($_GET['size']) ? $_GET['size'] : '500x500';
$filename = isset($_GET['filename']) ? $_GET['filename'] : 'QRCode.png';

$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}&data={$data}";

// Fetch the image using cURL to handle it more robustly
$ch = curl_init($qrUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$image = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($image === false || $httpCode !== 200) {
    die("Failed to fetch QR code");
}

// Force download
header('Content-Description: File Transfer');
header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . strlen($image));

echo $image;
exit;
?>
