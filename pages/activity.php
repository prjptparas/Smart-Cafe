<?php
/**
 * Smart Cafe - Community Activity Page
 * Shows live anonymized order feed and real customer reviews.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

// Fetch recent orders (Anonymized)
$stmtOrders = $pdo->query("
    SELECT o.created_at, o.table_number, COUNT(oi.id) as items_count 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    GROUP BY o.id 
    ORDER BY o.created_at DESC 
    LIMIT 10
");
$recentOrders = $stmtOrders->fetchAll();

// Fetch customer feedback/reviews
$stmtReviews = $pdo->query("
    SELECT customer_name, rating, comment, created_at 
    FROM feedback 
    WHERE comment IS NOT NULL AND comment != '' 
    ORDER BY created_at DESC 
    LIMIT 12
");
$reviews = $stmtReviews->fetchAll();

$pageTitle = 'Community Activity';
$currentPage = 'activity';
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 style="font-family:var(--font-heading);font-weight:800;color:var(--text-primary)">Community Activity</h1>
            <p style="color:var(--text-secondary);max-width:600px;margin:0 auto">See what our community is loving right now. Live orders and real customer feedback.</p>
        </div>
    </div>

    <div class="row g-5">
        <!-- Live Orders Feed -->
        <div class="col-lg-5" id="orders">
            <div class="d-flex align-items-center mb-4 gap-2">
                <i class="bi bi-activity text-success fs-3"></i>
                <h3 style="font-family:var(--font-heading);font-weight:700;margin:0">Live Order Feed</h3>
            </div>
            
            <div class="activity-feed">
                <?php if (empty($recentOrders)): ?>
                    <p class="text-muted">No recent orders found.</p>
                <?php else: ?>
                    <?php foreach ($recentOrders as $idx => $order): ?>
                        <div class="order-feed-card interactive-card" style="animation-delay: <?php echo $idx * 0.1; ?>s">
                            <div class="d-flex align-items-center gap-3 w-100">
                                <div class="feed-icon">
                                    <i class="bi bi-bag-check-fill" style="color:var(--color-primary)"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1" style="font-weight:700">Guest at Table <?php echo htmlspecialchars($order['table_number']); ?></h6>
                                    <p class="mb-0" style="color:var(--text-secondary);font-size:0.85rem">Ordered <?php echo $order['items_count']; ?> item(s)</p>
                                </div>
                                <div class="text-end" style="color:var(--text-tertiary);font-size:0.75rem">
                                    <i class="bi bi-clock"></i> <?php echo date('h:i A', strtotime($order['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Customer Reviews -->
        <div class="col-lg-7" id="reviews">
            <div class="d-flex align-items-center mb-4 gap-2">
                <i class="bi bi-star-fill text-warning fs-3"></i>
                <h3 style="font-family:var(--font-heading);font-weight:700;margin:0">Real Customer Reviews</h3>
            </div>

            <div class="row g-4">
                <?php if (empty($reviews)): ?>
                    <div class="col-12"><p class="text-muted">No reviews yet.</p></div>
                <?php else: ?>
                    <?php foreach ($reviews as $idx => $review): ?>
                        <div class="col-md-6">
                            <div class="review-card interactive-card" style="animation-delay: <?php echo $idx * 0.1; ?>s">
                                <div class="d-flex justify-content-between w-100 mb-2">
                                    <h6 style="font-weight:700;margin:0;color:var(--color-primary)">
                                        <?php echo htmlspecialchars($review['customer_name']); ?>
                                    </h6>
                                    <div class="text-warning" style="font-size:0.8rem">
                                        <?php for ($i=1; $i<=5; $i++): ?>
                                            <i class="bi <?php echo $i <= $review['rating'] ? 'bi-star-fill' : 'bi-star'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="mb-2 w-100" style="color:var(--text-secondary);font-size:0.9rem;font-style:italic">
                                    "<?php echo htmlspecialchars($review['comment']); ?>"
                                </p>
                                <div class="w-100" style="color:var(--text-tertiary);font-size:0.75rem">
                                    <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* Specific Styles for Activity Page */
.activity-feed {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.order-feed-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 1rem;
    animation: fadeUpIn 0.5s ease backwards;
}
.feed-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(230, 126, 34, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}
.review-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 1.25rem;
    height: 100%;
    animation: fadeUpIn 0.5s ease backwards;
}
@keyframes fadeUpIn {
    0% { opacity: 0; transform: translateY(20px); }
    100% { opacity: 1; transform: translateY(0); }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
