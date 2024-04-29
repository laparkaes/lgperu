-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 24-04-28 23:56
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
-- 테이블 구조 `ar_aging`
--

CREATE TABLE `ar_aging` (
  `id_aging` int(11) NOT NULL,
  `cus_num` varchar(100) NOT NULL,
  `cus_h_name` varchar(200) NOT NULL,
  `ar_class` varchar(50) NOT NULL,
  `payterm` varchar(200) NOT NULL,
  `currency` varchar(5) NOT NULL,
  `balance` decimal(10,0) NOT NULL,
  `aging_day` int(11) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `enter_time` time NOT NULL,
  `leave_time` time NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `currency`
--

CREATE TABLE `currency` (
  `currency_id` int(11) NOT NULL,
  `currency` varchar(5) NOT NULL,
  `symbol` varchar(5) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `customer`
--

CREATE TABLE `customer` (
  `customer_id` int(11) NOT NULL,
  `subsidiary_id` int(11) DEFAULT NULL,
  `customer` varchar(250) NOT NULL,
  `bill_to_code` varchar(20) NOT NULL,
  `registered` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `customer_ship_to`
--

CREATE TABLE `customer_ship_to` (
  `ship_to_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `ship_to_code` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `department`
--

CREATE TABLE `department` (
  `department_id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `department` varchar(50) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `employee`
--

CREATE TABLE `employee` (
  `employee_id` int(11) NOT NULL,
  `location_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `employee_number` varchar(20) NOT NULL,
  `ep_mail` varchar(50) DEFAULT NULL,
  `password` varchar(250) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `is_supervised` tinyint(1) DEFAULT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `inventory` varchar(50) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `invoice`
--

CREATE TABLE `invoice` (
  `invoice_id` int(11) NOT NULL,
  `invoice` varchar(50) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `location`
--

CREATE TABLE `location` (
  `location_id` int(11) NOT NULL,
  `location` varchar(15) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `order_`
--

CREATE TABLE `order_` (
  `order_id` int(11) NOT NULL,
  `subsidiary_id` int(11) DEFAULT NULL,
  `status_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `sales_channel_id` int(11) NOT NULL,
  `sales_person_id` int(11) NOT NULL,
  `payment_term_id` int(11) NOT NULL,
  `order_category_id` int(11) NOT NULL,
  `currency_id` int(11) NOT NULL,
  `order_no` varchar(50) NOT NULL,
  `order_date` date DEFAULT NULL,
  `customer_po_no` varchar(50) DEFAULT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1,
  `updated` timestamp NULL DEFAULT NULL,
  `registered` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `order_category`
--

CREATE TABLE `order_category` (
  `category_id` int(11) NOT NULL,
  `category` varchar(30) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `order_item`
--

CREATE TABLE `order_item` (
  `item_id` int(11) NOT NULL,
  `subsidiary_id` int(11) DEFAULT NULL,
  `order_id` int(11) NOT NULL,
  `order_status_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `ship_to_id` int(11) NOT NULL,
  `division_id` int(11) NOT NULL,
  `product_l1_line_id` int(11) NOT NULL,
  `product_l2_line_id` int(11) NOT NULL,
  `product_l3_line_id` int(11) NOT NULL,
  `product_l4_line_id` int(11) NOT NULL,
  `product_category_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `inventory_id` int(11) DEFAULT NULL,
  `sub_inventory_id` int(11) DEFAULT NULL,
  `currency_id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `line_no` varchar(20) NOT NULL,
  `shipment_date` date DEFAULT NULL,
  `closed_date` date DEFAULT NULL,
  `order_qty` int(11) NOT NULL,
  `unit_list_price` decimal(10,2) NOT NULL,
  `unit_selling_price` decimal(10,2) NOT NULL,
  `total_amount_pen` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_amount_pen` decimal(10,2) NOT NULL,
  `order_amount` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) NOT NULL,
  `dc_amount` decimal(10,2) NOT NULL,
  `dc_rate` decimal(10,2) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1,
  `updated` timestamp NULL DEFAULT NULL,
  `registered` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `order_itme_type`
--

CREATE TABLE `order_itme_type` (
  `type_id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `order_status`
--

CREATE TABLE `order_status` (
  `status_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `organization`
--

CREATE TABLE `organization` (
  `organization_id` int(11) NOT NULL,
  `subsidiary_id` int(11) NOT NULL,
  `organization` varchar(50) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `payment_term`
--

CREATE TABLE `payment_term` (
  `term_id` int(11) NOT NULL,
  `term` varchar(20) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `line_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `model` varchar(250) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `product_category`
--

CREATE TABLE `product_category` (
  `category_id` int(11) NOT NULL,
  `category` varchar(20) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `product_line`
--

CREATE TABLE `product_line` (
  `line_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT -1,
  `level` int(11) NOT NULL,
  `line` varchar(200) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `product_sku`
--

CREATE TABLE `product_sku` (
  `sku_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `purchase_order_template`
--

CREATE TABLE `purchase_order_template` (
  `template_id` int(11) NOT NULL,
  `template` varchar(50) NOT NULL,
  `code` varchar(20) NOT NULL,
  `filename` varchar(50) DEFAULT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `sales_channel`
--

CREATE TABLE `sales_channel` (
  `channel_id` int(11) NOT NULL,
  `channel` varchar(50) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `sales_person`
--

CREATE TABLE `sales_person` (
  `person_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `sell_in`
--

CREATE TABLE `sell_in` (
  `sell_in_id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `currency_id` int(11) NOT NULL,
  `closed_date` date NOT NULL,
  `order_qty` int(11) NOT NULL,
  `unit_selling_price` double NOT NULL,
  `order_amount` double NOT NULL,
  `order_amount_pen` double NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1,
  `registered` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `sell_out`
--

CREATE TABLE `sell_out` (
  `sell_out_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `channel_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `qty` int(11) NOT NULL,
  `amount` double NOT NULL,
  `stock` int(11) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `sell_out_channel`
--

CREATE TABLE `sell_out_channel` (
  `channel_id` int(11) NOT NULL,
  `channel` varchar(50) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `subsidiary`
--

CREATE TABLE `subsidiary` (
  `subsidiary_id` int(11) NOT NULL,
  `subsidiary` varchar(100) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `vacation`
--

CREATE TABLE `vacation` (
  `vacation_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `day_count` float NOT NULL,
  `register` timestamp NOT NULL DEFAULT current_timestamp(),
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `vacation_type`
--

CREATE TABLE `vacation_type` (
  `type_id` int(11) NOT NULL,
  `type` varchar(15) NOT NULL,
  `valid` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `working_hour`
--

CREATE TABLE `working_hour` (
  `working_hour_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `wh_option_id` int(11) NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `register` timestamp NOT NULL DEFAULT current_timestamp(),
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `working_hour_option`
--

CREATE TABLE `working_hour_option` (
  `option_id` int(11) NOT NULL,
  `entrance_time` time NOT NULL,
  `exit_time` time NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 덤프된 테이블의 인덱스
--

--
-- 테이블의 인덱스 `ar_aging`
--
ALTER TABLE `ar_aging`
  ADD PRIMARY KEY (`id_aging`);

--
-- 테이블의 인덱스 `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`);

--
-- 테이블의 인덱스 `currency`
--
ALTER TABLE `currency`
  ADD PRIMARY KEY (`currency_id`);

--
-- 테이블의 인덱스 `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_id`);

--
-- 테이블의 인덱스 `customer_ship_to`
--
ALTER TABLE `customer_ship_to`
  ADD PRIMARY KEY (`ship_to_id`);

--
-- 테이블의 인덱스 `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`department_id`);

--
-- 테이블의 인덱스 `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`employee_id`);

--
-- 테이블의 인덱스 `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`);

--
-- 테이블의 인덱스 `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`invoice_id`);

--
-- 테이블의 인덱스 `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`location_id`);

--
-- 테이블의 인덱스 `order_`
--
ALTER TABLE `order_`
  ADD PRIMARY KEY (`order_id`);

--
-- 테이블의 인덱스 `order_category`
--
ALTER TABLE `order_category`
  ADD PRIMARY KEY (`category_id`);

--
-- 테이블의 인덱스 `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`item_id`);

--
-- 테이블의 인덱스 `order_itme_type`
--
ALTER TABLE `order_itme_type`
  ADD PRIMARY KEY (`type_id`);

--
-- 테이블의 인덱스 `order_status`
--
ALTER TABLE `order_status`
  ADD PRIMARY KEY (`status_id`);

--
-- 테이블의 인덱스 `organization`
--
ALTER TABLE `organization`
  ADD PRIMARY KEY (`organization_id`),
  ADD KEY `fk_org_sub` (`subsidiary_id`);

--
-- 테이블의 인덱스 `payment_term`
--
ALTER TABLE `payment_term`
  ADD PRIMARY KEY (`term_id`);

--
-- 테이블의 인덱스 `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`);

--
-- 테이블의 인덱스 `product_category`
--
ALTER TABLE `product_category`
  ADD PRIMARY KEY (`category_id`);

--
-- 테이블의 인덱스 `product_line`
--
ALTER TABLE `product_line`
  ADD PRIMARY KEY (`line_id`);

--
-- 테이블의 인덱스 `product_sku`
--
ALTER TABLE `product_sku`
  ADD PRIMARY KEY (`sku_id`);

--
-- 테이블의 인덱스 `purchase_order_template`
--
ALTER TABLE `purchase_order_template`
  ADD PRIMARY KEY (`template_id`);

--
-- 테이블의 인덱스 `sales_channel`
--
ALTER TABLE `sales_channel`
  ADD PRIMARY KEY (`channel_id`);

--
-- 테이블의 인덱스 `sales_person`
--
ALTER TABLE `sales_person`
  ADD PRIMARY KEY (`person_id`);

--
-- 테이블의 인덱스 `sell_in`
--
ALTER TABLE `sell_in`
  ADD PRIMARY KEY (`sell_in_id`);

--
-- 테이블의 인덱스 `sell_out`
--
ALTER TABLE `sell_out`
  ADD PRIMARY KEY (`sell_out_id`);

--
-- 테이블의 인덱스 `sell_out_channel`
--
ALTER TABLE `sell_out_channel`
  ADD PRIMARY KEY (`channel_id`);

--
-- 테이블의 인덱스 `subsidiary`
--
ALTER TABLE `subsidiary`
  ADD PRIMARY KEY (`subsidiary_id`);

--
-- 테이블의 인덱스 `vacation`
--
ALTER TABLE `vacation`
  ADD PRIMARY KEY (`vacation_id`);

--
-- 테이블의 인덱스 `vacation_type`
--
ALTER TABLE `vacation_type`
  ADD PRIMARY KEY (`type_id`);

--
-- 테이블의 인덱스 `working_hour`
--
ALTER TABLE `working_hour`
  ADD PRIMARY KEY (`working_hour_id`);

--
-- 테이블의 인덱스 `working_hour_option`
--
ALTER TABLE `working_hour_option`
  ADD PRIMARY KEY (`option_id`);

--
-- 덤프된 테이블의 AUTO_INCREMENT
--

--
-- 테이블의 AUTO_INCREMENT `ar_aging`
--
ALTER TABLE `ar_aging`
  MODIFY `id_aging` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `currency`
--
ALTER TABLE `currency`
  MODIFY `currency_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `customer_ship_to`
--
ALTER TABLE `customer_ship_to`
  MODIFY `ship_to_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `department`
--
ALTER TABLE `department`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `employee`
--
ALTER TABLE `employee`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `invoice`
--
ALTER TABLE `invoice`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `location`
--
ALTER TABLE `location`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `order_`
--
ALTER TABLE `order_`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `order_category`
--
ALTER TABLE `order_category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `order_item`
--
ALTER TABLE `order_item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `order_itme_type`
--
ALTER TABLE `order_itme_type`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `order_status`
--
ALTER TABLE `order_status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `organization`
--
ALTER TABLE `organization`
  MODIFY `organization_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `payment_term`
--
ALTER TABLE `payment_term`
  MODIFY `term_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `product_category`
--
ALTER TABLE `product_category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `product_line`
--
ALTER TABLE `product_line`
  MODIFY `line_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `product_sku`
--
ALTER TABLE `product_sku`
  MODIFY `sku_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `purchase_order_template`
--
ALTER TABLE `purchase_order_template`
  MODIFY `template_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `sales_channel`
--
ALTER TABLE `sales_channel`
  MODIFY `channel_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `sales_person`
--
ALTER TABLE `sales_person`
  MODIFY `person_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `sell_in`
--
ALTER TABLE `sell_in`
  MODIFY `sell_in_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `sell_out`
--
ALTER TABLE `sell_out`
  MODIFY `sell_out_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `sell_out_channel`
--
ALTER TABLE `sell_out_channel`
  MODIFY `channel_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `subsidiary`
--
ALTER TABLE `subsidiary`
  MODIFY `subsidiary_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `vacation`
--
ALTER TABLE `vacation`
  MODIFY `vacation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `vacation_type`
--
ALTER TABLE `vacation_type`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `working_hour`
--
ALTER TABLE `working_hour`
  MODIFY `working_hour_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `working_hour_option`
--
ALTER TABLE `working_hour_option`
  MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 덤프된 테이블의 제약사항
--

--
-- 테이블의 제약사항 `organization`
--
ALTER TABLE `organization`
  ADD CONSTRAINT `fk_org_sub` FOREIGN KEY (`subsidiary_id`) REFERENCES `subsidiary` (`subsidiary_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
