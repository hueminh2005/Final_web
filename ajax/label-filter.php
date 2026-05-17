<?php
/**
 * Label Filter AJAX
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../models/Label.php';

AuthMiddleware::check();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$labelIds = $_GET['labels'] ?? [];
$page = $_GET['page'] ?? 1;

if (!is_array($labelIds) || count($labelIds) === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'No labels specified']);
    exit;
}

try {
    $labelModel = new Label($pdo);
    $notes = $labelModel->getNotesByLabels($_SESSION['user_id'], $labelIds, $page, ITEMS_PER_PAGE);
    
    // Add labels to each note
    foreach ($notes as &$note) {
        $note['labels'] = $labelModel->getNotesLabels($note['id']);
    }
    
    echo json_encode([
        'success' => true,
        'notes' => $notes,
        'count' => count($notes)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
