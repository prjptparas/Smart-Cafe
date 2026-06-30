<?php
/**
 * Admin - Orders Management
 * 
 * View all orders with status filtering and update order status.
 */

$adminPage = 'orders';
$adminPageTitle = 'Orders';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$message = '';
$messageType = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $orderId = sanitizeInt($_POST['order_id'] ?? 0);
        $newStatus = trim($_POST['status'] ?? '');
        $validStatuses = ['pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled'];
        
        if ($orderId > 0 && in_array($newStatus, $validStatuses)) {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $orderId]);
            $message = 'Order status updated to ' . ucfirst($newStatus) . '!';
            $messageType = 'success';
        }
    }
}

// Status filter
$statusFilter = trim($_GET['status'] ?? '');
$validStatuses = ['pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled'];

$query = "SELECT o.*, (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS item_count FROM orders o";
$params = [];

if ($statusFilter && in_array($statusFilter, $validStatuses)) {
    $query .= " WHERE o.status = ?";
    $params[] = $statusFilter;
}

$query .= " ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Count by status for filter tabs
$statusCounts = [];
$countStmt = $pdo->query("SELECT status, COUNT(*) as cnt FROM orders GROUP BY status");
while ($row = $countStmt->fetch()) {
    $statusCounts[$row['status']] = $row['cnt'];
}
$totalOrders = array_sum($statusCounts);

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?> d-flex align-items-center gap-2 alert-dismissible fade show" role="alert" style="border-radius:var(--radius-md)">
    <i class="bi bi-check-circle-fill"></i> <?php echo sanitize($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Status Filter Tabs -->
<div class="category-scroll mb-4">
    <a href="<?php echo BASE_URL; ?>/admin/orders.php" class="category-chip <?php echo empty($statusFilter) ? 'active' : ''; ?>" style="text-decoration:none">
        All (<?php echo $totalOrders; ?>)
    </a>
    <?php foreach ($validStatuses as $st): ?>
    <a href="<?php echo BASE_URL; ?>/admin/orders.php?status=<?php echo $st; ?>" 
       class="category-chip <?php echo $statusFilter === $st ? 'active' : ''; ?>" style="text-decoration:none">
        <?php echo ucfirst($st); ?> (<?php echo $statusCounts[$st] ?? 0; ?>)
    </a>
    <?php endforeach; ?>
</div>

<!-- Orders Table -->
<div class="admin-table-container">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Table</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="bi bi-inbox"></i></div>
                            <h4>No orders found</h4>
                            <p>No orders match the selected filter.</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($orders as $order): ?>
                <tr data-order-id="<?php echo $order['id']; ?>">
                    <td>
                        <span style="font-weight:600;color:var(--color-primary)"><?php echo sanitize($order['order_number']); ?></span>
                    </td>
                    <td>
                        <div style="font-weight:500"><?php echo sanitize($order['customer_name']); ?></div>
                        <?php if ($order['customer_phone']): ?>
                        <small style="color:var(--text-tertiary)"><?php echo sanitize($order['customer_phone']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td><span class="table-indicator" style="padding:2px 10px;font-size:0.75rem">T<?php echo (int)$order['table_number']; ?></span></td>
                    <td><?php echo (int)$order['item_count']; ?></td>
                    <td style="font-weight:600"><?php echo formatPrice($order['total']); ?></td>
                    <td style="text-transform:uppercase;font-size:var(--font-size-xs);font-weight:600"><?php echo sanitize($order['payment_method']); ?></td>
                    <td><span class="status-badge <?php echo $order['status']; ?>"><?php echo getStatusLabel($order['status']); ?></span></td>
                    <td style="color:var(--text-tertiary);font-size:var(--font-size-sm)"><?php echo timeAgo($order['created_at']); ?></td>
                    <td>
                        <div class="d-flex gap-1 align-items-center">
                            <!-- View details -->
                            <button class="action-btn" title="View Details" onclick='viewOrder(<?php echo json_encode($order); ?>)'>
                                <i class="bi bi-eye"></i>
                            </button>
                            <!-- Status dropdown -->
                            <select class="sort-select" style="min-width:110px;padding:4px 8px;font-size:0.75rem" 
                                    onchange="changeOrderStatus(<?php echo $order['id']; ?>, this.value)">
                                <?php foreach ($validStatuses as $st): ?>
                                <option value="<?php echo $st; ?>" <?php echo $order['status'] === $st ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($st); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade modal-smart" id="orderDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailTitle">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetailBody">
                <!-- Loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<script>
function changeOrderStatus(orderId, newStatus) {
    var form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = '<input type="hidden" name="action" value="update_status">' +
        '<input type="hidden" name="order_id" value="' + orderId + '">' +
        '<input type="hidden" name="status" value="' + newStatus + '">';
    document.body.appendChild(form);
    form.submit();
}

function viewOrder(order) {
    document.getElementById('orderDetailTitle').textContent = 'Order ' + order.order_number;
    
    var html = '<div class="mb-3">' +
        '<div class="row g-2">' +
        '<div class="col-6"><strong>Customer:</strong><br>' + (order.customer_name || 'Guest') + '</div>' +
        '<div class="col-6"><strong>Phone:</strong><br>' + (order.customer_phone || 'N/A') + '</div>' +
        '<div class="col-6"><strong>Table:</strong><br>Table ' + order.table_number + '</div>' +
        '<div class="col-6"><strong>Payment:</strong><br>' + order.payment_method.toUpperCase() + '</div>' +
        '<div class="col-6"><strong>Status:</strong><br><span class="status-badge ' + order.status + '">' + order.status.charAt(0).toUpperCase() + order.status.slice(1) + '</span></div>' +
        '<div class="col-6"><strong>Total:</strong><br><span style="color:var(--color-primary);font-weight:700">' + formatPrice(order.total) + '</span></div>' +
        '</div></div>';
    
    if (order.special_instructions) {
        html += '<div class="mb-3"><strong>Special Instructions:</strong><br><em style="color:var(--text-secondary)">' + order.special_instructions + '</em></div>';
    }

    html += '<hr style="border-color:var(--border-color)">' +
        '<div class="text-center">' +
        '<a href="<?php echo BASE_URL; ?>/pages/bill.php?order=' + encodeURIComponent(order.order_number) + '" target="_blank" class="btn-outline-smart btn-sm-smart">' +
        '<i class="bi bi-receipt"></i> View Bill</a></div>';
    
    document.getElementById('orderDetailBody').innerHTML = html;
    var modal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
    modal.show();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
