-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 25-03-10 00:02
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
-- 테이블 구조 `scm_warehouse_shipment_advice`
--

CREATE TABLE `scm_warehouse_shipment_advice` (
  `advice_id` int(11) NOT NULL,
  `supply_type_origin` varchar(10) DEFAULT NULL,
  `organization_cd` varchar(10) DEFAULT NULL,
  `subinventory_code_origin` varchar(50) DEFAULT NULL,
  `sa_no` int(11) NOT NULL,
  `sa_line_no` int(11) NOT NULL,
  `quantity_origin` int(11) DEFAULT NULL,
  `ftv_etw` timestamp NULL DEFAULT NULL,
  `eta_bl` timestamp NULL DEFAULT NULL,
  `updated_etw` timestamp NULL DEFAULT NULL,
  `po_rsd` timestamp NULL DEFAULT NULL,
  `shipped_date` timestamp NULL DEFAULT NULL,
  `shipment_from_supplier_code` varchar(30) DEFAULT NULL,
  `final_customer_code` varchar(30) DEFAULT NULL,
  `cdc_org_id` varchar(30) DEFAULT NULL,
  `container_num` varchar(10) DEFAULT NULL,
  `po_no` int(11) DEFAULT NULL,
  `po_line_no` int(11) DEFAULT NULL,
  `house_bl_no` varchar(30) DEFAULT NULL,
  `route` varchar(30) DEFAULT NULL,
  `shipment_method_code` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 덤프된 테이블의 인덱스
--

--
-- 테이블의 인덱스 `scm_warehouse_shipment_advice`
--
ALTER TABLE `scm_warehouse_shipment_advice`
  ADD PRIMARY KEY (`advice_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
