<?php
/**
 * API: Get Menu Items
 * 
 * Returns food items as JSON with optional filtering.
 * Query params: category, veg_only, sort, search
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $query = "SELECT f.*, c.name AS category_name FROM foods f 
              JOIN categories c ON f.category_id = c.id 
              WHERE f.is_available = 1";
    $params = [];

    // Category filter
    if (!empty($_GET['category'])) {
        $query .= " AND f.category_id = ?";
        $params[] = sanitizeInt($_GET['category']);
    }

    // Veg only filter
    if (!empty($_GET['veg_only'])) {
        $query .= " AND f.is_veg = 1";
    }

    // Search filter
    if (!empty($_GET['search'])) {
        $query .= " AND (f.name LIKE ? OR f.description LIKE ?)";
        $searchTerm = '%' . $_GET['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    // Sorting
    $sort = $_GET['sort'] ?? '';
    switch ($sort) {
        case 'price_low':
            $query .= " ORDER BY f.price ASC";
            break;
        case 'price_high':
            $query .= " ORDER BY f.price DESC";
            break;
        case 'name':
            $query .= " ORDER BY f.name ASC";
            break;
        case 'prep_time':
            $query .= " ORDER BY f.prep_time ASC";
            break;
        default:
            $query .= " ORDER BY f.is_featured DESC, f.name ASC";
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $foods = $stmt->fetchAll();

    // Add image URLs
    foreach ($foods as &$food) {
        $food['image_url'] = getFoodImageUrl($food['image']);
    }

    jsonResponse(['success' => true, 'data' => $foods, 'count' => count($foods)]);

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Failed to load menu items.'], 500);
}
