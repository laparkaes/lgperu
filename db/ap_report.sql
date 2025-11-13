-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 12, 2025 at 11:17 PM
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
-- Table structure for table `ap_report`
--

CREATE TABLE `ap_report` (
  `id` int(11) NOT NULL,
  `key_ap` varchar(255) DEFAULT NULL,
  `week` varchar(100) DEFAULT NULL,
  `month` varchar(100) DEFAULT NULL,
  `year` varchar(100) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `division_name` varchar(100) DEFAULT NULL,
  `division_name_desc` varchar(100) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `department_name` varchar(100) DEFAULT NULL,
  `department_name_desc` varchar(255) DEFAULT NULL,
  `account_name` varchar(100) DEFAULT NULL,
  `account_name_desc` varchar(255) DEFAULT NULL,
  `invoice_num` varchar(100) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `invoice_amount` float DEFAULT NULL,
  `payment_amount` float DEFAULT NULL,
  `porcentage_3` float DEFAULT NULL,
  `amount_remaining` float DEFAULT NULL,
  `pay_terms` varchar(100) DEFAULT NULL,
  `file_num` varchar(100) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `porcentage_3_378` float DEFAULT NULL,
  `exchange_rate` float DEFAULT NULL,
  `invoice_amount_fun` float DEFAULT NULL,
  `payment_amount_fun` float DEFAULT NULL,
  `amount_remaining_fun` float DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `voucher_number` varchar(255) DEFAULT NULL,
  `gl_date` date DEFAULT NULL,
  `creation_date` date DEFAULT NULL,
  `created_by` varchar(255) DEFAULT NULL,
  `source` varchar(255) DEFAULT NULL,
  `supplier_code` varchar(255) DEFAULT NULL,
  `pay_group` varchar(100) DEFAULT NULL,
  `biz_reg_no` varchar(100) DEFAULT NULL,
  `consulta_ruc` varchar(100) DEFAULT NULL,
  `batch_name` varchar(255) DEFAULT NULL,
  `invoice_received_date` date DEFAULT NULL,
  `item_amount` float DEFAULT NULL,
  `tax_amount` float DEFAULT NULL,
  `pay_method` varchar(100) DEFAULT NULL,
  `approver_emp_no` varchar(100) DEFAULT NULL,
  `approver_name` varchar(255) DEFAULT NULL,
  `approver_dept` varchar(100) DEFAULT NULL,
  `last_updated` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ap_report`
--
ALTER TABLE `ap_report`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ap_report`
--
ALTER TABLE `ap_report`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
