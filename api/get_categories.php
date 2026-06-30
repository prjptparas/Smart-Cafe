<?php
/**
 * API: Get Categories
 * Returns all active categories as JSON.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $stmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC");
    $categories = $stmt->fetchAll();

    jsonResponse(['success' => true, 'data' => $categories]);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Failed to load categories.'], 500);
}
