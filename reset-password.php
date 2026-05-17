<?php
/**
 * Reset Password Page
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/session.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . APP_URL . '/index.php');
    exit;
}

$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$otp = $_GET['otp'] ?? '';

if (empty($token)) {
    $error = 'Invalid reset link';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $resetToken = $_POST['token'] ?? '';
    
    if (empty($password) || empty($passwordConfirm)) {
        $error = 'Please fill in all password fields';
    } elseif ($password !== $passwordConfirm) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
    } else {
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/models/User.php';
        
        $userModel = new User($pdo);
        
        if ($userModel->resetPassword($resetToken, $password)) {
            $success = 'Password has been reset successfully';
        } else {
            $error = 'Invalid or expired reset link';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - NoteCraft</title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="auth-wrapper">
        <!-- Panel trái -->
        <div class="auth-panel">
            <div class="auth-panel-logo">🔐</div>
            <h2>Secure Your Account</h2>
            <p>Set a new password to regain access to your notes</p>
            <a href="login.php" class="auth-panel-btn">Back to Login</a>
        </div>

        <!-- Panel phải (form) -->
        <div class="auth-form-panel">
            <h1>NEW PASSWORD</h1>
            <p class="auth-subtitle">Create a strong password for your account</p>

            <?php if ($error): ?>
                <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div>
                <div style="text-align: center; margin-top: 1.5rem;">
                    <p style="color: #64748b; margin-bottom: 1rem;">Your password has been reset. You can now login with your new password.</p>
                    <a href="login.php" class="btn-auth" style="display: inline-block; text-decoration: none; width: auto;">GO TO LOGIN</a>
                </div>
            <?php else: ?>
                <form method="POST" autocomplete="off">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token); ?>">

                    <div class="input-group password-field">
                        <span class="input-icon">🔒</span>
                        <input id="passwordInput" type="password" name="password" placeholder="New password" required autofocus>
                        <button type="button" class="password-toggle" id="togglePassword" aria-label="Show password">👁️</button>
                    </div>
                    <small style="display: block; color: #94a3b8; font-size: 0.78rem; margin-top: 0.3rem; margin-left: 0.25rem;">
                        At least <?= PASSWORD_MIN_LENGTH; ?> characters
                    </small>

                    <div class="input-group password-field" style="margin-top: 1rem;">
                        <span class="input-icon">🔐</span>
                        <input id="confirmPasswordInput" type="password" name="password_confirm" placeholder="Confirm password" required>
                        <button type="button" class="password-toggle" id="toggleConfirmPassword" aria-label="Show password">👁️</button>
                    </div>

                    <button type="submit" class="btn-auth" style="margin-top: 0.75rem;">RESET PASSWORD</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var togglePassword = document.getElementById('togglePassword');
            var passwordInput = document.getElementById('passwordInput');
            var toggleConfirm = document.getElementById('toggleConfirmPassword');
            var confirmPasswordInput = document.getElementById('confirmPasswordInput');

            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function(e) {
                    e.preventDefault();
                    var isPassword = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                    this.textContent = isPassword ? '🙈' : '👁️';
                });
            }

            if (toggleConfirm && confirmPasswordInput) {
                toggleConfirm.addEventListener('click', function(e) {
                    e.preventDefault();
                    var isPassword = confirmPasswordInput.getAttribute('type') === 'password';
                    confirmPasswordInput.setAttribute('type', isPassword ? 'text' : 'password');
                    this.textContent = isPassword ? '🙈' : '👁️';
                });
            }
        });
    </script>
</body>
</html>
?>
