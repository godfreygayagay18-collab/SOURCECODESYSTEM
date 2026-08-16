SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


CREATE TABLE `download_requests` (
  `id` int(11) NOT NULL,
  `user_username` varchar(255) NOT NULL,
  `code_id` int(11) NOT NULL,
  `gcash_ref` varchar(50) DEFAULT 'N/A',
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `file_path`, `created_at`, `is_read`) VALUES
(1, 17, 1, 'boi', NULL, '2026-08-09 08:54:06', 1),
(2, 17, 1, 'oh', NULL, '2026-08-09 08:55:46', 1),
(3, 17, 1, 'ff', NULL, '2026-08-09 08:57:32', 1),
(4, 17, 1, 'dd', NULL, '2026-08-09 08:59:52', 1),
(5, 17, 1, 'dff', NULL, '2026-08-09 09:02:26', 1),
(6, 17, 1, 'fgfg', NULL, '2026-08-09 09:04:30', 1),
(8, 17, 1, 'sds', NULL, '2026-08-09 09:06:17', 1),
(11, 17, 1, 'fdf', NULL, '2026-08-09 09:06:36', 1),
(13, 17, 1, 'ss', NULL, '2026-08-09 09:10:10', 1),
(15, 17, 1, 'sa', NULL, '2026-08-09 09:11:51', 1),
(16, 17, 1, 'sd', NULL, '2026-08-09 09:12:48', 1),
(18, 18, 1, 'we', NULL, '2026-08-09 09:14:05', 1),
(20, 1, 18, 'ds', NULL, '2026-08-09 09:16:59', 0),
(21, 18, 1, 'sds', NULL, '2026-08-09 09:17:07', 1),
(22, 1, 17, 'tol', NULL, '2026-08-10 14:00:31', 0),
(23, 17, 1, 'po', NULL, '2026-08-10 14:00:45', 1),
(24, 1, 17, 'tol', NULL, '2026-08-14 12:00:12', 0),
(25, 21, 1, 'sir', NULL, '2026-08-14 12:01:42', 1),
(26, 1, 21, 'yes', NULL, '2026-08-14 12:01:53', 0),
(27, 21, 1, 'boss', NULL, '2026-08-15 01:21:05', 1),
(28, 1, 21, 'sir', NULL, '2026-08-15 01:21:45', 0),
(29, 1, 28, 'kkkk', NULL, '2026-08-15 14:59:24', 0),
(30, 28, 1, 'gggg', NULL, '2026-08-15 14:59:57', 1),
(31, 1, 28, 'sed', 'uploads/chat_files/1786806019_screenshot laptop.png', '2026-08-15 15:00:19', 0),
(32, 1, 29, 'g', NULL, '2026-08-16 01:18:40', 0),
(33, 1, 29, 'g', NULL, '2026-08-16 01:18:48', 0),
(34, 1, 29, 'w', 'uploads/chat_files/1786843138_screenshot laptop.png', '2026-08-16 01:18:58', 0),
(35, 1, 29, 'f', NULL, '2026-08-16 01:19:07', 0),
(36, 1, 29, 'ss', NULL, '2026-08-16 01:19:13', 0),
(37, 1, 29, 'sdsd', 'uploads/chat_files/1786843164_screenshot laptop.png', '2026-08-16 01:19:24', 0),
(38, 1, 29, 'ff', 'uploads/chat_files/1786843184_pos.jpg', '2026-08-16 01:19:44', 0),
(39, 1, 29, 'df', 'uploads/chat_files/1786843503_curfew.jpg', '2026-08-16 01:25:03', 0),
(40, 1, 29, 's', NULL, '2026-08-16 01:28:05', 0),
(41, 1, 29, 's', NULL, '2026-08-16 01:28:10', 0),
(42, 1, 29, 's', 'uploads/chat_files/1786843698_Daily Expense Tracker.jpg', '2026-08-16 01:28:18', 0),
(43, 29, 1, 'ss', 'uploads/chat_files/1786843716_hotel.jpg', '2026-08-16 01:28:36', 1),
(44, 29, 1, '', 'uploads/chat_files/1786844074_Daily Expense Tracker.jpg', '2026-08-16 01:34:34', 1);


