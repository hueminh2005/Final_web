<?php
/**
 * Authentication Middleware
 */

class AuthMiddleware {
    public static function check() {
        if (!isset($_SESSION['user_id'])) {
            // If request is AJAX, return JSON 401 instead of redirecting
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            $acceptsJson = isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

            if ($isAjax || $acceptsJson) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }

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
