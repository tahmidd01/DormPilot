-- Add supervisor_assignments table to track which students are assigned to which supervisors
-- Multiple students can be assigned to one supervisor

CREATE TABLE IF NOT EXISTS `supervisor_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supervisor_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_assignment` (`supervisor_id`, `student_id`),
  KEY `supervisor_id` (`supervisor_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `supervisor_assignments_ibfk_1` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `supervisor_assignments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

