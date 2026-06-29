<?php
require_once 'config.php';

if (!isset($_SESSION['registered_unique_id'])) {
    header("Location: index.php");
    exit;
}
$uniqueId = $_SESSION['registered_unique_id'];
unset($_SESSION['registered_unique_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration Successful</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box text-center">
            <h2 style="color: #2e7d32;">Registration Successful!</h2>
            <div class="alert alert-success" style="margin-top: 20px;">
                <p>Your account has been created successfully.</p>
                <p>Your Unique User ID is:</p>
                <h3 style="letter-spacing: 2px; font-weight: bold; font-size: 24px; color: #1e3a8a; margin: 10px 0;"><?= htmlspecialchars($uniqueId) ?></h3>
                <p style="font-weight: bold; color: #c62828;">Please save this ID. You will need it to log in.</p>
            </div>
            
            <a href="index.php" class="btn btn-primary" style="display:inline-block; margin-top: 20px;">Proceed to Login</a>
        </div>
    </div>
</body>
</html>
