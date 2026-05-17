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
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all the information.';
    } else {
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/models/User.php';

        $userModel = new User($pdo);
        $result = $userModel->login($email, $password);

        if ($result['success']) {
            $_SESSION['user_id'] = $result['user']['id'];
            $_SESSION['user_email'] = $result['user']['email'];
            $_SESSION['user_name'] = $result['user']['display_name'];
            header('Location: ' . APP_URL . '/index.php');
            exit;
        } else {
            $error = $result['error'] ?? 'Login failed.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - NoteCraft</title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="auth-wrapper">

        <!-- Panel trái -->
        <div class="auth-panel">
            <div class="auth-panel-logo">📝</div>
            <h2>Welcome!</h2>
            <p>Don't have an account?<br>Register now to get started</p>
            <a href="register.php" class="auth-panel-btn">Register</a>
        </div>

        <!-- Panel phải (form) -->
        <div class="auth-form-panel">
            <h1>LOGIN</h1>
            <p class="auth-subtitle">Enter your account information</p>

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
                    <span class="input-icon">🔒</span>
                    <input type="password" name="password" placeholder="Password" required>
                </div>

                <a href="forgot-password.php" class="forgot-link">Forgot password?</a>

                <button type="submit" class="btn-auth">LOGIN</button>
            </form>
        </div>

    </div>
</body>
</html>