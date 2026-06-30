<?php
/**
 * Admin Dashboard
 * 
 * Shows key metrics, recent orders, and revenue chart.
 */

$adminPage = 'dashboard';
$adminPageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

// ---- Dashboard Stats ----
// Today's orders
$todayOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn();

// Today's revenue
$todayRevenue = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'")->fetchColumn();

// Pending orders
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('pending', 'confirmed', 'preparing')")->fetchColumn();

// Average rating
$avgRating = $pdo->query("SELECT ROUND(AVG(rating), 1) FROM feedback")->fetchColumn() ?: '0.0';

// Total orders (all time)
$totalOrdersAll = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

// Total revenue (all time)
$totalRevenueAll = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != 'cancelled'")->fetchColumn();

// Recent orders (last 10)
$recentOrders = $pdo->query("
    SELECT o.*, 
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS item_count
    FROM orders o 
    ORDER BY o.created_at DESC 
    LIMIT 10
")->fetchAll();

// Revenue for chart (last 7 days)
$chartData = $pdo->query("
    SELECT DATE(created_at) AS order_date, 
           COALESCE(SUM(total), 0) AS daily_revenue
    FROM orders 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) 
    AND status != 'cancelled'
    GROUP BY DATE(created_at)
    ORDER BY order_date ASC
")->fetchAll();

// Build chart arrays for last 7 days
$chartLabels = [];
$chartValues = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dayLabel = date('D', strtotime("-$i days"));
    $chartLabels[] = $dayLabel;
    
    // Find revenue for this date
    $found = false;
    foreach ($chartData as $row) {
        if ($row['order_date'] === $date) {
            $chartValues[] = floatval($row['daily_revenue']);
            $found = true;
            break;
        }
    }
    if (!$found) $chartValues[] = 0;
}

// Order status distribution for donut chart
$statusCounts = $pdo->query("
    SELECT status, COUNT(*) AS cnt 
    FROM orders 
    GROUP BY status
")->fetchAll();
?>

<!-- ======== STAT CARDS ======== -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card primary">
            <div class="stat-card-header">
                <div class="stat-card-icon"><i class="bi bi-receipt"></i></div>
            </div>
            <div class="stat-card-value"><?php echo $todayOrders; ?></div>
            <div class="stat-card-label">Today's Orders</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card success">
            <div class="stat-card-header">
                <div class="stat-card-icon"><i class="bi bi-currency-rupee"></i></div>
            </div>
            <div class="stat-card-value"><?php echo CURRENCY_SYMBOL . ' ' . number_format($todayRevenue, 0); ?></div>
            <div class="stat-card-label">Today's Revenue</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card warning">
            <div class="stat-card-header">
                <div class="stat-card-icon"><i class="bi bi-hourglass-split"></i></div>
            </div>
            <div class="stat-card-value"><?php echo $pendingOrders; ?></div>
            <div class="stat-card-label">Active Orders</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card info">
            <div class="stat-card-header">
                <div class="stat-card-icon"><i class="bi bi-star-fill"></i></div>
            </div>
            <div class="stat-card-value"><?php echo $avgRating; ?> <small style="font-size:0.5em"><i class="bi bi-star-fill"></i></small></div>
            <div class="stat-card-label">Average Rating</div>
        </div>
    </div>
</div>

<!-- ======== CHARTS ROW ======== -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="chart-card">
            <div class="chart-card-header">
                <h3 class="chart-card-title">Revenue (Last 7 Days)</h3>
                <span style="font-size:var(--font-size-sm);color:var(--text-tertiary)">
                    Total: <?php echo formatPrice($totalRevenueAll); ?>
                </span>
            </div>
            <div class="chart-canvas-wrapper">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="chart-card">
            <div class="chart-card-header">
                <h3 class="chart-card-title">Order Status</h3>
            </div>
            <div class="chart-canvas-wrapper" style="height:200px;display:flex;justify-content:center">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="mt-3">
                <?php foreach ($statusCounts as $sc): ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="status-badge <?php echo $sc['status']; ?>"><?php echo getStatusLabel($sc['status']); ?></span>
                    </div>
                    <span style="font-weight:600;font-size:var(--font-size-sm)"><?php echo $sc['cnt']; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- ======== RECENT ORDERS TABLE ======== -->
<div class="admin-table-container">
    <div class="admin-table-header">
        <h3 class="admin-table-title">Recent Orders</h3>
        <a href="<?php echo BASE_URL; ?>/admin/orders.php" class="btn-outline-smart btn-sm-smart">
            View All <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Table</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentOrders)): ?>
                <tr>
                    <td colspan="7" class="text-center" style="padding:2rem">
                        <div class="empty-state-icon"><i class="bi bi-inbox"></i></div>
                        <p style="color:var(--text-tertiary)">No orders yet</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($recentOrders as $order): ?>
                <tr>
                    <td>
                        <span style="font-weight:600;color:var(--color-primary)"><?php echo sanitize($order['order_number']); ?></span>
                    </td>
                    <td><?php echo sanitize($order['customer_name']); ?></td>
                    <td><span class="table-indicator" style="padding:2px 10px;font-size:0.75rem">T<?php echo (int)$order['table_number']; ?></span></td>
                    <td><?php echo (int)$order['item_count']; ?></td>
                    <td style="font-weight:600"><?php echo formatPrice($order['total']); ?></td>
                    <td><span class="status-badge <?php echo $order['status']; ?>"><?php echo getStatusLabel($order['status']); ?></span></td>
                    <td style="color:var(--text-tertiary);font-size:var(--font-size-sm)"><?php echo timeAgo($order['created_at']); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chart Rendering Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue bar chart
    drawBarChart('revenueChart', 
        <?php echo json_encode($chartLabels); ?>,
        <?php echo json_encode($chartValues); ?>,
        '#e67e22'
    );

    // Status donut chart
    var statusData = <?php echo json_encode(array_map(function($s) {
        return ['label' => $s['status'], 'value' => (int)$s['cnt']];
    }, $statusCounts)); ?>;

    var statusColors = ['#f39c12', '#3498db', '#e67e22', '#27ae60', '#6c757d', '#e74c3c'];
    drawDonutChart('statusChart', statusData, statusColors);

    // Redraw charts on theme change
    var origToggle = window.SmartCafeTheme.toggle;
    window.SmartCafeTheme.toggle = function() {
        origToggle();
        setTimeout(function() {
            drawBarChart('revenueChart', 
                <?php echo json_encode($chartLabels); ?>,
                <?php echo json_encode($chartValues); ?>,
                '#e67e22'
            );
            drawDonutChart('statusChart', statusData, statusColors);
        }, 100);
    };
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
