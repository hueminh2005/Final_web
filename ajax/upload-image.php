<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../services/UploadService.php';
require_once __DIR__ . '/../services/NoteService.php';

AuthMiddleware::check();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No image file provided']);
    exit;
}

$file = $_FILES['image'];
$upload = UploadService::uploadNoteImage($file);

if (!$upload['success']) {
    http_response_code(400);
    echo json_encode(['error' => $upload['error']]);
    exit;
}

$noteId = $_POST['note_id'] ?? null;
if ($noteId) {
    require_once __DIR__ . '/../models/Note.php';
    $noteModel = new Note($pdo);
    $note = $noteModel->getNoteById($noteId, $_SESSION['user_id']);
    if ($note) {
        $noteService = new NoteService($pdo);
        $noteService->addAttachment(
            $noteId,
            $upload['filename'], 
            $upload['filepath'], 
            $file['size'], 
            $file['type']
        );
    }
}

echo json_encode([
    'success' => true,
    'url' => $upload['url'],
    'filename' => $upload['filename']
]);
