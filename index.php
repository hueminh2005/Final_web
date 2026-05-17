<?php
/**
 * Main Application Entry Point
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/session.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Load dependencies
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Note.php';
require_once __DIR__ . '/models/Label.php';
require_once __DIR__ . '/models/Preference.php';

// Get current user
$userModel = new User($pdo);
$currentUser = $userModel->getUserById($_SESSION['user_id']);

if (!$currentUser) {
    session_destroy();
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get user preferences
require_once __DIR__ . '/models/Preference.php';
$preferenceModel = new Preference($pdo);
$preferences = $preferenceModel->getPreferences($_SESSION['user_id']);

// Get notes
$noteModel = new Note($pdo);
$page = $_GET['page'] ?? 1;
$notes = $noteModel->getUserNotes($_SESSION['user_id'], $page, $preferences['notes_per_page']);
$totalNotes = $noteModel->getTotalNotesCount($_SESSION['user_id']);

// Get labels
$labelModel = new Label($pdo);
$labels = $labelModel->getUserLabels($_SESSION['user_id']);

// Calculate pagination
$totalPages = ceil($totalNotes / $preferences['notes_per_page']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Note Management - Home</title>
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <meta name="theme-color" content="#007bff">
    <meta name="apple-mobile-web-app-capable" content="yes">
</head>
<body class="theme-<?= htmlspecialchars($preferences['theme']); ?>" style="font-size: <?= $preferences['font_size']; ?>px;">
    <?php include __DIR__ . '/views/layouts/header.php'; ?>
    
    <div class="main-container">
        <?php include __DIR__ . '/views/layouts/sidebar.php'; ?>
        
        <div class="content">
            <!-- Verification Alert -->
            <?php if (!$currentUser['is_verified']): ?>
                <div class="alert alert-warning" role="alert">
                    <strong>Account Verification Pending</strong> - Please check your email to verify your account.
                    <a href="resend-verification.php">Resend verification email</a>
                </div>
            <?php endif; ?>
            
            <!-- Search Bar -->
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Search notes..." class="search-input">
                <span id="searchClear" class="search-clear" style="display: none;">✕</span>
            </div>
            
            <!-- View Toggle -->
            <div class="view-toggle">
                <button id="gridViewBtn" class="view-btn active" title="Grid View">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                        <rect x="2" y="2" width="7" height="7"/>
                        <rect x="11" y="2" width="7" height="7"/>
                        <rect x="2" y="11" width="7" height="7"/>
                        <rect x="11" y="11" width="7" height="7"/>
                    </svg>
                </button>
                <button id="listViewBtn" class="view-btn" title="List View">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                        <rect x="2" y="3" width="16" height="2"/>
                        <rect x="2" y="9" width="16" height="2"/>
                        <rect x="2" y="15" width="16" height="2"/>
                    </svg>
                </button>
            </div>
            
            <!-- Notes Container -->
            <div id="notesContainer" class="notes-<?= $preferences['default_view']; ?>">
                <?php if (empty($notes)): ?>
                    <div class="empty-state">
                        <p>No notes yet. <a href="editor.php">Create your first note</a></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notes as $note): ?>
                        <div class="note-card" data-note-id="<?= $note['id']; ?>">
                            <div class="note-header">
                                <h3><?= htmlspecialchars($note['title']); ?></h3>
                                <div class="note-icons">
                                    <?php if ($note['is_pinned']): ?>
                                        <span class="icon-pinned" title="Pinned">📌</span>
                                    <?php endif; ?>
                                    <?php if ($note['is_password_protected']): ?>
                                        <span class="icon-locked" title="Password Protected">🔒</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <p class="note-preview"><?= substr(strip_tags($note['content']), 0, 100); ?>...</p>
                            <div class="note-labels">
                                <?php 
                                $noteLabels = $labelModel->getNotesLabels($note['id']);
                                foreach ($noteLabels as $label): 
                                ?>
                                    <span class="label" style="background-color: <?= htmlspecialchars($label['color']); ?>">
                                        <?= htmlspecialchars($label['name']); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                            <div class="note-meta">
                                <small><?= date('M d, Y H:i', strtotime($note['updated_at'])); ?></small>
                            </div>
                            <div class="note-actions">
                                <a href="editor.php?id=<?= $note['id']; ?>" class="btn-small">Edit</a>
                                <button class="btn-small btn-pin-toggle" data-pinned="<?= $note['is_pinned'] ? '1' : '0'; ?>">
                                    <?= $note['is_pinned'] ? 'Unpin' : 'Pin'; ?>
                                </button>
                                <button class="btn-small btn-delete">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i; ?>" class="page-link <?= $page == $i ? 'active' : ''; ?>">
                            <?= $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Create Note FAB -->
    <a href="editor.php" class="fab">+</a>
    
    <?php include __DIR__ . '/views/layouts/footer.php'; ?>
    
    <script src="assets/js/app.js"></script>
    <script src="offline/service-worker-init.js"></script>
    <script>
        const preferences = <?= json_encode($preferences); ?>;
        const currentUser = <?= json_encode($currentUser); ?>;
    </script>
</body>
</html>
?>
