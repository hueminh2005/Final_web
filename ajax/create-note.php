<?php
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

$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$noteColor = $_POST['note_color'] ?? '#ffffff';

if (!$title || !$content) {
    http_response_code(400);
    echo json_encode(['error' => 'Thiếu tiêu đề hoặc nội dung']);
    exit;
}

try {
    $noteModel = new Note($pdo);
    $noteId = $noteModel->create($_SESSION['user_id'], $title, $content, $noteColor);

    if ($noteId) {
        echo json_encode(['success' => true, 'note_id' => $noteId]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Không thể tạo ghi chú']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
