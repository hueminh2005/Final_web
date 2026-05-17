<?php
/**
 * Guest Middleware
 */

class GuestMiddleware {
    public static function check() {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/index.php');
            exit;
        }
    }
}
?>
