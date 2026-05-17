<?php
/**
 * Session Configuration
 */

require_once __DIR__ . '/constants.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_TIMEOUT,
        'path' => '/',
        'domain' => '',
        'secure' => false, // Set to true in production with HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Session timeout check
if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        session_destroy();
        $_SESSION = [];
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}
$_SESSION['last_activity'] = time();

// Regenerate session ID periodically for security
if (!isset($_SESSION['session_created'])) {
    $_SESSION['session_created'] = time();
} elseif (time() - $_SESSION['session_created'] > 3600) {
    session_regenerate_id(true);
    $_SESSION['session_created'] = time();
}
?>
