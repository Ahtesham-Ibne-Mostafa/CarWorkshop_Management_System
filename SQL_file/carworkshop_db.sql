-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 18, 2025 at 07:12 PM
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
-- Database: `carworkshop_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `client_name` varchar(120) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `car_license` varchar(50) NOT NULL,
  `car_engine` varchar(50) NOT NULL,
  `appointment_date` date NOT NULL,
  `mechanic_id` int(11) NOT NULL,
  `status` enum('approved','cancelled') NOT NULL DEFAULT 'approved',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `slot` enum('9-11','11.30-1.30','2-4','4.30-6.30') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `client_name`, `address`, `phone`, `car_license`, `car_engine`, `appointment_date`, `mechanic_id`, `status`, `created_at`, `updated_at`, `slot`) VALUES
(4, 'dfdf', 'fdsfsddsf', '01621924444', 'dfsfsdsd', 'dsfdsds', '2025-11-19', 1, 'approved', '2025-11-18 17:08:32', '2025-11-18 17:08:32', '9-11'),
(5, 'dfdf', 'fdsfsddsf', '01621924443', 'dfsfsdsd', 'dsfdsds', '2025-11-19', 1, 'approved', '2025-11-18 17:08:57', '2025-11-18 17:08:57', '9-11'),
(6, 'dfdf', 'fdsfsddsf', '01621924449', 'dfsfsdsd', 'dsfdsds', '2025-11-19', 1, 'approved', '2025-11-18 17:09:15', '2025-11-18 17:09:15', '9-11'),
(7, 'dfdf', 'fdsfsddsf', '01621924442', 'dfsfsdsd', 'dsfdsds', '2025-11-19', 1, 'approved', '2025-11-18 17:09:22', '2025-11-18 17:09:22', '9-11'),
(8, 'dfdf', 'fdsfsddsf', '0162192456655', 'dfsfsdsd', 'dsfdsds', '2025-11-21', 2, 'approved', '2025-11-18 17:13:54', '2025-11-18 17:13:54', '9-11'),
(9, 'dfdf', 'fdsfsddsf', '0162192456657', 'dfsfsdsd', 'dsfdsds', '2025-11-21', 2, 'approved', '2025-11-18 17:13:58', '2025-11-18 17:13:58', '9-11'),
(10, 'dfdf', 'fdsfsddsf', '0162192456657', 'dfsfsdsd', 'dsfdsds', '2025-11-18', 1, 'approved', '2025-11-18 17:14:17', '2025-11-18 17:14:17', '9-11'),
(11, 'dfdf', 'fdsfsddsf', '0162192456555', 'dfsfsdsd', 'dsfdsds', '2025-11-18', 1, 'approved', '2025-11-18 17:14:23', '2025-11-18 17:14:23', '9-11'),
(12, 'dfdf', 'fsgsf', '01998440309', 'fsgsf', 'fgsgfssgf', '2025-11-20', 1, 'approved', '2025-11-18 18:10:31', '2025-11-18 18:10:31', '9-11'),
(13, 'dfdf', 'fsgsf', '01998440308', 'fsgsf', 'fgsgfssgf', '2025-11-20', 1, 'approved', '2025-11-18 18:10:54', '2025-11-18 18:10:54', '11.30-1.30');

-- --------------------------------------------------------

--
-- Table structure for table `mechanics`
--

CREATE TABLE `mechanics` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `max_daily_capacity` int(11) NOT NULL DEFAULT 4,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mechanics`
--

INSERT INTO `mechanics` (`id`, `name`, `max_daily_capacity`, `is_active`) VALUES
(1, 'Abdul Karim', 4, 1),
(2, 'Nazmul Hasan', 4, 1),
(3, 'Shafiqul Islam', 4, 1),
(4, 'Rashid Ahmed', 4, 1),
(5, 'Farhana Rahman', 4, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_client_per_date` (`phone`,`appointment_date`),
  ADD KEY `fk_mechanic` (`mechanic_id`);

--
-- Indexes for table `mechanics`
--
ALTER TABLE `mechanics`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `mechanics`
--
ALTER TABLE `mechanics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_mechanic` FOREIGN KEY (`mechanic_id`) REFERENCES `mechanics` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
