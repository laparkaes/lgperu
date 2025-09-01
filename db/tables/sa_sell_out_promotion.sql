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
-- Table structure for table `sa_sell_out_promotion`
--

CREATE TABLE `sa_sell_out_promotion` (
  `promotion_sell_out_id` int(11) NOT NULL,
  `pr` varchar(50) DEFAULT NULL,
  `customer` varchar(100) DEFAULT NULL,
  `acct_gtm` varchar(100) DEFAULT NULL,
  `customer_model` varchar(255) DEFAULT NULL,
  `model_code` varchar(255) DEFAULT NULL,
  `txn_date` date DEFAULT NULL,
  `cust_store_code` varchar(255) DEFAULT NULL,
  `cust_store_name` varchar(255) DEFAULT NULL,
  `sellout_unit` decimal(20,2) DEFAULT NULL,
  `sellout_amt` decimal(20,12) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `ticket` decimal(10,4) DEFAULT NULL,
  `promo1_price` float DEFAULT NULL,
  `target1_flag` varchar(100) DEFAULT NULL,
  `promo2_price` decimal(10,2) DEFAULT NULL,
  `target2_flag` varchar(100) DEFAULT NULL,
  `promo3_price` decimal(10,2) DEFAULT NULL,
  `target3_flag` varchar(100) DEFAULT NULL,
  `promo4_price` decimal(10,2) DEFAULT NULL,
  `target4_flag` varchar(100) DEFAULT NULL,
  `promo5_price` decimal(10,2) DEFAULT NULL,
  `target5_flag` varchar(100) DEFAULT NULL,
  `promo6_price` decimal(10,2) DEFAULT NULL,
  `target6_flag` varchar(100) DEFAULT NULL,
  `promo7_price` decimal(10,2) DEFAULT NULL,
  `target7_flag` varchar(100) DEFAULT NULL,
  `promo8_price` decimal(10,2) DEFAULT NULL,
  `target8_flag` varchar(100) DEFAULT NULL,
  `promo9_price` decimal(10,2) DEFAULT NULL,
  `target9_flag` varchar(100) DEFAULT NULL,
  `promo10_price` decimal(10,2) DEFAULT NULL,
  `target10_flag` varchar(100) DEFAULT NULL,
  `updated` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sa_sell_out_promotion`
--
ALTER TABLE `sa_sell_out_promotion`
  ADD PRIMARY KEY (`promotion_sell_out_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sa_sell_out_promotion`
--
ALTER TABLE `sa_sell_out_promotion`
  MODIFY `promotion_sell_out_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
