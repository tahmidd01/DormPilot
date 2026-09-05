-- Fixed migration script that handles existing columns
-- Run this script - it will skip columns that already exist

-- Add resolution_description to complaints table
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'complaints' AND COLUMN_NAME = 'resolution_description');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `complaints` ADD COLUMN `resolution_description` TEXT DEFAULT NULL AFTER `status`', 
    'SELECT "Column resolution_description already exists in complaints"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add resolution_cost to complaints table
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'complaints' AND COLUMN_NAME = 'resolution_cost');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `complaints` ADD COLUMN `resolution_cost` DECIMAL(10,2) DEFAULT 0.00 AFTER `resolution_description`', 
    'SELECT "Column resolution_cost already exists in complaints"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add resolved_at to complaints table
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'complaints' AND COLUMN_NAME = 'resolved_at');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `complaints` ADD COLUMN `resolved_at` TIMESTAMP NULL DEFAULT NULL AFTER `resolution_cost`', 
    'SELECT "Column resolved_at already exists in complaints"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add resolution_description to tasks table
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tasks' AND COLUMN_NAME = 'resolution_description');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `tasks` ADD COLUMN `resolution_description` TEXT DEFAULT NULL AFTER `status`', 
    'SELECT "Column resolution_description already exists in tasks"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add resolution_cost to tasks table
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tasks' AND COLUMN_NAME = 'resolution_cost');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `tasks` ADD COLUMN `resolution_cost` DECIMAL(10,2) DEFAULT 0.00 AFTER `resolution_description`', 
    'SELECT "Column resolution_cost already exists in tasks"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add resolved_at to tasks table
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tasks' AND COLUMN_NAME = 'resolved_at');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `tasks` ADD COLUMN `resolved_at` TIMESTAMP NULL DEFAULT NULL AFTER `resolution_cost`', 
    'SELECT "Column resolved_at already exists in tasks"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add task_id to repair_costs table (supervisor_id already exists, so we skip it)
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'repair_costs' AND COLUMN_NAME = 'task_id');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `repair_costs` ADD COLUMN `task_id` INT(11) DEFAULT NULL AFTER `complaint_id`', 
    'SELECT "Column task_id already exists in repair_costs"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index for task_id if it doesn't exist
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'repair_costs' AND INDEX_NAME = 'task_id');
SET @sql = IF(@idx_exists = 0, 
    'ALTER TABLE `repair_costs` ADD KEY `task_id` (`task_id`)', 
    'SELECT "Index task_id already exists in repair_costs"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index for supervisor_id if it doesn't exist (it might already exist)
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'repair_costs' AND INDEX_NAME = 'supervisor_id');
SET @sql = IF(@idx_exists = 0, 
    'ALTER TABLE `repair_costs` ADD KEY `supervisor_id` (`supervisor_id`)', 
    'SELECT "Index supervisor_id already exists in repair_costs"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key constraint for task_id if it doesn't exist
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'repair_costs' AND CONSTRAINT_NAME = 'repair_costs_ibfk_2');
SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE `repair_costs` ADD CONSTRAINT `repair_costs_ibfk_2` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL', 
    'SELECT "Foreign key repair_costs_ibfk_2 already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key constraint for supervisor_id if it doesn't exist
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'repair_costs' AND CONSTRAINT_NAME = 'repair_costs_ibfk_3');
SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE `repair_costs` ADD CONSTRAINT `repair_costs_ibfk_3` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL', 
    'SELECT "Foreign key repair_costs_ibfk_3 already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

