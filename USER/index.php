<?php
require_once __DIR__ . '/config.php';
require_once dirname(__DIR__) . '/includes/Security.php';
require_once dirname(__DIR__) . '/includes/Logger.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$alertMessage = '';
$alertType = '';

if (isset($_SESSION['login_alert'])) {
    $alertMessage = $_SESSION['login_alert'];
    $alertType = 'alert-success';
    unset($_SESSION['login_alert']);
}

if (isset($_GET['expired']) && $_GET['expired'] == 1) {
    $alertMessage = 'Your session has expired due to inactivity. Please sign in again.';
    $alertType = 'alert-error';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !Security::validateCSRFToken($_POST['csrf_token'])) {
        $alertMessage = 'Invalid request. Please try again.';
        $alertType = 'alert-error';
    } else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            $alertMessage = 'Please enter a valid email address and password.';
            $alertType = 'alert-error';
        } else {
            try {
                $stmt = $pdo->prepare('SELECT user_id, unique_user_id, full_name, email, password, account_status, failed_login_attempts, locked_until FROM users WHERE email = ? LIMIT 1');
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    $alertMessage = 'Invalid email or password.';
                    $alertType = 'alert-error';
                    Logger::logAudit($pdo, 'Login', 'Failed User Not Found (' . $email . ')', null, null);
                } elseif (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
                    $alertMessage = 'Account locked due to multiple failed login attempts. Please try again later.';
                    $alertType = 'alert-error';
                    Logger::logAudit($pdo, 'Login', 'Locked Account Attempt', $user['user_id'], null);
                } elseif (!password_verify($password, $user['password'])) {
                    $fails = (int) $user['failed_login_attempts'] + 1;
                    $lockedUntil = $fails >= 5 ? date('Y-m-d H:i:s', strtotime('+15 minutes')) : null;
                    $updateStmt = $pdo->prepare('UPDATE users SET failed_login_attempts = ?, locked_until = ? WHERE user_id = ?');
                    $updateStmt->execute([$fails, $lockedUntil, $user['user_id']]);
                    $alertMessage = $fails >= 5
                        ? 'Account locked for 15 minutes due to multiple failed attempts.'
                        : 'Invalid email or password. Attempts left: ' . (5 - $fails);
                    $alertType = 'alert-error';
                    Logger::logAudit($pdo, 'Login', $fails >= 5 ? 'Account Locked' : 'Failed Password', $user['user_id'], null);
                } elseif ($user['account_status'] !== 'Active') {
                    $alertMessage = 'Your account is currently ' . htmlspecialchars($user['account_status']) . '. Please contact support.';
                    $alertType = 'alert-error';
                    Logger::logAudit($pdo, 'Login', 'Failed (Account Status)', $user['user_id'], null);
                } else {
                    $resetStmt = $pdo->prepare('UPDATE users SET failed_login_attempts = 0, locked_until = NULL, last_login = NOW() WHERE user_id = ?');
                    $resetStmt->execute([$user['user_id']]);

                    $deviceInfo = Security::parseUserAgent($_SERVER['HTTP_USER_AGENT'] ?? '');
                    $logStmt = $pdo->prepare('INSERT INTO user_login_logs (user_id, ip_address, browser, os, device_type) VALUES (?, ?, ?, ?, ?)');
                    $logStmt->execute([$user['user_id'], $_SERVER['REMOTE_ADDR'] ?? '', $deviceInfo['browser'], $deviceInfo['os'], $deviceInfo['device']]);

                    $notifStmt = $pdo->prepare("INSERT INTO system_notifications (type, title, message) VALUES ('login', 'User Login', ?)");
                    $notifStmt->execute([$user['full_name'] . ' (' . $user['email'] . ') logged in from ' . $deviceInfo['browser'] . ' on ' . $deviceInfo['os']]);
                    Logger::logAudit($pdo, 'Login', 'Success', $user['user_id'], null);

                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['unique_id'] = $user['unique_user_id'];
                    $_SESSION['last_activity'] = time();

                    header('Location: dashboard.php');
                    exit;
                }
            } catch (PDOException $e) {
                error_log('User login failed: ' . $e->getMessage());
                $alertMessage = 'System error occurred. Please try again later.';
                $alertType = 'alert-error';
            }
        }
    }
}
$csrfToken = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Login - Saraswati Abhyasika</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <img src="../IMAGES/SHREE SARASWATI ABHYASIKA LOGO.png" alt="Saraswati Abhyasika Logo" class="auth-logo">
            <h1>सरस्वती अभ्यासिका</h1>
            <p>Login with your registered email address</p>
        </div>

        <?php if ($alertMessage): ?>
            <div class="alert <?= htmlspecialchars($alertType) ?>"><?= htmlspecialchars($alertMessage) ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required autocomplete="email" placeholder="student@example.com">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Enter your password">
            </div>
            <button type="submit" class="btn-primary">Login</button>
        </form>

        <div class="auth-footer">
            <a href="forgot_password.php" class="auth-link">Forgot password?</a>
            <a href="register.php" class="btn-secondary">Create Account</a>
            <a href="../index.php" class="auth-link">Back to home</a>
        </div>
    </div>
</body>
</html>
