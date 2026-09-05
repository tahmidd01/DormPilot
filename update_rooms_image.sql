-- Add image column to rooms table
-- Run this if you already have the rooms table without image column
ALTER TABLE `rooms` ADD COLUMN `image` varchar(255) DEFAULT NULL AFTER `status`;

