-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 24-05-31 00:15
-- 서버 버전: 10.4.27-MariaDB
-- PHP 버전: 7.4.33

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
-- 테이블 구조 `dash_closed_order_inquiry`
--

CREATE TABLE `dash_closed_order_inquiry` (
  `order_id` int(11) NOT NULL,
  `test_int` int(11) NOT NULL,
  `test_float` float NOT NULL,
  `test_varchar` varchar(250) NOT NULL,
  `test_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- 덤프된 테이블의 인덱스
--

--
-- 테이블의 인덱스 `dash_closed_order_inquiry`
--
ALTER TABLE `dash_closed_order_inquiry`
  ADD PRIMARY KEY (`order_id`);

--
-- 덤프된 테이블의 AUTO_INCREMENT
--

--
-- 테이블의 AUTO_INCREMENT `dash_closed_order_inquiry`
--
ALTER TABLE `dash_closed_order_inquiry`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
