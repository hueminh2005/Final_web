<?php
/**
 * Forgot Password Page
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/session.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . APP_URL . '/index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/models/User.php';
        require_once __DIR__ . '/services/MailService.php';
        
        $userModel = new User($pdo);
        $user = $userModel->getUserByEmail($email);
        
        if ($user) {
            $result = $userModel->requestPasswordReset($email);
            
            if ($result['success']) {
                $mailService = new MailService();
                $mailService->sendPasswordResetEmail($email, $user['display_name'], $result['token'], $result['otp']);
                $success = 'Password reset link has been sent to your email';
            } else {
                $error = 'Failed to generate reset token';
            }
        } else {
            $success = 'If this email exists, a password reset link has been sent';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - NoteCraft</title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="auth-wrapper">
        <!-- Panel trái -->
        <div class="auth-panel">
            <div class="auth-panel-logo">📝</div>
            <h2>Remember Your Password?</h2>
            <p>Go back to login and access your account</p>
            <a href="login.php" class="auth-panel-btn">Back to Login</a>
        </div>

        <!-- Panel phải (form) -->
        <div class="auth-form-panel">
            <h1>RESET PASSWORD</h1>
            <p class="auth-subtitle">Enter your email to receive a reset link</p>

            <?php if ($error): ?>
                <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div>
                <p style="text-align: center; color: #64748b; margin-top: 1rem;">
                    Check your email for the password reset link. It may take a few minutes to arrive.
                </p>
            <?php endif; ?>

            <?php if (!$success): ?>
                <form method="POST" autocomplete="on">
                    <div class="input-group">
                        <span class="input-icon">✉️</span>
                        <input type="email" name="email" placeholder="Email address" required autofocus
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>

                    <button type="submit" class="btn-auth">SEND RESET LINK</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
?>
