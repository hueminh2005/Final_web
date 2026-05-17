-- Add note_color field to notes table
ALTER TABLE notes ADD COLUMN note_color VARCHAR(7) DEFAULT '#ffffff' AFTER is_password_protected;

-- Update user_preferences to include default_note_color
ALTER TABLE user_preferences ADD COLUMN default_note_color VARCHAR(7) DEFAULT '#ffffff' AFTER notes_per_page;
