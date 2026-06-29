<?php
// User/index.php
require_once __DIR__ . '/config.php';
require_once dirname(__DIR__) . '/includes/Security.php';
require_once dirname(__DIR__) . '/includes/Logger.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
// If already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
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
    $alertMessage = "Your session has expired due to inactivity. Please sign in again.";
    $alertType = 'alert-error';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF Validation
    if (
        !isset($_POST['csrf_token']) ||
        !Security::validateCSRFToken($_POST['csrf_token'])
    ) {
        $alertMessage = 'Invalid request. Please try again.';
        $alertType = 'alert-error';
    } else {

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {

            $alertMessage = 'Please enter Email and Password.';
            $alertType = 'alert-error';
        } else {

            try {

                $stmt = $pdo->prepare("
                    SELECT
                        user_id,
                        unique_user_id,
                        full_name,
                        email,
                        password,
                        account_status,
                        failed_login_attempts,
                        locked_until
                    FROM users
                    WHERE email = ?
                    LIMIT 1
                ");

                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {

                    // Check lock status
                    if (
                        !empty($user['locked_until']) &&
                        strtotime($user['locked_until']) > time()
                    ) {

                        $alertMessage = 'Account locked due to multiple failed login attempts. Please try again later.';
                        $alertType = 'alert-error';

                        Logger::logAudit(
                            $pdo,
                            'Login',
                            'Locked Account Attempt',
                            $user['user_id'],
                            null
                        );
                    } else {

                        if (password_verify($password, $user['password'])) {

                            if ($user['account_status'] === 'Active') {

                                // Reset failed attempts
                                $resetStmt = $pdo->prepare("
                                    UPDATE users
                                    SET
                                        failed_login_attempts = 0,
                                        locked_until = NULL,
                                        last_login = NOW()
                                    WHERE user_id = ?
                                ");

                                $resetStmt->execute([$user['user_id']]);

                                // Login Log
                                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
                                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

                                $deviceInfo = Security::parseUserAgent($userAgent);

                                $logStmt = $pdo->prepare("
                                    INSERT INTO user_login_logs
                                    (
                                        user_id,
                                        ip_address,
                                        browser,
                                        os,
                                        device_type
                                    )
                                    VALUES (?, ?, ?, ?, ?)
                                ");

                                $logStmt->execute([
                                    $user['user_id'],
                                    $ipAddress,
                                    $deviceInfo['browser'],
                                    $deviceInfo['os'],
                                    $deviceInfo['device']
                                ]);

                                // System Notification
                                $notifStmt = $pdo->prepare("
                                    INSERT INTO system_notifications
                                    (type, title, message)
                                    VALUES ('login', 'User Login', ?)
                                ");

                                $notifMsg =
                                    $user['full_name'] .
                                    ' (' . $user['email'] . ')' .
                                    ' logged in from ' .
                                    $deviceInfo['browser'] .
                                    ' on ' .
                                    $deviceInfo['os'];

                                $notifStmt->execute([$notifMsg]);

                                Logger::logAudit(
                                    $pdo,
                                    'Login',
                                    'Success',
                                    $user['user_id'],
                                    null
                                );

                                // Regenerate Session
                                session_regenerate_id(true);

                                $_SESSION['user_id'] = $user['user_id'];
                                $_SESSION['user_name'] = $user['full_name'];
                                $_SESSION['user_email'] = $user['email'];
                                $_SESSION['unique_id'] = $user['unique_user_id'];

                                header("Location: dashboard.php");
                                exit;
                            } else {

                                $alertMessage =
                                    'Your account is currently ' .
                                    htmlspecialchars($user['account_status']) .
                                    '. Please contact support.';

                                $alertType = 'alert-error';

                                Logger::logAudit(
                                    $pdo,
                                    'Login',
                                    'Failed (Account Status)',
                                    $user['user_id'],
                                    null
                                );
                            }
                        } else {

                            $fails = (int)$user['failed_login_attempts'] + 1;
                            $lockedUntil = null;

                            if ($fails >= 5) {

                                $lockedUntil = date(
                                    'Y-m-d H:i:s',
                                    strtotime('+15 minutes')
                                );

                                $alertMessage =
                                    'Account locked for 15 minutes due to multiple failed attempts.';

                                $notifStmt = $pdo->prepare("
                                    INSERT INTO system_notifications
                                    (type, title, message)
                                    VALUES
                                    (
                                        'login',
                                        'Security Alert: Account Locked',
                                        ?
                                    )
                                ");

                                $notifStmt->execute([
                                    'Multiple failed login attempts for ' .
                                        $user['email'] .
                                        '. Account temporarily locked.'
                                ]);

                                Logger::logAudit(
                                    $pdo,
                                    'Login',
                                    'Account Locked',
                                    $user['user_id'],
                                    null
                                );
                            } else {

                                $alertMessage =
                                    'Invalid Email or Password. Attempts left: ' .
                                    (5 - $fails);

                                Logger::logAudit(
                                    $pdo,
                                    'Login',
                                    'Failed Password',
                                    $user['user_id'],
                                    null
                                );
                            }

                            $updateStmt = $pdo->prepare("
                                UPDATE users
                                SET
                                    failed_login_attempts = ?,
                                    locked_until = ?
                                WHERE user_id = ?
                            ");

                            $updateStmt->execute([
                                $fails,
                                $lockedUntil,
                                $user['user_id']
                            ]);

                            $alertType = 'alert-error';
                        }
                    }
                } else {

                    $alertMessage = 'Invalid Email or Password.';
                    $alertType = 'alert-error';

                    Logger::logAudit(
                        $pdo,
                        'Login',
                        'Failed User Not Found (' . $email . ')',
                        null,
                        null
                    );
                }
            } catch (PDOException $e) {

                $alertMessage = 'System error occurred. Please try again later.';
                $alertType = 'alert-error';

                // Uncomment for debugging
                // die($e->getMessage());
            }
        }
    }
}
