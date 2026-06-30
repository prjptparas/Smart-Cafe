<?php
/**
 * Smart Cafe - Menu Page
 * 
 * Full menu browsing with category filters, search, and sort.
 * Food items are loaded server-side initially, then can be
 * filtered dynamically via JavaScript.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

// Get selected category filter from URL
$selectedCategory = isset($_GET['category']) ? sanitizeInt($_GET['category']) : 0;

// Fetch all active categories
$stmtCats = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC");
$categories = $stmtCats->fetchAll();

// Build food query with optional category filter
$query = "SELECT f.*, c.name AS category_name FROM foods f 
          JOIN categories c ON f.category_id = c.id 
          WHERE f.is_available = 1";
$params = [];

if ($selectedCategory > 0) {
    $query .= " AND f.category_id = ?";
    $params[] = $selectedCategory;
}

$query .= " ORDER BY f.is_featured DESC, f.name ASC";

$stmtFoods = $pdo->prepare($query);
$stmtFoods->execute($params);
$foods = $stmtFoods->fetchAll();

$currentPage = 'menu';
$pageTitle = 'Menu';
$pageDescription = 'Browse our delicious menu with categories, search, and filters.';
include __DIR__ . '/../includes/header.php';
?>

<!-- ======== PAGE HEADER ======== -->
<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Menu</li>
        </ol>
    </nav>
    <h1 class="section-title">Our Menu</h1>
    <p class="section-subtitle">Discover dishes crafted with passion and fresh ingredients</p>
</div>

<section style="padding-bottom:4rem">
    <div class="container">
        <!-- ======== SEARCH BAR ======== -->
        <div class="search-container">
            <i class="bi bi-search search-icon"></i>
            <input type="text" class="search-input" id="menuSearch" placeholder="Search for dishes, drinks..." autocomplete="off">
            <button class="search-clear" id="searchClear"><i class="bi bi-x-lg"></i></button>
        </div>

        <!-- ======== CATEGORY CHIPS ======== -->
        <div class="category-scroll mb-4">
            <button class="category-chip <?php echo $selectedCategory === 0 ? 'active' : ''; ?>" 
                    onclick="filterByCategory(0, this)">
                <i class="bi bi-grid-fill chip-icon"></i> All
            </button>
            <?php foreach ($categories as $cat): ?>
            <button class="category-chip <?php echo $selectedCategory === (int)$cat['id'] ? 'active' : ''; ?>"
                    onclick="filterByCategory(<?php echo $cat['id']; ?>, this)">
                <i class="bi <?php echo sanitize($cat['icon']); ?> chip-icon"></i> 
                <?php echo sanitize($cat['name']); ?>
            </button>
            <?php endforeach; ?>
        </div>

        <!-- ======== FILTER BAR ======== -->
        <div class="filter-bar">
            <button class="filter-chip" id="filterVeg" onclick="toggleVegFilter(this)">
                <span class="veg-indicator veg"></span> Veg Only
            </button>
            <button class="filter-chip" id="filterNonVeg" onclick="toggleNonVegFilter(this)">
                <span class="veg-indicator nonveg"></span> Non-Veg
            </button>
            <div style="flex:1"></div>
            <select class="sort-select" id="sortSelect" onchange="sortMenu(this.value)">
                <option value="">Sort By</option>
                <option value="price_low">Price: Low to High</option>
                <option value="price_high">Price: High to Low</option>
                <option value="name">Name: A-Z</option>
                <option value="prep_time">Prep Time</option>
            </select>
            <span style="font-size:var(--font-size-sm);color:var(--text-tertiary)" id="itemCount">
                <?php echo count($foods); ?> items
            </span>
        </div>

        <!-- ======== FOOD GRID ======== -->
        <div class="food-grid" id="foodGrid">
            <?php if (empty($foods)): ?>
                <div class="empty-cart" style="grid-column: 1 / -1;">
                    <div class="empty-cart-icon"><i class="bi bi-basket"></i></div>
                    <h3>No items found</h3>
                    <p>Try selecting a different category or check back later.</p>
                </div>
            <?php else: ?>
                <?php foreach ($foods as $food): ?>
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
                <div class="food-card clickable-card" 
                     data-food-id="<?php echo $food['id']; ?>" 
                     data-category="<?php echo $food['category_id']; ?>"
                     data-veg="<?php echo $food['is_veg']; ?>"
                     data-price="<?php echo $food['price']; ?>"
                     data-name="<?php echo sanitize($food['name']); ?>"
                     data-prep="<?php echo $food['prep_time']; ?>"
                     onclick="openFoodModal(<?php echo $foodJson; ?>)">
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
                        <?php if ($food['is_featured']): ?>
                            <span class="badge-featured"><i class="bi bi-star-fill"></i> Popular</span>
                        <?php endif; ?>
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
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Search JS -->
<script src="<?php echo BASE_URL; ?>/assets/js/search.js"></script>

<script>
/**
 * Client-side filtering for categories, veg/nonveg, and sort.
 * Uses data attributes on the food cards already rendered by PHP.
 */

