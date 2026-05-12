-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 12, 2026 at 08:42 AM
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
-- Database: `isp_manager`
--

-- --------------------------------------------------------

--
-- Table structure for table `coverage_zones`
--

CREATE TABLE `coverage_zones` (
  `id` int(11) NOT NULL,
  `district` varchar(100) NOT NULL,
  `upazila` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','upcoming') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coverage_zones`
--

INSERT INTO `coverage_zones` (`id`, `district`, `upazila`, `description`, `status`, `created_at`) VALUES
(1, 'Dhaka', 'Dhanmondi', 'laksjdfoiauwer;lsdjfc', 'active', '2026-05-12 05:54:03');

-- --------------------------------------------------------

--
-- Table structure for table `designations`
--

CREATE TABLE `designations` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `designations`
--

INSERT INTO `designations` (`id`, `title`) VALUES
(1, 'Field Technician'),
(2, 'Network Engineer'),
(3, 'Billing Manager'),
(4, 'Customer Support'),
(5, 'Sales Executive'),
(6, 'System Admin');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `expense_id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `expense_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `invoice_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `subscription_id` int(11) DEFAULT NULL,
  `invoice_number` varchar(100) NOT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` date DEFAULT NULL,
  `status` varchar(50) DEFAULT 'unpaid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`invoice_id`, `user_id`, `subscription_id`, `invoice_number`, `period_start`, `period_end`, `amount`, `due_date`, `status`, `created_at`) VALUES
(11, 20, 10, 'INV-6A02C2F66A402', '2026-05-12', '2026-06-11', 500.00, '2026-05-15', 'unpaid', '2026-05-12 06:04:38');

-- --------------------------------------------------------

--
-- Table structure for table `network_logs`
--

CREATE TABLE `network_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `data_used_gb` float DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `type`, `message`, `is_read`, `sent_at`) VALUES
(1, 4, NULL, '🔥 New Order: Ibrahim has requested a new internet connection. Please assign a technician from \'Manage Customers\'.', 1, '2026-05-09 17:35:26'),
(2, 13, NULL, '🚨 New Job: You have been assigned for a New Connection Setup (Customer ID: #19). Please check your pending tasks.', 1, '2026-05-09 17:36:17'),
(3, 4, NULL, '✅ Job Completed: Ticket #10 has been marked as resolved by Technician. Please review or ACTIVATE the line.', 1, '2026-05-09 17:36:51'),
(4, 4, NULL, '✅ Job Completed: Ticket #9 has been marked as resolved by Technician. Please review or ACTIVATE the line.', 1, '2026-05-09 17:37:08'),
(5, 4, NULL, '🚀 Upgrade Request: A customer requested to upgrade to BASIC+. Please check Support Tickets.', 1, '2026-05-09 18:09:25'),
(6, 4, NULL, '✅ Job Completed: Ticket #11 has been marked as resolved by Technician. Please review or ACTIVATE the line.', 1, '2026-05-09 18:26:14'),
(7, 4, NULL, '🚀 Upgrade & Invoice: Ibrahim requested to upgrade to POSITIVE+. New invoice generated.', 1, '2026-05-09 18:26:44'),
(8, 4, NULL, '✅ Job Completed: Ticket #12 has been marked as resolved by Technician. Please review or ACTIVATE the line.', 1, '2026-05-09 18:28:04'),
(9, 4, NULL, '💰 Payment Submitted: TrxID pouy9654783214 received for INV-69FF68B7E7ACB. Check Support Tickets to verify.', 1, '2026-05-09 19:04:29'),
(10, 4, NULL, '✅ Job Completed: Ticket #13 has been marked as resolved by Technician. Please review or ACTIVATE the line.', 1, '2026-05-09 19:05:31'),
(11, 4, NULL, '🔥 New Order: KHONDOKER MOIN HOSSAIN has requested a new internet connection. Please assign a technician from \'Manage Customers\'.', 1, '2026-05-12 06:04:38');

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `package_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(50) DEFAULT 'home',
  `features` varchar(255) DEFAULT NULL,
  `speed_mbps` float DEFAULT NULL,
  `quota_gb` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_days` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`package_id`, `name`, `type`, `features`, `speed_mbps`, `quota_gb`, `price`, `duration_days`, `status`) VALUES
(1, 'MINOR+', 'home', NULL, 20, 9999, 500.00, 30, 'active'),
(2, 'JUNIOR+', 'home', NULL, 30, 9999, 650.00, 30, 'active'),
(3, 'LEARNER+', 'home', NULL, 50, 9999, 800.00, 30, 'active'),
(4, 'BASIC+', 'home', NULL, 100, 9999, 1000.00, 30, 'active'),
(5, 'PRIMARY+', 'home', NULL, 125, 9999, 1200.00, 30, 'active'),
(6, 'DOMINANT+', 'home', NULL, 150, 9999, 1500.00, 30, 'active'),
(7, 'CONFIDENT+', 'home', NULL, 200, 9999, 2000.00, 30, 'active'),
(8, 'POSITIVE+', 'home', NULL, 250, 9999, 2500.00, 30, 'active'),
(17, 'CORPORATE STARTER', 'corporate', 'Static IP, Priority Support', 50, NULL, 3000.00, 30, 'active'),
(18, 'CORPORATE BASIC', 'corporate', '1 Static IP, SLA', 100, NULL, 4000.00, 30, 'active'),
(19, 'CORPORATE PLUS', 'corporate', 'Dedicated Support', 150, NULL, 6000.00, 30, 'active'),
(20, 'CORPORATE PRO', 'corporate', '2 Static IPs', 200, NULL, 8000.00, 30, 'active'),
(21, 'CORPORATE PREMIUM', 'corporate', 'Dedicated bandwidth', 300, NULL, 12000.00, 30, 'active'),
(22, 'ENTERPRISE', 'corporate', 'SLA + Dedicated line', 0, NULL, 0.00, 30, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` varchar(100) DEFAULT NULL,
  `transaction_ref` varchar(255) DEFAULT NULL,
  `paid_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `permissions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `subscription_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `package_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`subscription_id`, `user_id`, `package_id`, `start_date`, `end_date`, `status`) VALUES
