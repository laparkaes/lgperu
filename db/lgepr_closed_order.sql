-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 27, 2024 at 12:41 AM
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
-- Table structure for table `lgepr_closed_order`
--

CREATE TABLE `lgepr_closed_order` (
  `order_id` int(11) NOT NULL,
  `dash_division` varchar(30) DEFAULT NULL,
  `dash_category` varchar(30) DEFAULT NULL,
  `category` text DEFAULT NULL,
  `bill_to_name` text DEFAULT NULL,
  `ship_to_name` text DEFAULT NULL,
  `model` text DEFAULT NULL,
  `order_qty` int(11) DEFAULT NULL,
  `total_amount_usd` float DEFAULT NULL,
  `total_amount` float DEFAULT NULL,
  `order_amount_usd` float DEFAULT NULL,
  `order_amount` float DEFAULT NULL,
  `line_charge_amount` float DEFAULT NULL,
  `header_charge_amount` float DEFAULT NULL,
  `tax_amount` float DEFAULT NULL,
  `dc_amount` float DEFAULT NULL,
  `dc_rate` float DEFAULT NULL,
  `currency` varchar(5) DEFAULT NULL,
  `inventory_org` text DEFAULT NULL,
  `sub_inventory` text DEFAULT NULL,
  `sales_person` text DEFAULT NULL,
  `customer_department` text DEFAULT NULL,
  `product_level1_name` text DEFAULT NULL,
  `product_level2_name` text DEFAULT NULL,
  `product_level3_name` text DEFAULT NULL,
  `product_level4_name` text DEFAULT NULL,
  `model_category` text DEFAULT NULL,
  `item_weight` float DEFAULT NULL,
  `item_cbm` float DEFAULT NULL,
  `order_date` date DEFAULT NULL,
  `closed_date` date DEFAULT NULL,
  `bill_to_code` text DEFAULT NULL,
  `ship_to_code` text DEFAULT NULL,
  `ship_to_city` text DEFAULT NULL,
  `sales_channel` text DEFAULT NULL,
  `order_source` text DEFAULT NULL,
  `order_line` text DEFAULT NULL,
  `order_no` text DEFAULT NULL,
  `line_no` text DEFAULT NULL,
  `customer_po_no` text DEFAULT NULL,
  `project_code` text DEFAULT NULL,
  `product_level4` text DEFAULT NULL,
  `receiver_city` text DEFAULT NULL,
  `invoice_no` text DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `shipping_method` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `lgepr_closed_order`
--
ALTER TABLE `lgepr_closed_order`
  ADD PRIMARY KEY (`order_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `lgepr_closed_order`
--
ALTER TABLE `lgepr_closed_order`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
