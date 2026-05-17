<?php
/**
 * Application Constants
 */

define('APP_NAME', 'Note Management');
define('APP_URL', 'http://localhost/note-management');
define('APP_ENV', 'development');

// Session
define('SESSION_TIMEOUT', 3600); // 1 hour
define('SESSION_NAME', 'note_app_session');

// Email
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your_email@gmail.com');
define('SMTP_PASS', 'your_app_password');
define('SENDER_EMAIL', 'noreply@noteapp.com');
define('SENDER_NAME', 'Note Management App');

// File Upload
define('MAX_FILE_SIZE', 5242880); // 5MB
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Security
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_OPTIONS', ['cost' => 12]);

// API
define('API_RESPONSE_FORMAT', 'json');
define('ITEMS_PER_PAGE', 20);

// WebSocket
define('WEBSOCKET_HOST', 'localhost');
define('WEBSOCKET_PORT', 8081);

// Note Settings
define('NOTE_AUTO_SAVE_DELAY', 300); // 300ms
define('SEARCH_DEBOUNCE_DELAY', 300); // 300ms
?>
