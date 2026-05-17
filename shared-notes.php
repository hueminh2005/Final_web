<?php
/**
 * Shared Notes Page
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/session.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Share.php';
require_once __DIR__ . '/models/Label.php';

$shareModel = new Share($pdo);
$labelModel = new Label($pdo);
$page = $_GET['page'] ?? 1;
$sharedNotes = $shareModel->getSharedNotes($_SESSION['user_id'], $page, 20);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shared Notes - Note Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
    <?php include __DIR__ . '/views/layouts/header.php'; ?>
    
    <div class="main-container">
        <?php include __DIR__ . '/views/layouts/sidebar.php'; ?>
        
        <div class="content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1>Shared Notes</h1>
                <a href="index.php" class="btn-small">← My Notes</a>
            </div>
            
            <div id="notesContainer" class="notes-list">
                <?php if (empty($sharedNotes)): ?>
                    <div class="empty-state">
                        <p>No notes shared with you yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($sharedNotes as $note): ?>
                        <div class="note-card" data-note-id="<?= $note['id']; ?>" style="display: grid; grid-template-columns: 1fr auto;">
                            <div>
                                <div class="note-header">
                                    <h3><?= htmlspecialchars($note['title']); ?></h3>
                                </div>
                                <p class="note-preview"><?= substr(strip_tags($note['content']), 0, 100); ?>...</p>
                                <div class="note-meta" style="margin-bottom: 0.5rem;">
                                    <small>
                                        Shared by: <strong><?= htmlspecialchars($note['owner_name']); ?></strong> (<?= htmlspecialchars($note['owner_email']); ?>)<br>
                                        Permission: <strong><?= ucfirst($note['permission']); ?></strong><br>
                                        Shared on: <?= date('M d, Y H:i', strtotime($note['shared_at'])); ?>
                                    </small>
                                </div>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; justify-content: center;">
                                <a href="editor.php?id=<?= $note['id']; ?>" class="btn-small">View/Edit</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/views/layouts/footer.php'; ?>
</body>
</html>
?>
