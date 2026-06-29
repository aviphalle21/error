<?php
require_once 'config.php';
echo "Session ID: " . session_id() . "<br>";
if (isset($_SESSION['csrf_token'])) {
    echo "CSRF Token: " . $_SESSION['csrf_token'] . "<br>";
} else {
    echo "CSRF Token not set.<br>";
}
?>
