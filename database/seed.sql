-- Test Data for Note Management Application

-- Insert test user (password: password123)
INSERT INTO users (email, display_name, password, is_verified, created_at) VALUES
('test@example.com', 'Test User', '$2y$12$YQU.Z1.sj8ZvYCv0w0YE7OPBqKZnqKBd3qKKqkKZxKZxKZxKZxKZ1', TRUE, NOW());

-- Insert test labels
INSERT INTO labels (user_id, name, color, created_at) VALUES
(1, 'Work', '#007bff', NOW()),
(1, 'Personal', '#28a745', NOW()),
(1, 'Urgent', '#dc3545', NOW()),
(1, 'Ideas', '#ffc107', NOW());

-- Insert test notes
INSERT INTO notes (user_id, title, content, created_at, updated_at) VALUES
(1, 'Welcome to Note Management', 'This is your first note! You can edit, delete, share, and organize your notes with labels.', NOW(), NOW()),
(1, 'Getting Started', 'Tips for using the app:\n1. Create notes with title and content\n2. Add labels to organize your notes\n3. Share notes with others\n4. Pin important notes\n5. Protect sensitive notes with passwords', NOW(), NOW()),
(1, 'Project Ideas', 'Interesting ideas for future projects:\n- Web application for X\n- Mobile app for Y\n- Automation tool for Z', NOW(), NOW()),
(1, 'Meeting Notes', 'Key points from today''s meeting:\n- Discussed Q1 goals\n- Reviewed budget allocation\n- Scheduled follow-up for next week', NOW(), NOW());

-- Attach labels to notes
INSERT INTO note_labels (note_id, label_id) VALUES
(1, 2), -- Welcome note - Personal
(2, 2), -- Getting Started - Personal
(3, 4), -- Project Ideas - Ideas
(4, 1); -- Meeting Notes - Work
