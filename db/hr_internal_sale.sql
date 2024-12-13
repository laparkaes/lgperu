-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 14, 2024 at 12:12 AM
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
-- Table structure for table `hr_internal_sale`
--

CREATE TABLE `hr_internal_sale` (
  `sale_id` int(11) NOT NULL,
  `division` enum('HA','HE','BS') DEFAULT NULL,
  `model` varchar(255) NOT NULL,
  `grade` enum('A','B','C') NOT NULL,
  `remark` text DEFAULT NULL,
  `price_list` float NOT NULL,
  `price_offer` float NOT NULL,
  `created_at` date NOT NULL,
  `end_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `hr_internal_sale`
--

INSERT INTO `hr_internal_sale` (`sale_id`, `division`, `model`, `grade`, `remark`, `price_list`, `price_offer`, `created_at`, `end_date`) VALUES
(1, '', '', '', NULL, 0, 0, '2024-12-13', '0000-00-00'),
(2, '', '', '', NULL, 0, 0, '2024-12-13', '0000-00-00'),
(3, '', '', '', NULL, 0, 0, '2024-12-13', '0000-00-00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `hr_internal_sale`
--
ALTER TABLE `hr_internal_sale`
  ADD PRIMARY KEY (`sale_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hr_internal_sale`
--
ALTER TABLE `hr_internal_sale`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
