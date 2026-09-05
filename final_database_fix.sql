-- Final database fix - Only adds missing foreign key constraint for supervisor_id in repair_costs
-- All other columns and indexes already exist in your database

-- Add foreign key constraint for supervisor_id in repair_costs if it doesn't exist
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'repair_costs' 
    AND CONSTRAINT_NAME = 'repair_costs_ibfk_3');

SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE `repair_costs` ADD CONSTRAINT `repair_costs_ibfk_3` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL', 
    'SELECT "Foreign key repair_costs_ibfk_3 already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verify all required columns exist (just for confirmation, won't add if they exist)
SELECT 
    'complaints.resolution_description' as column_check,
    CASE WHEN COUNT(*) > 0 THEN 'EXISTS' ELSE 'MISSING' END as status
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'complaints' AND COLUMN_NAME = 'resolution_description'
UNION ALL
SELECT 
    'complaints.resolution_cost' as column_check,
    CASE WHEN COUNT(*) > 0 THEN 'EXISTS' ELSE 'MISSING' END as status
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'complaints' AND COLUMN_NAME = 'resolution_cost'
UNION ALL
SELECT 
    'complaints.resolved_at' as column_check,
    CASE WHEN COUNT(*) > 0 THEN 'EXISTS' ELSE 'MISSING' END as status
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'complaints' AND COLUMN_NAME = 'resolved_at'
UNION ALL
SELECT 
    'tasks.resolution_description' as column_check,
    CASE WHEN COUNT(*) > 0 THEN 'EXISTS' ELSE 'MISSING' END as status
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tasks' AND COLUMN_NAME = 'resolution_description'
UNION ALL
SELECT 
    'tasks.resolution_cost' as column_check,
    CASE WHEN COUNT(*) > 0 THEN 'EXISTS' ELSE 'MISSING' END as status
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tasks' AND COLUMN_NAME = 'resolution_cost'
UNION ALL
SELECT 
    'tasks.resolved_at' as column_check,
    CASE WHEN COUNT(*) > 0 THEN 'EXISTS' ELSE 'MISSING' END as status
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tasks' AND COLUMN_NAME = 'resolved_at'
UNION ALL
SELECT 
    'repair_costs.task_id' as column_check,
    CASE WHEN COUNT(*) > 0 THEN 'EXISTS' ELSE 'MISSING' END as status
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'repair_costs' AND COLUMN_NAME = 'task_id'
UNION ALL
SELECT 
    'repair_costs.supervisor_id' as column_check,
    CASE WHEN COUNT(*) > 0 THEN 'EXISTS' ELSE 'MISSING' END as status
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'repair_costs' AND COLUMN_NAME = 'supervisor_id';

