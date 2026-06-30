<?php
/**
 * Smart Cafe - Order Success Page
 * 
 * Shown after a successful order. Displays order number,
 * estimated time, and links to track order or view bill.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

$orderNumber = isset($_GET['order']) ? sanitize($_GET['order']) : '';

// Fetch order details if order number provided
$order = null;
$orderItems = [];
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
$pageTitle = 'Order Placed Successfully';
include __DIR__ . '/../includes/header.php';
?>

<section class="success-section">
    <div class="container">
        <?php if ($order): ?>
        <div style="max-width:600px;margin:0 auto">
            <!-- Success Icon -->
            <div class="success-icon">
                <i class="bi bi-check-lg"></i>
            </div>

            <h1 class="section-title" style="animation:fadeInUp 0.5s ease 0.2s both">Order Placed Successfully!</h1>
            <p class="section-subtitle" style="animation:fadeInUp 0.5s ease 0.3s both">
                Thank you, <?php echo sanitize($order['customer_name']); ?>! Your order has been received.
            </p>

            <!-- Order Number -->
            <div style="animation:fadeInUp 0.5s ease 0.4s both">
                <div class="order-number-display"><?php echo sanitize($order['order_number']); ?></div>
            </div>

            <!-- Order Details Card -->
            <div class="glass-card" style="text-align:left;margin:2rem 0;animation:fadeInUp 0.5s ease 0.5s both">
                <div class="row g-3">
                    <div class="col-6">
                        <div style="font-size:0.8rem;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:1px">Table</div>
                        <div style="font-weight:700;font-size:1.1rem">Table <?php echo (int)$order['table_number']; ?></div>
                    </div>
                    <div class="col-6">
                        <div style="font-size:0.8rem;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:1px">Total</div>
                        <div style="font-weight:700;font-size:1.1rem;color:var(--color-primary)"><?php echo formatPrice($order['total']); ?></div>
                    </div>
                    <div class="col-6">
                        <div style="font-size:0.8rem;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:1px">Payment</div>
                        <div style="font-weight:700;font-size:1.1rem"><?php echo ucfirst(sanitize($order['payment_method'])); ?></div>
                    </div>
                    <div class="col-6">
                        <div style="font-size:0.8rem;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:1px">Items</div>
                        <div style="font-weight:700;font-size:1.1rem"><?php echo count($orderItems); ?> items</div>
                    </div>
                </div>
            </div>

            <!-- Estimated Time -->
            <div class="glass-card" style="text-align:center;margin-bottom:2rem;animation:fadeInUp 0.5s ease 0.6s both;background:rgba(var(--color-primary-rgb),0.05);border-color:rgba(var(--color-primary-rgb),0.15)">
                <i class="bi bi-clock" style="font-size:1.5rem;color:var(--color-primary)"></i>
                <h5 style="font-family:var(--font-heading);font-weight:700;margin:0.5rem 0">Estimated Time</h5>
                <p style="color:var(--text-secondary);margin:0">Your order will be ready in approximately <strong style="color:var(--color-primary)">15-25 minutes</strong></p>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-3 justify-content-center flex-wrap" style="animation:fadeInUp 0.5s ease 0.7s both">
                <a href="<?php echo BASE_URL; ?>/pages/track-order.php?order=<?php echo urlencode($orderNumber); ?>" class="btn-primary-smart">
                    <i class="bi bi-geo-alt"></i> Track Order
                </a>
                <a href="<?php echo BASE_URL; ?>/pages/bill.php?order=<?php echo urlencode($orderNumber); ?>" class="btn-outline-smart">
                    <i class="bi bi-receipt"></i> View Bill
                </a>
                <a href="<?php echo BASE_URL; ?>/pages/menu.php" class="btn-outline-smart">
                    <i class="bi bi-grid"></i> Back to Menu
                </a>
            </div>
        </div>
        <?php else: ?>
        <!-- No order found -->
        <div style="max-width:500px;margin:0 auto">
            <div class="empty-cart-icon"><i class="bi bi-exclamation-circle" style="font-size:4rem;color:var(--text-tertiary)"></i></div>
            <h2 class="section-title">Order Not Found</h2>
            <p class="section-subtitle">We couldn't find this order. Please check the order number and try again.</p>
            <a href="<?php echo BASE_URL; ?>/pages/menu.php" class="btn-primary-smart">
                <i class="bi bi-grid-fill"></i> Browse Menu
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
