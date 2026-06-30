<?php
/**
 * Admin Header Include
 * 
 * Contains HTML head, sidebar navigation, and top bar.
 * Requires admin login. Included at top of every admin page.
 * 
 * Variables:
 *   $adminPage - Current page identifier (e.g., 'dashboard', 'foods')
 *   $adminPageTitle - Page title for display
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

// Require admin login
requireAdminLogin();

$adminPage = isset($adminPage) ? $adminPage : '';
$adminPageTitle = isset($adminPageTitle) ? $adminPageTitle : 'Dashboard';

// Count pending orders for badge
$pendingCount = $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('pending','confirmed')")->fetchColumn();
// Count unread feedback
$unreadFeedback = $pdo->query("SELECT COUNT(*) FROM feedback WHERE is_read = 0")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?php echo BASE_URL; ?>">
    <title><?php echo sanitize($adminPageTitle); ?> | <?php echo APP_NAME; ?> Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/variables.css?v=1.1" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/style.css?v=1.1" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/admin.css?v=1.1" rel="stylesheet">
    <script src="<?php echo BASE_URL; ?>/assets/js/theme.js"></script>
</head>
<body>

<div class="admin-wrapper">
    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ======== SIDEBAR ======== -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Smart Cafe Logo" class="brand-logo-img" style="max-height: 40px; width: auto;">
            <div class="sidebar-brand">
                <?php echo APP_NAME; ?>
                <small>Admin Panel</small>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section">Main</div>
            <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="sidebar-link <?php echo $adminPage === 'dashboard' ? 'active' : ''; ?>">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>

            <div class="sidebar-section">Management</div>
            <a href="<?php echo BASE_URL; ?>/admin/orders.php" class="sidebar-link <?php echo $adminPage === 'orders' ? 'active' : ''; ?>">
                <i class="bi bi-receipt"></i> Orders
                <?php if ($pendingCount > 0): ?>
                <span class="badge bg-warning text-dark"><?php echo $pendingCount; ?></span>
                <?php endif; ?>
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/foods.php" class="sidebar-link <?php echo $adminPage === 'foods' ? 'active' : ''; ?>">
                <i class="bi bi-egg-fried"></i> Food Items
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/categories.php" class="sidebar-link <?php echo $adminPage === 'categories' ? 'active' : ''; ?>">
                <i class="bi bi-tags"></i> Categories
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/feedback.php" class="sidebar-link <?php echo $adminPage === 'feedback' ? 'active' : ''; ?>">
                <i class="bi bi-chat-heart"></i> Feedback
                <?php if ($unreadFeedback > 0): ?>
                <span class="badge bg-info"><?php echo $unreadFeedback; ?></span>
                <?php endif; ?>
            </a>

            <div class="sidebar-section">System</div>
            <a href="<?php echo BASE_URL; ?>/admin/settings.php" class="sidebar-link <?php echo $adminPage === 'settings' ? 'active' : ''; ?>">
                <i class="bi bi-gear"></i> Settings
            </a>
            <a href="<?php echo BASE_URL; ?>/qr/generate.php" class="sidebar-link" target="_blank">
                <i class="bi bi-qr-code"></i> QR Codes
            </a>
            <a href="<?php echo BASE_URL; ?>/index.php" class="sidebar-link" target="_blank">
                <i class="bi bi-globe"></i> View Website
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-avatar">
                    <?php echo strtoupper(substr(getAdminName(), 0, 1)); ?>
                </div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name"><?php echo sanitize(getAdminName()); ?></div>
                    <div class="sidebar-user-role">Administrator</div>
                </div>
                <a href="<?php echo BASE_URL; ?>/api/admin/logout.php" class="btn-icon" title="Logout" style="border:none">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- ======== MAIN CONTENT ======== -->
    <div class="admin-main">
        <div class="admin-topbar">
            <div>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="admin-page-title"><?php echo sanitize($adminPageTitle); ?></h1>
            </div>
            <div class="topbar-actions">
                <button class="theme-toggle" id="themeToggle" title="Toggle Theme">
                    <i class="bi bi-moon-fill"></i>
                </button>
            </div>
        </div>
