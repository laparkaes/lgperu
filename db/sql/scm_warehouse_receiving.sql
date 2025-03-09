-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 25-03-08 20:45
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
-- 테이블 구조 `scm_warehouse_receiving`
--

CREATE TABLE `scm_warehouse_receiving` (
  `receiving_id` int(11) NOT NULL,
  `source_header_no` int(11) DEFAULT NULL,
  `source_line_no` int(11) DEFAULT NULL,
  `transaction_date` timestamp NULL DEFAULT NULL,
  `transfer_date` timestamp NULL DEFAULT NULL,
  `step_code` varchar(10) DEFAULT NULL,
  `source_type_code` int(11) DEFAULT NULL,
  `container_no` varchar(30) DEFAULT NULL,
  `organization_code` varchar(10) DEFAULT NULL,
  `subinventory_code` varchar(30) DEFAULT NULL,
  `item_code` varchar(50) DEFAULT NULL,
  `order_qty` int(11) DEFAULT NULL,
  `transfer_flag` char(1) DEFAULT NULL,
  `error_message_text` text DEFAULT NULL,
  `cancel_flag` char(1) DEFAULT NULL,
  `transaction_date_3pl` timestamp NULL DEFAULT NULL,
  `receipt_qty` int(11) DEFAULT NULL,
  `shipping_qty` int(11) DEFAULT NULL,
  `good_set_qty` int(11) DEFAULT NULL,
  `unit_damage_qty` int(11) DEFAULT NULL,
  `box_damage_qty` int(11) DEFAULT NULL,
  `wrong_model_qty` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 덤프된 테이블의 인덱스
--

--
-- 테이블의 인덱스 `scm_warehouse_receiving`
--
ALTER TABLE `scm_warehouse_receiving`
  ADD PRIMARY KEY (`receiving_id`);

--
-- 덤프된 테이블의 AUTO_INCREMENT
--

--
-- 테이블의 AUTO_INCREMENT `scm_warehouse_receiving`
--
ALTER TABLE `scm_warehouse_receiving`
  MODIFY `receiving_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
