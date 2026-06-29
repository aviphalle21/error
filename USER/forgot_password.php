<?php
// USER/forgot_password.php
require_once 'config.php';
require_once '../includes/SessionManager.php';
require_once '../includes/Security.php';

SessionManager::startSecureSession();

$csrfToken = Security::generateCSRFToken();

// Clear previous reset sessions when landing here
unset($_SESSION['reset_user_id']);
unset($_SESSION['reset_email']);
unset($_SESSION['reset_email_fake']);
unset($_SESSION['otp_verified']);
unset($_SESSION['reset_token_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - User Portal</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <script src="theme.js"></script>
    <style>
        .hidden { display: none; }
        .spinner {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
            vertical-align: middle;
            margin-right: 8px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .otp-input {
            letter-spacing: 5px;
            font-size: 1.5rem;
            text-align: center;
            font-weight: bold;
        }
        #alert-box {
            display: none;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="auth-header">
        <img src="../IMAGES/SHREE SARASWATI ABHYASIKA LOGO.png" alt="Saraswati Abhyasika Logo" class="auth-logo">
        <h1>सरस्वती अभ्यासिका</h1>
        <p>Reset Your Password</p>
    </div>

    <div id="alert-box" class="alert alert-error"></div>

    <!-- Step 1: Email -->
    <div id="step-1">
        <p style="text-align:center; color: var(--text-muted); font-size: 0.9rem; margin-bottom: 20px;">
            Enter your registered email address and we will send you a 6-digit OTP to reset your password.
        </p>
        <form id="form-email" onsubmit="sendOTP(event)">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="rahul@example.com">
            </div>
            <button type="submit" id="btn-send-otp" class="btn-primary">Send OTP</button>
        </form>
    </div>

    <!-- Step 2: OTP -->
    <div id="step-2" class="hidden">
        <p style="text-align:center; color: var(--text-muted); font-size: 0.9rem; margin-bottom: 20px;">
            We sent a 6-digit code to your email. It will expire in 5 minutes.
        </p>
        <form id="form-otp" onsubmit="verifyOTP(event)">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <div class="form-group">
                <input type="text" id="otp" name="otp" class="otp-input" required maxlength="6" pattern="[0-9]{6}" placeholder="------">
            </div>
            <button type="submit" id="btn-verify-otp" class="btn-primary">Verify OTP</button>
        </form>
        <div style="text-align:center; margin-top: 15px;">
            <a href="#" onclick="showStep(1); return false;" style="font-size: 0.9rem; color: var(--primary);">Use a different email</a>
        </div>
    </div>

    <!-- Step 3: Reset Password -->
    <div id="step-3" class="hidden">
        <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-size: 0.85rem; color: #555;">
            <strong>Password Requirements:</strong>
            <ul style="margin: 5px 0 0 20px; padding: 0;">
                <li>Minimum 8 characters</li>
                <li>One uppercase, one lowercase</li>
                <li>One number, one special character</li>
            </ul>
        </div>
        <form id="form-password" onsubmit="resetPassword(event)">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter strong password">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="Type your password again">
            </div>
            <button type="submit" id="btn-reset-password" class="btn-primary">Save New Password</button>
        </form>
    </div>

    <div class="auth-footer">
        <a href="index.php" class="btn-secondary">Back to Login</a>
    </div>
</div>

<script>
    function showAlert(message, type = 'error') {
        const alertBox = document.getElementById('alert-box');
        alertBox.textContent = message;
        alertBox.className = `alert alert-${type}`;
        alertBox.style.display = 'block';
    }

    function hideAlert() {
        document.getElementById('alert-box').style.display = 'none';
    }

    function showStep(step) {
        hideAlert();
        document.getElementById('step-1').classList.add('hidden');
        document.getElementById('step-2').classList.add('hidden');
        document.getElementById('step-3').classList.add('hidden');
        document.getElementById(`step-${step}`).classList.remove('hidden');
    }

    async function sendOTP(e) {
        e.preventDefault();
        hideAlert();
        const btn = document.getElementById('btn-send-otp');
        const form = document.getElementById('form-email');
        const formData = new FormData(form);

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Sending OTP...';

        try {
            const response = await fetch('../api/send_otp.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                showStep(2);
                showAlert(data.message, 'success');
            } else {
                showAlert(data.message, 'error');
            }
        } catch (error) {
            showAlert('A network error occurred. Please try again.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Send OTP';
        }
    }

    async function verifyOTP(e) {
        e.preventDefault();
        hideAlert();
        const btn = document.getElementById('btn-verify-otp');
        const form = document.getElementById('form-otp');
        const formData = new FormData(form);

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Verifying...';

        try {
            const response = await fetch('../api/verify_otp.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                showStep(3);
                showAlert(data.message, 'success');
            } else {
                showAlert(data.message, 'error');
            }
        } catch (error) {
            showAlert('A network error occurred. Please try again.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Verify OTP';
        }
    }

    async function resetPassword(e) {
        e.preventDefault();
        hideAlert();
        const btn = document.getElementById('btn-reset-password');
        const form = document.getElementById('form-password');
        const formData = new FormData(form);

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Saving...';

        try {
            const response = await fetch('../api/reset_password.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                window.location.href = 'index.php?reset=success';
            } else {
                showAlert(data.message, 'error');
                btn.disabled = false;
                btn.innerHTML = 'Save New Password';
            }
        } catch (error) {
            showAlert('A network error occurred. Please try again.', 'error');
            btn.disabled = false;
            btn.innerHTML = 'Save New Password';
        }
    }
</script>

</body>
</html>
