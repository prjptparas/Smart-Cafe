<?php
/**
 * Smart Cafe - Bill / Receipt Page
 * 
 * Displays a printable receipt for an order.
 * Includes a print button for customers to print the bill.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

$orderNumber = isset($_GET['order']) ? sanitize($_GET['order']) : '';
$order = null;
$orderItems = [];
$settings = getAllSettings($pdo);

if ($orderNumber) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch();
    
    if ($order) {
        $stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmtItems->execute([$order['id']]);
        $orderItems = $stmtItems->fetchAll();
    }
}

$currentPage = '';
$pageTitle = 'Bill';
include __DIR__ . '/../includes/header.php';
?>

<section style="padding:2rem 0 4rem">
    <div class="container">
        <?php if ($order): ?>
        
        <!-- Print Button -->
        <div class="text-center mb-3 no-print">
            <button onclick="window.print()" class="btn-primary-smart">
                <i class="bi bi-printer"></i> Print Bill
            </button>
            <a href="<?php echo BASE_URL; ?>/pages/track-order.php?order=<?php echo urlencode($orderNumber); ?>" class="btn-outline-smart">
                <i class="bi bi-arrow-left"></i> Back to Tracking
            </a>
        </div>

        <!-- Bill -->
        <div class="bill-container">
            <!-- Header -->
            <div class="bill-header">
                <h3 style="font-family:var(--font-heading);font-weight:800;margin-bottom:0.25rem">
                    <i class="bi bi-cup-hot-fill" style="color:var(--color-primary)"></i> 
                    <?php echo sanitize($settings['cafe_name'] ?? APP_NAME); ?>
                </h3>
                <p style="color:var(--text-secondary);font-size:0.85rem;margin:0">
                    <?php echo sanitize($settings['cafe_address'] ?? '123 Food Street, Tech City'); ?>
                </p>
                <p style="color:var(--text-secondary);font-size:0.85rem;margin:0">
                    Phone: <?php echo sanitize($settings['cafe_phone'] ?? '+91 98765 43210'); ?>
                </p>
            </div>

            <!-- Order Info -->
            <div style="margin-bottom:1rem">
                <div class="bill-item-row">
                    <span style="color:var(--text-tertiary)">Order No:</span>
                    <span style="font-weight:600"><?php echo sanitize($order['order_number']); ?></span>
                </div>
                <div class="bill-item-row">
                    <span style="color:var(--text-tertiary)">Date:</span>
                    <span><?php echo formatDate($order['created_at']); ?></span>
                </div>
                <div class="bill-item-row">
                    <span style="color:var(--text-tertiary)">Table:</span>
                    <span>Table <?php echo (int)$order['table_number']; ?></span>
                </div>
                <div class="bill-item-row">
                    <span style="color:var(--text-tertiary)">Customer:</span>
                    <span><?php echo sanitize($order['customer_name']); ?></span>
                </div>
                <div class="bill-item-row">
                    <span style="color:var(--text-tertiary)">Payment:</span>
                    <span><?php echo ucfirst(sanitize($order['payment_method'])); ?></span>
                </div>
            </div>

            <hr class="bill-divider">

            <!-- Items -->
            <div class="bill-items">
                <div class="bill-item-row" style="font-weight:700;margin-bottom:0.5rem">
                    <span>Item</span>
                    <span>Amount</span>
                </div>
                <?php foreach ($orderItems as $item): ?>
                <div class="bill-item-row">
                    <div>
                        <span><?php echo sanitize($item['food_name']); ?></span>
                        <div style="color:var(--text-tertiary);font-size:0.8rem">
                            <?php echo formatPrice($item['food_price']); ?> × <?php echo (int)$item['quantity']; ?>
                        </div>
                    </div>
                    <span style="font-weight:500"><?php echo formatPrice($item['subtotal']); ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <hr class="bill-divider">

            <!-- Totals -->
            <div class="bill-total-row">
                <span>Subtotal</span>
                <span><?php echo formatPrice($order['subtotal']); ?></span>
            </div>
            <div class="bill-total-row">
                <span>GST (<?php echo $settings['tax_rate'] ?? DEFAULT_TAX_RATE; ?>%)</span>
                <span><?php echo formatPrice($order['tax']); ?></span>
            </div>
            <div class="bill-total-row grand-total">
                <span>Grand Total</span>
                <span><?php echo formatPrice($order['total']); ?></span>
            </div>

            <!-- Footer -->
            <div class="bill-footer">
                <p style="margin:0"><strong>Thank you for dining with us!</strong></p>
                <p style="margin:0.25rem 0 0">Visit again soon</p>
                <p style="margin:0.5rem 0 0;font-size:0.75rem">
                    Powered by <?php echo APP_NAME; ?> v<?php echo APP_VERSION; ?>
                </p>
            </div>
        </div>

        <?php else: ?>
        <!-- No order found -->
        <div class="text-center" style="padding:4rem 0">
            <i class="bi bi-receipt" style="font-size:4rem;color:var(--text-tertiary)"></i>
            <h3 style="margin:1rem 0 0.5rem;font-family:var(--font-heading)">Bill Not Found</h3>
            <p style="color:var(--text-secondary)">Please provide a valid order number to view the bill.</p>
            <a href="<?php echo BASE_URL; ?>/pages/track-order.php" class="btn-primary-smart mt-2">
                <i class="bi bi-search"></i> Find Your Order
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
