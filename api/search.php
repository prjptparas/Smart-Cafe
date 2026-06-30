<?php
/**
 * API: Search Food Items
 * Returns matching food items for the search query.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$query = trim($_GET['q'] ?? '');

if (strlen($query) < 2) {
    jsonResponse(['success' => false, 'message' => 'Search query must be at least 2 characters.'], 400);
}

try {
    $searchTerm = '%' . $query . '%';
    $stmt = $pdo->prepare("
        SELECT f.*, c.name AS category_name 
        FROM foods f 
        JOIN categories c ON f.category_id = c.id 
        WHERE f.is_available = 1 
        AND (f.name LIKE ? OR f.description LIKE ? OR c.name LIKE ?)
        ORDER BY f.is_featured DESC, f.name ASC
        LIMIT 20
    ");
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $foods = $stmt->fetchAll();

    // Add image URLs
    foreach ($foods as &$food) {
        $food['image_url'] = getFoodImageUrl($food['image']);
    }

    jsonResponse([
        'success' => count($foods) > 0, 
        'data' => $foods, 
        'count' => count($foods),
        'query' => $query
    ]);

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Search failed.'], 500);
}
