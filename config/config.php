<?php
// Central Bootstrap Configuration

require_once __DIR__ . '/../includes/SessionManager.php';
SessionManager::startSecureSession();

require_once __DIR__ . '/DotEnv.php';

// Check if installed
$installedLock = __DIR__ . '/../.installed';
if (!file_exists($installedLock)) {
    // Exclude redirecting if we are already in the installer
    if (strpos($_SERVER['SCRIPT_NAME'], '/install/') === false) {
        header('Location: /N2/install/index.php');
        exit;
    }
} else {
    // Load Environment Variables
    $dotenv = new DotEnv(__DIR__ . '/../.env');
    $dotenv->load();

    // Database Connection
    $db_host = getenv('DB_HOST') ?: 'localhost';
    $db_port = getenv('DB_PORT') ?: '3306';
    $db_name = getenv('DB_NAME');
    $db_user = getenv('DB_USER');
    $db_pass = getenv('DB_PASS');

    try {
        $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        // Log technical error, display professional error
        error_log("Database connection failed: " . $e->getMessage());
        die("<h1>Service Unavailable</h1><p>The system is currently unable to connect to the database. Please try again later.</p>");
    }

    // Load System Settings from DB
    $settings = [];
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Exception $e) {
        // system_settings table might not exist yet if installing?
        // Actually, if .installed exists, it must exist.
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
}
