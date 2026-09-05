-- Safe migration: Add columns only if they don't exist
-- This script checks for existing columns before adding them

-- Add resolution_description to complaints table (if it doesn't exist)
ALTER TABLE `complaints` 
ADD COLUMN IF NOT EXISTS `resolution_description` TEXT DEFAULT NULL AFTER `status`;

-- Add resolution_cost to complaints table (if it doesn't exist)
ALTER TABLE `complaints` 
ADD COLUMN IF NOT EXISTS `resolution_cost` DECIMAL(10,2) DEFAULT 0.00 AFTER `resolution_description`;

-- Add resolved_at to complaints table (if it doesn't exist)
ALTER TABLE `complaints` 
ADD COLUMN IF NOT EXISTS `resolved_at` TIMESTAMP NULL DEFAULT NULL AFTER `resolution_cost`;

-- Add resolution_description to tasks table (if it doesn't exist)
ALTER TABLE `tasks` 
ADD COLUMN IF NOT EXISTS `resolution_description` TEXT DEFAULT NULL AFTER `status`;

-- Add resolution_cost to tasks table (if it doesn't exist)
ALTER TABLE `tasks` 
ADD COLUMN IF NOT EXISTS `resolution_cost` DECIMAL(10,2) DEFAULT 0.00 AFTER `resolution_description`;

-- Add resolved_at to tasks table (if it doesn't exist)
ALTER TABLE `tasks` 
ADD COLUMN IF NOT EXISTS `resolved_at` TIMESTAMP NULL DEFAULT NULL AFTER `resolution_cost`;

-- Add task_id to repair_costs table (if it doesn't exist)
-- Note: supervisor_id already exists in repair_costs, so we skip adding it
ALTER TABLE `repair_costs` 
ADD COLUMN IF NOT EXISTS `task_id` INT(11) DEFAULT NULL AFTER `complaint_id`;

-- Add indexes if they don't exist (MySQL doesn't support IF NOT EXISTS for indexes, so we use IGNORE)
ALTER TABLE `repair_costs` 
ADD INDEX IF NOT EXISTS `task_id` (`task_id`);

-- Note: supervisor_id index might already exist, but we'll try to add it
ALTER TABLE `repair_costs` 
ADD INDEX IF NOT EXISTS `supervisor_id_idx` (`supervisor_id`);

-- Add foreign key constraints (drop first if they exist, then add)
-- Check if constraint exists before adding
SET @constraint_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'repair_costs' 
    AND CONSTRAINT_NAME = 'repair_costs_ibfk_2'
);

SET @sql = IF(@constraint_exists = 0,
    'ALTER TABLE `repair_costs` ADD CONSTRAINT `repair_costs_ibfk_2` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL',
    'SELECT "Constraint repair_costs_ibfk_2 already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @constraint_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'repair_costs' 
    AND CONSTRAINT_NAME = 'repair_costs_ibfk_3'
);

SET @sql = IF(@constraint_exists = 0,
    'ALTER TABLE `repair_costs` ADD CONSTRAINT `repair_costs_ibfk_3` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL',
    'SELECT "Constraint repair_costs_ibfk_3 already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

