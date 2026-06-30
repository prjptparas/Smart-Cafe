<?php
/**
 * API: Get Order Status
 * Returns the current status of an order by order number.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$orderNumber = trim($_GET['order'] ?? '');

if (empty($orderNumber)) {
    jsonResponse(['success' => false, 'message' => 'Order number is required.'], 400);
}

try {
    $stmt = $pdo->prepare("SELECT order_number, status, table_number, total, created_at, updated_at FROM orders WHERE order_number = ?");
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch();

    if ($order) {
        jsonResponse(['success' => true, 'data' => $order]);
    } else {
        jsonResponse(['success' => false, 'message' => 'Order not found.'], 404);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Failed to fetch order status.'], 500);
}
