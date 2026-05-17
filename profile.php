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
require_once __DIR__ . '/services/UploadService.php';

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
        $displayName = trim($_POST['display_name'] ?? '');
        $avatarUrl = null;

        if (empty($displayName)) {
            $error = 'Display name cannot be empty';
        } else {
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
                $upload = UploadService::uploadAvatar($_FILES['avatar']);
                if ($upload['success']) {
                    $avatarUrl = $upload['url'];

                    if (!empty($currentUser['avatar'])) {
                        $oldAvatar = $currentUser['avatar'];
                        if (strpos($oldAvatar, APP_URL) === 0) {
                            $oldAvatarPath = UPLOAD_DIR . basename($oldAvatar);
                        } elseif (strpos($oldAvatar, '/assets/uploads/') !== false || strpos($oldAvatar, 'assets/uploads/') !== false) {
                            $oldAvatarPath = UPLOAD_DIR . basename($oldAvatar);
                        } else {
                            $oldAvatarPath = UPLOAD_DIR . basename($oldAvatar);
                        }

                        if (!empty($oldAvatarPath) && file_exists($oldAvatarPath)) {
                            @unlink($oldAvatarPath);
                        }
                    }
                } else {
                    $error = 'Avatar upload failed: ' . $upload['error'];
                }
            }

            if (empty($error)) {
                if ($userModel->updateProfile($_SESSION['user_id'], $displayName, $avatarUrl)) {
                    $success = 'Profile updated successfully';
                    $currentUser = $userModel->getUserById($_SESSION['user_id']);
                    $_SESSION['user_name'] = $currentUser['display_name'];
                    $_SESSION['user_avatar'] = $currentUser['avatar'] ?? '';
                } else {
                    $error = 'Failed to update profile';
                }
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
        $preferences['default_note_color'] = $_POST['default_note_color'] ?? '#ffffff';
        
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
        <div class="content profile-page">
            <div class="profile-header">
                <div>
                    <p class="eyebrow">Profile</p>
                    <h1>My Profile</h1>
                </div>
                <a href="index.php" class="btn btn-small">← Back to notes</a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="profile-grid">
                <section class="profile-card profile-summary-card">
                    <div class="profile-avatar-box">
                        <?php if (!empty($currentUser['avatar'])): ?>
                            <img id="avatarPreview" src="<?= htmlspecialchars($currentUser['avatar']); ?>" alt="Profile Avatar" class="profile-avatar">
                        <?php else: ?>
                            <div id="avatarPreview" class="profile-avatar profile-avatar-fallback">
                                <?= htmlspecialchars(mb_substr($currentUser['display_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="profile-summary-content">
                        <p class="profile-label">Welcome back</p>
                        <h2><?= htmlspecialchars($currentUser['display_name']); ?></h2>
                        <p class="profile-email"><?= htmlspecialchars($currentUser['email']); ?></p>
                    </div>

                    <div class="profile-meta-list">
                        <div class="profile-meta-item">
                            <span>Member since</span>
                            <strong><?= date('F d, Y', strtotime($currentUser['created_at'])); ?></strong>
                        </div>
                        <div class="profile-meta-item">
                            <span>Status</span>
                            <strong><?= $currentUser['is_verified'] ? 'Verified' : 'Unverified'; ?></strong>
                        </div>
                    </div>

                    <form method="POST" enctype="multipart/form-data" class="profile-form">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="form-group">
                            <label for="avatarInput">Avatar</label>
                            <input type="file" id="avatarInput" name="avatar" accept="image/png, image/jpeg, image/gif, image/webp">
                            <small>Upload a profile image (Max 5MB).</small>
                        </div>

                        <div class="form-group">
                            <label for="display_name">Display Name</label>
                            <input type="text" id="display_name" name="display_name" value="<?= htmlspecialchars($currentUser['display_name']); ?>" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Update profile</button>
                    </form>
                </section>

                <section class="profile-panel profile-main-panel">
                    <div class="panel-header">
                        <h2>Account security</h2>
                        <p>Update your password to keep your account safe.</p>
                    </div>
                    <form method="POST" class="panel-form">
                        <input type="hidden" name="action" value="change_password">

                        <div class="form-group">
                            <label for="old_password">Current Password</label>
                            <input type="password" id="old_password" name="old_password" required>
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required>
                            <small>At least <?= PASSWORD_MIN_LENGTH; ?> characters.</small>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Change password</button>
                    </form>
                </section>
            </div>

            <div class="profile-grid profile-secondary-grid">
                <section class="profile-panel">
                    <div class="panel-header">
                        <h2>Workspace preferences</h2>
                        <p>Make your notes feel more like you.</p>
                    </div>
                    <form method="POST" class="panel-form">
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
                        
                        <div class="form-group">
                            <label for="default_note_color">Default Note Color</label>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="color" id="default_note_color" name="default_note_color" 
                                       value="<?= htmlspecialchars($preferences['default_note_color'] ?? '#ffffff'); ?>"
                                       style="width: 50px; height: 36px; border: 1px solid var(--border); border-radius: 4px; cursor: pointer;">
                                <small>Choose a default background color for new notes</small>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save preferences</button>
                    </form>
                </section>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/views/layouts/footer.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var avatarInput = document.getElementById('avatarInput');
            var avatarPreview = document.getElementById('avatarPreview');

            if (avatarInput && avatarPreview) {
                avatarInput.addEventListener('change', function(event) {
                    var file = event.target.files[0];
                    if (!file) {
                        return;
                    }

                    if (file.type.startsWith('image/')) {
                        var url = URL.createObjectURL(file);
                        if (avatarPreview.tagName === 'IMG') {
                            avatarPreview.src = url;
                        } else {
                            var image = document.createElement('img');
                            image.id = 'avatarPreview';
                            image.className = 'profile-avatar';
                            image.src = url;
                            avatarPreview.parentNode.replaceChild(image, avatarPreview);
                            avatarPreview = image;
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
?>
