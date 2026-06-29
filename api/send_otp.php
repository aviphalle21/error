<?php
// api/send_otp.php
header('Content-Type: application/json');

require_once '../USER/config.php';
require_once '../includes/SessionManager.php';
require_once '../includes/Security.php';
require_once '../services/EmailService.php';

SessionManager::startSecureSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$token = $_POST['csrf_token'] ?? '';

if (!Security::validateCSRFToken($token)) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token. Please try again.']);
    exit;
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}



// Ensure api dir exists (for context, not strictly needed, but let's be safe)
if (!is_dir(__DIR__)) { mkdir(__DIR__, 0755, true); }

// IP throttling (prevent abuse from same IP)
$ipAddress = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

$stmt = $pdo->prepare("SELECT COUNT(*) FROM password_reset_tokens WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
$stmt->execute([$ipAddress]);
$ipRequests = $stmt->fetchColumn();

if ($ipRequests >= 10) {
    echo json_encode(['success' => false, 'message' => 'Too many requests from your network. Please try again later.']);
    exit;
}

// Check user
$stmt = $pdo->prepare("SELECT user_id, full_name, email FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    // Check rate limit for this user
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM password_reset_tokens WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $stmt->execute([$user['user_id']]);
    $userRequests = $stmt->fetchColumn();

    if ($userRequests >= 3) {
        echo json_encode(['success' => false, 'message' => 'Too many requests. Please try again after 15 minutes.']);
        exit;
    }

    // Generate 6-digit OTP
    try {
        $otp = sprintf("%06d", random_int(100000, 999999));
    } catch (Exception $e) {
        $otp = sprintf("%06d", mt_rand(100000, 999999));
    }
    
    $hashedOtp = password_hash($otp, PASSWORD_DEFAULT);
    
    // Invalidate any previous active tokens
    $pdo->prepare("UPDATE password_reset_tokens SET expires_at = NOW() WHERE user_id = ?")->execute([$user['user_id']]);

    // Store in DB
    $insStmt = $pdo->prepare("INSERT INTO password_reset_tokens (user_id, email, otp, expires_at, ip_address, user_agent) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE), ?, ?)");
    $insStmt->execute([$user['user_id'], $user['email'], $hashedOtp, $ipAddress, $userAgent]);
    
    // Send Email
    $mailSent = EmailService::sendOTP($pdo, $user['email'], $user['full_name'], $otp);
    
    if ($mailSent === 'DEVELOPMENT_MODE') {
        echo json_encode(['success' => false, 'message' => 'DEVELOPMENT MODE: Please configure your Brevo API key in config/brevo.php.']);
        exit;
    }
    
    if ($mailSent) {
        $_SESSION['reset_user_id'] = $user['user_id'];
        $_SESSION['reset_email'] = $user['email'];
        echo json_encode(['success' => true, 'message' => 'OTP has been sent to your email.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Unable to send verification email. Please try again later.']);
    }
} else {
    // Generic success for non-existent email to prevent enumeration
    $_SESSION['reset_email_fake'] = $email;
    echo json_encode(['success' => true, 'message' => 'OTP has been sent to your email.']);
}