(10, 20, 1, NULL, NULL, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_replies`
--

CREATE TABLE `ticket_replies` (
  `reply_id` int(11) NOT NULL,
  `ticket_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `replied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','customer','staff') DEFAULT 'customer',
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `role_id`, `full_name`, `designation`, `email`, `password_hash`, `role`, `phone`, `address`, `status`, `created_at`) VALUES
(4, NULL, 'Khondoker Moin Hossain', NULL, 'admin@amarit.com', '$2y$10$JaCY6PCN1vPHlwsLfeErMesRFggmyoC1Rxh7z8w4.Cq8D9eLZ0erm', 'admin', '01711000000', 'Jigatola, Dhaka', 'active', '2026-05-07 19:28:48'),
(13, NULL, 'Rahim', 'Field Technician', 'rahim@gmail.com', '$2y$10$DC1BSU.ZRGfM7mvHDRANReEk7GcNT2Fepcb7k3wPxfWq2eifT/jzi', 'staff', '01647655555', 'Dhanmondi 15', 'active', '2026-05-09 13:01:35'),
(14, NULL, 'Karim', 'Network Engineer', 'karim@gmail.com', '$2y$10$X.GeC7OGGp4IHAFCC5WLzOLTae/pXl2uci9ktZ6ai0nSvwMF8E9Zy', 'staff', '01647666666', 'Dhaka', 'active', '2026-05-09 13:02:23'),
(15, NULL, 'Salam', 'Billing Manager', 'salam@gmail.com', '$2y$10$WWzWFBhclxDW9.a4BL4Xj.1ClFOw.hvx5NMd/4O6KblADD5Uv9uhq', 'staff', '01647777777', 'Dhaka', 'active', '2026-05-09 13:02:57'),
(16, NULL, 'Rakib', 'Customer Support', 'rakib@gmail.com', '$2y$10$XaTnTtCHquc7MFxLTmozDOh5liWmqvpHUNcjK4AqT4fnOoh.8tOUS', 'staff', '01648888888', 'Dhaka', 'active', '2026-05-09 13:03:37'),
(20, NULL, 'KHONDOKER MOIN HOSSAIN', NULL, 'comt-2005131@dti.ac', '$2y$10$2hQrAQIbCbdnweh4Wjj9N.vU2i7yM.LIVS566zC6RRBs9AHvWGDWS', 'customer', '01647615608', 'Dhaka', 'active', '2026-05-12 06:04:38');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `coverage_zones`
--
ALTER TABLE `coverage_zones`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `designations`
--
ALTER TABLE `designations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`expense_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoice_id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `subscription_id` (`subscription_id`);

--
-- Indexes for table `network_logs`
--
ALTER TABLE `network_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`package_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`subscription_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `package_id` (`package_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`ticket_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `ticket_replies`
--
ALTER TABLE `ticket_replies`
  ADD PRIMARY KEY (`reply_id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_2` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `coverage_zones`
--
ALTER TABLE `coverage_zones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `designations`
--
ALTER TABLE `designations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `expense_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `network_logs`
--
ALTER TABLE `network_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `package_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `subscription_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `ticket_replies`
--
ALTER TABLE `ticket_replies`
  MODIFY `reply_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`subscription_id`) ON DELETE CASCADE;

--
-- Constraints for table `network_logs`
--
ALTER TABLE `network_logs`
  ADD CONSTRAINT `network_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscriptions_ibfk_2` FOREIGN KEY (`package_id`) REFERENCES `packages` (`package_id`);

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `ticket_replies`
--
ALTER TABLE `ticket_replies`
  ADD CONSTRAINT `ticket_replies_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`ticket_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
