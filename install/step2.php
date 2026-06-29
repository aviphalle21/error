<?php
session_start();
$installedLock = __DIR__ . '/../.installed';
if (file_exists($installedLock)) {
    die("<h1>Already Installed</h1>");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['db_host'] ?? 'localhost';
    $port = $_POST['db_port'] ?? '3306';
    $name = $_POST['db_name'] ?? '';
    $user = $_POST['db_user'] ?? '';
    $pass = $_POST['db_pass'] ?? '';
    
    try {
        // 1. Test Connection
        $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        
        // 2. Create Database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name`");
        $pdo->exec("USE `$name`");
        
        // 3. Import Schema
        $schemaFile = __DIR__ . '/../database/schema.sql';
        if (file_exists($schemaFile)) {
            $sql = file_get_contents($schemaFile);
            $pdo->exec($sql);
        } else {
            throw new Exception("Schema file not found in database/schema.sql");
        }
        
        // 4. Write .env
        $envContent = "DB_HOST={$host}\nDB_PORT={$port}\nDB_NAME={$name}\nDB_USER={$user}\nDB_PASS={$pass}\n";
        file_put_contents(__DIR__ . '/../.env', $envContent);
        
        // 5. Next Step
        header("Location: step3.php");
        exit;
        
    } catch (PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Database Setup - Saraswati Abhyasika</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f3f4f6; color: #374151; margin: 0; padding: 40px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #111827; text-align: center; }
        .step { text-align: center; color: #6b7280; font-size: 14px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"], input[type="number"] { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; }
        .btn { display: block; width: 100%; padding: 12px; background: #4f46e5; color: white; text-align: center; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; margin-top: 20px; }
        .btn:hover { background: #4338ca; }
        .alert-error { background: #fee2e2; color: #dc2626; padding: 15px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Database Setup</h1>
        <div class="step">Step 2 of 5: Database Configuration</div>
        
        <?php if ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Database Host</label>
                <input type="text" name="db_host" value="localhost" required>
            </div>
            <div class="form-group">
                <label>Database Port</label>
                <input type="number" name="db_port" value="3306" required>
            </div>
            <div class="form-group">
                <label>Database Name</label>
                <input type="text" name="db_name" value="library_db" required>
            </div>
            <div class="form-group">
                <label>Database Username</label>
                <input type="text" name="db_user" value="root" required>
            </div>
            <div class="form-group">
                <label>Database Password</label>
                <input type="password" name="db_pass">
            </div>
            <button type="submit" class="btn">Test Connection & Build Database</button>
        </form>
    </div>
</body>
</html>
