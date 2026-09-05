-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 28, 2026 at 02:00 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dormpilot`
--

-- --------------------------------------------------------

--
-- Table structure for table `admissions`
--

CREATE TABLE `admissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nid` varchar(30) NOT NULL,
  `course` varchar(50) NOT NULL,
  `father_name` varchar(100) NOT NULL,
  `father_phone` varchar(20) NOT NULL,
  `mother_name` varchar(100) NOT NULL,
  `mother_phone` varchar(20) NOT NULL,
  `preferred_room` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_pic` varchar(255) DEFAULT NULL,
  `documents` varchar(255) DEFAULT NULL,
  `nid_photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admissions`
--

INSERT INTO `admissions` (`id`, `user_id`, `name`, `phone`, `email`, `nid`, `course`, `father_name`, `father_phone`, `mother_name`, `mother_phone`, `preferred_room`, `status`, `created_at`, `profile_pic`, `documents`, `nid_photo`) VALUES
(1, 4, 'Mahdee Muhammad Shafee', '01881145564', 'shonchoy02@gmail.com', '0123443585745792', 'ENG 101', 'MD delwar hossain', '01244531531', 'Tauhida Sultana', '01912055747', 'Room 1, Block B', 'approved', '2026-01-12 20:07:19', NULL, NULL, NULL),
(2, 3, 'seam', '1234', 'setuu@gmail.com', '098', 'EEE', 'j', '12', 'k', '23', '', 'pending', '2026-01-27 06:41:18', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `supervisor_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `supervisor_id`, `title`, `message`, `created_at`) VALUES
(1, 6, 'about closing the hostel', 'from 15th jan the hostel will remain closed', '2026-01-12 20:06:00');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `resolution_description` text DEFAULT NULL,
  `resolution_cost` decimal(10,2) DEFAULT 0.00,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `supervisor_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `user_id`, `type`, `description`, `photo`, `status`, `resolution_description`, `resolution_cost`, `resolved_at`, `supervisor_id`, `created_at`, `updated_at`) VALUES
