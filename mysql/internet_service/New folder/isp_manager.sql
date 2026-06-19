-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 09, 2026 at 10:42 AM
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

/* INSERT INTO `coverage_zones` (`id`, `district`, `upazila`, `description`, `status`, `created_at`) VALUES
(1, 'Dhaka', 'Dhanmondi', 'laksjdfoiauwer;lsdjfc', 'active', '2026-05-12 05:54:03'); */

INSERT INTO `coverage_zones` (`id`, `district`, `upazila`, `description`, `status`, `created_at`)
VALUES 
(1, 'Dhaka', 'Dhanmondi', 'Residential and commercial area with hospitals, universities, and shopping centers.', 'active', '2026-05-12 05:54:03'),

(2, 'Dhaka', 'Banani', 'High-end commercial zone with corporate offices, restaurants, and luxury apartments.', 'active', '2026-05-12 05:54:03'),

(3, 'Dhaka', 'Gulshan', 'Diplomatic and corporate area with embassies, offices, and premium residences.', 'active', '2026-05-12 05:54:03'),

(4, 'Dhaka', 'Uttara', 'Large residential zone near airport with schools, markets, and transport hubs.', 'active', '2026-05-12 05:54:03'),

(5, 'Dhaka', 'Mirpur', 'Dense residential area with stadium, markets, and industrial zones.', 'active', '2026-05-12 05:54:03'),

(6, 'Dhaka', 'Mohammadpur', 'Residential area with schools, hospitals, and local markets.', 'active', '2026-05-12 05:54:03'),

(7, 'Chattogram', 'Agrabad', 'Main business hub with banks, offices, and commercial centers.', 'active', '2026-05-12 05:54:03'),

(8, 'Chattogram', 'Halishahar', 'Residential and semi-industrial area with housing and local markets.', 'active', '2026-05-12 05:54:03'),

(9, 'Chattogram', 'Pahartali', 'Port-connected industrial and residential zone.', 'active', '2026-05-12 05:54:03'),

(10, 'Sylhet', 'Zindabazar', 'Main commercial area with shops, hotels, and business centers.', 'active', '2026-05-12 05:54:03'),

(11, 'Rajshahi', 'Boalia', 'Administrative and residential urban area with offices and schools.', 'active', '2026-05-12 05:54:03'),

(12, 'Khulna', 'Sonadanga', 'Commercial area with markets, offices, and transport facilities.', 'active', '2026-05-12 05:54:03');


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
(1, 7, 1, '2026-05-15', '2026-06-15', 'expired'),
(2, 8, 2, '2026-05-21', '2026-06-21', 'active'),
(3, 9, 3, '2026-05-20', '2026-06-20', 'active'),
(4, 10, 4, '2026-06-20', '2026-07-20', 'active'),
(5, 11, 5, NULL, NULL, 'pending');

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
(1, NULL, 'Khondoker Moin Hossain', NULL, 'admin@amarit.com', '$2y$10$JaCY6PCN1vPHlwsLfeErMesRFggmyoC1Rxh7z8w4.Cq8D9eLZ0erm', 'admin', '01711000000', 'Jigatola, Dhaka', 'active', '2026-05-07 19:28:48'),
(2, NULL, 'Rahim', 'Field Technician', 'rahim@gmail.com', '$2y$10$DC1BSU.ZRGfM7mvHDRANReEk7GcNT2Fepcb7k3wPxfWq2eifT/jzi', 'staff', '01647655555', 'Dhanmondi 15', 'active', '2026-05-09 13:01:35'),
(3, NULL, 'Karim', 'Network Engineer', 'karim@gmail.com', '$2y$10$X.GeC7OGGp4IHAFCC5WLzOLTae/pXl2uci9ktZ6ai0nSvwMF8E9Zy', 'staff', '01647666666', 'Dhaka', 'active', '2026-05-09 13:02:23'),
(4, NULL, 'Salam', 'Billing Manager', 'salam@gmail.com', '$2y$10$WWzWFBhclxDW9.a4BL4Xj.1ClFOw.hvx5NMd/4O6KblADD5Uv9uhq', 'staff', '01647777777', 'Dhaka', 'active', '2026-05-09 13:02:57'),
(5, NULL, 'Rakib', 'Customer Support', 'rakib@gmail.com', '$2y$10$XaTnTtCHquc7MFxLTmozDOh5liWmqvpHUNcjK4AqT4fnOoh.8tOUS', 'staff', '01648888888', 'Dhaka', 'active', '2026-05-09 13:03:37'),
(6, NULL, 'KHONDOKER MOIN HOSSAIN', NULL, 'comt-2005131@dti.ac', '$2y$10$2hQrAQIbCbdnweh4Wjj9N.vU2i7yM.LIVS566zC6RRBs9AHvWGDWS', 'customer', '01647615608', 'Dhaka', 'active', '2026-05-12 06:04:38'),
(7, NULL, 'Test Suspended', NULL, 'suspended@test.com', '$2y$10$JaCY6PCN1vPHlwsLfeErMesRFggmyoC1Rxh7z8w4.Cq8D9eLZ0erm', 'customer', '01700000101', 'Dhaka', 'suspended', '2026-05-15 10:00:00'),
(8, NULL, 'Test 1 Day Left', NULL, '1day@test.com', '$2y$10$JaCY6PCN1vPHlwsLfeErMesRFggmyoC1Rxh7z8w4.Cq8D9eLZ0erm', 'customer', '01700000102', 'Dhaka', 'active', '2026-05-21 10:00:00'),
(9, NULL, 'Test Expires Today', NULL, 'today@test.com', '$2y$10$JaCY6PCN1vPHlwsLfeErMesRFggmyoC1Rxh7z8w4.Cq8D9eLZ0erm', 'customer', '01700000103', 'Dhaka', 'active', '2026-05-20 10:00:00'),
(10, NULL, 'Test Active Month', NULL, 'active@test.com', '$2y$10$JaCY6PCN1vPHlwsLfeErMesRFggmyoC1Rxh7z8w4.Cq8D9eLZ0erm', 'customer', '01700000104', 'Dhaka', 'active', '2026-06-20 10:00:00'),
(11, NULL, 'Test Pending Line', NULL, 'pending@test.com', '$2y$10$JaCY6PCN1vPHlwsLfeErMesRFggmyoC1Rxh7z8w4.Cq8D9eLZ0erm', 'customer', '01700000105', 'Dhaka', 'active', '2026-06-20 10:00:00');

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
-- Constraints for table `network_logs`
--
ALTER TABLE `network_logs`
  ADD CONSTRAINT `network_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
