<?php
/**
 * Customer Header Include
 * 
 * Contains the HTML head, navbar, and opening body tag.
 * Included at the top of every customer-facing page.
 * 
 * Variables available:
 *   $pageTitle - Page title (optional, defaults to APP_NAME)
 *   $pageDescription - Meta description (optional)
 *   $currentPage - Current nav item to highlight (e.g., 'menu', 'cart')
 */

require_once __DIR__ . '/../config/constants.php';

$pageTitle = isset($pageTitle) ? $pageTitle . ' | ' . APP_NAME : APP_NAME . ' - ' . APP_TAGLINE;
$pageDescription = isset($pageDescription) ? $pageDescription : 'Order delicious food online at ' . APP_NAME . '. Browse our menu, add to cart, and enjoy!';
$currentPage = isset($currentPage) ? $currentPage : '';
$tableNumber = isset($_GET['table']) ? (int)$_GET['table'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta name="base-url" content="<?php echo BASE_URL; ?>">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <link href="<?php echo BASE_URL; ?>/assets/css/variables.css?v=1.1" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/style.css?v=1.1" rel="stylesheet">

    <!-- Theme Script (load early to prevent flash) -->
    <script src="<?php echo BASE_URL; ?>/assets/js/theme.js"></script>
</head>
<body>

<!-- ======== NAVBAR ======== -->
<nav class="navbar-smart">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between w-100">
            <!-- Brand -->
            <a href="<?php echo BASE_URL; ?>/index.php" class="navbar-brand-smart">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Smart Cafe Logo" class="brand-logo-img" style="max-height: 40px; width: auto;">
                <?php echo APP_NAME; ?>
            </a>

            <!-- Nav Links (Desktop) -->
            <div class="d-none d-md-flex align-items-center gap-1">
                <a href="<?php echo BASE_URL; ?>/index.php" class="nav-link-smart <?php echo $currentPage === 'home' ? 'active' : ''; ?>">
                    <i class="bi bi-house"></i> Home
                </a>
                <a href="<?php echo BASE_URL; ?>/pages/menu.php" class="nav-link-smart <?php echo $currentPage === 'menu' ? 'active' : ''; ?>">
                    <i class="bi bi-grid"></i> Menu
                </a>
                <a href="<?php echo BASE_URL; ?>/pages/track-order.php" class="nav-link-smart <?php echo $currentPage === 'track' ? 'active' : ''; ?>">
                    <i class="bi bi-geo-alt"></i> Track Order
                </a>
                <a href="<?php echo BASE_URL; ?>/pages/feedback.php" class="nav-link-smart <?php echo $currentPage === 'feedback' ? 'active' : ''; ?>">
                    <i class="bi bi-chat-heart"></i> Feedback
                </a>
            </div>

            <!-- Right Actions -->
            <div class="d-flex align-items-center gap-2">
                <?php if ($tableNumber || isset($_COOKIE['smartcafe_table'])): ?>
                <span class="table-indicator d-none d-sm-inline-flex">
                    <i class="bi bi-qr-code"></i> Table <?php echo $tableNumber ?: ($_COOKIE['smartcafe_table'] ?? ''); ?>
                </span>
                <?php endif; ?>

                <!-- Theme Toggle -->
                <button class="theme-toggle" id="themeToggle" title="Toggle Dark Mode" aria-label="Toggle theme">
                    <i class="bi bi-moon-fill"></i>
                </button>

                <!-- Cart -->
                <a href="<?php echo BASE_URL; ?>/pages/cart.php" class="nav-link-smart position-relative <?php echo $currentPage === 'cart' ? 'active' : ''; ?>" title="Cart">
                    <i class="bi bi-bag" style="font-size:1.2rem"></i>
                    <span class="cart-badge" id="cartBadge">0</span>
                </a>

                <!-- Mobile Menu Toggle -->
                <button class="theme-toggle d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-label="Open menu">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>
    </div>
</nav>

<!-- Mobile Offcanvas Nav -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="mobileNav" style="background:var(--bg-secondary)">
    <div class="offcanvas-header border-bottom" style="border-color:var(--border-color)!important">
        <h5 class="offcanvas-title" style="font-family:var(--font-heading);font-weight:700;color:var(--text-primary)"><?php echo APP_NAME; ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close" style="filter:var(--text-primary)"></button>
    </div>
    <div class="offcanvas-body">
        <div class="d-flex flex-column gap-2">
            <a href="<?php echo BASE_URL; ?>/index.php" class="nav-link-smart <?php echo $currentPage === 'home' ? 'active' : ''; ?>">
                <i class="bi bi-house me-2"></i> Home
            </a>
            <a href="<?php echo BASE_URL; ?>/pages/menu.php" class="nav-link-smart <?php echo $currentPage === 'menu' ? 'active' : ''; ?>">
                <i class="bi bi-grid me-2"></i> Menu
            </a>
            <a href="<?php echo BASE_URL; ?>/pages/cart.php" class="nav-link-smart <?php echo $currentPage === 'cart' ? 'active' : ''; ?>">
                <i class="bi bi-bag me-2"></i> Cart
            </a>
            <a href="<?php echo BASE_URL; ?>/pages/track-order.php" class="nav-link-smart <?php echo $currentPage === 'track' ? 'active' : ''; ?>">
                <i class="bi bi-geo-alt me-2"></i> Track Order
            </a>
            <a href="<?php echo BASE_URL; ?>/pages/feedback.php" class="nav-link-smart <?php echo $currentPage === 'feedback' ? 'active' : ''; ?>">
                <i class="bi bi-chat-heart me-2"></i> Feedback
            </a>
        </div>
    </div>
</div>

<!-- Main Content Start -->
<main>