(1, 4, 'other', 'Food is not good , dont like it', '', 'resolved', 'food fixed', 0.00, '2026-01-28 00:17:36', 14, '2026-01-12 20:07:38', '2026-01-28 00:17:36'),
(2, 3, 'electricity', 'kk', '', 'in-progress', NULL, 0.00, NULL, 14, '2026-01-13 13:22:41', '2026-01-28 00:44:53'),
(3, 15, 'plumbing', 'sewerage overflowed', '', 'pending', NULL, 0.00, NULL, NULL, '2026-01-28 00:23:00', '2026-01-28 00:23:00');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`) VALUES
(1, 3, 1, 'hi', '2026-01-02 17:36:10'),
(2, 3, 1, 'hello', '2026-01-02 17:36:18'),
(3, 3, 1, 'helloooo', '2026-01-02 17:36:31'),
(4, 3, 1, 'kkk', '2026-01-04 03:50:57'),
(5, 4, 0, 'hello supervisor\r\n', '2026-01-12 19:57:27'),
(6, 4, 6, 'i need urgent consultancy please', '2026-01-12 20:08:07'),
(7, 6, 4, 'okay, i\'ll let you know in short\r\n', '2026-01-12 20:13:23'),
(8, 4, 6, 'okay', '2026-01-13 04:09:54'),
(9, 4, 6, 'okay', '2026-01-13 04:10:11'),
(10, 3, 6, 'hi', '2026-01-13 13:20:58');

-- --------------------------------------------------------

--
-- Table structure for table `profile`
--

CREATE TABLE `profile` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `room` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `profile`
--

INSERT INTO `profile` (`id`, `user_id`, `fullname`, `email`, `phone`, `room`, `password`, `created_at`, `updated_at`) VALUES
(1, 3, 'setu', 'setuu@gmail.com', '222', 'Room 2', '$2y$10$lXreWjsWkiNPLg7EC78k6.FNMwqQfarfBXkz/bjZDQ5WRsHQdaQFe', '2026-01-02 16:39:06', '2026-01-12 20:15:48');

-- --------------------------------------------------------

--
-- Table structure for table `repair_costs`
--

CREATE TABLE `repair_costs` (
  `id` int(11) NOT NULL,
  `complaint_id` int(11) DEFAULT NULL,
  `task_id` int(11) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `supervisor_id` int(11) NOT NULL,
  `status` enum('pending','resolved') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `repair_costs`
--

INSERT INTO `repair_costs` (`id`, `complaint_id`, `task_id`, `description`, `cost`, `date`, `created_at`, `supervisor_id`, `status`) VALUES
(1, NULL, NULL, 'Additional charge', 50.00, '2026-01-12', '2026-01-12 20:04:01', 0, 'pending'),
(2, NULL, 6, 'Task Resolution: completed fully', 30.00, '2026-01-28', '2026-01-28 00:17:10', 14, 'pending'),
(3, NULL, 6, 'Task Resolution: completed fully', 30.00, '2026-01-28', '2026-01-28 00:17:15', 14, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `room_number` varchar(50) NOT NULL,
  `block` varchar(50) DEFAULT NULL,
  `capacity` int(11) NOT NULL,
  `occupancy` int(11) DEFAULT 0,
  `status` varchar(20) DEFAULT 'available',
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `room_number`, `block`, `capacity`, `occupancy`, `status`, `image`, `created_at`) VALUES
(1, 'Room 1', 'B', 10, 0, 'available', NULL, '2026-01-12 20:01:36'),
(2, 'Room 2', 'C', 15, 0, 'available', NULL, '2026-01-12 20:01:47'),
(3, 'Room 3', 'D', 20, 0, 'available', NULL, '2026-01-12 20:01:56');

-- --------------------------------------------------------

--
-- Table structure for table `room_assignments`
--

CREATE TABLE `room_assignments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_assignments`
--

INSERT INTO `room_assignments` (`id`, `user_id`, `room_id`, `assigned_at`) VALUES
(1, 4, 2, '2026-01-12 20:02:29'),
(3, 3, 2, '2026-01-12 20:15:48'),
(4, 1, 2, '2026-01-27 23:56:31'),
(5, 15, 3, '2026-01-28 00:45:58');

-- --------------------------------------------------------

--
-- Table structure for table `room_change_requests`
--

CREATE TABLE `room_change_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `current_room` varchar(50) NOT NULL,
  `new_room` varchar(50) NOT NULL,
  `reason` text NOT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_change_requests`
--

INSERT INTO `room_change_requests` (`id`, `user_id`, `student_name`, `student_id`, `phone`, `current_room`, `new_room`, `reason`, `status`, `request_date`) VALUES
(1, 3, 'setu', '99', '222', '2', '1', 'j', 'pending', '2025-12-12 15:54:43'),
(2, 3, 'setu', '99', '222', '2', '1', 'kkk', 'approved', '2026-01-04 03:49:39'),
(3, 4, 'Mahdee Muhammad Shafee', '0112230399', '012331531', 'room 1', 'room 2', 'Fighting and noise', 'pending', '2026-01-12 20:08:49');

-- --------------------------------------------------------

--
-- Table structure for table `supervisor_assignments`
--

CREATE TABLE `supervisor_assignments` (
  `id` int(11) NOT NULL,
  `supervisor_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supervisor_assignments`
--

INSERT INTO `supervisor_assignments` (`id`, `supervisor_id`, `student_id`, `assigned_at`) VALUES
(1, 6, 1, '2026-01-27 23:56:31'),
(2, 14, 3, '2026-01-27 23:57:59'),
(3, 14, 15, '2026-01-28 00:45:58');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `supervisor_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `resolution_description` text DEFAULT NULL,
  `resolution_cost` decimal(10,2) DEFAULT 0.00,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `supervisor_id`, `student_id`, `title`, `description`, `status`, `resolution_description`, `resolution_cost`, `resolved_at`, `created_at`) VALUES
(1, 6, 4, 'Payment', 'Tell him to pay the money ASAP', 'resolved', NULL, 0.00, NULL, '2026-01-12 20:03:22'),
(2, 6, 3, 'k', 'll', 'pending', NULL, 0.00, NULL, '2026-01-26 21:25:44'),
(3, 6, 3, 'k', 'll', 'pending', NULL, 0.00, NULL, '2026-01-26 21:42:26'),
(4, 6, 3, 'k', 'll', 'pending', NULL, 0.00, NULL, '2026-01-26 21:42:36'),
(5, 14, 3, 'HOstel bed', 'no bed', 'pending', NULL, 0.00, NULL, '2026-01-27 23:59:45'),
(6, 14, 3, 'fix her bed', 'budget is given properly', 'resolved', 'completed fully', 30.00, '2026-01-28 00:17:15', '2026-01-28 00:02:03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'student',
  `phone` varchar(20) DEFAULT NULL,
  `block` varchar(50) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `nid_number` varchar(50) DEFAULT NULL,
  `nid_photo` varchar(255) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `role`, `phone`, `block`, `photo`, `nid_number`, `nid_photo`, `gender`) VALUES
