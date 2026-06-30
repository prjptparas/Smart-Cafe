<?php
/**
 * Admin API: Orders
 * Handles order status updates via AJAX POST.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!isAdminLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
}

$orderId = sanitizeInt($_POST['order_id'] ?? 0);
$newStatus = trim($_POST['status'] ?? '');
$validStatuses = ['pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled'];

if ($orderId < 1 || !in_array($newStatus, $validStatuses)) {
    jsonResponse(['success' => false, 'message' => 'Invalid order ID or status.'], 400);
}

try {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $orderId]);
    
    jsonResponse(['success' => true, 'message' => 'Order status updated.']);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Failed to update order status.'], 500);
}
