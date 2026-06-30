<?php
/**
 * Authentication & Session Helpers
 * 
 * Handles session management and admin authentication.
 * Must be included at the top of any page requiring auth.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Secure session configuration
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

/**
 * Check if admin is currently logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Require admin login - redirect to login page if not authenticated
 */
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        // Store the intended destination for after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . getAdminBaseUrl() . '/index.php');
        exit;
    }
    
    // Regenerate session ID periodically for security
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) {
        // Regenerate every 5 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

/**
 * Login an admin user
 */
function loginAdmin($pdo, $username, $password) {
    // Rate limiting: check failed attempts
    if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
        $lockoutTime = $_SESSION['lockout_time'] ?? 0;
        if (time() - $lockoutTime < 300) { // 5 minute lockout
            return ['success' => false, 'message' => 'Too many failed attempts. Please try again in 5 minutes.'];
        }
        // Reset after lockout period
        unset($_SESSION['login_attempts']);
        unset($_SESSION['lockout_time']);
    }
    
    // Look up admin by username
    $stmt = $pdo->prepare("SELECT id, username, password, full_name, email FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password'])) {
        // Successful login - regenerate session ID
        session_regenerate_id(true);
        
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['last_regeneration'] = time();
        
        // Reset login attempts
        unset($_SESSION['login_attempts']);
        unset($_SESSION['lockout_time']);
        
        return ['success' => true, 'message' => 'Login successful!'];
    }
    
    // Failed login - increment attempt counter
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
    if ($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
        $_SESSION['lockout_time'] = time();
    }
    
    return ['success' => false, 'message' => 'Invalid username or password.'];
}

/**
 * Logout the current admin
 */
function logoutAdmin() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Get the admin panel base URL
 */
function getAdminBaseUrl() {
    // Calculate from constants if available
    if (defined('BASE_URL')) {
        return BASE_URL . '/admin';
    }
    // Fallback: determine from current script
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $protocol . '://' . $host . '/smart-cafe/admin';
}

/**
 * Get current admin's display name
 */
function getAdminName() {
    return $_SESSION['admin_name'] ?? 'Admin';
}

/**
 * Get current admin's ID
 */
function getAdminId() {
    return $_SESSION['admin_id'] ?? 0;
}
