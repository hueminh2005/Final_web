<?php
/**
 * User Model
 */

class User {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Register new user
     */
    public function register($email, $displayName, $password) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $verificationToken = bin2hex(random_bytes(32));
            
            $stmt = $this->pdo->prepare('
                INSERT INTO users (email, display_name, password, verification_token)
                VALUES (:email, :display_name, :password, :verification_token)
            ');
            
            $result = $stmt->execute([
                ':email' => $email,
                ':display_name' => $displayName,
                ':password' => $hashedPassword,
                ':verification_token' => $verificationToken
            ]);
            
            if ($result) {
                $userId = $this->pdo->lastInsertId();
                // Create default preferences
                $this->createDefaultPreferences($userId);
                return ['success' => true, 'user_id' => $userId, 'token' => $verificationToken];
            }
            return ['success' => false];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Create default preferences for new user
     */
    private function createDefaultPreferences($userId) {
        $stmt = $this->pdo->prepare('
            INSERT INTO user_preferences (user_id, font_size, theme, default_view)
            VALUES (:user_id, 14, "light", "grid")
        ');
        $stmt->execute([':user_id' => $userId]);
    }
    
    /**
     * Login user
     */
    public function login($email, $password) {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                return ['success' => true, 'user' => $user];
            }
            return ['success' => false, 'error' => 'Invalid email or password'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($userId) {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
            $stmt->execute([':id' => $userId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Get user by email
     */
    public function getUserByEmail($email) {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
            $stmt->execute([':email' => $email]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Verify account
     */
    public function verifyAccount($token) {
        try {
            $stmt = $this->pdo->prepare('
                UPDATE users 
                SET is_verified = TRUE, verification_token = NULL
                WHERE verification_token = :token
            ');
            return $stmt->execute([':token' => $token]);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($userId, $displayName, $avatar = null) {
        try {
            if ($avatar) {
                $stmt = $this->pdo->prepare('
                    UPDATE users 
                    SET display_name = :display_name, avatar = :avatar
                    WHERE id = :id
                ');
                return $stmt->execute([
                    ':display_name' => $displayName,
                    ':avatar' => $avatar,
                    ':id' => $userId
                ]);
            } else {
                $stmt = $this->pdo->prepare('
                    UPDATE users 
                    SET display_name = :display_name
                    WHERE id = :id
                ');
                return $stmt->execute([
                    ':display_name' => $displayName,
                    ':id' => $userId
                ]);
            }
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Change password
     */
    public function changePassword($userId, $oldPassword, $newPassword) {
        try {
            $user = $this->getUserById($userId);
            if (!$user || !password_verify($oldPassword, $user['password'])) {
                return false;
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $this->pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
            return $stmt->execute([
                ':password' => $hashedPassword,
                ':id' => $userId
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Request password reset
     */
    public function requestPasswordReset($email) {
        try {
            $user = $this->getUserByEmail($email);
            if (!$user) {
                return ['success' => false];
            }
            
            $token = bin2hex(random_bytes(32));
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            $stmt = $this->pdo->prepare('
                INSERT INTO password_reset_tokens (user_id, token, otp, expires_at)
                VALUES (:user_id, :token, :otp, DATE_ADD(NOW(), INTERVAL 1 HOUR))
            ');
            
            $result = $stmt->execute([
                ':user_id' => $user['id'],
                ':token' => $token,
                ':otp' => $otp
            ]);
            
            return $result ? ['success' => true, 'token' => $token, 'otp' => $otp] : ['success' => false];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Reset password with token
     */
    public function resetPassword($token, $newPassword) {
        try {
            $stmt = $this->pdo->prepare('
                SELECT * FROM password_reset_tokens 
                WHERE token = :token AND expires_at > NOW() AND used_at IS NULL
            ');
            $stmt->execute([':token' => $token]);
            $resetToken = $stmt->fetch();
            
            if (!$resetToken) {
                return false;
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            
            // Update password
            $stmt = $this->pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
            $stmt->execute([
                ':password' => $hashedPassword,
                ':id' => $resetToken['user_id']
            ]);
            
            // Mark token as used
            $stmt = $this->pdo->prepare('
                UPDATE password_reset_tokens 
                SET used_at = NOW() 
                WHERE id = :id
            ');
            $stmt->execute([':id' => $resetToken['id']]);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
