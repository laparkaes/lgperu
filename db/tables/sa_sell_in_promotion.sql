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
-- Table structure for table `sa_sell_in_promotion`
--

CREATE TABLE `sa_sell_in_promotion` (
  `promotion_id` int(11) NOT NULL,
  `pr` varchar(50) DEFAULT NULL,
  `seq` int(11) DEFAULT NULL,
  `division_name` varchar(20) DEFAULT NULL,
  `bill_to_customer_code` varchar(100) DEFAULT NULL,
  `item_name` varchar(100) DEFAULT NULL,
  `sales_yyyymmdd` date DEFAULT NULL,
  `invoice_no` varchar(255) DEFAULT NULL,
  `week_yyyywww` varchar(100) DEFAULT NULL,
  `transaction_currency_code` varchar(10) DEFAULT NULL,
  `sales_order_qty` int(11) DEFAULT NULL,
  `unit_selling_price` decimal(20,2) DEFAULT NULL,
  `sales_order_amount_tax_exclude` decimal(20,2) DEFAULT NULL,
  `tc` decimal(20,2) DEFAULT NULL,
  `unit_selling_price_pen` decimal(20,2) DEFAULT NULL,
  `cal_seq1` varchar(10) DEFAULT NULL,
  `new_cost1` decimal(10,2) DEFAULT NULL,
  `sellout1` int(11) DEFAULT NULL,
  `stock1` int(11) DEFAULT NULL,
  `cal_seq2` varchar(20) DEFAULT NULL,
  `new_cost2` decimal(10,2) DEFAULT NULL,
  `sellout2` int(11) DEFAULT NULL,
  `stock2` int(11) DEFAULT NULL,
  `cal_seq3` varchar(20) DEFAULT NULL,
  `new_cost3` decimal(10,2) DEFAULT NULL,
  `sellout3` int(11) DEFAULT NULL,
  `stock3` int(11) DEFAULT NULL,
  `cal_seq4` varchar(20) DEFAULT NULL,
  `new_cost4` decimal(10,2) DEFAULT NULL,
  `sellout4` int(11) DEFAULT NULL,
  `stock4` int(11) DEFAULT NULL,
  `cal_seq5` varchar(20) DEFAULT NULL,
  `new_cost5` decimal(10,2) DEFAULT NULL,
  `sellout5` int(11) DEFAULT NULL,
  `stock5` int(11) DEFAULT NULL,
  `cal_seq6` varchar(20) DEFAULT NULL,
  `new_cost6` decimal(10,2) DEFAULT NULL,
  `sellout6` int(11) DEFAULT NULL,
  `stock6` int(11) DEFAULT NULL,
  `cal_seq7` varchar(20) DEFAULT NULL,
  `new_cost7` decimal(10,2) DEFAULT NULL,
  `sellout7` int(11) DEFAULT NULL,
  `stock7` int(11) DEFAULT NULL,
  `cal_seq8` varchar(20) DEFAULT NULL,
  `new_cost8` decimal(10,2) DEFAULT NULL,
  `sellout8` int(11) DEFAULT NULL,
  `stock8` int(11) DEFAULT NULL,
  `cal_seq9` varchar(20) DEFAULT NULL,
  `new_cost9` decimal(10,2) DEFAULT NULL,
  `sellout9` int(11) DEFAULT NULL,
  `stock9` int(11) DEFAULT NULL,
  `cal_seq10` varchar(20) DEFAULT NULL,
  `new_cost10` decimal(10,2) DEFAULT NULL,
  `sellout10` int(11) DEFAULT NULL,
  `stock10` int(11) DEFAULT NULL,
  `bill_to_customer_name` varchar(255) DEFAULT NULL,
  `so_no` int(11) DEFAULT NULL,
  `so_type_name` varchar(255) DEFAULT NULL,
  `updated` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sa_sell_in_promotion`
--
ALTER TABLE `sa_sell_in_promotion`
  ADD PRIMARY KEY (`promotion_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sa_sell_in_promotion`
--
ALTER TABLE `sa_sell_in_promotion`
  MODIFY `promotion_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
