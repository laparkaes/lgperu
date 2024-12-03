-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 03, 2024 at 01:28 AM
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
-- Table structure for table `sys_access`
--

CREATE TABLE `sys_access` (
  `access_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `module` varchar(100) NOT NULL,
  `function_id` int(11) NOT NULL,
  `valid` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `sys_access`
--

INSERT INTO `sys_access` (`access_id`, `employee_id`, `module`, `function_id`, `valid`) VALUES
(1, 22, 'hr_employee', 2, 0),
(2, 22, 'lgepr_sales_order', 10, 0),
(3, 22, 'ar_exchange_rate', 12, 0),
(4, 22, 'hr_attendance', 1, 0),
(5, 22, 'ism_activity_management', 3, 0),
(6, 22, 'obs_gerp', 14, 0),
(7, 22, 'obs_magento', 15, 0),
(8, 22, 'obs_most_likely', 16, 0),
(9, 22, 'obs_report', 4, 0),
(10, 22, 'sa_promotion', 6, 0),
(11, 22, 'sa_sell_inout', 7, 0),
(12, 22, 'sa_sell_out', 17, 0),
(13, 22, 'scm_purchase_order', 8, 0),
(14, 22, 'tax_invoice_comparison', 9, 0),
(15, 22, 'tax_paperless_document', 18, 0),
(16, 24, 'gerp_sales_order', 0, 0),
(17, 24, 'ar_exchange_rate', 12, 0),
(18, 24, 'hr_attendance', 1, 0),
(19, 24, 'hr_employee', 2, 0),
(20, 24, 'ism_activity_management', 3, 0),
(21, 24, 'obs_gerp', 14, 0),
(22, 24, 'obs_magento', 15, 0),
(23, 24, 'obs_most_likely', 16, 0),
(24, 24, 'obs_report', 4, 0),
(25, 24, 'sa_promotion', 6, 0),
(26, 24, 'sa_sell_inout', 7, 0),
(27, 24, 'sa_sell_out', 17, 0),
(28, 24, 'scm_purchase_order', 8, 0),
(29, 24, 'tax_invoice_comparison', 9, 0),
(30, 24, 'tax_paperless_document', 18, 0),
(31, 24, 'pi_listening', 5, 0),
(32, 45, 'obs_report', 4, 0),
(33, 10, 'obs_report', 4, 0),
(34, 46, 'obs_report', 4, 0),
(35, 47, 'obs_report', 4, 0),
(36, 44, 'obs_report', 4, 0),
(37, 22, 'pi_listening', 5, 0),
(38, 22, 'hr_access_record', 13, 0),
(39, 22, 'lgepr_stock', 11, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sys_access`
--
ALTER TABLE `sys_access`
  ADD PRIMARY KEY (`access_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sys_access`
--
ALTER TABLE `sys_access`
  MODIFY `access_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
