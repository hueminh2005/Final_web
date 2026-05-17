<?php
/**
 * Preference Model
 */

class Preference {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get user preferences
     */
    public function getPreferences($userId) {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM user_preferences WHERE user_id = :user_id');
            $stmt->execute([':user_id' => $userId]);
            $prefs = $stmt->fetch();
            
            if (!$prefs) {
                return $this->getDefaults();
            }
            return $prefs;
        } catch (Exception $e) {
            return $this->getDefaults();
        }
    }
    
    /**
     * Get default preferences
     */
    private function getDefaults() {
        return [
            'font_size' => 14,
            'theme' => 'light',
            'default_view' => 'grid',
            'notes_per_page' => 20
        ];
    }
    
    /**
     * Update preferences
     */
    public function update($userId, $preferences) {
        try {
            $stmt = $this->pdo->prepare('
                UPDATE user_preferences
                SET font_size = :font_size,
                    theme = :theme,
                    default_view = :default_view,
                    notes_per_page = :notes_per_page
                WHERE user_id = :user_id
            ');
            
            return $stmt->execute([
                ':font_size' => $preferences['font_size'] ?? 14,
                ':theme' => $preferences['theme'] ?? 'light',
                ':default_view' => $preferences['default_view'] ?? 'grid',
                ':notes_per_page' => $preferences['notes_per_page'] ?? 20,
                ':user_id' => $userId
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
