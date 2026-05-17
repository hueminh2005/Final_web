<?php
/**
 * Authentication Middleware
 */

class AuthMiddleware {
    public static function check() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/login.php');
            exit;
        }
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public static function requireGuest() {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/index.php');
            exit;
        }
    }
    
    public static function requireVerified($pdo) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        $stmt = $pdo->prepare('SELECT is_verified FROM users WHERE id = :id');
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        return $user && $user['is_verified'];
    }
}
?>
