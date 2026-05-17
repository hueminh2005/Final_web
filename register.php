<?php
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/session.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . APP_URL . '/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $displayName = $_POST['display_name'] ?? '';
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if (empty($email) || empty($displayName) || empty($password) || empty($passwordConfirm)) {
        $error = 'Please fill in all the information.';
    } elseif ($password !== $passwordConfirm) {
        $error = "The verification password doesn't match";
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $error = 'The password must have at least ' . PASSWORD_MIN_LENGTH . ' characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email.';
    } else {
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/models/User.php';
        require_once __DIR__ . '/services/MailService.php';

        $userModel = new User($pdo);

        if ($userModel->getUserByEmail($email)) {
            $error = 'This email address has already been registered.';
        } else {
            $result = $userModel->register($email, $displayName, $password);

            if ($result['success']) {
                $mailService = new MailService();
                @$mailService->sendVerificationEmail($email, $displayName, $result['token']);

                $_SESSION['user_id'] = $result['user_id'];
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $displayName;
                $_SESSION['verification_pending'] = true;

                header('Location: ' . APP_URL . '/index.php');
                exit;
            } else {
                $error = $result['error'] ?? 'Registration failed.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - NoteCraft</title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="auth-wrapper">

        <!-- Panel phải (form) - đặt trước để form ở bên trái -->
        <div class="auth-form-panel">
            <h1>Create an account</h1>
            <p class="auth-subtitle">Fill in your information to start using NoteCraft.</p>

            <?php if ($error): ?>
                <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" autocomplete="on">
                <div class="input-group">
                    <span class="input-icon">✉️</span>
                    <input type="email" name="email" placeholder="Email address" required autofocus
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="input-group">
                    <span class="input-icon">👤</span>
                    <input type="text" name="display_name" placeholder="Display name" required
                           value="<?= htmlspecialchars($_POST['display_name'] ?? '') ?>">
                </div>

                <div class="input-group">
                    <span class="input-icon">🔒</span>
                    <input type="password" name="password" placeholder="Password" required>
                    <small>At least <?= PASSWORD_MIN_LENGTH ?> characters</small>
                </div>

                <div class="input-group">
                    <span class="input-icon">🔒</span>
                    <input type="password" name="password_confirm" placeholder="Confirm password" required>
                </div>

                <button type="submit" class="btn-auth">Create an account</button>
            </form>
        </div>

        <!-- Panel phải (màu) -->
        <div class="auth-panel">
            <div class="auth-panel-logo">📝</div>
            <h2>Do you already have an account?</h2>
            <p>Log in to continue<br>Manage your notes.</p>
            <a href="login.php" class="auth-panel-btn">LOGIN</a>
        </div>

    </div>
</body>
</html>