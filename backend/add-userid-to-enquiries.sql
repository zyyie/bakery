-- Add userID column to enquiries table
-- Run this in your MySQL database

ALTER TABLE enquiries 
ADD COLUMN userID INT NULL,
ADD FOREIGN KEY (userID) REFERENCES users(userID) ON DELETE SET NULL;
