<?php
require_once __DIR__ . '/../config/config.php';

if (file_exists(__DIR__ . '/../.installed')) {
    die("<h1>Already Installed</h1>");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['admin_name'] ?? '';
    $email = $_POST['admin_email'] ?? '';
    $username = $_POST['admin_username'] ?? '';
    $password = $_POST['admin_pass'] ?? '';
    $cpassword = $_POST['admin_cpass'] ?? '';
    
    if ($password !== $cpassword) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        try {
            if (!$pdo instanceof PDO) {
                throw new Exception('Database connection is not available. Please complete database setup first.');
            }
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admin (name, email, username, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $username, $hashed]);
            
            header("Location: step5.php");
            exit;
        } catch (Exception $e) {
            $error = "Failed to create admin: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Setup - Saraswati Abhyasika</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f3f4f6; color: #374151; margin: 0; padding: 40px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #111827; text-align: center; }
        .step { text-align: center; color: #6b7280; font-size: 14px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; }
        .btn { display: block; width: 100%; padding: 12px; background: #4f46e5; color: white; text-align: center; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; margin-top: 20px; }
        .btn:hover { background: #4338ca; }
        .alert-error { background: #fee2e2; color: #dc2626; padding: 15px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Administrator Account</h1>
        <div class="step">Step 4 of 5: Super Admin Creation</div>
        
        <?php if ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Admin Name</label>
                <input type="text" name="admin_name" required>
            </div>
            <div class="form-group">
                <label>Admin Email</label>
                <input type="email" name="admin_email" required>
            </div>
            <div class="form-group">
                <label>Admin Username</label>
                <input type="text" name="admin_username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="admin_pass" required minlength="8">
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="admin_cpass" required minlength="8">
            </div>
            <button type="submit" class="btn">Create Administrator</button>
        </form>
    </div>
</body>
</html>
