<?php
/**
 * Note Model
 */

class Note {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Create a new note
     */
    public function create($userId, $title, $content) {
        try {
            $stmt = $this->pdo->prepare('
                INSERT INTO notes (user_id, title, content)
                VALUES (:user_id, :title, :content)
            ');
            
            $result = $stmt->execute([
                ':user_id' => $userId,
                ':title' => $title,
                ':content' => $content
            ]);
            
            return $result ? $this->pdo->lastInsertId() : false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Update note
     */
    public function update($noteId, $userId, $title, $content) {
        try {
            $stmt = $this->pdo->prepare('
                UPDATE notes 
                SET title = :title, content = :content, updated_at = NOW()
                WHERE id = :id AND user_id = :user_id
            ');
            
            return $stmt->execute([
                ':title' => $title,
                ':content' => $content,
                ':id' => $noteId,
                ':user_id' => $userId
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get note by ID
     */
    public function getNoteById($noteId, $userId = null) {
        try {
            if ($userId) {
                $stmt = $this->pdo->prepare('
                    SELECT * FROM notes 
                    WHERE id = :id AND user_id = :user_id
                ');
                $stmt->execute([':id' => $noteId, ':user_id' => $userId]);
            } else {
                $stmt = $this->pdo->prepare('SELECT * FROM notes WHERE id = :id');
                $stmt->execute([':id' => $noteId]);
            }
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Get all notes for user
     */
    public function getUserNotes($userId, $page = 1, $perPage = 20) {
        try {
            $offset = ($page - 1) * $perPage;
            
            $stmt = $this->pdo->prepare('
                SELECT * FROM notes 
                WHERE user_id = :user_id
                ORDER BY is_pinned DESC, pinned_at DESC, created_at DESC
                LIMIT :limit OFFSET :offset
            ');
            
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get total notes count for user
     */
    public function getTotalNotesCount($userId) {
        try {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) as count FROM notes WHERE user_id = :user_id');
            $stmt->execute([':user_id' => $userId]);
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Delete note
     */
    public function delete($noteId, $userId) {
        try {
            $stmt = $this->pdo->prepare('
                DELETE FROM notes 
                WHERE id = :id AND user_id = :user_id
            ');
            
            return $stmt->execute([
                ':id' => $noteId,
                ':user_id' => $userId
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Pin/Unpin note
     */
    public function togglePin($noteId, $userId, $isPinned) {
        try {
            if ($isPinned) {
                $stmt = $this->pdo->prepare('
                    UPDATE notes 
                    SET is_pinned = TRUE, pinned_at = NOW()
                    WHERE id = :id AND user_id = :user_id
                ');
            } else {
                $stmt = $this->pdo->prepare('
                    UPDATE notes 
                    SET is_pinned = FALSE, pinned_at = NULL
                    WHERE id = :id AND user_id = :user_id
                ');
            }
            
            return $stmt->execute([
                ':id' => $noteId,
                ':user_id' => $userId
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Search notes
     */
    public function search($userId, $query, $page = 1, $perPage = 20) {
        try {
            $offset = ($page - 1) * $perPage;
            $searchQuery = '%' . $query . '%';
            
            $stmt = $this->pdo->prepare('
                SELECT * FROM notes 
                WHERE user_id = :user_id AND (title LIKE :query OR content LIKE :query)
                ORDER BY is_pinned DESC, created_at DESC
                LIMIT :limit OFFSET :offset
            ');
            
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':query', $searchQuery, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Set password protection
     */
    public function setPasswordProtection($noteId, $userId, $password = null) {
        try {
            if ($password) {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $stmt = $this->pdo->prepare('
                    UPDATE notes 
                    SET is_password_protected = TRUE, password_hash = :password
                    WHERE id = :id AND user_id = :user_id
                ');
                return $stmt->execute([
                    ':password' => $hashedPassword,
                    ':id' => $noteId,
                    ':user_id' => $userId
                ]);
            } else {
                $stmt = $this->pdo->prepare('
                    UPDATE notes 
                    SET is_password_protected = FALSE, password_hash = NULL
                    WHERE id = :id AND user_id = :user_id
                ');
                return $stmt->execute([
                    ':id' => $noteId,
                    ':user_id' => $userId
                ]);
            }
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Verify note password
     */
    public function verifyPassword($noteId, $password) {
        try {
            $stmt = $this->pdo->prepare('
                SELECT password_hash FROM notes 
                WHERE id = :id AND is_password_protected = TRUE
            ');
            $stmt->execute([':id' => $noteId]);
            $note = $stmt->fetch();
            
            if ($note && password_verify($password, $note['password_hash'])) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
