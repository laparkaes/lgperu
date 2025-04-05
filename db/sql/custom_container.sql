-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 25-04-05 17:16
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
-- 테이블 구조 `custom_container`
--

CREATE TABLE `custom_container` (
  `container_id` int(11) NOT NULL,
  `sa_no` int(11) DEFAULT NULL,
  `sa_line_no` varchar(10) DEFAULT NULL,
  `container` varchar(30) DEFAULT NULL,
  `organization` varchar(5) DEFAULT NULL,
  `sub_inventory` varchar(20) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `cbm` float DEFAULT NULL,
  `weight` float DEFAULT NULL,
  `eta` date DEFAULT NULL,
  `ata` date DEFAULT NULL,
  `picked_up` date DEFAULT NULL,
  `3pl_arrival` date DEFAULT NULL,
  `returned` date DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 덤프된 테이블의 인덱스
--

--
-- 테이블의 인덱스 `custom_container`
--
ALTER TABLE `custom_container`
  ADD PRIMARY KEY (`container_id`);

--
-- 덤프된 테이블의 AUTO_INCREMENT
--

--
-- 테이블의 AUTO_INCREMENT `custom_container`
--
ALTER TABLE `custom_container`
  MODIFY `container_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
