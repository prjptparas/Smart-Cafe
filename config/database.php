<?php
/**
 * Database Configuration
 * 
 * Establishes a PDO connection to the MySQL database.
 * Uses utf8mb4 charset for full Unicode support.
 * 
 * Usage: require_once __DIR__ . '/../config/database.php';
 *        Then use the $pdo variable for queries.
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'smart_cafe');
define('DB_USER', 'root');
define('DB_PASS', '');        // Default XAMPP has no password
define('DB_CHARSET', 'utf8mb4');

// Build DSN (Data Source Name)
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

// PDO connection options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // Return associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                    // Use real prepared statements
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // In production, log the error and show a friendly message
    error_log("Database Connection Error: " . $e->getMessage());
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed. Please ensure MySQL is running in XAMPP.'
    ]));
}
