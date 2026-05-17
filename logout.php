<?php
/**
 * Logout
 */

require_once __DIR__ . '/config/session.php';

session_destroy();
$_SESSION = [];

header('Location: ' . APP_URL . '/login.php');
exit;
?>
