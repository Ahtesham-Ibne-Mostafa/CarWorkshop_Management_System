-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 30, 2025 at 09:06 PM
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
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `created_at`) VALUES
(1, 'admin', '$2y$10$MDcn6p.3GUqKM7K7qcTygeWYSer2ywJnIAnCiQd9JG0zLCfpc8DNq', '2025-11-30 18:38:32'),
(2, 'admin1', '$2y$10$EN.jpXDH9l1DBmDC//oKjerxkjCyw33hAn8Muj0hsLGhm8/M5H.ny', '2025-11-30 18:45:19'),
(3, 'admin3', '$2y$10$Mt8Kgzpn94qvp3/MXDy3QOC3Ba1bkTawh5txE7mF10ctSC15ATUgi', '2025-11-30 18:45:35');

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
(12, 'dfdf', 'fsgsf', '01998440309', 'fsgsf', 'fgsgfssgf', '2025-11-20', 1, 'approved', '2025-11-18 18:10:31', '2025-11-18 18:10:31', '9-11');

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
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

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
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
