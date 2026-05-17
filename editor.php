<?php
/**
 * Note Editor Page
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/session.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Note.php';
require_once __DIR__ . '/models/Label.php';
require_once __DIR__ . '/models/Preference.php';

$userModel = new User($pdo);
$currentUser = $userModel->getUserById($_SESSION['user_id']);

if (!$currentUser) {
    header('Location: ' . APP_URL . '/logout.php');
    exit;
}

$noteId = $_GET['id'] ?? null;
$noteModel = new Note($pdo);
$labelModel = new Label($pdo);
$preferenceModel = new Preference($pdo);
$userPreferences = $preferenceModel->getPreferences($_SESSION['user_id']);

$note = null;
$noteLabels = [];
$noteColor = $userPreferences['default_note_color'] ?? '#ffffff';

if ($noteId) {
    $note = $noteModel->getNoteById($noteId, $_SESSION['user_id']);
    if (!$note) {
        header('Location: ' . APP_URL . '/index.php');
        exit;
    }
    $noteLabels = $labelModel->getNotesLabels($noteId);
    $noteColor = $note['note_color'] ?? '#ffffff';
}

$allLabels = $labelModel->getUserLabels($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $note ? 'Edit Note' : 'Create Note'; ?> - Note Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/editor.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
    <?php include __DIR__ . '/views/layouts/header.php'; ?>
    
    <div class="editor-container">
        <div class="editor-header">
            <a href="index.php" class="btn-back">← Back</a>
            <div class="editor-actions">
                <span id="saveStatus" class="save-status">All changes saved</span>
            </div>
        </div>
        
        <form id="noteForm" class="editor-form">
            <input type="hidden" id="noteId" name="note_id" value="<?= $note ? htmlspecialchars($noteId) : ''; ?>">
            
            <input type="text" id="noteTitle" name="title" placeholder="Note Title" class="note-title" 
                   value="<?= $note ? htmlspecialchars($note['title']) : ''; ?>" required autofocus>
            
            <div class="editor-toolbar">
                <button type="button" class="toolbar-btn" data-action="bold" title="Bold">
                    <strong>B</strong>
                </button>
                <button type="button" class="toolbar-btn" data-action="italic" title="Italic">
                    <em>I</em>
                </button>
                <button type="button" class="toolbar-btn" data-action="underline" title="Underline">
                    <u>U</u>
                </button>
                <button type="button" class="toolbar-btn" data-action="insertImage" title="Insert Image">
                    🖼️
                </button>
                <input id="imageInput" type="file" accept="image/*" style="display:none">
            </div>
            
            <div id="editor" class="rich-editor" contenteditable="true" 
                 style="background-color: <?= htmlspecialchars($noteColor); ?>;"><?= $note ? $note['content'] : ''; ?></div>
            <input type="hidden" id="noteContent" name="content">
            
            <div class="editor-sidebar">
                <div class="labels-section">
                    <h4>Labels</h4>
                    <div id="labelList" class="label-list">
                        <?php foreach ($allLabels as $label): ?>
                            <label class="label-checkbox">
                                <input type="checkbox" value="<?= $label['id']; ?>" 
                                       <?= in_array($label['id'], array_column($noteLabels, 'id')) ? 'checked' : ''; ?>>
                                <span><?= htmlspecialchars($label['name']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="note-options">
                    <h4>Options</h4>
                    <label>
                        <input type="checkbox" id="pinNote" <?= $note && $note['is_pinned'] ? 'checked' : ''; ?>>
                        Pin this note
                    </label>
                    <label>
                        <input type="checkbox" id="passwordProtect" <?= $note && $note['is_password_protected'] ? 'checked' : ''; ?>>
                        Password protect
                    </label>
                    <div id="passwordProtectOptions" style="display: none;">
                        <input type="password" id="notePassword" placeholder="Set password">
                        <input type="password" id="notePasswordConfirm" placeholder="Confirm password">
                    </div>
                    
                    <div style="margin-top: 1rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                        <label for="noteColor">Note Color</label>
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.5rem;">
                            <input type="color" id="noteColor" value="<?= htmlspecialchars($noteColor); ?>" 
                                   style="width: 50px; height: 36px; border: 1px solid var(--border); border-radius: 4px; cursor: pointer;">
                            <div id="noteColorPreview" style="width: 50px; height: 36px; border-radius: 4px; border: 2px solid var(--border);
                                   background-color: <?= htmlspecialchars($noteColor); ?>;" title="Color preview"></div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        
        <div class="editor-footer">
            <button type="button" id="saveBtn" class="btn btn-primary">Save Note</button>
            <button type="button" id="deleteBtn" class="btn btn-danger" style="display: <?= $note ? 'inline-block' : 'none'; ?>">Delete</button>
        </div>
    </div>
    
    <?php include __DIR__ . '/views/layouts/footer.php'; ?>
    
    <script src="assets/js/editor.js"></script>
</body>
</html>
?>
