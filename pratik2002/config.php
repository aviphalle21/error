<?php
// Admin config.php
require_once __DIR__ . '/../includes/SessionManager.php';
require_once __DIR__ . '/../includes/Security.php';
// Production Error Reporting (Hide from users, log to file)
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../error_log.txt');
define('DB_HOST', 'localhost');
define('DB_NAME', 'library');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Admin Database connection failed: " . $e->getMessage());
    echo $e->getMessage();
}

SessionManager::startSecureSession();