let activeCategory = <?php echo $selectedCategory; ?>;
let vegOnly = false;
let nonVegOnly = false;

/**
 * Filter food cards by category
 */
function filterByCategory(categoryId, btn) {
    activeCategory = categoryId;
    
    // Update active chip
    document.querySelectorAll('.category-chip').forEach(function(c) {
        c.classList.remove('active');
    });
    if (btn) btn.classList.add('active');

    applyFilters();
}

/**
 * Toggle vegetarian filter
 */
function toggleVegFilter(btn) {
    vegOnly = !vegOnly;
    if (vegOnly) nonVegOnly = false;
    
    btn.classList.toggle('active', vegOnly);
    document.getElementById('filterNonVeg').classList.remove('active');
    
    applyFilters();
}

/**
 * Toggle non-vegetarian filter
 */
function toggleNonVegFilter(btn) {
    nonVegOnly = !nonVegOnly;
    if (nonVegOnly) vegOnly = false;
    
    btn.classList.toggle('active', nonVegOnly);
    document.getElementById('filterVeg').classList.remove('active');
    
    applyFilters();
}

/**
 * Apply all active filters and sort
 */
function applyFilters() {
    const cards = document.querySelectorAll('.food-card');
    let visibleCount = 0;

    cards.forEach(function(card) {
        let show = true;

        // Category filter
        if (activeCategory > 0 && parseInt(card.dataset.category) !== activeCategory) {
            show = false;
        }

        // Veg filter
        if (vegOnly && card.dataset.veg !== '1') {
            show = false;
        }

        // Non-veg filter
        if (nonVegOnly && card.dataset.veg !== '0') {
            show = false;
        }

        card.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });

    // Update count
    document.getElementById('itemCount').textContent = visibleCount + ' item' + (visibleCount !== 1 ? 's' : '');

    // Show empty state if no items visible
    const grid = document.getElementById('foodGrid');
    let emptyState = grid.querySelector('.empty-filter-state');
    
    if (visibleCount === 0) {
        if (!emptyState) {
            emptyState = document.createElement('div');
            emptyState.className = 'empty-cart empty-filter-state';
            emptyState.style.gridColumn = '1 / -1';
            emptyState.innerHTML = 
                '<div class="empty-cart-icon"><i class="bi bi-funnel"></i></div>' +
                '<h3>No matching items</h3>' +
                '<p>Try adjusting your filters or search criteria.</p>';
            grid.appendChild(emptyState);
        }
        emptyState.style.display = '';
    } else if (emptyState) {
        emptyState.style.display = 'none';
    }
}

/**
 * Sort menu items
 */
function sortMenu(sortBy) {
    const grid = document.getElementById('foodGrid');
    const cards = Array.from(grid.querySelectorAll('.food-card'));

    cards.sort(function(a, b) {
        switch (sortBy) {
            case 'price_low':
                return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
            case 'price_high':
                return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
            case 'name':
                return a.dataset.name.localeCompare(b.dataset.name);
            case 'prep_time':
                return parseInt(a.dataset.prep) - parseInt(b.dataset.prep);
            default:
                return 0;
        }
    });

    cards.forEach(function(card) {
        grid.appendChild(card);
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
