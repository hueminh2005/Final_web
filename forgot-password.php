<?php
/**
 * Forgot Password Page
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/session.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } else {
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/models/User.php';
        require_once __DIR__ . '/services/MailService.php';
        
        $userModel = new User($pdo);
        $result = $userModel->requestPasswordReset($email);
        
        if ($result['success']) {
            $mailService = new MailService();
            $user = $userModel->getUserByEmail($email);
            $mailService->sendPasswordResetEmail($email, $user['display_name'], $result['token'], $result['otp']);
            $success = 'Password reset link has been sent to your email';
        } else {
            $error = 'Email not found';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Note Management</title>
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <h1>Note Management</h1>
            <h2>Reset Password</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required autofocus value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
            </form>
            
            <div class="auth-links">
                <a href="login.php">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>
?>
