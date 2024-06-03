-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 02, 2024 at 06:32 AM
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
-- Database: `mapecon`
--

-- --------------------------------------------------------

--
-- Table structure for table `leave_applications`
--

CREATE TABLE `leave_applications` (
  `id` bigint(50) NOT NULL,
  `application_id` int(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `date_filed` date NOT NULL,
  `leave_type` varchar(255) NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `working_days_covered` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(50) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `user_status` varchar(100) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `contactnumber` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `otp` int(6) DEFAULT NULL,
  `token_expired` DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `user_status`, `firstname`, `lastname`, `contactnumber`, `email`, `password`, `department`, `otp`,`token_expired`) VALUES
(0, 1, 'Admin', 'Admin', '', '', 'admin@gmail.com', '$2y$10$FmjPZzCQbq9ggeBoQOfzMOoK11ecmjzIQxnZqtkMLdZQe5WnIvMxC', '', NULL, NULL), /*Pass: 123456*/
(1, 2, 'User', 'fname dummy', 'lname dummy', '09999099543', 'dummy@gmail.com', '$2y$10$FmjPZzCQbq9ggeBoQOfzMOoK11ecmjzIQxnZqtkMLdZQe5WnIvMxC', 'Accounting', NULL, NULL); /*Pass: 123456*/

--
-- Indexes for dumped tables
--
-- Indexes for table `users`
--
ALTER TABLE `users`
  MODIFY COLUMN `id` bigint(50) NOT NULL AUTO_INCREMENT,
  ADD PRIMARY KEY (`id`);
--
-- AUTO_INCREMENT for table `leave_applications`
--
ALTER TABLE `leave_applications`
  ADD PRIMARY KEY (`id`),
  MODIFY `id` bigint(50) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
