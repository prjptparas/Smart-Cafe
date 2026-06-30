<?php
/**
 * API: Submit Feedback
 * Accepts POST with feedback data (rating, comment, order).
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        // Fall back to form data
        $input = $_POST;
    }

    $name = trim($input['customer_name'] ?? $input['name'] ?? 'Anonymous');
    $rating = intval($input['rating'] ?? 0);
    $comment = trim($input['comment'] ?? '');
    $orderNumber = trim($input['order_number'] ?? '');

    // Validate rating
    if ($rating < 1 || $rating > 5) {
        jsonResponse(['success' => false, 'message' => 'Rating must be between 1 and 5.'], 400);
    }

    // Find order ID if provided
    $orderId = null;
    if ($orderNumber) {
        $stmt = $pdo->prepare("SELECT id FROM orders WHERE order_number = ?");
        $stmt->execute([$orderNumber]);
        $orderId = $stmt->fetchColumn() ?: null;
    }

    $stmt = $pdo->prepare("INSERT INTO feedback (order_id, customer_name, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->execute([$orderId, $name ?: 'Anonymous', $rating, $comment]);

    jsonResponse(['success' => true, 'message' => 'Thank you for your feedback!']);

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Failed to submit feedback.'], 500);
}
