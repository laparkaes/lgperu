-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 20, 2024 at 08:46 PM
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
-- Table structure for table `lgepr_sales_order`
--

CREATE TABLE `lgepr_sales_order` (
  `sales_order_id` int(11) NOT NULL,
  `bill_to_name` varchar(30) DEFAULT NULL,
  `ship_to_name` varchar(30) DEFAULT NULL,
  `model` varchar(30) DEFAULT NULL,
  `order_line` varchar(30) DEFAULT NULL,
  `order_no` varchar(30) DEFAULT NULL,
  `line_no` varchar(30) DEFAULT NULL,
  `order_type` varchar(30) DEFAULT NULL,
  `line_status` varchar(30) DEFAULT NULL,
  `ordered_qty` int(11) DEFAULT NULL,
  `unit_selling_price` float DEFAULT NULL,
  `sales_amount` float DEFAULT NULL,
  `tax_amount` float DEFAULT NULL,
  `charge_amount` float DEFAULT NULL,
  `line_total` float DEFAULT NULL,
  `currency` varchar(30) DEFAULT NULL,
  `booked_date` date DEFAULT NULL,
  `req_arrival_date_to` date DEFAULT NULL,
  `shipment_date` date DEFAULT NULL,
  `close_date` date DEFAULT NULL,
  `bill_to` varchar(30) DEFAULT NULL,
  `customer_department` varchar(30) DEFAULT NULL,
  `ship_to` varchar(30) DEFAULT NULL,
  `inventory_org` varchar(30) DEFAULT NULL,
  `sub_inventory` text DEFAULT NULL,
  `order_status` varchar(30) DEFAULT NULL,
  `order_category` varchar(30) DEFAULT NULL,
  `receiver_city` text DEFAULT NULL,
  `item_division` varchar(30) DEFAULT NULL,
  `product_level1_name` varchar(100) DEFAULT NULL,
  `product_level2_name` varchar(100) DEFAULT NULL,
  `product_level3_name` varchar(100) DEFAULT NULL,
  `product_level4_name` varchar(100) DEFAULT NULL,
  `product_level4_code` varchar(100) DEFAULT NULL,
  `model_category` varchar(30) DEFAULT NULL,
  `item_type_desctiption` varchar(30) DEFAULT NULL,
  `create_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `lgepr_sales_order`
--
ALTER TABLE `lgepr_sales_order`
  ADD PRIMARY KEY (`sales_order_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `lgepr_sales_order`
--
ALTER TABLE `lgepr_sales_order`
  MODIFY `sales_order_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
