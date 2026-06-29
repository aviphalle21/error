<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

$alertMessage = '';
$alertType = '';
$admin_id = $_SESSION['admin_id'];

// Handle Profile Update
if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    
    try {
        $stmt = $pdo->prepare("UPDATE admin SET name = ?, email = ?, username = ? WHERE admin_id = ?");
        if ($stmt->execute([$name, $email, $username, $admin_id])) {
            $alertMessage = "Profile updated successfully!";
            $alertType = "alert-success";
        }
    } catch (PDOException $e) {
        $alertMessage = "Error updating profile. Email or username might already be in use.";
        $alertType = "alert-error";
    }
}

// Handle System Settings Update
if (isset($_POST['update_system_settings'])) {
    $keys = [
        'library_name', 'website_url', 'support_email', 'contact_number', 'timezone', 'currency',
        'email_provider', 'brevo_api_key', 'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_encryption'
    ];
    
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
        foreach ($keys as $key) {
            if (isset($_POST[$key])) {
                $stmt->execute([$_POST[$key], $key]);
            }
        }
        $pdo->commit();
        $alertMessage = "System Configuration updated successfully!";
        $alertType = "alert-success";
        
        // Refresh globals
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
        while ($row = $stmt->fetch()) {
            global $settings;
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $alertMessage = "Failed to update settings: " . $e->getMessage();
        $alertType = "alert-error";
    }
}