CREATE TABLE `source_codes` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` varchar(100) DEFAULT 'Free',
  `language` varchar(100) NOT NULL,
  `zip_file` varchar(255) NOT NULL,
  `uploaded_by` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `source_codes` (`id`, `title`, `description`, `price`, `language`, `zip_file`, `uploaded_by`, `created_at`, `image_path`) VALUES
(5, 'pos with scanner', 'READ THE README TEXT', '3500', 'PHP/MYSQL', '1780304768-vaqpe_shopp.final final.zip', 'GODFREY', '2026-06-01 09:06:08', NULL),
(7, 'library management system', 'dont forget to read the readme txt', '1500', 'php\\mysql', '1780305997-Online-Library-Management-System-PHP.zip', 'GODFREY', '2026-06-01 09:26:37', NULL),
(8, 'Hostel Management System', 'read the readme txt', '800', 'using Apache server and MySQL', '1780306162-Hostel-Management-Syste-Updated-Code.zip', 'GODFREY', '2026-06-01 09:29:22', NULL),
(9, 'Student Record System In php', 'dont forgot to read the readme.txt', '1000', 'php/mysql', '1780306218-Student-Record-Management-System-PHP.zip', 'GODFREY', '2026-06-01 09:30:18', NULL),
(10, 'Daily Expense Tracker Using PHP', 'dont forgot to read the readme txt', '800', 'php/mysql', '1780306284-Daily-Expense-Tracker-Project.zip', 'GODFREY', '2026-06-01 09:31:24', NULL),
(11, 'Curfew e-Pass Management System', 'read the readme.txt', '850', 'php/mysql', '1780306496-Curfew-e-Pass-Management-System-Project.zip', 'GODFREY', '2026-06-01 09:34:56', 'uploads/thumbnails/1786845337-curfew.jpg'),
(12, 'Tourism Management System: Project Output Screens', 'dont forgot to read the txtsdffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff', '2000', 'php/mysql', '1780306625-Tourism-Management-System-PHP-Updated.zip', 'GODFREY', '2026-06-01 09:37:05', NULL),
(13, 'billing management system', 'billing', '100', 'c#', '1786806337-Simple Billing System in C#.zip', 'GODFREY', '2026-08-15 15:05:37', NULL),
(14, 'dd', 'dd', '12', 'dd', '1786844587-Civil Service Reviewer.zip', 'GODFREY', '2026-08-16 01:43:07', ''),
(17, 'ss', 'ss', 'ss', 'ss', '1786844653-Group-12---OOP-Project---Library-management-system-main.zip', 'GODFREY', '2026-08-16 01:44:13', ''),
(19, '21221', 'dd', '1221', 'dd', '1786844979-1786844653-Group-12---OOP-Project---Library-management-system-main.zip', 'GODFREY', '2026-08-16 01:49:39', 'uploads/thumbnails/1786844979-curfew.jpg'),
(21, 'ggg', 'ggg', 'ggg', 'gg', '1786845151-1786844653-Group-12---OOP-Project---Library-management-system-main.zip', 'GODFREY', '2026-08-16 01:52:31', 'uploads/thumbnails/1786845151-gcash.jfif');



CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `school_attended` varchar(255) NOT NULL,
  `mobile_email` varchar(255) NOT NULL,
  `contact` varchar(255) NOT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `security_question` varchar(255) DEFAULT NULL,
  `security_answer` varchar(255) DEFAULT NULL,
  `reset_otp` varchar(6) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `users` (`id`, `username`, `password`, `firstname`, `lastname`, `address`, `school_attended`, `mobile_email`, `contact`, `status`, `created_at`, `security_question`, `security_answer`, `reset_otp`, `otp_expiry`) VALUES
(22, 'GODFREY', 'b1ffaff09eb2dcefdf0220087ee35947', '', '', '', '', '', '', 'active', '2026-08-15 01:03:40', NULL, NULL, NULL, NULL),
(29, 'vincent', 'b19e29b67229921a7b1f4e5ff81f6b3e', '11', '11', '11', '11', 'godfreygayagay18@gmail.com', '', 'approved', '2026-08-16 01:05:20', NULL, NULL, NULL, NULL);


ALTER TABLE `download_requests`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `source_codes`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);


ALTER TABLE `download_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;


ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;


ALTER TABLE `source_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;


ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;
COMMIT;

