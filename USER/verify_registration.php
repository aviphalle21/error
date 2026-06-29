<?php
require_once 'config.php';
require_once '../includes/Security.php';
require_once '../services/EmailService.php';
require_once '../includes/Logger.php';

if (!isset($_SESSION['pending_registration'])) {
    header("Location: register.php");
    exit;
}

$alertMessage = '';
$alertType = '';

if (isset($_SESSION['dev_otp_msg'])) {
    $alertMessage = $_SESSION['dev_otp_msg'];
    $alertType = 'alert-info';
    unset($_SESSION['dev_otp_msg']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !Security::validateCSRFToken($_POST['csrf_token'])) {
        $alertMessage = 'Invalid request (CSRF check failed).';
        $alertType = 'alert-error';
    } else {
        $otp = trim($_POST['otp'] ?? '');
        
        if (empty($otp) || strlen($otp) !== 6) {
            $alertMessage = 'Please enter a valid 6-digit OTP.';
            $alertType = 'alert-error';
        } elseif (time() > $_SESSION['registration_otp_expiry']) {
            $alertMessage = 'OTP has expired. Please register again.';
            $alertType = 'alert-error';
            unset($_SESSION['pending_registration']);
        } elseif (!password_verify($otp, $_SESSION['registration_otp'])) {
            $alertMessage = 'Invalid OTP. Please try again.';
            $alertType = 'alert-error';
        } else {
            // OTP Verified! Insert into DB.
            $data = $_SESSION['pending_registration'];
            
            try {
                $stmt = $pdo->prepare("INSERT INTO users (unique_user_id, full_name, email, phone, address, password, registration_ip, registration_device, registration_browser) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['unique_user_id'], 
                    $data['full_name'], 
                    $data['email'], 
                    $data['phone'], 
                    $data['address'], 
                    $data['password'], 
                    $data['registration_ip'], 
                    $data['registration_device'], 
                    $data['registration_browser']
                ]);
                $newUserId = $pdo->lastInsertId();
                
                // Log and notify
                Logger::logAudit($pdo, 'Registration', 'Success', $newUserId, null);
                
                EmailService::sendAdminNotification($pdo, 'New Registration', "New user registered: " . $data['full_name'] . " (" . $data['unique_user_id'] . ")");
                
                // Clear session data
                unset($_SESSION['pending_registration']);
                unset($_SESSION['registration_otp']);
                unset($_SESSION['registration_otp_expiry']);
                
                // Set generated ID for success display
                $_SESSION['registered_unique_id'] = $data['unique_user_id'];
                
                header("Location: register_success.php");
                exit;
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $alertMessage = 'Email or Phone number is already registered by another verified account.';
                } else {
                    $alertMessage = 'Database error occurred. Please try again later.';
                }
                $alertType = 'alert-error';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Registration</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <h2>Verify Email</h2>
            <p>An OTP has been sent to <?= htmlspecialchars($_SESSION['pending_registration']['email']) ?></p>

            <?php if ($alertMessage): ?>
                <div class="alert <?= $alertType ?>">
                    <?= $alertMessage ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= Security::getCSRFToken() ?>">
                
                <div class="form-group">
                    <label>Enter 6-digit OTP</label>
                    <input type="text" name="otp" required maxlength="6" pattern="[0-9]{6}">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top:15px;">Verify & Create Account</button>
            </form>
        </div>
    </div>
</body>
</html>
