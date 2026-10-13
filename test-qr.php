<?php
require 'vendor/autoload.php';
use BaconQrCode\QrCode;
$qr = QrCode::render('Hello World', BaconQrCode\Renderer\Renderer::IMAGE_SVG);
echo "QR Code length: " . strlen($qr) . " bytes\n";
echo "QR Code sample:\n";
echo substr($qr, 0, 200) . "\n";
?>