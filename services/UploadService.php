<?php
/**
 * Upload Service
 */

class UploadService {
    /**
     * Upload image for note
     */
    public static function uploadNoteImage($file) {
        try {
            // Validate file
            if (!isset($file['tmp_name']) || !$file['tmp_name']) {
                return ['success' => false, 'error' => 'No file uploaded'];
            }
            
            // Check file size
            if ($file['size'] > MAX_FILE_SIZE) {
                return ['success' => false, 'error' => 'File size exceeds limit'];
            }
            
            // Check MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mime, ALLOWED_IMAGE_TYPES)) {
                return ['success' => false, 'error' => 'Invalid file type'];
            }
            
            // Generate unique filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'note_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            $filepath = UPLOAD_DIR . $filename;
            
            // Create directory if not exists
            if (!is_dir(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0755, true);
            }
            
            // Move file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                return [
                    'success' => true,
                    'filename' => $filename,
                    'filepath' => $filepath,
                    'url' => APP_URL . '/assets/uploads/' . $filename
                ];
            }
            
            return ['success' => false, 'error' => 'Failed to upload file'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Upload avatar
     */
    public static function uploadAvatar($file) {
        try {
            $upload = self::uploadNoteImage($file);
            
            if ($upload['success']) {
                $upload['filename'] = 'avatar_' . $upload['filename'];
                return $upload;
            }
            
            return $upload;
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Delete file
     */
    public static function deleteFile($filepath) {
        try {
            if (file_exists($filepath)) {
                return unlink($filepath);
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get image dimensions
     */
    public static function getImageDimensions($filepath) {
        try {
            $size = getimagesize($filepath);
            return [
                'width' => $size[0],
                'height' => $size[1]
            ];
        } catch (Exception $e) {
            return null;
        }
    }
}
?>
