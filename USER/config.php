<?php
// User/config.php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/SessionManager.php';

// Session Timeout Logic (5 Minutes Inactivity)
if (isset($_SESSION['user_id'])) {
    $timeout_duration = 300; // 5 minutes in seconds
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
        // Session expired
        session_unset();
        session_destroy();
        // Clear auth cookies
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        header("Location: index.php?expired=1");
        exit;
    }
    // Update last activity timestamp
    $_SESSION['last_activity'] = time();
}
?>
