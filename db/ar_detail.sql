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
-- Table structure for table `ar_detail`
--

CREATE TABLE `ar_detail` (
  `id` int(11) NOT NULL,
  `key_detail` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `invoice_no` varchar(255) DEFAULT NULL,
  `ar_class` varchar(100) DEFAULT NULL,
  `ar_type` varchar(100) DEFAULT NULL,
  `trx_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `gl_date` date DEFAULT NULL,
  `period` varchar(20) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `original_amount_entered_curr` float DEFAULT NULL,
  `offset` float DEFAULT NULL,
  `cash_receipt` float DEFAULT NULL,
  `on_account` float DEFAULT NULL,
  `note_to_cash` float DEFAULT NULL,
  `cash_discount` float DEFAULT NULL,
  `other_expense` float DEFAULT NULL,
  `note` float DEFAULT NULL,
  `note_balance` float DEFAULT NULL,
  `balance_total` float DEFAULT NULL,
  `original_amount_functional_curr` float DEFAULT NULL,
  `receipt_amount_functional_curr` float DEFAULT NULL,
  `on_account_functional` float DEFAULT NULL,
  `balance_total_functional_curr` float DEFAULT NULL,
  `opc_balance` float DEFAULT NULL,
  `hq_balance_except_opc` float DEFAULT NULL,
  `batch_source` varchar(255) DEFAULT NULL,
  `transaction_type` varchar(100) DEFAULT NULL,
  `bill_to_code` varchar(100) DEFAULT NULL,
  `bill_to_name` varchar(255) DEFAULT NULL,
  `au` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `account` varchar(100) DEFAULT NULL,
  `payment_term` varchar(150) DEFAULT NULL,
  `order_number` varchar(200) DEFAULT NULL,
  `model_category` varchar(255) DEFAULT NULL,
  `reference_no` varchar(255) DEFAULT NULL,
  `po_no` varchar(255) DEFAULT NULL,
  `salesperson` varchar(255) DEFAULT NULL,
  `ship_to_code` varchar(255) DEFAULT NULL,
  `ship_to_name` varchar(255) DEFAULT NULL,
  `voucher_no` varchar(255) DEFAULT NULL,
  `comments` varchar(255) DEFAULT NULL,
  `ar_no` varchar(255) DEFAULT NULL,
  `bad_ar` varchar(255) DEFAULT NULL,
  `commbiz_no` varchar(255) DEFAULT NULL,
  `collector` varchar(255) DEFAULT NULL,
  `reason_code` varchar(100) DEFAULT NULL,
  `sales_channel` varchar(255) DEFAULT NULL,
  `dd_status` varchar(255) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `creation_date` date NOT NULL,
  `fapiao_no` varchar(255) DEFAULT NULL,
  `biz_no` varchar(255) DEFAULT NULL,
  `worksheet_remark` varchar(255) DEFAULT NULL,
  `last_updated` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ar_detail`
--
ALTER TABLE `ar_detail`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ar_detail`
--
ALTER TABLE `ar_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
