<?php
require_once __DIR__ . '/../config/config.php';

if (file_exists(__DIR__ . '/../.installed')) {
    die("<h1>Already Installed</h1>");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!$pdo instanceof PDO) {
            throw new Exception('Database connection is not available. Please complete database setup first.');
        }
        $keys = [
            'library_name', 'library_logo', 'website_url', 
            'contact_number', 'support_email', 'timezone', 
            'currency', 'language'
        ];
        
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        
        foreach ($keys as $key) {
            if (isset($_POST[$key])) {
                $stmt->execute([$key, $_POST[$key]]);
            }
        }
        
        $pdo->commit();
        header("Location: step4.php");
        exit;
    } catch (Exception $e) {
        if ($pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = "Failed to save settings: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>App Configuration - Saraswati Abhyasika</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f3f4f6; color: #374151; margin: 0; padding: 40px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #111827; text-align: center; }
        .step { text-align: center; color: #6b7280; font-size: 14px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"], select { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; }
        .btn { display: block; width: 100%; padding: 12px; background: #4f46e5; color: white; text-align: center; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; margin-top: 20px; }
        .btn:hover { background: #4338ca; }
        .alert-error { background: #fee2e2; color: #dc2626; padding: 15px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>App Configuration</h1>
        <div class="step">Step 3 of 5: General Settings</div>
        
        <?php if ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Library Name</label>
                <input type="text" name="library_name" value="Saraswati Abhyasika" required>
            </div>
            <div class="form-group">
                <label>Website URL</label>
                <input type="text" name="website_url" value="http://localhost/N2" required>
            </div>
            <div class="form-group">
                <label>Support Email</label>
                <input type="email" name="support_email" value="support@library.com" required>
            </div>
            <div class="form-group">
                <label>Contact Number</label>
                <input type="text" name="contact_number" value="+91 0000000000" required>
            </div>
            <div class="form-group">
                <label>Time Zone</label>
                <select name="timezone">
                    <option value="Asia/Kolkata">Asia/Kolkata</option>
                    <option value="America/New_York">America/New_York</option>
                    <option value="Europe/London">Europe/London</option>
                </select>
            </div>
            <div class="form-group">
                <label>Currency</label>
                <select name="currency">
                    <option value="INR">INR (₹)</option>
                    <option value="USD">USD ($)</option>
                    <option value="GBP">GBP (£)</option>
                </select>
            </div>
            <button type="submit" class="btn">Save & Continue</button>
        </form>
    </div>
</body>
</html>
