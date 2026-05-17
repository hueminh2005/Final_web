<?php
/**
 * Reset Password Page
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/session.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$otp = $_GET['otp'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $resetToken = $_POST['token'] ?? '';
    $resetOtp = $_POST['otp'] ?? '';
    
    if (empty($password) || empty($passwordConfirm)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $passwordConfirm) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
    } else {
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/models/User.php';
        
        $userModel = new User($pdo);
        
        if ($userModel->resetPassword($resetToken, $password)) {
            $success = 'Password reset successful. <a href="login.php">Login now</a>';
        } else {
            $error = 'Invalid or expired reset token';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Note Management</title>
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
                <div class="alert alert-success"><?= $success; ?></div>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token); ?>">
                    <input type="hidden" name="otp" value="<?= htmlspecialchars($otp); ?>">
                    
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" required autofocus>
                        <small>At least <?= PASSWORD_MIN_LENGTH; ?> characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirm">Confirm Password</label>
                        <input type="password" id="password_confirm" name="password_confirm" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
?>
