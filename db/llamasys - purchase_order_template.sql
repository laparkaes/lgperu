-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 24-06-10 18:26
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
-- 테이블 구조 `purchase_order_template`
--

CREATE TABLE `purchase_order_template` (
  `template_id` int(11) NOT NULL,
  `template` varchar(50) NOT NULL,
  `code` varchar(20) NOT NULL,
  `filename` varchar(50) DEFAULT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- 테이블의 덤프 데이터 `purchase_order_template`
--

INSERT INTO `purchase_order_template` (`template_id`, `template`, `code`, `filename`, `valid`) VALUES
(1, 'Hiraoka Pre-distubution (PDF)', 'hiraoka_pre', NULL, 1),
(2, 'Hiraoka SKU (PDF)', 'hiraoka_sku', NULL, 1),
(3, 'Conecta (Excel)', 'conecta_excel', NULL, 1),
(4, 'Sodimac (PDF)', 'sodimac', NULL, 1),
(5, 'Estilos SKU (PDF)', 'estilos_sku', NULL, 1),
(6, 'Chancafe (PDF)', 'chancafe', NULL, 1);

--
-- 덤프된 테이블의 인덱스
--

--
-- 테이블의 인덱스 `purchase_order_template`
--
ALTER TABLE `purchase_order_template`
  ADD PRIMARY KEY (`template_id`);

--
-- 덤프된 테이블의 AUTO_INCREMENT
--

--
-- 테이블의 AUTO_INCREMENT `purchase_order_template`
--
ALTER TABLE `purchase_order_template`
  MODIFY `template_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
