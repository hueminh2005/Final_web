NOTE MANAGEMENT APPLICATION - README
====================================

PROJECT OVERVIEW:
This is a comprehensive note management application built with PHP, MySQL, and modern web technologies. It includes offline capabilities (PWA), real-time collaboration, and advanced security features.

FEATURES IMPLEMENTED:
✓ User authentication (registration, login, password reset)
✓ Email verification
✓ User profile management
✓ Note creation, editing, deletion with auto-save
✓ Grid and list view layouts
✓ Note search with live search functionality
✓ Label management and filtering
✓ Note pinning
✓ Password-protected notes
✓ Note sharing with permissions
✓ Offline capability (PWA with Service Worker)
✓ IndexedDB for offline storage
✓ Responsive design (mobile, tablet, desktop)
✓ Dark/Light theme toggle
✓ User preferences (font size, theme, view type)

SYSTEM REQUIREMENTS:
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite enabled
- Modern web browser with JavaScript enabled

SETUP INSTRUCTIONS:

1. DATABASE SETUP:
   a) Create a MySQL database:
      CREATE DATABASE note_management_db;

   b) Import the schema:
      mysql -u root -p note_management_db < database/schema.sql

2. CONFIGURATION:
   Update the following files with your settings:
   - config/constants.php: APP_URL, EMAIL settings
   - config/database.php: DB credentials
   - config/mail.php: SMTP settings for email

3. FILE PERMISSIONS:
   chmod -R 755 assets/uploads/
   chmod -R 755 offline/

4. FOLDER STRUCTURE:
   Place the entire note-management folder in your web server root:
   - XAMPP: htdocs/note-management
   - Apache: var/www/html/note-management

5. RUNNING WITH DOCKER:
   docker-compose up -d
   Access at: http://localhost:8080

ACCESSING THE APPLICATION:
- Web: http://localhost:8080/
- PhpMyAdmin (Docker): http://localhost:8081/
- Default login credentials: Set during registration

USER ACCOUNTS FOR TESTING:
Create test accounts through the registration page.

DEFAULT PORTS:
- Web Server: 8080
- MySQL: 3306
- PhpMyAdmin: 8081

API ENDPOINTS:
POST /ajax/autosave-note.php - Auto-save note
GET  /ajax/search-note.php - Search notes
POST /ajax/pin-note.php - Pin/unpin note
POST /ajax/delete-note.php - Delete note
GET  /ajax/label-filter.php - Filter by labels

BROWSER SUPPORT:
- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- Mobile browsers with Service Worker support

SECURITY FEATURES:
- Bcrypt password hashing (cost: 12)
- Email verification required
- Session timeout (1 hour)
- CSRF protection (implement as needed)
- SQL injection prevention (prepared statements)
- XSS prevention (HTML escaping)
- Secure headers (.htaccess)

TROUBLESHOOTING:

1. Database connection error:
   - Check MySQL is running
   - Verify credentials in config/database.php
   - Ensure database exists

2. Session issues:
   - Check PHP session.save_path has write permissions
   - Verify PHP configuration

3. Email not sending:
   - Configure SMTP settings in config/mail.php
   - For Gmail, use App Passwords instead of regular password
   - Enable "Less secure app access" if using Gmail

4. Offline features not working:
   - Ensure browser supports Service Workers
   - Check browser's storage permissions
   - Enable JavaScript

5. File upload issues:
   - Check assets/uploads/ folder permissions
   - Verify PHP upload_max_filesize setting

PERFORMANCE OPTIMIZATION:
- Notes are cached in browser (Service Worker)
- Offline data stored in IndexedDB
- Auto-save debounced (300ms)
- Lazy loading for images
- CSS/JS minification recommended for production

MAINTENANCE:
- Regularly backup the MySQL database
- Monitor server logs for errors
- Update PHP and MySQL to latest versions
- Clear old password reset tokens regularly

DEPLOYMENT TO PRODUCTION:
1. Set APP_ENV = 'production' in config/constants.php
2. Configure HTTPS/SSL
3. Update database credentials
4. Set up email configuration
5. Enable proper error logging
6. Configure backup schedule
7. Set up monitoring and alerts

SUPPORT & DOCUMENTATION:
- For issues, check the error logs in browser console
- Review config files for configuration options
- Check database schema for data structure

For more information, contact: maivanmanh@tdtu.edu.vn
