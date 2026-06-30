<?php
/**
 * Application Constants
 * 
 * Central place for all app-wide configuration values.
 * These are loaded once and available everywhere.
 */

// ---- Application Info ----
define('APP_NAME', 'Smart Cafe');
define('APP_VERSION', '1.0.0');
define('APP_TAGLINE', 'Delicious Food, Smart Ordering');

// ---- Paths ----
// Auto-detect the base URL for the project
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Calculate the base path from the script location
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
// Walk up to find the smart-cafe root
$basePath = $scriptDir;
// If we're in a subdirectory like /admin or /pages, go up
if (preg_match('#/smart-cafe#', $basePath)) {
    $basePath = substr($basePath, 0, strpos($basePath, '/smart-cafe') + strlen('/smart-cafe'));
}
define('BASE_URL', $protocol . '://' . $host . $basePath);

// File system paths
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/admin/uploads/');
define('UPLOAD_URL', BASE_URL . '/admin/uploads/');

// ---- Business Rules ----
define('DEFAULT_TAX_RATE', 5);           // 5% GST
define('CURRENCY_SYMBOL', '₹');
define('MIN_ORDER_AMOUNT', 50);
define('TOTAL_TABLES', 20);
define('ORDER_PREFIX', 'SC');

// ---- Security ----
define('MAX_LOGIN_ATTEMPTS', 5);
define('SESSION_LIFETIME', 3600);        // 1 hour
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// ---- Pagination ----
define('ITEMS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 10);
