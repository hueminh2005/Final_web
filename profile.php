<?php
/**
 * User Profile Page
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/session.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Preference.php';

$userModel = new User($pdo);
$currentUser = $userModel->getUserById($_SESSION['user_id']);

if (!$currentUser) {
    header('Location: ' . APP_URL . '/logout.php');
    exit;
}

$error = '';
$success = '';
$preferenceModel = new Preference($pdo);
$preferences = $preferenceModel->getPreferences($_SESSION['user_id']);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $displayName = $_POST['display_name'] ?? '';
        
        if (empty($displayName)) {
            $error = 'Display name cannot be empty';
        } else {
            if ($userModel->updateProfile($_SESSION['user_id'], $displayName)) {
                $success = 'Profile updated successfully';
                $currentUser = $userModel->getUserById($_SESSION['user_id']);
            } else {
                $error = 'Failed to update profile';
            }
        }
    }
    
    if ($_POST['action'] === 'change_password') {
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'Please fill in all password fields';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match';
        } elseif (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
            $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
        } else {
            if ($userModel->changePassword($_SESSION['user_id'], $oldPassword, $newPassword)) {
                $success = 'Password changed successfully';
            } else {
                $error = 'Invalid old password';
            }
        }
    }
    
    if ($_POST['action'] === 'update_preferences') {
        $preferences['font_size'] = (int)($_POST['font_size'] ?? 14);
        $preferences['theme'] = $_POST['theme'] ?? 'light';
        $preferences['default_view'] = $_POST['default_view'] ?? 'grid';
        $preferences['notes_per_page'] = (int)($_POST['notes_per_page'] ?? 20);
        
        if ($preferenceModel->update($_SESSION['user_id'], $preferences)) {
            $success = 'Preferences updated successfully';
        } else {
            $error = 'Failed to update preferences';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Note Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
    <?php include __DIR__ . '/views/layouts/header.php'; ?>
    
    <div class="main-container">
        <div class="content" style="max-width: 600px; margin: 0 auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1>My Profile</h1>
                <a href="index.php" class="btn-small">← Back</a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <!-- Profile Information -->
            <div class="profile-section" style="background: var(--secondary-bg); padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                <h2>Account Information</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" value="<?= htmlspecialchars($currentUser['email']); ?>" disabled style="opacity: 0.6;">
                    </div>
                    
                    <div class="form-group">
                        <label for="display_name">Display Name</label>
                        <input type="text" id="display_name" name="display_name" value="<?= htmlspecialchars($currentUser['display_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="joined_date">Member Since</label>
                        <input type="text" id="joined_date" value="<?= date('F d, Y', strtotime($currentUser['created_at'])); ?>" disabled style="opacity: 0.6;">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
            
            <!-- Change Password -->
            <div class="profile-section" style="background: var(--secondary-bg); padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                <h2>Change Password</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label for="old_password">Current Password</label>
                        <input type="password" id="old_password" name="old_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                        <small>At least <?= PASSWORD_MIN_LENGTH; ?> characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </form>
            </div>
            
            <!-- Preferences -->
            <div class="profile-section" style="background: var(--secondary-bg); padding: 1.5rem; border-radius: 8px;">
                <h2>Preferences</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="update_preferences">
                    
                    <div class="form-group">
                        <label for="font_size">Font Size</label>
                        <select id="font_size" name="font_size">
                            <option value="12" <?= $preferences['font_size'] === 12 ? 'selected' : ''; ?>>Small (12px)</option>
                            <option value="14" <?= $preferences['font_size'] === 14 ? 'selected' : ''; ?>>Medium (14px)</option>
                            <option value="16" <?= $preferences['font_size'] === 16 ? 'selected' : ''; ?>>Large (16px)</option>
                            <option value="18" <?= $preferences['font_size'] === 18 ? 'selected' : ''; ?>>Extra Large (18px)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="theme">Theme</label>
                        <select id="theme" name="theme">
                            <option value="light" <?= $preferences['theme'] === 'light' ? 'selected' : ''; ?>>Light</option>
                            <option value="dark" <?= $preferences['theme'] === 'dark' ? 'selected' : ''; ?>>Dark</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="default_view">Default Note View</label>
                        <select id="default_view" name="default_view">
                            <option value="grid" <?= $preferences['default_view'] === 'grid' ? 'selected' : ''; ?>>Grid</option>
                            <option value="list" <?= $preferences['default_view'] === 'list' ? 'selected' : ''; ?>>List</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes_per_page">Notes Per Page</label>
                        <select id="notes_per_page" name="notes_per_page">
                            <option value="10" <?= $preferences['notes_per_page'] === 10 ? 'selected' : ''; ?>>10</option>
                            <option value="20" <?= $preferences['notes_per_page'] === 20 ? 'selected' : ''; ?>>20</option>
                            <option value="50" <?= $preferences['notes_per_page'] === 50 ? 'selected' : ''; ?>>50</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Preferences</button>
                </form>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/views/layouts/footer.php'; ?>
</body>
</html>
?>
