-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 09, 2025 at 11:46 AM
-- Server version: 10.11.10-MariaDB
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u676630011_page`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `status` varchar(50) DEFAULT 'active',
  `role` enum('editor_home','editor_about','editor_both','full_admin') NOT NULL DEFAULT 'editor_home'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `status`, `role`) VALUES
(6, 'humaid', '$2y$10$DUCwLyw7KssAtUtcOOYL7OpH0MngWQEGquLVAMBiY9scAA1JVlvEW', 'active', 'full_admin');

-- --------------------------------------------------------

--
-- Table structure for table `email_notifications`
--

CREATE TABLE `email_notifications` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_notifications`
--

INSERT INTO `email_notifications` (`id`, `email`) VALUES
(7, '22f22683@mec.edu.om'),
(8, 'adeeb@irts.om');

-- --------------------------------------------------------

--
-- Table structure for table `errors`
--

CREATE TABLE `errors` (
  `id` int(11) NOT NULL,
  `error_message` text NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `device_type` varchar(255) DEFAULT NULL,
  `operating_system` varchar(255) DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `username`, `device_type`, `operating_system`, `ip_address`, `country`, `timestamp`, `status`) VALUES
(51, 'humaid', 'Desktop', 'Windows 10', '188.135.90.187', 'OM', '2024-12-23 12:33:36', 'active'),
(52, 'humaid', 'Desktop', 'Windows 10', '188.135.82.22', 'OM', '2024-12-30 11:02:01', 'active'),
(53, 'johannes34@webmai.co', 'Desktop', 'Mac OS X', '2a03:b0c0:2:d0::1495:2001', 'NL', '2024-12-30 18:51:03', 'failed'),
(54, 'femke31', 'Desktop', 'Mac OS X', '2a03:b0c0:2:d0::1495:2001', 'NL', '2024-12-30 18:51:05', 'failed'),
(55, 'femke31', 'Desktop', 'Mac OS X', '2a03:b0c0:2:d0::1495:2001', 'NL', '2024-12-30 18:51:07', 'failed'),
(56, 'johannes34@webmai.co', 'Desktop', 'Mac OS X', '2a03:b0c0:2:d0::1495:2001', 'NL', '2024-12-30 18:51:08', 'failed'),
(57, 'johannes34@webmai.co', 'Desktop', 'Mac OS X', '2a03:b0c0:2:d0::1495:2001', 'NL', '2024-12-30 18:51:18', 'failed'),
(58, 'guadalupe_zapata9@webmai.co', 'Desktop', 'Mac OS X', '128.90.119.86', 'US', '2024-12-30 18:53:36', 'failed'),
(59, 'arturo29', 'Desktop', 'Mac OS X', '128.90.119.86', 'US', '2024-12-30 18:53:38', 'failed'),
(60, 'arturo29', 'Desktop', 'Mac OS X', '128.90.119.86', 'US', '2024-12-30 18:53:40', 'failed'),
(61, 'guadalupe_zapata9@webmai.co', 'Desktop', 'Mac OS X', '128.90.119.86', 'US', '2024-12-30 18:53:42', 'failed'),
(62, 'guadalupe_zapata9@webmai.co', 'Desktop', 'Mac OS X', '128.90.119.86', 'US', '2024-12-30 18:53:51', 'failed'),
(63, 'humaid', 'Desktop', 'Windows 10', '188.135.82.22', 'OM', '2024-12-31 11:00:37', 'active'),
(64, 'humaid', 'Desktop', 'Windows 10', '78.111.38.76', 'OM', '2025-01-01 04:10:36', 'failed'),
(65, 'humaid', 'Desktop', 'Windows 10', '78.111.38.76', 'OM', '2025-01-01 04:10:40', 'active'),
(66, 'humaid', 'Desktop', 'Windows 10', '96.9.137.116', 'OM', '2025-01-09 08:20:48', 'active'),
(67, 'humaid', 'Desktop', 'Windows 10', '78.111.38.76', 'OM', '2025-01-09 08:40:08', 'failed'),
(68, 'humaid', 'Desktop', 'Windows 10', '78.111.38.76', 'OM', '2025-01-09 08:40:14', 'active'),
(69, 'humaid', 'Desktop', 'Windows 10', '78.111.38.76', 'OM', '2025-01-09 08:55:49', 'active'),
(70, 'SELECT * FROM users WHERE name=\'tom\'', 'Desktop', 'Mac OS X', '94.176.20.178', 'OM', '2025-01-09 10:47:06', 'failed'),
(71, 'humaid', 'Desktop', 'Mac OS X', '94.176.20.178', 'OM', '2025-01-09 10:50:00', 'active'),
(72, 'humaid', 'Desktop', 'Windows 10', '78.111.38.76', 'OM', '2025-01-09 11:28:07', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `page_content`
--

CREATE TABLE `page_content` (
  `id` int(11) NOT NULL,
  `page_name` varchar(100) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `page_content`
--

INSERT INTO `page_content` (`id`, `page_name`, `content`) VALUES
(1, 'home', 'Test 123'),
(2, 'about', 'ssss');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `email_notifications`
--
ALTER TABLE `email_notifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `errors`
--
ALTER TABLE `errors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `page_content`
--
ALTER TABLE `page_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `page_name` (`page_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `email_notifications`
--
ALTER TABLE `email_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `errors`
--
ALTER TABLE `errors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `page_content`
--
ALTER TABLE `page_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
