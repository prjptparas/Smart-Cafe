<?php
/**
 * Smart Cafe - Home Page (Landing)
 * 
 * The main entry point for customers.
 * Shows hero section, featured items, and category navigation.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/includes/functions.php';

// Fetch featured food items
$stmtFeatured = $pdo->prepare("
    SELECT f.*, c.name AS category_name 
    FROM foods f 
    JOIN categories c ON f.category_id = c.id 
    WHERE f.is_featured = 1 AND f.is_available = 1 
    ORDER BY f.id DESC LIMIT 8
");
$stmtFeatured->execute();
$featuredItems = $stmtFeatured->fetchAll();

// Fetch active categories
$stmtCats = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC");
$categories = $stmtCats->fetchAll();

// Stats for hero section
$totalFoods = $pdo->query("SELECT COUNT(*) FROM foods WHERE is_available = 1")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$avgRating = $pdo->query("SELECT ROUND(AVG(rating), 1) FROM feedback")->fetchColumn() ?: '4.8';

$currentPage = 'home';
$pageTitle = 'Welcome';
include __DIR__ . '/includes/header.php';
?>

<!-- ======== HERO SECTION ======== -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-badge">
                    <i class="bi bi-qr-code-scan"></i>
                    Scan &middot; Order &middot; Enjoy
                </div>

                <h1 class="hero-title">
                    Delicious Food,<br>
                    <span class="gradient-text">Smart Ordering</span>
                </h1>

                <p class="hero-description">
                    Simply scan the QR code on your table, browse our carefully crafted menu, 
                    and place your order instantly. No waiting, no hassle.
                </p>

                <div class="hero-actions">
                    <a href="<?php echo BASE_URL; ?>/pages/menu.php" class="btn-primary-smart btn-lg-smart">
                        <i class="bi bi-grid-fill"></i> Browse Menu
                    </a>
                    <a href="<?php echo BASE_URL; ?>/pages/track-order.php" class="btn-outline-smart btn-lg-smart">
                        <i class="bi bi-geo-alt"></i> Track Order
                    </a>
                </div>

                <div class="hero-stats">
                    <a href="<?php echo BASE_URL; ?>/pages/menu.php" class="hero-stat interactive-card">
                        <div class="stat-number"><?php echo $totalFoods; ?>+</div>
                        <div class="stat-label">Menu Items</div>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/pages/activity.php" class="hero-stat interactive-card">
                        <div class="stat-number"><?php echo $totalOrders; ?>+</div>
                        <div class="stat-label">Orders Served</div>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/pages/activity.php#reviews" class="hero-stat interactive-card">
                        <div class="stat-number"><?php echo $avgRating; ?> <i class="bi bi-star-fill" style="font-size:0.8em"></i></div>
                        <div class="stat-label">Avg Rating</div>
                    </a>
                </div>
            </div>

            <div class="col-lg-6 d-none d-lg-block">
                <div class="hero-visual">
                    <div class="hero-food-card">
                        <div class="text-center p-4">
                            <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Smart Cafe Logo" class="brand-logo-img-large" style="max-height: 120px; width: auto; margin-bottom: 2rem;">
                            <h3 style="font-family:var(--font-heading);margin-top:1rem;font-weight:700">Your Table is Ready</h3>
                            <p style="color:var(--text-secondary)">Scan the QR code to start ordering</p>
                        </div>
                    </div>

                    <!-- Floating badges for visual interest -->
                    <a href="<?php echo BASE_URL; ?>/pages/menu.php?featured=1" class="floating-badge badge-1 interactive-badge">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-fire" style="color:var(--color-primary)"></i>
                            <div>
                                <div style="font-weight:700;font-size:0.85rem">Popular</div>
                                <div style="font-size:0.7rem;color:var(--text-tertiary)">Trending Now</div>
                            </div>
                        </div>
                    </a>

                    <a href="<?php echo BASE_URL; ?>/pages/activity.php" class="floating-badge badge-2 interactive-badge">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-clock" style="color:var(--color-accent)"></i>
                            <div>
                                <div style="font-weight:700;font-size:0.85rem">15 min</div>
                                <div style="font-size:0.7rem;color:var(--text-tertiary)">Avg Prep Time</div>
                            </div>
                        </div>
                    </a>

                    <a href="<?php echo BASE_URL; ?>/pages/activity.php#reviews" class="floating-badge badge-3 interactive-badge">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-star-fill" style="color:#f39c12"></i>
                            <div>
                                <div style="font-weight:700;font-size:0.85rem"><?php echo $avgRating; ?>/5</div>
                                <div style="font-size:0.7rem;color:var(--text-tertiary)">Customer Rating</div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ======== CATEGORIES SECTION ======== -->
<section class="category-section">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title">Explore Our Menu</h2>
            <p class="section-subtitle">Choose from a variety of categories</p>
        </div>

        <div class="row g-3 justify-content-center">
            <?php foreach ($categories as $cat): ?>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?php echo BASE_URL; ?>/pages/menu.php?category=<?php echo $cat['id']; ?>" class="glass-card text-center d-block text-decoration-none" style="padding:1.5rem 1rem">
                    <div style="font-size:2rem;margin-bottom:0.5rem">
                        <i class="bi <?php echo sanitize($cat['icon']); ?>" style="color:var(--color-primary)"></i>
                    </div>
                    <h6 style="font-weight:600;color:var(--text-primary);margin:0;font-size:0.9rem"><?php echo sanitize($cat['name']); ?></h6>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ======== FEATURED ITEMS ======== -->
<?php if (!empty($featuredItems)): ?>
<section style="padding:2rem 0 4rem">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title">
                <i class="bi bi-star-fill" style="color:#f39c12;font-size:0.8em"></i> 
                Popular Dishes
            </h2>
            <p class="section-subtitle">Our most loved items by customers</p>
        </div>

        <div class="food-grid">
            <?php foreach ($featuredItems as $food): ?>
            <?php 
                // Prepare food object for JS modal
                $foodForModal = [
                    'id' => $food['id'],
                    'name' => $food['name'],
                    'price' => $food['price'],
                    'description' => $food['description'],
                    'category' => $food['category_name'],
                    'image' => getFoodImageUrl($food['image'])
                ];
                $foodJson = htmlspecialchars(json_encode($foodForModal), ENT_QUOTES, 'UTF-8');
            ?>
            <div class="food-card clickable-card" data-food-id="<?php echo $food['id']; ?>" onclick="openFoodModal(<?php echo $foodJson; ?>)">
                <div class="food-card-image">
                    <img src="<?php echo getFoodImageUrl($food['image']); ?>" 
                         alt="<?php echo sanitize($food['name']); ?>"
                         loading="lazy"
                         onerror="this.src='<?php echo BASE_URL; ?>/assets/images/default-food.jpg'">
                    <div class="food-card-badges">
                        <?php if ($food['is_veg']): ?>
                            <span class="badge-veg" title="Vegetarian"><i class="bi bi-circle-fill" style="font-size:0.5rem"></i></span>
                        <?php else: ?>
                            <span class="badge-nonveg" title="Non-Vegetarian"><i class="bi bi-triangle-fill" style="font-size:0.5rem"></i></span>
                        <?php endif; ?>
                    </div>
                    <span class="badge-featured"><i class="bi bi-star-fill"></i> Popular</span>
                </div>
                <div class="food-card-body">
                    <div class="food-card-category"><?php echo sanitize($food['category_name']); ?></div>
                    <h3 class="food-card-name"><?php echo sanitize($food['name']); ?></h3>
                    <p class="food-card-desc"><?php echo sanitize($food['description']); ?></p>
                    <div class="food-card-footer">
                        <div>
                            <span class="food-card-price"><?php echo formatPrice($food['price']); ?></span>
                            <div class="food-card-meta">
                                <i class="bi bi-clock"></i> <?php echo (int)$food['prep_time']; ?> min
                            </div>
                        </div>
                        <div class="cart-controls" 
                             data-food-id="<?php echo $food['id']; ?>" 
                             data-food-name="<?php echo sanitize($food['name']); ?>" 
                             data-food-price="<?php echo $food['price']; ?>" 
                             data-food-image="<?php echo getFoodImageUrl($food['image']); ?>"
                             onclick="event.stopPropagation();">
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <a href="<?php echo BASE_URL; ?>/pages/menu.php" class="btn-outline-smart">
                View Full Menu <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ======== HOW IT WORKS ======== -->
<section style="padding:4rem 0;background:var(--bg-secondary)">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title">How It Works</h2>
            <p class="section-subtitle">Ordering food has never been easier</p>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="glass-card text-center animate-on-scroll">
                    <div style="width:60px;height:60px;background:rgba(var(--color-primary-rgb),0.1);border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.5rem;color:var(--color-primary)">
                        <i class="bi bi-qr-code-scan"></i>
                    </div>
                    <h5 style="font-family:var(--font-heading);font-weight:700">1. Scan QR Code</h5>
                    <p style="color:var(--text-secondary);font-size:0.9rem">Scan the QR code placed on your table to open the menu automatically.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card text-center animate-on-scroll">
                    <div style="width:60px;height:60px;background:rgba(26,188,156,0.1);border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.5rem;color:var(--color-accent)">
                        <i class="bi bi-bag-check"></i>
                    </div>
                    <h5 style="font-family:var(--font-heading);font-weight:700">2. Browse & Order</h5>
                    <p style="color:var(--text-secondary);font-size:0.9rem">Browse the menu, add items to your cart, and place your order with a tap.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card text-center animate-on-scroll">
                    <div style="width:60px;height:60px;background:rgba(52,152,219,0.1);border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.5rem;color:var(--color-info)">
                        <i class="bi bi-emoji-smile"></i>
                    </div>
                    <h5 style="font-family:var(--font-heading);font-weight:700">3. Enjoy Your Meal</h5>
                    <p style="color:var(--text-secondary);font-size:0.9rem">Track your order in real-time and enjoy freshly prepared food at your table.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
