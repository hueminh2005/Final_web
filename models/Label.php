<?php
/**
 * Label Model
 */

class Label {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Create a new label
     */
    public function create($userId, $name, $color = '#999999') {
        try {
            $stmt = $this->pdo->prepare('
                INSERT INTO labels (user_id, name, color)
                VALUES (:user_id, :name, :color)
            ');
            
            $result = $stmt->execute([
                ':user_id' => $userId,
                ':name' => $name,
                ':color' => $color
            ]);
            
            return $result ? $this->pdo->lastInsertId() : false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get all labels for user
     */
    public function getUserLabels($userId) {
        try {
            $stmt = $this->pdo->prepare('
                SELECT * FROM labels 
                WHERE user_id = :user_id
                ORDER BY created_at ASC
            ');
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get label by ID
     */
    public function getLabelById($labelId, $userId = null) {
        try {
            if ($userId) {
                $stmt = $this->pdo->prepare('
                    SELECT * FROM labels 
                    WHERE id = :id AND user_id = :user_id
                ');
                $stmt->execute([':id' => $labelId, ':user_id' => $userId]);
            } else {
                $stmt = $this->pdo->prepare('SELECT * FROM labels WHERE id = :id');
                $stmt->execute([':id' => $labelId]);
            }
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Update label
     */
    public function update($labelId, $userId, $name, $color = null) {
        try {
            $oldLabel = $this->getLabelById($labelId, $userId);
            if (!$oldLabel) {
                return false;
            }
            
            if ($color) {
                $stmt = $this->pdo->prepare('
                    UPDATE labels 
                    SET name = :name, color = :color
                    WHERE id = :id AND user_id = :user_id
                ');
                return $stmt->execute([
                    ':name' => $name,
                    ':color' => $color,
                    ':id' => $labelId,
                    ':user_id' => $userId
                ]);
            } else {
                $stmt = $this->pdo->prepare('
                    UPDATE labels 
                    SET name = :name
                    WHERE id = :id AND user_id = :user_id
                ');
                return $stmt->execute([
                    ':name' => $name,
                    ':id' => $labelId,
                    ':user_id' => $userId
                ]);
            }
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Delete label (but keep notes)
     */
    public function delete($labelId, $userId) {
        try {
            $stmt = $this->pdo->prepare('
                DELETE FROM labels 
                WHERE id = :id AND user_id = :user_id
            ');
            
            return $stmt->execute([
                ':id' => $labelId,
                ':user_id' => $userId
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Attach label to note
     */
    public function attachToNote($noteId, $labelId) {
        try {
            $stmt = $this->pdo->prepare('
                INSERT INTO note_labels (note_id, label_id)
                VALUES (:note_id, :label_id)
                ON DUPLICATE KEY UPDATE created_at = NOW()
            ');
            
            return $stmt->execute([
                ':note_id' => $noteId,
                ':label_id' => $labelId
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Remove label from note
     */
    public function removeFromNote($noteId, $labelId) {
        try {
            $stmt = $this->pdo->prepare('
                DELETE FROM note_labels 
                WHERE note_id = :note_id AND label_id = :label_id
            ');
            
            return $stmt->execute([
                ':note_id' => $noteId,
                ':label_id' => $labelId
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get labels for note
     */
    public function getNotesLabels($noteId) {
        try {
            $stmt = $this->pdo->prepare('
                SELECT l.* FROM labels l
                INNER JOIN note_labels nl ON l.id = nl.label_id
                WHERE nl.note_id = :note_id
                ORDER BY l.created_at ASC
            ');
            $stmt->execute([':note_id' => $noteId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get notes by labels
     */
    public function getNotesByLabels($userId, $labelIds, $page = 1, $perPage = 20) {
        try {
            $offset = ($page - 1) * $perPage;
            $placeholders = implode(',', array_fill(0, count($labelIds), '?'));
            
            $stmt = $this->pdo->prepare("
                SELECT DISTINCT n.* FROM notes n
                INNER JOIN note_labels nl ON n.id = nl.note_id
                WHERE n.user_id = ? AND nl.label_id IN ($placeholders)
                ORDER BY n.is_pinned DESC, n.created_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $params = array_merge([$userId], $labelIds, [$perPage, $offset]);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
}
?>
