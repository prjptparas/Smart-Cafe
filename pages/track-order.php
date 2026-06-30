<?php
/**
 * Smart Cafe - Order Tracking Page
 * 
 * Customers can enter their order number to see the current
 * status of their order with a visual stepper UI.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

$orderNumber = isset($_GET['order']) ? sanitize($_GET['order']) : '';
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

// Status steps for the stepper
$statusSteps = ['pending', 'confirmed', 'preparing', 'ready', 'delivered'];
$statusIcons = [
    'pending'   => 'bi-hourglass-split',
    'confirmed' => 'bi-check-circle',
    'preparing' => 'bi-fire',
    'ready'     => 'bi-bell',
    'delivered' => 'bi-bag-check',
];

$currentPage = 'track';
$pageTitle = 'Track Order';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
            <li class="breadcrumb-item active">Track Order</li>
        </ol>
    </nav>
    <h1 class="section-title"><i class="bi bi-geo-alt"></i> Track Your Order</h1>
    <p class="section-subtitle">Enter your order number to see real-time status</p>
</div>

<section style="padding-bottom:4rem">
    <div class="container" style="max-width:700px">
        <!-- Search Form -->
        <form method="GET" action="" class="search-container mb-4">
            <i class="bi bi-search search-icon"></i>
            <input type="text" class="search-input" name="order" 
                   placeholder="Enter order number (e.g., SC-20260628-001)" 
                   value="<?php echo sanitize($orderNumber); ?>" required>
            <button type="submit" class="btn-primary-smart btn-sm-smart" style="position:absolute;right:6px;top:50%;transform:translateY(-50%)">
                Track
            </button>
        </form>

        <?php if ($orderNumber && $order): ?>
        <!-- Order Found -->
        <div class="glass-card" style="animation:fadeInUp 0.5s ease both">
            <!-- Order Info -->
            <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
                <div>
                    <h4 style="font-family:var(--font-heading);font-weight:700;margin-bottom:0.25rem">
                        Order <?php echo sanitize($order['order_number']); ?>
                    </h4>
                    <p style="color:var(--text-secondary);font-size:0.9rem;margin:0">
                        <i class="bi bi-calendar3"></i> <?php echo formatDate($order['created_at']); ?>
                    </p>
                </div>
                <span class="status-badge <?php echo $order['status']; ?>">
                    <?php echo getStatusLabel($order['status']); ?>
                </span>
            </div>

            <!-- Tracking Stepper -->
            <?php if ($order['status'] !== 'cancelled'): ?>
            <div class="tracking-stepper">
                <?php 
                $currentIdx = array_search($order['status'], $statusSteps);
                if ($currentIdx === false) $currentIdx = -1;
                
                foreach ($statusSteps as $idx => $step): 
                    $stepClass = '';
                    if ($idx < $currentIdx) $stepClass = 'completed';
                    elseif ($idx === $currentIdx) $stepClass = 'active';
                ?>
                <div class="tracking-step <?php echo $stepClass; ?>">
                    <div class="step-circle">
                        <i class="bi <?php echo $idx < $currentIdx ? 'bi-check-lg' : $statusIcons[$step]; ?>"></i>
                    </div>
                    <div class="step-label"><?php echo getStatusLabel($step); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-3">
                <i class="bi bi-x-circle" style="font-size:2rem;color:var(--color-danger)"></i>
                <p style="color:var(--color-danger);font-weight:600;margin-top:0.5rem">This order has been cancelled.</p>
            </div>
            <?php endif; ?>

            <hr style="border-color:var(--border-color);margin:1.5rem 0">

            <!-- Order Items -->
            <h6 style="font-weight:700;margin-bottom:1rem">Order Items</h6>
            <?php foreach ($orderItems as $item): ?>
            <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid var(--border-color)">
                <div>
                    <span style="font-weight:600"><?php echo sanitize($item['food_name']); ?></span>
                    <span style="color:var(--text-tertiary);font-size:0.85rem"> × <?php echo (int)$item['quantity']; ?></span>
                </div>
                <span style="font-weight:600"><?php echo formatPrice($item['subtotal']); ?></span>
            </div>
            <?php endforeach; ?>

            <div class="d-flex justify-content-between mt-3 pt-2" style="border-top:2px solid var(--border-color)">
                <span style="font-weight:700;font-size:1.1rem">Total</span>
                <span style="font-weight:700;font-size:1.1rem;color:var(--color-primary)"><?php echo formatPrice($order['total']); ?></span>
            </div>

            <!-- Action buttons -->
            <div class="d-flex gap-2 mt-4 flex-wrap">
                <a href="<?php echo BASE_URL; ?>/pages/bill.php?order=<?php echo urlencode($order['order_number']); ?>" class="btn-outline-smart btn-sm-smart">
                    <i class="bi bi-receipt"></i> View Bill
                </a>
                <?php if ($order['status'] === 'delivered'): ?>
                <a href="<?php echo BASE_URL; ?>/pages/feedback.php?order=<?php echo urlencode($order['order_number']); ?>" class="btn-primary-smart btn-sm-smart">
                    <i class="bi bi-star"></i> Give Feedback
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Auto-refresh notice -->
        <?php if (!in_array($order['status'], ['delivered', 'cancelled'])): ?>
        <div class="text-center mt-3">
            <p style="color:var(--text-tertiary);font-size:0.85rem">
                <i class="bi bi-arrow-clockwise"></i> This page auto-refreshes every 30 seconds
            </p>
        </div>
        <script>
            // Auto-refresh for active orders
            setTimeout(function() { location.reload(); }, 30000);
        </script>
        <?php endif; ?>

        <?php elseif ($orderNumber && !$order): ?>
        <!-- Order Not Found -->
        <div class="glass-card text-center" style="animation:fadeInUp 0.5s ease both">
            <i class="bi bi-search" style="font-size:3rem;color:var(--text-tertiary)"></i>
            <h4 style="font-family:var(--font-heading);margin:1rem 0 0.5rem">Order Not Found</h4>
            <p style="color:var(--text-secondary)">
                No order found with number "<strong><?php echo sanitize($orderNumber); ?></strong>". 
                Please check the number and try again.
            </p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
