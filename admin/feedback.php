<?php
/**
 * Admin - Feedback Management
 * View feedback, mark as read, delete.
 */

$adminPage = 'feedback';
$adminPageTitle = 'Feedback';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = sanitizeInt($_POST['feedback_id'] ?? 0);
    
    if ($action === 'mark_read' && $id > 0) {
        $pdo->prepare("UPDATE feedback SET is_read = 1 WHERE id = ?")->execute([$id]);
        $message = 'Marked as read.';
        $messageType = 'success';
    } elseif ($action === 'mark_all_read') {
        $pdo->query("UPDATE feedback SET is_read = 1 WHERE is_read = 0");
        $message = 'All feedback marked as read.';
        $messageType = 'success';
    } elseif ($action === 'delete' && $id > 0) {
        $pdo->prepare("DELETE FROM feedback WHERE id = ?")->execute([$id]);
        $message = 'Feedback deleted.';
        $messageType = 'success';
    }
}

// Fetch all feedback
$feedbackList = $pdo->query("
    SELECT f.*, o.order_number 
    FROM feedback f 
    LEFT JOIN orders o ON f.order_id = o.id 
    ORDER BY f.is_read ASC, f.created_at DESC
")->fetchAll();

$unreadCount = 0;
foreach ($feedbackList as $fb) {
    if (!$fb['is_read']) $unreadCount++;
}

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?> d-flex align-items-center gap-2 alert-dismissible fade show" role="alert" style="border-radius:var(--radius-md)">
    <i class="bi bi-check-circle-fill"></i> <?php echo sanitize($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <p style="color:var(--text-secondary);margin:0">
        <?php echo count($feedbackList); ?> total &middot; <?php echo $unreadCount; ?> unread
    </p>
    <?php if ($unreadCount > 0): ?>
    <form method="POST" style="display:inline">
        <input type="hidden" name="action" value="mark_all_read">
        <button type="submit" class="btn-outline-smart btn-sm-smart">
            <i class="bi bi-check-all"></i> Mark All Read
        </button>
    </form>
    <?php endif; ?>
</div>

<!-- Feedback Cards -->
<div class="row g-3">
    <?php if (empty($feedbackList)): ?>
    <div class="col-12">
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-chat-heart"></i></div>
            <h4>No feedback yet</h4>
            <p>Customer feedback will appear here.</p>
        </div>
    </div>
    <?php else: ?>
    <?php foreach ($feedbackList as $fb): ?>
    <div class="col-md-6 col-lg-4">
        <div class="glass-card" style="<?php echo !$fb['is_read'] ? 'border-left:3px solid var(--color-primary)' : ''; ?>">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <h6 style="font-weight:600;margin:0"><?php echo sanitize($fb['customer_name']); ?></h6>
                    <?php if ($fb['order_number']): ?>
                    <small style="color:var(--color-primary)"><?php echo sanitize($fb['order_number']); ?></small>
                    <?php endif; ?>
                </div>
                <div class="rating-stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="bi <?php echo $i <= $fb['rating'] ? 'bi-star-fill' : 'bi-star'; ?>"></i>
                    <?php endfor; ?>
                </div>
            </div>
            
            <?php if ($fb['comment']): ?>
            <p style="color:var(--text-secondary);font-size:var(--font-size-sm);margin-bottom:0.75rem;line-height:1.5">
                "<?php echo sanitize($fb['comment']); ?>"
            </p>
            <?php endif; ?>
            
            <div class="d-flex justify-content-between align-items-center">
                <small style="color:var(--text-tertiary)"><?php echo timeAgo($fb['created_at']); ?></small>
                <div class="d-flex gap-1">
                    <?php if (!$fb['is_read']): ?>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="action" value="mark_read">
                        <input type="hidden" name="feedback_id" value="<?php echo $fb['id']; ?>">
                        <button type="submit" class="action-btn" title="Mark as Read"><i class="bi bi-check-lg"></i></button>
                    </form>
                    <?php endif; ?>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Delete this feedback?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="feedback_id" value="<?php echo $fb['id']; ?>">
                        <button type="submit" class="action-btn delete" title="Delete"><i class="bi bi-trash3"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
