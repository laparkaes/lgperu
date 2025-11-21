-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 20, 2025 at 06:02 PM
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
-- Table structure for table `ar_cash_report`
--

CREATE TABLE `ar_cash_report` (
  `id` int(11) NOT NULL,
  `key_cash_report` varchar(255) DEFAULT NULL,
  `statement_id` int(11) NOT NULL,
  `gl_date` date DEFAULT NULL,
  `deposit_amount` float DEFAULT NULL,
  `bill_to_code` varchar(255) DEFAULT NULL,
  `bill_to_name` varchar(255) DEFAULT NULL,
  `deposit_currency` varchar(10) DEFAULT NULL,
  `alloc_amount` float DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `bank_account` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `requested_date` date DEFAULT NULL,
  `requested_by` varchar(255) DEFAULT NULL,
  `receipt_type` varchar(255) DEFAULT NULL,
  `batch_no` varchar(255) DEFAULT NULL,
  `last_updated` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ar_cash_report`
--
ALTER TABLE `ar_cash_report`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ar_cash_report`
--
ALTER TABLE `ar_cash_report`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
