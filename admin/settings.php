<?php
/**
 * Admin - Settings Page
 * 
 * Manage app-wide settings stored in the settings table.
 */

$adminPage = 'settings';
$adminPageTitle = 'Settings';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$message = '';
$messageType = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settingsToUpdate = [
        'cafe_name', 'cafe_tagline', 'cafe_phone', 'cafe_email', 
        'cafe_address', 'tax_rate', 'currency_symbol', 'total_tables',
        'order_prefix', 'min_order_amount'
    ];
    
    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
    
    foreach ($settingsToUpdate as $key) {
        if (isset($_POST[$key])) {
            $value = trim($_POST[$key]);
            $stmt->execute([$value, $key]);
        }
    }

    // Handle password change
    if (!empty($_POST['new_password'])) {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Verify current password
        $adminId = getAdminId();
        $admin = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
        $admin->execute([$adminId]);
        $adminData = $admin->fetch();

        if (!$adminData || !password_verify($currentPassword, $adminData['password'])) {
            $message = 'Current password is incorrect.';
            $messageType = 'danger';
        } elseif ($newPassword !== $confirmPassword) {
            $message = 'New passwords do not match.';
            $messageType = 'danger';
        } elseif (strlen($newPassword) < 6) {
            $message = 'Password must be at least 6 characters.';
            $messageType = 'danger';
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?")->execute([$hashedPassword, $adminId]);
            $message = 'Settings and password updated successfully!';
            $messageType = 'success';
        }
    }
    
    if (empty($message)) {
        $message = 'Settings updated successfully!';
        $messageType = 'success';
    }
}

// Fetch current settings
$settings = getAllSettings($pdo);

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?> d-flex align-items-center gap-2 alert-dismissible fade show" role="alert" style="border-radius:var(--radius-md)">
    <i class="bi <?php echo $messageType === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill'; ?>"></i>
    <?php echo sanitize($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST" action="">
    <div class="row g-4">
        <!-- General Settings -->
        <div class="col-lg-6">
            <div class="admin-form-card">
                <h5 style="font-family:var(--font-heading);font-weight:700;margin-bottom:1.5rem">
                    <i class="bi bi-building" style="color:var(--color-primary)"></i> Cafe Information
                </h5>
                <div class="mb-3">
                    <label class="form-label">Cafe Name</label>
                    <input type="text" class="form-control" name="cafe_name" value="<?php echo sanitize($settings['cafe_name'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Tagline</label>
                    <input type="text" class="form-control" name="cafe_tagline" value="<?php echo sanitize($settings['cafe_tagline'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" class="form-control" name="cafe_phone" value="<?php echo sanitize($settings['cafe_phone'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="cafe_email" value="<?php echo sanitize($settings['cafe_email'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea class="form-control" name="cafe_address" rows="2"><?php echo sanitize($settings['cafe_address'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Business Settings -->
        <div class="col-lg-6">
            <div class="admin-form-card mb-4">
                <h5 style="font-family:var(--font-heading);font-weight:700;margin-bottom:1.5rem">
                    <i class="bi bi-gear" style="color:var(--color-primary)"></i> Business Settings
                </h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Tax Rate (%)</label>
                        <input type="number" class="form-control" name="tax_rate" step="0.1" min="0" max="30" value="<?php echo sanitize($settings['tax_rate'] ?? '5'); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Currency Symbol</label>
                        <input type="text" class="form-control" name="currency_symbol" value="<?php echo sanitize($settings['currency_symbol'] ?? '₹'); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Total Tables</label>
                        <input type="number" class="form-control" name="total_tables" min="1" max="100" value="<?php echo sanitize($settings['total_tables'] ?? '20'); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Order Prefix</label>
                        <input type="text" class="form-control" name="order_prefix" value="<?php echo sanitize($settings['order_prefix'] ?? 'SC'); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Min Order Amount</label>
                        <input type="number" class="form-control" name="min_order_amount" min="0" value="<?php echo sanitize($settings['min_order_amount'] ?? '50'); ?>">
                    </div>
                </div>
            </div>

            <!-- Password Change -->
            <div class="admin-form-card">
                <h5 style="font-family:var(--font-heading);font-weight:700;margin-bottom:1.5rem">
                    <i class="bi bi-shield-lock" style="color:var(--color-primary)"></i> Change Password
                </h5>
                <div class="mb-3">
                    <label class="form-label">Current Password</label>
                    <input type="password" class="form-control" name="current_password" placeholder="Enter current password">
                </div>
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" class="form-control" name="new_password" placeholder="Enter new password" minlength="6">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" name="confirm_password" placeholder="Confirm new password">
                </div>
                <small style="color:var(--text-tertiary)">Leave blank to keep current password.</small>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn-primary-smart">
            <i class="bi bi-check-lg"></i> Save Settings
        </button>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
