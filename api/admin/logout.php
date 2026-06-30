<?php
/**
 * Admin API: Logout
 * Destroys session and redirects to login page.
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/auth.php';

logoutAdmin();
header('Location: ' . BASE_URL . '/admin/index.php');
exit;
