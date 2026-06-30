<?php
/**
 * Utility Functions
 * 
 * Shared helper functions used across the application.
 * Includes sanitization, formatting, CSRF, and more.
 */

// ---- Input Sanitization ----

/**
 * Sanitize a string for safe HTML output (prevents XSS)
 */
function sanitize($value) {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize an integer input
 */
function sanitizeInt($value) {
    return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
}

/**
 * Sanitize a decimal/float input
 */
function sanitizeFloat($value) {
    return (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

// ---- CSRF Protection ----

/**
 * Generate a CSRF token and store it in the session
 */
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate a CSRF token against the session token
 */
function validateCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Output a hidden CSRF input field for forms
 */
function csrfField() {
    $token = generateCSRFToken();
    echo '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

// ---- Formatting ----

/**
 * Format price with currency symbol
 */
function formatPrice($amount) {
    return CURRENCY_SYMBOL . ' ' . number_format((float)$amount, 2);
}

/**
 * Format a date for display
 */
function formatDate($dateString) {
    return date('d M Y, h:i A', strtotime($dateString));
}

/**
 * Format a date as relative time (e.g., "5 minutes ago")
 */
function timeAgo($dateString) {
    $timestamp = strtotime($dateString);
    $diff = time() - $timestamp;

    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hr ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('d M Y', $timestamp);
}

// ---- Order Number Generation ----

/**
 * Generate a unique order number like SC-20260628-001
 */
function generateOrderNumber($pdo) {
    $prefix = ORDER_PREFIX;
    $date = date('Ymd');
    
    // Find the last order number for today
    $stmt = $pdo->prepare("SELECT order_number FROM orders WHERE order_number LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$prefix . '-' . $date . '-%']);
    $lastOrder = $stmt->fetchColumn();
    
    if ($lastOrder) {
        // Extract the sequence number and increment
        $parts = explode('-', $lastOrder);
        $sequence = (int) end($parts) + 1;
    } else {
        $sequence = 1;
    }
    
    return $prefix . '-' . $date . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
}

// ---- Status Badge Helper ----

/**
 * Return Bootstrap badge class based on order status
 */
function getStatusBadge($status) {
    $badges = [
        'pending'   => 'bg-warning text-dark',
        'confirmed' => 'bg-info text-white',
        'preparing' => 'bg-primary',
        'ready'     => 'bg-success',
        'delivered' => 'bg-secondary',
        'cancelled' => 'bg-danger',
    ];
    return $badges[$status] ?? 'bg-secondary';
}

/**
 * Return a readable status label
 */
function getStatusLabel($status) {
    return ucfirst($status);
}

// ---- Image Handling ----

/**
 * Get the full URL for a food image
 */
function getFoodImageUrl($imageName) {
    if (empty($imageName) || $imageName === 'default-food.jpg') {
        return BASE_URL . '/assets/images/default-food.jpg';
    }
    // Check if file exists in uploads
    $uploadedPath = ROOT_PATH . '/admin/uploads/' . $imageName;
    if (file_exists($uploadedPath)) {
        return BASE_URL . '/admin/uploads/' . $imageName;
    }
    // Fall back to assets/images/food
    return BASE_URL . '/assets/images/food/' . $imageName;
}

/**
 * Handle file upload for food images
 * Returns the saved filename or false on failure
 */
function handleImageUpload($file) {
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error.'];
    }
    
    // Validate file size
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'message' => 'File too large. Maximum size is 2MB.'];
    }
    
    // Validate file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and WebP are allowed.'];
    }
    
    // Create upload directory if it doesn't exist
    if (!is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0755, true);
    }
    
    // Generate a unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'food_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    
    // Move the uploaded file
    $destination = UPLOAD_PATH . $filename;
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'Failed to save uploaded file.'];
}

// ---- JSON Response Helper ----

/**
 * Send a JSON response and exit
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// ---- Redirect Helper ----

/**
 * Redirect to a URL and exit
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

// ---- Settings Helper ----

/**
 * Get a setting value from the database
 */
function getSetting($pdo, $key, $default = '') {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $value = $stmt->fetchColumn();
    return $value !== false ? $value : $default;
}

/**
 * Get all settings as an associative array
 */
function getAllSettings($pdo) {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

// ---- Validation Helpers ----

/**
 * Validate a phone number (basic Indian format)
 */
function isValidPhone($phone) {
    return preg_match('/^[6-9]\d{9}$/', preg_replace('/[\s\-\+]/', '', $phone));
}

/**
 * Validate an email address
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Check if a string is not empty after trimming
 */
function isNotEmpty($value) {
    return !empty(trim($value));
}
