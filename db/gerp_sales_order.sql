-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 24-08-11 20:00
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
-- 테이블 구조 `gerp_sales_order`
--

CREATE TABLE `gerp_sales_order` (
  `sales_order_id` int(11) NOT NULL,
  `bill_to_name` varchar(30) DEFAULT NULL,
  `ship_to_name` varchar(30) DEFAULT NULL,
  `model` varchar(30) DEFAULT NULL,
  `order_no` varchar(30) DEFAULT NULL,
  `line_no` varchar(30) DEFAULT NULL,
  `order_type` varchar(30) DEFAULT NULL,
  `line_status` varchar(30) DEFAULT NULL,
  `hold_flag` char(1) DEFAULT NULL,
  `ready_to_pick` char(1) DEFAULT NULL,
  `pick_released` char(1) DEFAULT NULL,
  `instock_flag` char(1) DEFAULT NULL,
  `ordered_qty` int(11) DEFAULT NULL,
  `unit_selling_price` float DEFAULT NULL,
  `sales_amount` float DEFAULT NULL,
  `tax_amount` float DEFAULT NULL,
  `charge_amount` float DEFAULT NULL,
  `line_total` float DEFAULT NULL,
  `list_price` float DEFAULT NULL,
  `original_list_price` float DEFAULT NULL,
  `dc_rate` float DEFAULT NULL,
  `currency` varchar(30) DEFAULT NULL,
  `dfi_applicable` char(1) DEFAULT NULL,
  `aai_applicable` char(1) DEFAULT NULL,
  `cancel_qty` int(11) DEFAULT NULL,
  `booked_date` date DEFAULT NULL,
  `scheduled_cancel_date` date DEFAULT NULL,
  `expire_date` date DEFAULT NULL,
  `req_arrival_date_from` date DEFAULT NULL,
  `req_arrival_date_to` date DEFAULT NULL,
  `req_ship_date` date DEFAULT NULL,
  `shipment_date` date DEFAULT NULL,
  `close_date` date DEFAULT NULL,
  `line_type` varchar(30) DEFAULT NULL,
  `customer_name` varchar(30) DEFAULT NULL,
  `bill_to` varchar(30) DEFAULT NULL,
  `customer_department` varchar(30) DEFAULT NULL,
  `ship_to` varchar(30) DEFAULT NULL,
  `store_no` varchar(30) DEFAULT NULL,
  `price_condition` varchar(30) DEFAULT NULL,
  `payment_term` varchar(30) DEFAULT NULL,
  `customer_po_no` varchar(30) DEFAULT NULL,
  `customer_po_date` date DEFAULT NULL,
  `invoice_no` varchar(30) DEFAULT NULL,
  `invoice_line_no` int(11) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `sales_person` varchar(100) DEFAULT NULL,
  `pricing_group` char(1) DEFAULT NULL,
  `buying_group` char(1) DEFAULT NULL,
  `inventory_org` varchar(30) DEFAULT NULL,
  `sub_inventory` varchar(30) DEFAULT NULL,
  `shipping_method` varchar(100) DEFAULT NULL,
  `shipment_priority` varchar(30) DEFAULT NULL,
  `order_source` varchar(30) DEFAULT NULL,
  `order_status` varchar(30) DEFAULT NULL,
  `order_category` varchar(30) DEFAULT NULL,
  `quote_date` date DEFAULT NULL,
  `quote_expire_date` date DEFAULT NULL,
  `project_code` varchar(30) DEFAULT NULL,
  `comm_submission_no` varchar(30) DEFAULT NULL,
  `plp_submission_no` varchar(30) DEFAULT NULL,
  `bpm_request_no` varchar(30) DEFAULT NULL,
  `consumer_name` varchar(100) DEFAULT NULL,
  `receiver_name` varchar(100) DEFAULT NULL,
  `consumer_phone_no` varchar(30) DEFAULT NULL,
  `consumermobile_no` varchar(30) DEFAULT NULL,
  `receiver_phone_no` varchar(30) DEFAULT NULL,
  `receiver_mobile_no` varchar(30) DEFAULT NULL,
  `receiver_address1` text DEFAULT NULL,
  `receiver_address2` text DEFAULT NULL,
  `receiver_address3` text DEFAULT NULL,
  `receiver_city` varchar(250) DEFAULT NULL,
  `receiver_city_desc` text DEFAULT NULL,
  `receiver_county` varchar(30) DEFAULT NULL,
  `receiver_postal_code` varchar(30) DEFAULT NULL,
  `receiver_state` varchar(30) DEFAULT NULL,
  `receiver_province` varchar(30) DEFAULT NULL,
  `receiver_country` varchar(30) DEFAULT NULL,
  `item_division` varchar(30) DEFAULT NULL,
  `product_level1_name` varchar(100) DEFAULT NULL,
  `product_level2_name` varchar(100) DEFAULT NULL,
  `product_level3_name` varchar(100) DEFAULT NULL,
  `product_level4_name` varchar(100) DEFAULT NULL,
  `product_level4_code` varchar(100) DEFAULT NULL,
  `model_category` varchar(30) DEFAULT NULL,
  `item_type_desctiption` varchar(30) DEFAULT NULL,
  `item_weight` float DEFAULT NULL,
  `item_cbm` float DEFAULT NULL,
  `sales_channel_high` varchar(250) DEFAULT NULL,
  `sales_channel_low` varchar(30) DEFAULT NULL,
  `ship_group` char(1) DEFAULT NULL,
  `back_order_hold` char(1) DEFAULT NULL,
  `credit_hold` char(1) DEFAULT NULL,
  `overdue_hold` char(1) DEFAULT NULL,
  `customer_hold` char(1) DEFAULT NULL,
  `payterm_term_hold` char(1) DEFAULT NULL,
  `fp_hold` char(1) DEFAULT NULL,
  `minimum_hold` char(1) DEFAULT NULL,
  `future_hold` char(1) DEFAULT NULL,
  `reserve_hold` char(1) DEFAULT NULL,
  `manual_hold` char(1) DEFAULT NULL,
  `auto_pending_hold` char(1) DEFAULT NULL,
  `sa_hold` char(1) DEFAULT NULL,
  `form_hold` char(1) DEFAULT NULL,
  `bank_collateral_hold` char(1) DEFAULT NULL,
  `insurance_hold` char(1) DEFAULT NULL,
  `partial_flag` char(1) DEFAULT NULL,
  `load_hold_flag` char(1) DEFAULT NULL,
  `inventory_reserved` char(1) DEFAULT NULL,
  `pick_release_qty` int(11) DEFAULT NULL,
  `long_multi_flag` char(1) DEFAULT NULL,
  `so_sa_mapping` char(1) DEFAULT NULL,
  `picking_remark` text DEFAULT NULL,
  `shipping_remark` text DEFAULT NULL,
  `create_employee_name` varchar(100) DEFAULT NULL,
  `create_date` date DEFAULT NULL,
  `dls_interface` tinyint(1) DEFAULT NULL,
  `edi_customer_remark` tinyint(1) DEFAULT NULL,
  `sales_recognition_method` varchar(30) DEFAULT NULL,
  `billing_type` varchar(30) DEFAULT NULL,
  `lt_day` int(11) DEFAULT NULL,
  `carrier_code` varchar(30) DEFAULT NULL,
  `delivery_number` varchar(30) DEFAULT NULL,
  `manifest_grn_no` tinyint(1) DEFAULT NULL,
  `warehouse_job_no` tinyint(1) DEFAULT NULL,
  `customer_rad` timestamp NULL DEFAULT NULL,
  `others_out_reason` tinyint(1) DEFAULT NULL,
  `ship_set_name` tinyint(1) DEFAULT NULL,
  `promising_txn_status` tinyint(1) DEFAULT NULL,
  `promised_mad` tinyint(1) DEFAULT NULL,
  `promised_arrival_date` tinyint(1) DEFAULT NULL,
  `promised_ship_date` tinyint(1) DEFAULT NULL,
  `initial_promised_arrival_date` tinyint(1) DEFAULT NULL,
  `accounting_unit` varchar(30) DEFAULT NULL,
  `acd_original_warehouse` tinyint(1) DEFAULT NULL,
  `acd_original_wh_type` tinyint(1) DEFAULT NULL,
  `cnjp` varchar(30) DEFAULT NULL,
  `nota_no` varchar(30) DEFAULT NULL,
  `nota_date` date DEFAULT NULL,
  `so_status2` varchar(30) DEFAULT NULL,
  `sbp_tax_include` float DEFAULT NULL,
  `sbp_tax_exclude` float DEFAULT NULL,
  `rrp_tax_include` float DEFAULT NULL,
  `rrp_tax_exclude` float DEFAULT NULL,
  `so_fap_flag` char(1) DEFAULT NULL,
  `so_fap_slot_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 덤프된 테이블의 인덱스
--

--
-- 테이블의 인덱스 `gerp_sales_order`
--
ALTER TABLE `gerp_sales_order`
  ADD PRIMARY KEY (`sales_order_id`);

--
-- 덤프된 테이블의 AUTO_INCREMENT
--

--
-- 테이블의 AUTO_INCREMENT `gerp_sales_order`
--
ALTER TABLE `gerp_sales_order`
  MODIFY `sales_order_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
