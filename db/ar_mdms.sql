-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 05, 2025 at 04:39 PM
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
-- Table structure for table `ar_mdms`
--

CREATE TABLE `ar_mdms` (
  `ar_mdms_id` int(11) NOT NULL,
  `code_id` varchar(255) DEFAULT NULL,
  `lgediv_id` varchar(20) DEFAULT NULL,
  `lgediv_name` varchar(20) DEFAULT NULL,
  `company_affiliate_code_id` varchar(20) DEFAULT NULL,
  `company_affiliate_code_name` varchar(100) DEFAULT NULL,
  `short_name_eng` varchar(20) DEFAULT NULL,
  `au_id` varchar(20) DEFAULT NULL,
  `au_name` mediumtext DEFAULT NULL,
  `supplier_code` varchar(255) DEFAULT NULL,
  `supplier_name_loc` varchar(255) DEFAULT NULL,
  `supplier_name_eng` varchar(255) DEFAULT NULL,
  `biz_registration_no` varchar(255) DEFAULT NULL,
  `domain_type` varchar(20) DEFAULT NULL,
  `job_type_id` varchar(255) DEFAULT NULL,
  `job_type_name` varchar(255) DEFAULT NULL,
  `trade_type_id` varchar(10) DEFAULT NULL,
  `trade_type_name` varchar(100) DEFAULT NULL,
  `currency_code_id` varchar(10) DEFAULT NULL,
  `currency_code_name` varchar(20) DEFAULT NULL,
  `term_days` int(10) DEFAULT NULL,
  `payment_terms_name` varchar(20) DEFAULT NULL,
  `hub_use_flag_id` varchar(10) DEFAULT NULL,
  `hub_use_flag_name` varchar(10) DEFAULT NULL,
  `payterm_type` varchar(20) DEFAULT NULL,
  `available_period_from` timestamp NULL DEFAULT NULL,
  `available_period_to` timestamp NULL DEFAULT NULL,
  `settlement_type_id` varchar(10) DEFAULT NULL,
  `settlement_type_name` varchar(20) DEFAULT NULL,
  `due_counted_point_id` varchar(10) DEFAULT NULL,
  `due_counted_point_name` varchar(20) DEFAULT NULL,
  `prorate_basis_type_id` varchar(20) DEFAULT NULL,
  `prorate_basis_type_name` varchar(30) DEFAULT NULL,
  `payment_group_id` varchar(50) DEFAULT NULL,
  `payment_group_name` varchar(50) DEFAULT NULL,
  `payment_method` varchar(20) DEFAULT NULL,
  `collection_redem_at_sight_l_c` varchar(20) DEFAULT NULL,
  `bank_charge_payment_entity_l_c` varchar(20) DEFAULT NULL,
  `document_submit_days_l` varchar(20) DEFAULT NULL,
  `usane_l_c_type` varchar(20) DEFAULT NULL,
  `usance_l_c_interest_pay_type` varchar(20) DEFAULT NULL,
  `usance_l_c_interest_rate` varchar(20) DEFAULT NULL,
  `enabled_flag_id` varchar(5) DEFAULT NULL,
  `enabled_flag_name` varchar(5) DEFAULT NULL,
  `payterm_key` varchar(255) DEFAULT NULL,
  `creation_date` varchar(255) DEFAULT NULL,
  `creation_user_id` varchar(255) DEFAULT NULL,
  `last_update_date` varchar(255) DEFAULT NULL,
  `last_updated_by` varchar(255) DEFAULT NULL,
  `updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ar_mdms`
--
ALTER TABLE `ar_mdms`
  ADD PRIMARY KEY (`ar_mdms_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ar_mdms`
--
ALTER TABLE `ar_mdms`
  MODIFY `ar_mdms_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
