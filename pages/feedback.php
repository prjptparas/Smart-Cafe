<?php
/**
 * Smart Cafe - Feedback Page
 * 
 * Customers submit star ratings and comments, optionally
 * linked to an order number.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

$orderNumber = isset($_GET['order']) ? sanitize($_GET['order']) : '';
$submitted = false;
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? sanitize($_POST['name']) : 'Anonymous';
    $rating = isset($_POST['rating']) ? sanitizeInt($_POST['rating']) : 0;
    $comment = isset($_POST['comment']) ? sanitize($_POST['comment']) : '';
    $orderNum = isset($_POST['order_number']) ? sanitize($_POST['order_number']) : '';
    
    // Validate
    if ($rating < 1 || $rating > 5) {
        $error = 'Please select a rating between 1 and 5 stars.';
    } else {
        // Find order_id if order number provided
        $orderId = null;
        if ($orderNum) {
            $stmtOrder = $pdo->prepare("SELECT id FROM orders WHERE order_number = ?");
            $stmtOrder->execute([$orderNum]);
            $orderId = $stmtOrder->fetchColumn();
        }
        
        $stmt = $pdo->prepare("INSERT INTO feedback (order_id, customer_name, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$orderId, $name ?: 'Anonymous', $rating, $comment]);
        $submitted = true;
    }
}

$currentPage = 'feedback';
$pageTitle = 'Feedback';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
            <li class="breadcrumb-item active">Feedback</li>
        </ol>
    </nav>
    <h1 class="section-title"><i class="bi bi-chat-heart"></i> Your Feedback</h1>
    <p class="section-subtitle">We value your opinion. Help us improve!</p>
</div>

<section class="feedback-section">
    <div class="container" style="max-width:600px">
        <?php if ($submitted): ?>
        <!-- Success Message -->
        <div class="glass-card text-center" style="animation:scaleIn 0.5s ease both">
            <div class="success-icon" style="width:80px;height:80px;margin:0 auto 1.5rem">
                <i class="bi bi-heart-fill" style="font-size:2rem"></i>
            </div>
            <h3 style="font-family:var(--font-heading);font-weight:700">Thank You!</h3>
            <p style="color:var(--text-secondary);margin-bottom:1.5rem">
                Your feedback has been submitted successfully. We truly appreciate you taking the time to share your experience.
            </p>
            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <a href="<?php echo BASE_URL; ?>/pages/menu.php" class="btn-primary-smart">
                    <i class="bi bi-grid"></i> Back to Menu
                </a>
                <a href="<?php echo BASE_URL; ?>/index.php" class="btn-outline-smart">
                    <i class="bi bi-house"></i> Home
                </a>
            </div>
        </div>
        <?php else: ?>
        <!-- Feedback Form -->
        <div class="glass-card" style="animation:fadeInUp 0.5s ease both">
            <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2" role="alert" style="border-radius:var(--radius-md)">
                <i class="bi bi-exclamation-circle-fill"></i>
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="" id="feedbackForm">
                <!-- Star Rating -->
                <div class="text-center mb-4">
                    <h5 style="font-family:var(--font-heading);font-weight:700;margin-bottom:1rem">How was your experience?</h5>
                    <div class="star-rating" id="starRating">
                        <i class="bi bi-star star" data-rating="1" onclick="setRating(1)"></i>
                        <i class="bi bi-star star" data-rating="2" onclick="setRating(2)"></i>
                        <i class="bi bi-star star" data-rating="3" onclick="setRating(3)"></i>
                        <i class="bi bi-star star" data-rating="4" onclick="setRating(4)"></i>
                        <i class="bi bi-star star" data-rating="5" onclick="setRating(5)"></i>
                    </div>
                    <input type="hidden" name="rating" id="ratingInput" value="0" required>
                    <p style="color:var(--text-tertiary);font-size:0.85rem;margin-top:0.5rem" id="ratingText">Tap a star to rate</p>
                </div>

                <!-- Name -->
                <div class="mb-3">
                    <label class="form-label" for="feedbackName" style="font-weight:600">Your Name</label>
                    <input type="text" class="form-control" name="name" id="feedbackName" 
                           placeholder="Enter your name" 
                           style="background:var(--bg-card);border:2px solid var(--border-color);border-radius:var(--radius-md);padding:12px 16px;color:var(--text-primary)">
                </div>

                <!-- Order Number (optional) -->
                <div class="mb-3">
                    <label class="form-label" for="feedbackOrder" style="font-weight:600">Order Number (Optional)</label>
                    <input type="text" class="form-control" name="order_number" id="feedbackOrder" 
                           placeholder="e.g., SC-20260628-001" value="<?php echo sanitize($orderNumber); ?>"
                           style="background:var(--bg-card);border:2px solid var(--border-color);border-radius:var(--radius-md);padding:12px 16px;color:var(--text-primary)">
                </div>

                <!-- Comment -->
                <div class="mb-4">
                    <label class="form-label" for="feedbackComment" style="font-weight:600">Your Message</label>
                    <textarea class="form-control" name="comment" id="feedbackComment" rows="4" 
                              placeholder="Share your thoughts about the food, service, or experience..."
                              style="background:var(--bg-card);border:2px solid var(--border-color);border-radius:var(--radius-md);padding:12px 16px;color:var(--text-primary)"></textarea>
                </div>

                <button type="submit" class="btn-primary-smart w-100 justify-content-center" id="submitFeedback">
                    <i class="bi bi-send"></i> Submit Feedback
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
let selectedRating = 0;
const ratingLabels = ['', 'Poor', 'Below Average', 'Good', 'Very Good', 'Excellent'];

function setRating(rating) {
    selectedRating = rating;
    document.getElementById('ratingInput').value = rating;
    document.getElementById('ratingText').textContent = ratingLabels[rating];
    
    document.querySelectorAll('.star-rating .star').forEach(function(star) {
        const starRating = parseInt(star.dataset.rating);
        if (starRating <= rating) {
            star.className = 'bi bi-star-fill star active';
        } else {
            star.className = 'bi bi-star star';
        }
    });
}

// Form validation
document.getElementById('feedbackForm')?.addEventListener('submit', function(e) {
    if (selectedRating === 0) {
        e.preventDefault();
        showToast('Rating Required', 'Please select a star rating', 'error');
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
