<?php
// api/verify_otp.php
header('Content-Type: application/json');

require_once '../USER/config.php';
require_once '../includes/SessionManager.php';
require_once '../includes/Security.php';

SessionManager::startSecureSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$otp_entered = trim($_POST['otp'] ?? '');
$token = $_POST['csrf_token'] ?? '';

if (!Security::validateCSRFToken($token)) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token. Please try again.']);
    exit;
}

if (empty($otp_entered)) {
    echo json_encode(['success' => false, 'message' => 'Please enter the 6-digit OTP.']);
    exit;
}

if (isset($_SESSION['reset_user_id'])) {
    $stmt = $pdo->prepare("SELECT id, otp, attempts, expires_at FROM password_reset_tokens WHERE user_id = ? AND verified = 0 ORDER BY id DESC LIMIT 1");
    $stmt->execute([$_SESSION['reset_user_id']]);
    $tokenRecord = $stmt->fetch();
    
    if ($tokenRecord) {
        // Check Expiration
        if (strtotime($tokenRecord['expires_at']) < time()) {
            echo json_encode(['success' => false, 'message' => 'OTP has expired. Please request a new one.']);
            exit;
        } 
        // Check attempts
        elseif ($tokenRecord['attempts'] >= 5) {
            $pdo->prepare("UPDATE password_reset_tokens SET expires_at = NOW() WHERE id = ?")->execute([$tokenRecord['id']]);
            echo json_encode(['success' => false, 'message' => 'Maximum attempts exceeded. Please request a new OTP.']);
            exit;
        } 
        // Verify hash
        elseif (password_verify($otp_entered, $tokenRecord['otp'])) {
            // Success
            $pdo->prepare("UPDATE password_reset_tokens SET verified = 1 WHERE id = ?")->execute([$tokenRecord['id']]);
            $_SESSION['otp_verified'] = true;
            $_SESSION['reset_token_id'] = $tokenRecord['id'];
            echo json_encode(['success' => true, 'message' => 'OTP verified successfully.']);
            exit;
        } else {
            // Failed attempt
            $pdo->prepare("UPDATE password_reset_tokens SET attempts = attempts + 1 WHERE id = ?")->execute([$tokenRecord['id']]);
            echo json_encode(['success' => false, 'message' => 'Incorrect OTP. Please try again.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP. Please request a new one.']);
        exit;
    }
} else {
    // Fake user path (simulates failure to prevent enumeration)
    echo json_encode(['success' => false, 'message' => 'Incorrect OTP. Please try again.']);
    exit;
}
