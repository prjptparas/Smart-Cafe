<?php
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/includes/functions.php';

$testImages = ['espresso.jpg', 'cappuccino.jpg', 'margherita-pizza.jpg', 'fake-image.jpg'];

echo "ROOT_PATH: " . ROOT_PATH . "\n";
echo "BASE_URL: " . BASE_URL . "\n\n";

foreach ($testImages as $img) {
    $path = ROOT_PATH . '/admin/uploads/' . $img;
    $exists = file_exists($path) ? "YES" : "NO";
    echo str_pad($img, 25) . " | Exists? $exists | URL: " . getFoodImageUrl($img) . "\n";
}
