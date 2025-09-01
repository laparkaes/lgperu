-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 27, 2025 at 04:33 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `llamasys`
--

-- --------------------------------------------------------

--
-- Table structure for table `sa_calculate_promotion`
--

CREATE TABLE `sa_calculate_promotion` (
  `sa_promotion_id` int(11) NOT NULL,
  `pr` varchar(50) DEFAULT NULL,
  `seq` int(11) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `division_name` varchar(20) DEFAULT NULL,
  `promotion_no` varchar(255) DEFAULT NULL,
  `promotion_line_no` int(10) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `customer_code` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `pvp` decimal(10,2) DEFAULT NULL,
  `cost_sellin` decimal(10,2) DEFAULT NULL,
  `price_promotion` decimal(10,2) DEFAULT NULL,
  `new_margin` varchar(10) DEFAULT NULL,
  `cost_promotion` decimal(10,2) DEFAULT NULL,
  `difference` decimal(10,2) DEFAULT NULL,
  `unity` int(11) DEFAULT NULL,
  `gift` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `upload` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sa_calculate_promotion`
--
ALTER TABLE `sa_calculate_promotion`
  ADD PRIMARY KEY (`sa_promotion_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sa_calculate_promotion`
--
ALTER TABLE `sa_calculate_promotion`
  MODIFY `sa_promotion_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
