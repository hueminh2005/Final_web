<?php
/**
 * Resend Verification Email
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/session.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/services/MailService.php';

$userModel = new User($pdo);
$currentUser = $userModel->getUserById($_SESSION['user_id']);

if (!$currentUser) {
    header('Location: ' . APP_URL . '/logout.php');
    exit;
}

// If already verified, redirect
if ($currentUser['is_verified']) {
    header('Location: ' . APP_URL . '/index.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mailService = new MailService();
    $token = bin2hex(random_bytes(32));
    
    // Update verification token
    $stmt = $pdo->prepare('
        UPDATE users 
        SET verification_token = :token
        WHERE id = :id
    ');
    
    if ($stmt->execute([':token' => $token, ':id' => $_SESSION['user_id']])) {
        // Send email
        if ($mailService->sendVerificationEmail(
            $currentUser['email'],
            $currentUser['display_name'],
            $token
        )) {
            $message = 'Verification email sent successfully. Please check your inbox.';
        } else {
            $message = 'Failed to send email. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resend Verification - Note Management</title>
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <h1>Note Management</h1>
            <h2>Verify Your Email</h2>
            
            <div class="alert alert-warning" style="margin-bottom: 1.5rem;">
                Your account is not verified yet. Please verify your email to access all features.
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <p style="margin-bottom: 1rem; color: #666;">
                    A verification link will be sent to:<br>
                    <strong><?= htmlspecialchars($currentUser['email']); ?></strong>
                </p>
                
                <button type="submit" class="btn btn-primary btn-block">Send Verification Email</button>
            </form>
            
            <div class="auth-links" style="margin-top: 1rem;">
                <a href="index.php">Continue to Dashboard</a>
                <span>|</span>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</body>
</html>
?>