// Handle Password Update
if (isset($_POST['update_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (!preg_match('/^[0-9]{6}$/', $new_password)) {
        $alertMessage = "Password must be exactly 6 digits.";
        $alertType = "alert-error";
    } elseif ($new_password === $confirm_password) {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admin SET password = ? WHERE admin_id = ?");
        if ($stmt->execute([$hashed, $admin_id])) {
            $alertMessage = "Password updated successfully!";
            $alertType = "alert-success";
        }
    } else {
        $alertMessage = "Passwords do not match.";
        $alertType = "alert-error";
    }
}

// Fetch current admin details
$stmt = $pdo->prepare("SELECT * FROM admin WHERE admin_id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Library Management</title>
    <link rel="stylesheet" href="Dashboard.css">
    <style>
        .settings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }
        .settings-card {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        .settings-card h3 {
            margin-top: 0;
            color: var(--navy-blue);
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-gray);
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-main);
        }
        .form-group input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--border-gray);
            border-radius: 8px;
            font-size: 1rem;
            outline: none;
        }
        .form-group input:focus {
            border-color: var(--sidebar-active);
        }
        .btn-submit {
            background: var(--sidebar-active);
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-submit:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<?php 
$pageTitle = 'Settings';
$showBackButton = true;
?>
<body>
    <?php include 'header.php'; ?>

    <div style="max-width: 1000px; margin: 0 auto;">
        <?php if ($alertMessage): ?>
            <div class="alert <?= $alertType ?>"><?= htmlspecialchars($alertMessage) ?></div>
        <?php endif; ?>

        <div class="settings-grid">
            <!-- Theme Settings (Full Width) -->
            <div class="settings-card" style="grid-column: 1 / -1;">
                <h3>Theme Presets & Custom Colors</h3>
                
                <div style="margin-bottom: 25px; padding: 15px; background: #f8fafc; border-radius: 8px; display: flex; align-items: center; gap: 20px;">
                    <div>
                        <strong style="display: block; margin-bottom: 5px;">Create Your Own Theme</strong>
                        <span style="font-size: 0.85rem; color: var(--text-muted);">Pick a color for the sidebar and buttons!</span>
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="color" id="customColorPicker" value="#4f46e5" style="height: 40px; width: 60px; padding: 0; border: none; border-radius: 8px; cursor: pointer;">
                        <button type="button" class="btn-submit" onclick="applyCustomTheme()" style="padding: 8px 15px;">Apply Custom</button>
                    </div>
                </div>

                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <div class="theme-btn" onclick="setTheme('slate')" style="background: #0f172a; color: #f8fafc; padding: 15px 25px; border-radius: 8px; cursor: pointer; text-align: center; border: 2px solid transparent; flex: 1; min-width: 150px;">
                        <div style="font-weight: 600;">Rajpath Gray</div>
                        <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 5px;">Official Administrative Gray</div>
                    </div>
                    <div class="theme-btn" onclick="setTheme('zinc')" style="background: #18181b; color: #f4f4f5; padding: 15px 25px; border-radius: 8px; cursor: pointer; text-align: center; border: 2px solid transparent; flex: 1; min-width: 150px;">
                        <div style="font-weight: 600;">Deccan Stone</div>
                        <div style="font-size: 0.75rem; color: #a1a1aa; margin-top: 5px;">Strong Institutional Charcoal</div>
                    </div>
                    <div class="theme-btn" onclick="setTheme('sage')" style="background: #1c1917; color: #fafaf9; padding: 15px 25px; border-radius: 8px; cursor: pointer; text-align: center; border: 2px solid transparent; flex: 1; min-width: 150px;">
                        <div style="font-weight: 600;">Maru Gold</div>
                        <div style="font-size: 0.75rem; color: #a8a29e; margin-top: 5px;">Inspired by Rajasthan Sands</div>
                    </div>
                    <div class="theme-btn" onclick="setTheme('navy')" style="background: #172554; color: #eff6ff; padding: 15px 25px; border-radius: 8px; cursor: pointer; text-align: center; border: 2px solid transparent; flex: 1; min-width: 150px;">
                        <div style="font-weight: 600;">Satyamev Blue</div>
                        <div style="font-size: 0.75rem; color: #93c5fd; margin-top: 5px;">Official Deep Blue</div>
                    </div>
                </div>
            </div>

            <!-- Profile Settings -->
            <div class="settings-card">
                <h3>Admin Profile</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($admin['name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($admin['username']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>
                    </div>
                    <button type="submit" name="update_profile" class="btn-submit">Save Profile</button>
                </form>
            </div>

            <!-- Password Settings -->
            <div class="settings-card">
                <h3>Change Password</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required pattern="[0-9]{6}" maxlength="6" title="Please enter exactly 6 digits" placeholder="6-digit PIN">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required pattern="[0-9]{6}" maxlength="6" title="Please enter exactly 6 digits" placeholder="6-digit PIN">
                    </div>
                    <button type="submit" name="update_password" class="btn-submit">Update Password</button>
                </form>
            </div>
            
            <!-- System General Settings -->
            <div style="grid-column: 1 / -1;">
                <form method="POST">
                    <div class="settings-grid">
                        <div class="settings-card">
                            <h3>System Configuration</h3>
                            <div class="form-group">
                                <label>Library Name</label>
                                <input type="text" name="library_name" value="<?= htmlspecialchars(getSetting('library_name')) ?>">
                            </div>
                    <div class="form-group">
                        <label>Website URL</label>
                        <input type="text" name="website_url" value="<?= htmlspecialchars(getSetting('website_url')) ?>">
                    </div>
                    <div class="form-group">
                        <label>Support Email</label>
                        <input type="email" name="support_email" value="<?= htmlspecialchars(getSetting('support_email')) ?>">
                    </div>
                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" name="contact_number" value="<?= htmlspecialchars(getSetting('contact_number')) ?>">
                    </div>
                    <div class="form-group">
                        <label>Time Zone</label>
                        <input type="text" name="timezone" value="<?= htmlspecialchars(getSetting('timezone')) ?>">
                    </div>
                    <div class="form-group">
                        <label>Currency Code</label>
                        <input type="text" name="currency" value="<?= htmlspecialchars(getSetting('currency')) ?>">
                        </div>
                        
                        <!-- Email Service Settings -->
                        <div class="settings-card">
                            <h3>Email Integration</h3>
                            <div class="form-group">
                        <label>Email Provider</label>
                        <select name="email_provider" style="width: 100%; padding: 10px; border: 1px solid var(--border-gray); border-radius: 8px;">
                            <option value="Brevo" <?= getSetting('email_provider') === 'Brevo' ? 'selected' : '' ?>>Brevo API (Recommended)</option>
                            <option value="SMTP" <?= getSetting('email_provider') === 'SMTP' ? 'selected' : '' ?>>Custom SMTP</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Brevo API Key (v3)</label>
                        <input type="text" name="brevo_api_key" value="<?= htmlspecialchars(getSetting('brevo_api_key')) ?>" placeholder="xkeysib-...">
                    </div>
                    <div class="form-group">
                        <label>SMTP Host</label>
                        <input type="text" name="smtp_host" value="<?= htmlspecialchars(getSetting('smtp_host')) ?>">
                    </div>
                    <div class="form-group">
                        <label>SMTP Port</label>
                        <input type="text" name="smtp_port" value="<?= htmlspecialchars(getSetting('smtp_port')) ?>">
                    </div>
                    <div class="form-group">
                        <label>SMTP Username & Password</label>
                        <input type="text" name="smtp_user" value="<?= htmlspecialchars(getSetting('smtp_user')) ?>" placeholder="Username" style="margin-bottom: 5px;">
                        <input type="password" name="smtp_pass" value="<?= htmlspecialchars(getSetting('smtp_pass')) ?>" placeholder="Password">
                    </div>
                    <div class="form-group">
                        <label>SMTP Encryption</label>
                        <select name="smtp_encryption" style="width: 100%; padding: 10px; border: 1px solid var(--border-gray); border-radius: 8px;">
                            <option value="tls" <?= getSetting('smtp_encryption') === 'tls' ? 'selected' : '' ?>>TLS</option>
                            <option value="ssl" <?= getSetting('smtp_encryption') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                            <option value="none" <?= getSetting('smtp_encryption') === 'none' ? 'selected' : '' ?>>None</option>
                        </select>
                        </div>
                    </div>
                    <button type="submit" name="update_system_settings" class="btn-submit" style="width: 100%; margin-top: 15px;">Save System & Email Settings</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    const themes = {
        slate: { '--sidebar-bg': '#0f172a', '--sidebar-active': '#3b82f6', '--sidebar-hover': '#1e293b' },
        zinc:  { '--sidebar-bg': '#18181b', '--sidebar-active': '#71717a', '--sidebar-hover': '#27272a' },
        sage:  { '--sidebar-bg': '#1c1917', '--sidebar-active': '#d97706', '--sidebar-hover': '#292524' },
        navy:  { '--sidebar-bg': '#172554', '--sidebar-active': '#6366f1', '--sidebar-hover': '#1e3a8a' }
    };

    function setTheme(themeName) {
        const theme = themes[themeName];
        if (theme) {
            localStorage.setItem('appTheme', JSON.stringify(theme));
            localStorage.setItem('appThemeName', themeName);
            for (const key in theme) {
                document.documentElement.style.setProperty(key, theme[key]);
            }
            updateActiveButton(themeName);
        }
    }

    function updateActiveButton(themeName) {
        document.querySelectorAll('.theme-btn').forEach(btn => {
            btn.style.borderColor = 'transparent';
        });
        const activeBtn = document.querySelector(`.theme-btn[onclick="setTheme('${themeName}')"]`);
        if (activeBtn) {
            const activeColor = themes[themeName]['--sidebar-active'];
            activeBtn.style.borderColor = activeColor;
        }
    }

    function hexToRgb(hex) {
        let result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : null;
    }
    
    function applyCustomTheme() {
        const customColor = document.getElementById('customColorPicker').value;
        const rgb = hexToRgb(customColor);
        if (!rgb) return;
        
        // Make hover slightly darker
        const hoverColor = `rgb(${Math.max(0, rgb.r - 20)}, ${Math.max(0, rgb.g - 20)}, ${Math.max(0, rgb.b - 20)})`;
        // Make background even darker for sidebar
        const bgColor = `rgb(${Math.max(0, rgb.r - 40)}, ${Math.max(0, rgb.g - 40)}, ${Math.max(0, rgb.b - 40)})`;
        
        const customTheme = {
            '--sidebar-bg': bgColor,
            '--sidebar-active': customColor,
            '--sidebar-hover': hoverColor
        };
        
        localStorage.setItem('appTheme', JSON.stringify(customTheme));
        localStorage.setItem('appThemeName', 'custom');
        for (const key in customTheme) {
            document.documentElement.style.setProperty(key, customTheme[key]);
        }
        updateActiveButton('custom');
    }

    // Initialize on page load
    const currentThemeName = localStorage.getItem('appThemeName') || 'maratha';
    updateActiveButton(currentThemeName);
    </script>
    
<?php include 'footer.php'; ?>
