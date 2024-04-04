-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 24-04-03 23:55
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
-- 테이블 구조 `purchase_order_pdf`
--

CREATE TABLE `purchase_order_pdf` (
  `pdf_id` int(11) NOT NULL,
  `pdf` varchar(50) NOT NULL,
  `code` varchar(20) NOT NULL,
  `filename` varchar(50) DEFAULT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 테이블의 덤프 데이터 `purchase_order_pdf`
--

INSERT INTO `purchase_order_pdf` (`pdf_id`, `pdf`, `code`, `filename`, `valid`) VALUES
(1, 'Hiraoka', 'hiraoka_original', '', 1);

--
-- 덤프된 테이블의 인덱스
--

--
-- 테이블의 인덱스 `purchase_order_pdf`
--
ALTER TABLE `purchase_order_pdf`
  ADD PRIMARY KEY (`pdf_id`);

--
-- 덤프된 테이블의 AUTO_INCREMENT
--

--
-- 테이블의 AUTO_INCREMENT `purchase_order_pdf`
--
ALTER TABLE `purchase_order_pdf`
  MODIFY `pdf_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
