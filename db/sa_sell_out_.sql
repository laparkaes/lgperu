-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 24-08-09 00:06
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
-- 테이블 구조 `sa_sell_out_`
--

CREATE TABLE `sa_sell_out_` (
  `sell_out_id` int(11) NOT NULL,
  `customer` varchar(20) DEFAULT NULL,
  `acct_gtm` varchar(150) DEFAULT NULL,
  `customer_model` varchar(50) DEFAULT NULL,
  `model_suffix_code` varchar(50) DEFAULT NULL,
  `txn_date` date DEFAULT NULL,
  `cust_store_code` int(50) DEFAULT NULL,
  `cust_store_name` varchar(100) DEFAULT NULL,
  `sellout_unit` float DEFAULT NULL,
  `sellout_amt` float DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `ticket` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 덤프된 테이블의 인덱스
--

--
-- 테이블의 인덱스 `sa_sell_out_`
--
ALTER TABLE `sa_sell_out_`
  ADD PRIMARY KEY (`sell_out_id`);

--
-- 덤프된 테이블의 AUTO_INCREMENT
--

--
-- 테이블의 AUTO_INCREMENT `sa_sell_out_`
--
ALTER TABLE `sa_sell_out_`
  MODIFY `sell_out_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
