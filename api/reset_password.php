<?php
// api/reset_password.php
header('Content-Type: application/json');

require_once '../USER/config.php';
require_once '../includes/SessionManager.php';
require_once '../includes/Security.php';

SessionManager::startSecureSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!isset($_SESSION['reset_user_id']) || !isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true || !isset($_SESSION['reset_token_id'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please start over.']);
    exit;
}

$stmt = $pdo->prepare("SELECT verified FROM password_reset_tokens WHERE id = ? AND user_id = ?");
$stmt->execute([$_SESSION['reset_token_id'], $_SESSION['reset_user_id']]);
$isVerified = $stmt->fetchColumn();

if (!$isVerified) {
    echo json_encode(['success' => false, 'message' => 'Invalid session.']);
    exit;
}

$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$token = $_POST['csrf_token'] ?? '';

if (!Security::validateCSRFToken($token)) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token. Please try again.']);
    exit;
}

if (empty($password) || empty($confirmPassword)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in both password fields.']);
    exit;
}

if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long.']);
    exit;
}

if (!preg_match('/[A-Z]/', $password)) {
    echo json_encode(['success' => false, 'message' => 'Password must contain at least one uppercase letter.']);
    exit;
}

if (!preg_match('/[a-z]/', $password)) {
    echo json_encode(['success' => false, 'message' => 'Password must contain at least one lowercase letter.']);
    exit;
}

if (!preg_match('/[0-9]/', $password)) {
    echo json_encode(['success' => false, 'message' => 'Password must contain at least one number.']);
    exit;
}

if (!preg_match('/[\W_]/', $password)) {
    echo json_encode(['success' => false, 'message' => 'Password must contain at least one special character.']);
    exit;
}

if ($password !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
    exit;
}

// Hash and update
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
$stmt->execute([$hashedPassword, $_SESSION['reset_user_id']]);

// Clean up
$stmt = $pdo->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
$stmt->execute([$_SESSION['reset_user_id']]);

unset($_SESSION['reset_user_id']);
unset($_SESSION['reset_email']);
unset($_SESSION['reset_email_fake']);
unset($_SESSION['otp_verified']);
unset($_SESSION['reset_token_id']);

$_SESSION['login_alert'] = "Password successfully reset! You can now log in.";
echo json_encode(['success' => true, 'message' => 'Password successfully reset.']);