(1, 'setu', 'setu@gmail.com', '$2y$10$WKsPbnjknqVvmhEL6Y.u2uvBF97A4xnM9zNSn.QdUpw4UiZdQH.T2', 'student', NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'setuu', 'setuu@gmail.com', '$2y$10$y2SVtuOCUVT7ufUEmuYC9uPFRkTKxdGEM7gEi6.z6llJ3zqg/z4jq', 'student', NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'Mahdee Muhammad Shafee', 'Shonchoy02@gmail.com', '$2y$10$4ibvKrsKn6CRUbNrBEw76.B7FwOP1FnwbSRlmGD/ZzqqTBGrRjXk6', 'student', NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'Mahdee Muhammad Shafee (Shonchoy)', 'shafeeshonchoy002@gmail.com', '$2y$10$aUeJosUEeEhskli3fIsNr.mqvoV8hfxkNgG5GH5l2olbPS1xtUhMi', 'admin', NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'Tahmid Ahmed Talukder', 'talukder123@gmail.com', '$2y$10$3BQEGp50trYe92c4TWFrl.zcZ.PCaHSUYDUlzAaWVNxjwiyU3AplO', 'supervisor', '01234567890', 'B', NULL, NULL, NULL, NULL),
(13, 'setu', 'setu1@gmail.com', '$2y$10$yqGJ28q4V7sxXgIjZ6yI/OIlasDpTiMo3kGaJvEcLwYoYcii1.ehW', 'supervisor', '0192303033', '1A', '1769461207_profile.jpg', NULL, '1769461207_nid.jpg', NULL),
(14, 'shonchoy', 'shafeeshonchoy2@gmail.com', '$2y$10$NLMI369U1yIPK1vvGr27MONKsCFwBis9dOB993W8LwkAHB/r03hWS', 'supervisor', '01881145564', 'D', '1769558250_profile.png', NULL, '1769558250_nid.png', NULL),
(15, 'shonloy', 'shafeeshonchoy0@gmail.com', '$2y$10$vjoVprqi8ppqJq6NeAnWleCC6tKKhcxFETPBdqPxhUhyLrI/p7vrq', 'student', NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admissions`
--
ALTER TABLE `admissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admissions_ibfk_1` (`user_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `announcements_ibfk_1` (`supervisor_id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `supervisor_id` (`supervisor_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `profile`
--
ALTER TABLE `profile`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `repair_costs`
--
ALTER TABLE `repair_costs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `complaint_id` (`complaint_id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `supervisor_id` (`supervisor_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_number` (`room_number`);

--
-- Indexes for table `room_assignments`
--
ALTER TABLE `room_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `room_change_requests`
--
ALTER TABLE `room_change_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `supervisor_assignments`
--
ALTER TABLE `supervisor_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_assignment` (`supervisor_id`,`student_id`),
  ADD KEY `supervisor_id` (`supervisor_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supervisor_id` (`supervisor_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admissions`
--
ALTER TABLE `admissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `profile`
--
ALTER TABLE `profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `repair_costs`
--
ALTER TABLE `repair_costs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `room_assignments`
--
ALTER TABLE `room_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `room_change_requests`
--
ALTER TABLE `room_change_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `supervisor_assignments`
--
ALTER TABLE `supervisor_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admissions`
--
ALTER TABLE `admissions`
  ADD CONSTRAINT `admissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaints_ibfk_2` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `profile`
--
ALTER TABLE `profile`
  ADD CONSTRAINT `profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `repair_costs`
--
ALTER TABLE `repair_costs`
  ADD CONSTRAINT `repair_costs_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `repair_costs_ibfk_2` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `room_assignments`
--
ALTER TABLE `room_assignments`
  ADD CONSTRAINT `room_assignments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `room_assignments_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `room_change_requests`
--
ALTER TABLE `room_change_requests`
  ADD CONSTRAINT `room_change_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `supervisor_assignments`
--
ALTER TABLE `supervisor_assignments`
  ADD CONSTRAINT `supervisor_assignments_ibfk_1` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `supervisor_assignments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
