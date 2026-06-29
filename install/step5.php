<?php
require_once __DIR__ . '/../config/config.php';

if (file_exists(__DIR__ . '/../.installed')) {
    die("<h1>Already Installed</h1>");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $provider = $_POST['email_provider'] ?? 'Brevo';
    
    $keys = [
        'email_provider' => $provider,
        'brevo_api_key' => $_POST['brevo_api_key'] ?? '',
        'smtp_host' => $_POST['smtp_host'] ?? '',
        'smtp_port' => $_POST['smtp_port'] ?? '',
        'smtp_user' => $_POST['smtp_user'] ?? '',
        'smtp_pass' => $_POST['smtp_pass'] ?? '',
        'smtp_encryption' => $_POST['smtp_encryption'] ?? 'tls'
    ];
    
    try {
        if (!$pdo instanceof PDO) {
            throw new Exception('Database connection is not available. Please complete database setup first.');
        }
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        foreach ($keys as $k => $v) {
            $stmt->execute([$k, $v]);
        }
        $pdo->commit();
        
        // Lock Installer
        file_put_contents(__DIR__ . '/../.installed', date('Y-m-d H:i:s'));
        
        header("Location: ../pratik2002/index.php");
        exit;
    } catch (Exception $e) {
        if ($pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = "Failed to save email settings: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Setup - Saraswati Abhyasika</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f3f4f6; color: #374151; margin: 0; padding: 40px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #111827; text-align: center; }
        .step { text-align: center; color: #6b7280; font-size: 14px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"], select { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; }
        .btn { display: block; width: 100%; padding: 12px; background: #10b981; color: white; text-align: center; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; margin-top: 20px; font-weight: bold;}
        .btn:hover { background: #059669; }
        .alert-error { background: #fee2e2; color: #dc2626; padding: 15px; border-radius: 5px; margin-bottom: 15px; }
        .provider-section { display: none; padding: 15px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 5px; margin-bottom: 15px; }
        .provider-section.active { display: block; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Email Service Configuration</h1>
        <div class="step">Step 5 of 5: Final Setup</div>
        
        <?php if ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Provider</label>
                <select name="email_provider" id="providerSelect" onchange="toggleProvider()">
                    <option value="Brevo">Brevo API (Recommended)</option>
                    <option value="SMTP">Custom SMTP (Gmail, SendGrid, SES, etc.)</option>
                </select>
            </div>
            
            <div id="brevo_section" class="provider-section active">
                <div class="form-group">
                    <label>Brevo API Key (v3)</label>
                    <input type="text" name="brevo_api_key" placeholder="xkeysib-...">
                </div>
            </div>
            
            <div id="smtp_section" class="provider-section">
                <div class="form-group">
                    <label>SMTP Host</label>
                    <input type="text" name="smtp_host" placeholder="smtp.gmail.com">
                </div>
                <div class="form-group">
                    <label>SMTP Port</label>
                    <input type="text" name="smtp_port" placeholder="465">
                </div>
                <div class="form-group">
                    <label>SMTP Username</label>
                    <input type="text" name="smtp_user" placeholder="email@domain.com">
                </div>
                <div class="form-group">
                    <label>SMTP Password / App Password</label>
                    <input type="password" name="smtp_pass">
                </div>
                <div class="form-group">
                    <label>Encryption</label>
                    <select name="smtp_encryption">
                        <option value="tls">TLS</option>
                        <option value="ssl">SSL</option>
                        <option value="none">None</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn">Finish Installation</button>
        </form>
    </div>
    <script>
        function toggleProvider() {
            var val = document.getElementById('providerSelect').value;
            if (val === 'Brevo') {
                document.getElementById('brevo_section').classList.add('active');
                document.getElementById('smtp_section').classList.remove('active');
            } else {
                document.getElementById('brevo_section').classList.remove('active');
                document.getElementById('smtp_section').classList.add('active');
            }
        }
    </script>
</body>
</html>
