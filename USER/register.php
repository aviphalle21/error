<?php
// User/register.php
require_once 'config.php';
require_once '../includes/Security.php';
require_once '../includes/Logger.php';

$alertMessage = '';
$alertType = '';
$generatedId = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF
    if (!isset($_POST['csrf_token']) || !Security::validateCSRFToken($_POST['csrf_token'])) {
        $alertMessage = 'Invalid request (CSRF check failed). Please try again.';
        $alertType = 'alert-error';
    } else {
        $fullName = Security::sanitizeInput($_POST['full_name']);
        $email = Security::sanitizeInput($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];

        // Basic Validation
        if (empty($fullName) || empty($email) || empty($phone) || empty($address) || empty($password)) {
            $alertMessage = 'All fields are required.';
            $alertType = 'alert-error';
        } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
            $alertMessage = 'Phone number must be exactly 10 digits.';
            $alertType = 'alert-error';
        } elseif (!preg_match('/^[0-9]{6}$/', $password)) {
            $alertMessage = 'Password must be exactly 6 digits.';
            $alertType = 'alert-error';
        } elseif ($password !== $confirmPassword) {
            $alertMessage = 'Passwords do not match.';
            $alertType = 'alert-error';
        } else {
            // Generate Unique User ID (e.g., LIB-1045)
            // We'll use a loop to ensure it's truly unique
            do {
                $randomNum = mt_rand(1000, 9999);
                $newUniqueId = "LIB-" . $randomNum;
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE unique_user_id = ?");
                $stmt->execute([$newUniqueId]);
                $exists = $stmt->fetchColumn();
            } while ($exists > 0);

            // Hash Password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            try {
                $ipAddress = $_SERVER['REMOTE_ADDR'];
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                $deviceInfo = Security::parseUserAgent($userAgent);
                
                // Store registration data in session for verification
                $_SESSION['pending_registration'] = [
                    'unique_user_id' => $newUniqueId,
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $address,
                    'password' => $hashedPassword,
                    'registration_ip' => $ipAddress,
                    'registration_device' => $deviceInfo['device'],
                    'registration_browser' => $deviceInfo['browser']
                ];
                
                // Generate OTP
                $otp = sprintf("%06d", mt_rand(100000, 999999));
                $_SESSION['registration_otp'] = password_hash($otp, PASSWORD_DEFAULT);
                $_SESSION['registration_otp_expiry'] = time() + 300; // 5 mins
                
                require_once '../services/EmailService.php';
                $emailSent = EmailService::sendVerificationEmail($pdo, $email, $fullName, $otp);
                
                if ($emailSent === 'DEVELOPMENT_MODE') {
                    $alertMessage = "DEVELOPMENT MODE: Registration Verification OTP is {$otp}";
                    $alertType = 'alert-info';
                    // Automatically redirect to verification page with a message
                    $_SESSION['dev_otp_msg'] = $alertMessage;
                    header("Location: verify_registration.php");
                    exit;
                } elseif ($emailSent) {
                    header("Location: verify_registration.php");
                    exit;
                } else {
                    $alertMessage = "Unable to send verification email. Please try again later.";
                    $alertType = "alert-error";
                }


            } catch (PDOException $e) {
                // Check for duplicate email or phone
                if ($e->getCode() == 23000) {
                    $alertMessage = 'Email or Phone number is already registered.';
                } else {
                    $alertMessage = 'An error occurred during registration. Please try again.';
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - User Portal</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <script src="theme.js"></script>
</head>
<body>

<div class="auth-container">
    <div class="auth-header">
        <img src="../IMAGES/SHREE SARASWATI ABHYASIKA LOGO.png" alt="Saraswati Abhyasika Logo" class="auth-logo">
        <h1>सरस्वती अभ्यासिका</h1>
        <p>Join our library system today</p>
    </div>

    <?php if ($alertMessage): ?>
        <div class="alert <?= $alertType ?>"><?= htmlspecialchars($alertMessage) ?></div>
    <?php endif; ?>

    <?php if ($generatedId): ?>
        <div class="generated-id-box">
            <p>Your Unique Login ID is:</p>
            <h2><?= htmlspecialchars($generatedId) ?></h2>
        </div>
        <a href="index.php" class="btn-primary" style="display:block; text-align:center; text-decoration:none;">Go to Login</a>
    <?php else: ?>
        <form method="POST" action="register.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Security::generateCSRFToken()) ?>">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" required placeholder="Full Name">
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="rahul@example.com">
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" required pattern="[0-9]{10}" maxlength="10" title="Please enter a valid 10-digit phone number" placeholder="1234567890">
            </div>

            <div class="form-group">
                <label for="address">Full Address</label>
                <input type="text" id="address" name="address" required placeholder="123 Main St, City">
            </div>

            <div class="form-group">
                <label for="password">Password (6-digit PIN)</label>
                <input type="password" id="password" name="password" required pattern="[0-9]{6}" maxlength="6" title="Please enter exactly 6 digits" placeholder="6-digit PIN">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required pattern="[0-9]{6}" maxlength="6" title="Please enter exactly 6 digits" placeholder="Type your 6-digit PIN again">
            </div>

            <button type="submit" class="btn-primary">Create Account</button>
        </form>

        <div class="auth-footer">
            <a href="index.php" class="btn-secondary">Sign In</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
