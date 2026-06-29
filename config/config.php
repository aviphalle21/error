<?php
// Central Bootstrap Configuration

require_once __DIR__ . '/../includes/SessionManager.php';
SessionManager::startSecureSession();

require_once __DIR__ . '/DotEnv.php';

// Check if installed
$installedLock = __DIR__ . '/../.installed';
$isInstaller = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/install/') !== false;

if (!file_exists($installedLock) && !$isInstaller) {
    header('Location: /N2/install/index.php');
    exit;
}

// Load Environment Variables when available. Installer steps after database setup
// need the same PDO connection before the final .installed lock is created.
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $dotenv = new DotEnv($envFile);
    $dotenv->load();

    $db_host = getenv('DB_HOST') ?: 'localhost';
    $db_port = getenv('DB_PORT') ?: '3306';
    $db_name = getenv('DB_NAME');
    $db_user = getenv('DB_USER');
    $db_pass = getenv('DB_PASS');

    if ($db_name && $db_user !== false) {
        try {
            $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
            $pdo = new PDO($dsn, $db_user, $db_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            if ($isInstaller && !file_exists($installedLock)) {
                $pdo = null;
            } else {
                die("<h1>Service Unavailable</h1><p>The system is currently unable to connect to the database. Please try again later.</p>");
            }
        }
    }
}

if (!file_exists($installedLock) && $isInstaller && !isset($pdo)) {
    // Step 1/2 can run without an existing database connection; later steps will
    // show a clear error if the environment file was not created successfully.
    $pdo = null;
}

// Load System Settings from DB
$settings = [];
if (isset($pdo) && $pdo instanceof PDO) {
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Exception $e) {
        // The installer may still be creating the schema.
    }
}

// Set Timezone
$timezone = $settings['timezone'] ?? 'Asia/Kolkata';
date_default_timezone_set($timezone);

// Global settings access function
if (!function_exists('getSetting')) {
    function getSetting($key, $default = '') {
        global $settings;
        return $settings[$key] ?? $default;
    }
}
