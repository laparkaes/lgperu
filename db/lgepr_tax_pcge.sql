-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 17, 2025 at 07:15 PM
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
-- Table structure for table `lgepr_tax_pcge`
--

CREATE TABLE `lgepr_tax_pcge` (
  `lgepr_pcge_id` int(11) NOT NULL,
  `from_period` varchar(100) DEFAULT NULL,
  `to_period` varchar(20) DEFAULT NULL,
  `accounting_unit` varchar(20) DEFAULT NULL,
  `accounting_unit_desc` varchar(100) DEFAULT NULL,
  `account` varchar(20) DEFAULT NULL,
  `account_desc` varchar(255) DEFAULT NULL,
  `pcge` varchar(100) DEFAULT NULL,
  `pcge_decripcion` varchar(255) DEFAULT NULL,
  `updated` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `lgepr_tax_pcge`
--
ALTER TABLE `lgepr_tax_pcge`
  ADD PRIMARY KEY (`lgepr_pcge_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `lgepr_tax_pcge`
--
ALTER TABLE `lgepr_tax_pcge`
  MODIFY `lgepr_pcge_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
