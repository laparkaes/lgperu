-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 25-10-05 19:55
-- 서버 버전: 10.4.24-MariaDB
-- PHP 버전: 7.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 데이터베이스: `llamasys`
--

-- --------------------------------------------------------

--
-- 테이블 구조 `lgepr_order`
--

CREATE TABLE `lgepr_order` (
  `order_line` varchar(30) NOT NULL DEFAULT current_timestamp(),
  `dash_company` varchar(30) DEFAULT NULL,
  `dash_division` varchar(30) DEFAULT NULL,
  `category` varchar(10) DEFAULT NULL,
  `order_category` varchar(10) DEFAULT NULL,
  `department` varchar(10) DEFAULT NULL,
  `order_no` varchar(15) DEFAULT NULL,
  `line_no` varchar(10) DEFAULT NULL,
  `so_status` varchar(20) DEFAULT NULL,
  `order_status` varchar(30) DEFAULT NULL,
  `line_status` varchar(30) DEFAULT NULL,
  `original_list_pirce` float DEFAULT NULL,
  `unit_list__price` float DEFAULT NULL,
  `unit_selling__price` float DEFAULT NULL,
  `order_amount` float DEFAULT NULL,
  `order_amount_usd` float DEFAULT NULL,
  `tax_amount` float DEFAULT NULL,
  `charge_amount` float DEFAULT NULL,
  `total_amount` float DEFAULT NULL,
  `total_amount_usd` float DEFAULT NULL,
  `dc_rate` varchar(5) DEFAULT NULL,
  `currency` varchar(5) DEFAULT NULL,
  `delivery_number` varchar(20) DEFAULT NULL,
  `invoice_no` varchar(20) DEFAULT NULL,
  `customer_po_date` date DEFAULT NULL,
  `create_date` date DEFAULT NULL,
  `booked_date` date DEFAULT NULL,
  `order_date` date DEFAULT NULL,
  `customer_rad` timestamp NULL DEFAULT NULL,
  `req_arrival_date_from` date DEFAULT NULL,
  `req_arrival_date_to` date DEFAULT NULL,
  `appointment_date` date DEFAULT NULL,
  `req_ship_date` date DEFAULT NULL,
  `shipment_date` date DEFAULT NULL,
  `closed_date` date DEFAULT NULL,
  `inventory_org` varchar(5) DEFAULT NULL,
  `sub__inventory` varchar(20) DEFAULT NULL,
  `order_type` varchar(50) DEFAULT NULL,
  `line_type` varchar(50) DEFAULT NULL,
  `bill_to_code` varchar(20) DEFAULT NULL,
  `bill_to_name` varchar(100) DEFAULT NULL,
  `ship_to_code` varchar(20) DEFAULT NULL,
  `ship_to_name` varchar(100) DEFAULT NULL,
  `order_qty` int(11) DEFAULT NULL,
  `cancel_qty` int(11) DEFAULT NULL,
  `item_cbm` float DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `item_division` varchar(5) DEFAULT NULL,
  `model_category` varchar(5) DEFAULT NULL,
  `product_level1_name` varchar(50) DEFAULT NULL,
  `product_level2_name` varchar(50) DEFAULT NULL,
  `product_level3_name` varchar(50) DEFAULT NULL,
  `product_level4_name` varchar(50) DEFAULT NULL,
  `product_level4` varchar(10) DEFAULT NULL,
  `instock_flag` char(1) DEFAULT NULL,
  `inventory_reserved` char(1) DEFAULT NULL,
  `partial_flag` char(1) DEFAULT NULL,
  `pick_released` char(1) DEFAULT NULL,
  `ready_to_pick` char(1) DEFAULT NULL,
  `so_sa_mapping` char(1) DEFAULT NULL,
  `hold_flag` char(1) DEFAULT NULL,
  `credit_hold` char(1) DEFAULT NULL,
  `back_order_hold` char(1) DEFAULT NULL,
  `overdue_hold` char(1) DEFAULT NULL,
  `customer_hold` char(1) DEFAULT NULL,
  `manual_hold` char(1) DEFAULT NULL,
  `auto_pending_hold` char(1) DEFAULT NULL,
  `bank_collateral_hold` char(1) DEFAULT NULL,
  `form_hold` char(1) DEFAULT NULL,
  `fp_hold` char(1) DEFAULT NULL,
  `future_hold` char(1) DEFAULT NULL,
  `insurance_hold` char(1) DEFAULT NULL,
  `minimum_hold` char(1) DEFAULT NULL,
  `payterm_term_hold` char(1) DEFAULT NULL,
  `pick_cancel_manual_hold` char(1) DEFAULT NULL,
  `reserve_hold` char(1) DEFAULT NULL,
  `sa_hold` char(1) DEFAULT NULL,
  `accounting_unit` varchar(5) DEFAULT NULL,
  `book_currency` varchar(5) DEFAULT NULL,
  `carrier_code` varchar(5) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_po_no` varchar(30) DEFAULT NULL,
  `install_type` varchar(10) DEFAULT NULL,
  `interest_amt` float DEFAULT NULL,
  `item_type_desctiption` varchar(30) DEFAULT NULL,
  `item_weight` float DEFAULT NULL,
  `order_source` varchar(30) DEFAULT NULL,
  `payment_term` varchar(20) DEFAULT NULL,
  `pick_release_qty` int(11) DEFAULT NULL,
  `pricing_group` varchar(20) DEFAULT NULL,
  `project_code` varchar(30) DEFAULT NULL,
  `sales_channel` varchar(100) DEFAULT NULL,
  `sales_person` varchar(30) DEFAULT NULL,
  `ship_to_city` varchar(30) DEFAULT NULL,
  `shipping_method` varchar(30) DEFAULT NULL,
  `price_condition` varchar(5) DEFAULT NULL,
  `sales_updated_at` timestamp NULL DEFAULT NULL,
  `closed_updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 덤프된 테이블의 인덱스
--

--
-- 테이블의 인덱스 `lgepr_order`
--
ALTER TABLE `lgepr_order`
  ADD PRIMARY KEY (`order_line`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
