-- Add google_id column to users table for Google OAuth support
-- Run this SQL script to add Google OAuth support to your database
-- 
-- Note: If the column already exists, you may see an error. This is safe to ignore.
-- You can check if the column exists by running: DESCRIBE users;

USE DB_bakery;

-- Add google_id column
-- If you get an error that the column already exists, you can safely ignore it
ALTER TABLE users 
ADD COLUMN google_id VARCHAR(255) NULL UNIQUE AFTER email;

-- Add index for faster lookups
-- If you get an error that the index already exists, you can safely ignore it
CREATE INDEX idx_google_id ON users(google_id);

