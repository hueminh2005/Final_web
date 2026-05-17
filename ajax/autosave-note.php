<?php
/**
 * Auto-save Note AJAX
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
$title = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';

if (!$noteId || !$title || !$content) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

try {
    $noteModel = new Note($pdo);
    $result = $noteModel->update($noteId, $_SESSION['user_id'], $title, $content);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Note saved successfully',
            'saved_at' => date('Y-m-d H:i:s')
        ]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Failed to save note']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
