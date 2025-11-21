-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 20, 2025 at 06:03 PM
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
-- Table structure for table `ar_aging`
--

CREATE TABLE `ar_aging` (
  `id` int(11) NOT NULL,
  `key_aging` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `period` varchar(255) DEFAULT NULL,
  `er` float DEFAULT NULL,
  `index1` int(11) DEFAULT NULL,
  `index2` int(11) DEFAULT NULL,
  `index3` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `class` varchar(100) DEFAULT NULL,
  `invoice_no` varchar(100) DEFAULT NULL,
  `bill_code` varchar(255) DEFAULT NULL,
  `bill_name` varchar(255) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `ar_type` varchar(100) DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `overdue_amount` float DEFAULT NULL,
  `overdue_days` int(11) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `overdue_reason` varchar(255) DEFAULT NULL,
  `au` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `account_code` varchar(100) DEFAULT NULL,
  `sales_person` varchar(255) DEFAULT NULL,
  `reference_no` varchar(255) DEFAULT NULL,
  `trx_number` varchar(255) DEFAULT NULL,
  `remark` varchar(100) DEFAULT NULL,
  `bad_ar` varchar(255) DEFAULT NULL,
  `model_category` varchar(255) DEFAULT NULL,
  `payment_term_name` varchar(255) DEFAULT NULL,
  `sales_channel_name` varchar(255) DEFAULT NULL,
  `reason_code` varchar(100) DEFAULT NULL,
  `installment_seq` varchar(100) DEFAULT NULL,
  `last_updated` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ar_aging`
--
ALTER TABLE `ar_aging`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ar_aging`
--
ALTER TABLE `ar_aging`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
