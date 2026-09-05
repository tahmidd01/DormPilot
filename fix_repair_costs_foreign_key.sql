-- Fix repair_costs table foreign key constraint issues
-- This script fixes the supervisor_id column and foreign key constraint

-- Step 1: Update existing invalid supervisor_id values (0 or NULL) to NULL
UPDATE `repair_costs` SET `supervisor_id` = NULL WHERE `supervisor_id` = 0 OR `supervisor_id` NOT IN (SELECT `id` FROM `users`);

-- Step 2: Make supervisor_id nullable (change from NOT NULL to NULL)
ALTER TABLE `repair_costs` 
MODIFY COLUMN `supervisor_id` INT(11) DEFAULT NULL;

-- Step 3: Drop existing foreign key constraint if it exists (to recreate it properly)
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'repair_costs' 
    AND CONSTRAINT_NAME = 'repair_costs_ibfk_3');

SET @sql = IF(@fk_exists > 0, 
    'ALTER TABLE `repair_costs` DROP FOREIGN KEY `repair_costs_ibfk_3`', 
    'SELECT "Foreign key repair_costs_ibfk_3 does not exist"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 4: Add the foreign key constraint properly
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

-- Verification: Check if constraint was created successfully
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'repair_costs'
AND CONSTRAINT_NAME = 'repair_costs_ibfk_3';

