<?php
/**
 * Labels Management Page
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/session.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Label.php';

$labelModel = new Label($pdo);
$labels = $labelModel->getUserLabels($_SESSION['user_id']);
$error = '';
$success = '';

// Handle label operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $name = $_POST['label_name'] ?? '';
        $color = $_POST['label_color'] ?? '#999999';
        
        if (empty($name)) {
            $error = 'Label name cannot be empty';
        } else {
            if ($labelModel->create($_SESSION['user_id'], $name, $color)) {
                $success = 'Label created successfully';
                $labels = $labelModel->getUserLabels($_SESSION['user_id']);
            } else {
                $error = 'Failed to create label';
            }
        }
    } elseif ($action === 'delete') {
        $labelId = $_POST['label_id'] ?? null;
        if ($labelModel->delete($labelId, $_SESSION['user_id'])) {
            $success = 'Label deleted successfully';
            $labels = $labelModel->getUserLabels($_SESSION['user_id']);
        } else {
            $error = 'Failed to delete label';
        }
    } elseif ($action === 'update') {
        $labelId = $_POST['label_id'] ?? null;
        $name = $_POST['label_name'] ?? '';
        $color = $_POST['label_color'] ?? '#999999';
        
        if ($labelModel->update($labelId, $_SESSION['user_id'], $name, $color)) {
            $success = 'Label updated successfully';
            $labels = $labelModel->getUserLabels($_SESSION['user_id']);
        } else {
            $error = 'Failed to update label';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Labels - Note Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
    <?php include __DIR__ . '/views/layouts/header.php'; ?>
    
    <div class="main-container">
        <div class="content" style="max-width: 600px; margin: 0 auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1>Manage Labels</h1>
                <a href="index.php" class="btn-small">← Back</a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <!-- Create Label -->
            <div style="background: var(--secondary-bg); padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                <h2>Create New Label</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="form-group">
                        <label for="label_name">Label Name</label>
                        <input type="text" id="label_name" name="label_name" placeholder="e.g., Work, Personal" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="label_color">Color</label>
                        <input type="color" id="label_color" name="label_color" value="#999999">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Create Label</button>
                </form>
            </div>
            
            <!-- Labels List -->
            <div style="background: var(--secondary-bg); padding: 1.5rem; border-radius: 8px;">
                <h2>Your Labels (<?= count($labels); ?>)</h2>
                
                <?php if (empty($labels)): ?>
                    <p style="color: var(--text-light);">No labels yet. Create one above!</p>
                <?php else: ?>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border-color);">
                                <th style="text-align: left; padding: 0.75rem; color: var(--text-light);">Color</th>
                                <th style="text-align: left; padding: 0.75rem; color: var(--text-light);">Name</th>
                                <th style="text-align: right; padding: 0.75rem; color: var(--text-light);">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($labels as $label): ?>
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 0.75rem;">
                                        <div style="width: 24px; height: 24px; background-color: <?= htmlspecialchars($label['color']); ?>; border-radius: 4px;"></div>
                                    </td>
                                    <td style="padding: 0.75rem;"><?= htmlspecialchars($label['name']); ?></td>
                                    <td style="padding: 0.75rem; text-align: right;">
                                        <button type="button" class="btn-small" onclick="editLabel(<?= $label['id']; ?>)">Edit</button>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="label_id" value="<?= $label['id']; ?>">
                                            <button type="submit" class="btn-small btn-delete" onclick="return confirm('Delete this label?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/views/layouts/footer.php'; ?>
</body>
</html>
?>
