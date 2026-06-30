<?php
/**
 * Admin Login Page
 * 
 * Handles admin authentication with rate limiting.
 * Redirects to dashboard if already logged in.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $result = loginAdmin($pdo, $username, $password);
        if ($result['success']) {
            $redirect = $_SESSION['redirect_after_login'] ?? 'dashboard.php';
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?php echo BASE_URL; ?>">
    <title>Admin Login | <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/variables.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/admin.css" rel="stylesheet">
    <script src="<?php echo BASE_URL; ?>/assets/js/theme.js"></script>
</head>
<body>

<div class="admin-login-page">
    <div class="login-card">
        <div class="text-center mb-3">
            <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Smart Cafe Logo" class="brand-logo-img-large" style="max-height: 80px; width: auto;">
        </div>
        <h1 class="login-title"><?php echo APP_NAME; ?></h1>
        <p class="login-subtitle">Admin Dashboard Login</p>

        <?php if ($error): ?>
        <div class="login-error show">
            <i class="bi bi-exclamation-circle-fill"></i>
            <span><?php echo sanitize($error); ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <div class="login-input-group">
                <i class="bi bi-person input-icon"></i>
                <input type="text" class="form-control" name="username" 
                       placeholder="Username" required autofocus
                       value="<?php echo isset($_POST['username']) ? sanitize($_POST['username']) : ''; ?>">
            </div>

            <div class="login-input-group">
                <i class="bi bi-lock input-icon"></i>
                <input type="password" class="form-control" name="password" 
                       placeholder="Password" required>
            </div>

            <button type="submit" class="btn-login" id="loginBtn">
                <i class="bi bi-box-arrow-in-right me-2"></i> Sign In
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="<?php echo BASE_URL; ?>/index.php" style="color:#9ca3b4;font-size:0.85rem;text-decoration:none">
                <i class="bi bi-arrow-left"></i> Back to Website
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
