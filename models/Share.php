<?php
/**
 * Share Model
 */

class Share {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Share note with user
     */
    public function shareNote($noteId, $ownerId, $recipientId, $permission = 'read') {
        try {
            // Check if recipient exists
            $stmt = $this->pdo->prepare('SELECT id FROM users WHERE id = :id');
            $stmt->execute([':id' => $recipientId]);
            if (!$stmt->fetch()) {
                return ['success' => false, 'error' => 'Recipient not found'];
            }
            
            // Create or update share
            $stmt = $this->pdo->prepare('
                INSERT INTO note_shares (note_id, owner_id, recipient_id, permission)
                VALUES (:note_id, :owner_id, :recipient_id, :permission)
                ON DUPLICATE KEY UPDATE permission = :permission, revoked_at = NULL
            ');
            
            $result = $stmt->execute([
                ':note_id' => $noteId,
                ':owner_id' => $ownerId,
                ':recipient_id' => $recipientId,
                ':permission' => $permission
            ]);
            
            if ($result) {
                $shareId = $this->pdo->lastInsertId();
                $this->createShareNotification($recipientId, $shareId);
                return ['success' => true, 'share_id' => $shareId];
            }
            return ['success' => false];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Create share notification
     */
    private function createShareNotification($recipientId, $shareId) {
        try {
            $stmt = $this->pdo->prepare('
                INSERT INTO share_notifications (recipient_id, share_id, is_read)
                VALUES (:recipient_id, :share_id, FALSE)
            ');
            $stmt->execute([
                ':recipient_id' => $recipientId,
                ':share_id' => $shareId
            ]);
        } catch (Exception $e) {
            // Silently fail
        }
    }
    
    /**
     * Get shared notes for user
     */
    public function getSharedNotes($userId, $page = 1, $perPage = 20) {
        try {
            $offset = ($page - 1) * $perPage;
            
            $stmt = $this->pdo->prepare('
                SELECT ns.*, n.*, u.display_name as owner_name, u.email as owner_email
                FROM note_shares ns
                INNER JOIN notes n ON ns.note_id = n.id
                INNER JOIN users u ON ns.owner_id = u.id
                WHERE ns.recipient_id = :user_id AND ns.revoked_at IS NULL
                ORDER BY ns.shared_at DESC
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
     * Get share info
     */
    public function getShareInfo($shareId) {
        try {
            $stmt = $this->pdo->prepare('
                SELECT ns.*, n.*, u.display_name, u.email
                FROM note_shares ns
                INNER JOIN notes n ON ns.note_id = n.id
                INNER JOIN users u ON ns.owner_id = u.id
                WHERE ns.id = :id
            ');
            $stmt->execute([':id' => $shareId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Get share recipients for note
     */
    public function getShareRecipients($noteId, $ownerId) {
        try {
            $stmt = $this->pdo->prepare('
                SELECT ns.*, u.email, u.display_name
                FROM note_shares ns
                INNER JOIN users u ON ns.recipient_id = u.id
                WHERE ns.note_id = :note_id AND ns.owner_id = :owner_id AND ns.revoked_at IS NULL
                ORDER BY ns.shared_at DESC
            ');
            $stmt->execute([
                ':note_id' => $noteId,
                ':owner_id' => $ownerId
            ]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Check if note is shared with user
     */
    public function isSharedWithUser($noteId, $userId) {
        try {
            $stmt = $this->pdo->prepare('
                SELECT * FROM note_shares
                WHERE note_id = :note_id AND recipient_id = :user_id AND revoked_at IS NULL
            ');
            $stmt->execute([
                ':note_id' => $noteId,
                ':user_id' => $userId
            ]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Update share permission
     */
    public function updatePermission($shareId, $ownerId, $permission) {
        try {
            $stmt = $this->pdo->prepare('
                UPDATE note_shares 
                SET permission = :permission
                WHERE id = :id AND owner_id = :owner_id
            ');
            
            return $stmt->execute([
                ':permission' => $permission,
                ':id' => $shareId,
                ':owner_id' => $ownerId
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Revoke share
     */
    public function revokeShare($shareId, $ownerId) {
        try {
            $stmt = $this->pdo->prepare('
                UPDATE note_shares 
                SET revoked_at = NOW()
                WHERE id = :id AND owner_id = :owner_id
            ');
            
            return $stmt->execute([
                ':id' => $shareId,
                ':owner_id' => $ownerId
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get unread share notifications
     */
    public function getUnreadNotifications($userId) {
        try {
            $stmt = $this->pdo->prepare('
                SELECT sn.*, ns.*, u.display_name, u.email
                FROM share_notifications sn
                INNER JOIN note_shares ns ON sn.share_id = ns.id
                INNER JOIN users u ON ns.owner_id = u.id
                WHERE sn.recipient_id = :user_id AND sn.is_read = FALSE
                ORDER BY sn.created_at DESC
            ');
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Mark notification as read
     */
    public function markNotificationAsRead($notificationId) {
        try {
            $stmt = $this->pdo->prepare('
                UPDATE share_notifications 
                SET is_read = TRUE
                WHERE id = :id
            ');
            return $stmt->execute([':id' => $notificationId]);
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
