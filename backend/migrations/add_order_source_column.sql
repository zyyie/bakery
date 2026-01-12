-- Migration: Add source column to orders table
-- This allows tracking orders from different sources (KARNEEK bakery vs Carnick Canteen API)
-- Run this script in phpMyAdmin or via command line: mysql -u root db_bakery < add_order_source_column.sql

-- Add source column (ignore error if column already exists)
ALTER TABLE `orders` 
ADD COLUMN `source` VARCHAR(50) DEFAULT 'KARNEEK' AFTER `grand_total`;

-- Update existing orders to have default source
UPDATE `orders` SET `source` = 'KARNEEK' WHERE `source` IS NULL OR `source` = '';

-- Add index for better query performance (ignore error if index already exists)
CREATE INDEX `idx_orders_source` ON `orders` (`source`);

