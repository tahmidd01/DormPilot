-- Fixed CREATE TABLE statement for repair_costs
-- Use this if you need to recreate the table

CREATE TABLE IF NOT EXISTS `repair_costs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `complaint_id` int(11) DEFAULT NULL,
  `task_id` int(11) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `supervisor_id` int(11) DEFAULT NULL,  -- Changed from NOT NULL to DEFAULT NULL
  `status` enum('pending','resolved') DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `complaint_id` (`complaint_id`),
  KEY `task_id` (`task_id`),
  KEY `supervisor_id` (`supervisor_id`),
  CONSTRAINT `repair_costs_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE SET NULL,
  CONSTRAINT `repair_costs_ibfk_2` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `repair_costs_ibfk_3` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

