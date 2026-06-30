<?php
/**
 * API: Place Order
 * 
 * Accepts POST with JSON body containing order details.
 * Creates order and order_items in a transaction.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
}

try {
    // Parse JSON body
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        jsonResponse(['success' => false, 'message' => 'Invalid request data.'], 400);
    }

    // Validate required fields
    $tableNumber = sanitizeInt($input['table_number'] ?? 0);
    $customerName = trim($input['customer_name'] ?? 'Guest');
    $customerPhone = trim($input['customer_phone'] ?? '');
    $paymentMethod = trim($input['payment_method'] ?? 'cash');
    $specialInstructions = trim($input['special_instructions'] ?? '');
    $items = $input['items'] ?? [];

    if ($tableNumber < 1 || $tableNumber > TOTAL_TABLES) {
        jsonResponse(['success' => false, 'message' => 'Invalid table number.'], 400);
    }

    if (empty($items)) {
        jsonResponse(['success' => false, 'message' => 'Cart is empty.'], 400);
    }

    if (empty($customerName)) {
        $customerName = 'Guest';
    }

    // Calculate totals
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += floatval($item['food_price']) * intval($item['quantity']);
    }

    // Get tax rate from settings
    $taxRate = floatval(getSetting($pdo, 'tax_rate', DEFAULT_TAX_RATE));
    $tax = round($subtotal * $taxRate / 100, 2);
    $total = round($subtotal + $tax, 2);

    // Minimum order check
    $minOrder = floatval(getSetting($pdo, 'min_order_amount', MIN_ORDER_AMOUNT));
    if ($subtotal < $minOrder) {
        jsonResponse(['success' => false, 'message' => "Minimum order amount is " . CURRENCY_SYMBOL . " " . $minOrder], 400);
    }

    // Generate unique order number
    $orderNumber = generateOrderNumber($pdo);

    // Begin transaction
    $pdo->beginTransaction();

    // Insert order
    $stmtOrder = $pdo->prepare("
        INSERT INTO orders (order_number, table_number, customer_name, customer_phone, subtotal, tax, total, status, payment_method, special_instructions)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)
    ");
    $stmtOrder->execute([
        $orderNumber,
        $tableNumber,
        $customerName,
        $customerPhone ?: null,
        $subtotal,
        $tax,
        $total,
        $paymentMethod,
        $specialInstructions ?: null
    ]);

    $orderId = $pdo->lastInsertId();

    // Insert order items
    $stmtItem = $pdo->prepare("
        INSERT INTO order_items (order_id, food_id, food_name, food_price, quantity, subtotal)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($items as $item) {
        $itemSubtotal = floatval($item['food_price']) * intval($item['quantity']);
        $stmtItem->execute([
            $orderId,
            intval($item['food_id']),
            trim($item['food_name']),
            floatval($item['food_price']),
            intval($item['quantity']),
            $itemSubtotal
        ]);
    }

    // Commit transaction
    $pdo->commit();

    jsonResponse([
        'success' => true,
        'message' => 'Order placed successfully!',
        'order_number' => $orderNumber,
        'order_id' => $orderId,
        'total' => $total
    ]);

} catch (Exception $e) {
    // Rollback on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Place Order Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Failed to place order. Please try again.'], 500);
}
