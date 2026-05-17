<?php
/**
 * Verified Middleware
 */

class VerifiedMiddleware {
    public static function check($pdo) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/login.php');
            exit;
        }
        
        $stmt = $pdo->prepare('SELECT is_verified FROM users WHERE id = :id');
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if (!$user || !$user['is_verified']) {
            $_SESSION['verification_pending'] = true;
        }
    }
}
?>
