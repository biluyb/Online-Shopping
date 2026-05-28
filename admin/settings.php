<?php
/**
 * Online shopping registration system Admin Settings Manager
 * Manage website configuration variables.
 */

$page_title = 'Settings';
require_once __DIR__ . '/admin_header.php';

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'site_name' => sanitize_input($_POST['site_name'] ?? ''),
        'site_description' => sanitize_input($_POST['site_description'] ?? ''),
        'contact_email' => sanitize_input($_POST['contact_email'] ?? ''),
        'contact_phone' => sanitize_input($_POST['contact_phone'] ?? ''),
        'currency_symbol' => sanitize_input($_POST['currency_symbol'] ?? '$'),
        'tax_rate' => (float)($_POST['tax_rate'] ?? 0),
        'free_shipping_threshold' => (float)($_POST['free_shipping_threshold'] ?? 0)
    ];

    try {
        $pdo->beginTransaction();
        
        $stmt_check = $pdo->prepare("SELECT setting_key FROM settings WHERE setting_key = ?");
        $stmt_insert = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        $stmt_update = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");

        foreach ($settings as $key => $value) {
            $stmt_check->execute([$key]);
            if ($stmt_check->fetch()) {
                $stmt_update->execute([$value, $key]);
            } else {
                $stmt_insert->execute([$key, $value]);
            }
        }
        
        $pdo->commit();
        $_SESSION['flash_message'] = "Settings updated successfully.";
        $_SESSION['flash_type'] = "success";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['flash_message'] = "Failed to update settings.";
        $_SESSION['flash_type'] = "error";
    }
    redirect('settings.php');
    exit;
}

// Fetch current settings
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $db_settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    $db_settings = [];
}

// Default values if not set
$site_name = $db_settings['site_name'] ?? 'Online shopping registration system';
$site_description = $db_settings['site_description'] ?? 'Premium Online Shopping Experience';
$contact_email = $db_settings['contact_email'] ?? 'support@onlineshoppingregistrationsystem.com';
$contact_phone = $db_settings['contact_phone'] ?? '+1 (555) 123-4567';
$currency_symbol = $db_settings['currency_symbol'] ?? '$';
$tax_rate = $db_settings['tax_rate'] ?? '0';
$free_shipping_threshold = $db_settings['free_shipping_threshold'] ?? '100';

?>

<div class="page-header">
    <div>
        <h1>Settings</h1>
        <div class="breadcrumb">
            <a href="index.php">Admin</a> / Settings
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="admin-card">
            <div class="card-header">
                <h3>Global Configuration</h3>
            </div>
            <form method="POST" action="settings.php" class="admin-form">
                
                <h4 style="font-size: 0.9rem; color: #9ca3af; margin-bottom: 16px; border-bottom: 1px solid #2d3748; padding-bottom: 8px;">General Site Info</h4>
                
                <div class="form-group">
                    <label for="site_name">Website Name</label>
                    <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($site_name); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="site_description">Website Description (SEO)</label>
                    <textarea id="site_description" name="site_description" rows="3"><?php echo htmlspecialchars($site_description); ?></textarea>
                </div>
                
                <h4 style="font-size: 0.9rem; color: #9ca3af; margin-top: 30px; margin-bottom: 16px; border-bottom: 1px solid #2d3748; padding-bottom: 8px;">Contact Information</h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="contact_email">Support Email</label>
                            <input type="email" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars($contact_email); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="contact_phone">Support Phone</label>
                            <input type="text" id="contact_phone" name="contact_phone" value="<?php echo htmlspecialchars($contact_phone); ?>">
                        </div>
                    </div>
                </div>
                
                <h4 style="font-size: 0.9rem; color: #9ca3af; margin-top: 30px; margin-bottom: 16px; border-bottom: 1px solid #2d3748; padding-bottom: 8px;">E-Commerce Settings</h4>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="currency_symbol">Currency Symbol</label>
                            <input type="text" id="currency_symbol" name="currency_symbol" value="<?php echo htmlspecialchars($currency_symbol); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="tax_rate">Tax Rate (%)</label>
                            <input type="number" step="0.01" min="0" id="tax_rate" name="tax_rate" value="<?php echo htmlspecialchars($tax_rate); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="free_shipping_threshold">Free Shipping At ($)</label>
                            <input type="number" step="0.01" min="0" id="free_shipping_threshold" name="free_shipping_threshold" value="<?php echo htmlspecialchars($free_shipping_threshold); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-actions" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #2d3748;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="admin-card">
            <div class="card-header">
                <h3>System Status</h3>
            </div>
            <div style="font-size: 0.85rem; line-height: 1.8; color: #d1d5db;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(45,55,72,0.5); padding: 8px 0;">
                    <span style="color: #9ca3af;">PHP Version</span>
                    <strong><?php echo phpversion(); ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(45,55,72,0.5); padding: 8px 0;">
                    <span style="color: #9ca3af;">Server Software</span>
                    <strong><?php echo htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'); ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(45,55,72,0.5); padding: 8px 0;">
                    <span style="color: #9ca3af;">Database Driver</span>
                    <strong>PDO / MySQLi</strong>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(45,55,72,0.5); padding: 8px 0;">
                    <span style="color: #9ca3af;">Upload Max Size</span>
                    <strong><?php echo ini_get('upload_max_filesize'); ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 8px 0;">
                    <span style="color: #9ca3af;">Post Max Size</span>
                    <strong><?php echo ini_get('post_max_size'); ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
