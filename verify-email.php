<?php
/**
 * Verify Email Page
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/session.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if (!empty($token)) {
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/models/User.php';
    
    $userModel = new User($pdo);
    
    if ($userModel->verifyAccount($token)) {
        $success = 'Email verified successfully! Your account is now active.';
        if (isset($_SESSION['verification_pending'])) {
            unset($_SESSION['verification_pending']);
        }
    } else {
        $error = 'Invalid or expired verification token';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - Note Management</title>
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <h1>Note Management</h1>
            <h2>Email Verification</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <div class="text-center">
                <p><a href="<?= isset($_SESSION['user_id']) ? APP_URL . '/index.php' : APP_URL . '/login.php'; ?>" class="btn btn-primary">
                    <?= isset($_SESSION['user_id']) ? 'Go to Dashboard' : 'Go to Login'; ?>
                </a></p>
            </div>
        </div>
    </div>
</body>
</html>
?>
