-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 02, 2025 at 07:09 PM
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
-- Table structure for table `scm_gre_calculate`
--

CREATE TABLE `scm_gre_calculate` (
  `id` int(11) NOT NULL,
  `user_pr` varchar(50) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `ar_comment` varchar(255) DEFAULT NULL,
  `invoice_no` varchar(100) DEFAULT NULL,
  `ar_class` varchar(10) DEFAULT NULL,
  `ar_type` varchar(20) DEFAULT NULL,
  `trx_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `due_date_month` varchar(100) DEFAULT NULL,
  `month` int(11) NOT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `original_amount_entered_curr` decimal(10,2) NOT NULL,
  `bill_to_code` varchar(100) DEFAULT NULL,
  `bill_to_name` varchar(255) DEFAULT NULL,
  `po_no` varchar(255) DEFAULT NULL,
  `status_may` varchar(100) DEFAULT NULL,
  `delivery_note` varchar(100) DEFAULT NULL,
  `upload_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `scm_gre_calculate`
--
ALTER TABLE `scm_gre_calculate`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `scm_gre_calculate`
--
ALTER TABLE `scm_gre_calculate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
