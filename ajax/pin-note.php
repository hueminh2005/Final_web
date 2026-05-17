<?php
/**
 * Pin Note AJAX
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../models/Note.php';

AuthMiddleware::check();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$noteId = $_POST['note_id'] ?? null;
$isPinned = $_POST['is_pinned'] ?? 0;

if (!$noteId) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing note ID']);
    exit;
}

try {
    $noteModel = new Note($pdo);
    $result = $noteModel->togglePin($noteId, $_SESSION['user_id'], $isPinned);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => $isPinned ? 'Note pinned' : 'Note unpinned'
        ]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Failed to pin/unpin note']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
