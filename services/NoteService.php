<?php
/**
 * Note Service
 */

class NoteService {
    private $pdo;
    private $noteModel;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        require_once __DIR__ . '/../models/Note.php';
        $this->noteModel = new Note($pdo);
    }
    
    /**
     * Create note from API
     */
    public function createNote($userId, $title, $content) {
        return $this->noteModel->create($userId, $title, $content);
    }
    
    /**
     * Auto-save note
     */
    public function autoSaveNote($noteId, $userId, $title, $content) {
        return $this->noteModel->update($noteId, $userId, $title, $content);
    }
    
    /**
     * Get note with all attachments and labels
     */
    public function getNoteComplete($noteId, $userId = null) {
        $note = $this->noteModel->getNoteById($noteId, $userId);
        
        if (!$note) {
            return null;
        }
        
        // Get attachments
        $stmt = $this->pdo->prepare('
            SELECT * FROM note_attachments WHERE note_id = :note_id
        ');
        $stmt->execute([':note_id' => $noteId]);
        $note['attachments'] = $stmt->fetchAll();
        
        // Get labels
        require_once __DIR__ . '/../models/Label.php';
        $labelModel = new Label($this->pdo);
        $note['labels'] = $labelModel->getNotesLabels($noteId);
        
        // Get share info
        require_once __DIR__ . '/../models/Share.php';
        $shareModel = new Share($this->pdo);
        $note['shares'] = $shareModel->getShareRecipients($noteId, $note['user_id']);
        
        return $note;
    }
    
    /**
     * Delete note with all attachments
     */
    public function deleteNoteComplete($noteId, $userId) {
        try {
            // Get attachments
            $stmt = $this->pdo->prepare('SELECT * FROM note_attachments WHERE note_id = :note_id');
            $stmt->execute([':note_id' => $noteId]);
            $attachments = $stmt->fetchAll();
            
            // Delete files
            foreach ($attachments as $attachment) {
                UploadService::deleteFile($attachment['file_path']);
            }
            
            // Delete note (cascades will handle attachments, labels, shares)
            return $this->noteModel->delete($noteId, $userId);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Add attachment to note
     */
    public function addAttachment($noteId, $fileName, $filePath, $fileSize, $mimeType) {
        try {
            $stmt = $this->pdo->prepare('
                INSERT INTO note_attachments (note_id, file_name, file_path, file_size, mime_type)
                VALUES (:note_id, :file_name, :file_path, :file_size, :mime_type)
            ');
            
            return $stmt->execute([
                ':note_id' => $noteId,
                ':file_name' => $fileName,
                ':file_path' => $filePath,
                ':file_size' => $fileSize,
                ':mime_type' => $mimeType
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
