<?php
/**
 * Smart Cafe - Admin Password Reset Utility
 * 
 * Run this ONCE in your browser to reset the admin password.
 * URL: http://localhost/smart-cafe/reset_password.php
 * 
 * DELETE THIS FILE AFTER USE for security!
 */

require_once __DIR__ . '/config/database.php';

$newPassword = 'Admin@123';
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
$stmt->execute([$hashedPassword]);

echo "<div style='font-family:Arial,sans-serif;max-width:500px;margin:50px auto;padding:30px;background:#f8f9fc;border-radius:12px;text-align:center;'>";
echo "<h2 style='color:#27ae60;'>✅ Password Reset Successful!</h2>";
echo "<p><strong>Username:</strong> admin</p>";
echo "<p><strong>Password:</strong> " . htmlspecialchars($newPassword) . "</p>";
echo "<br>";
echo "<p style='color:#e74c3c;font-weight:bold;'>⚠️ DELETE this file (reset_password.php) immediately for security!</p>";
echo "<br>";
echo "<a href='admin/index.php' style='background:#e67e22;color:#fff;padding:10px 24px;border-radius:8px;text-decoration:none;font-weight:600;'>Go to Admin Login →</a>";
echo "</div>";
?>
