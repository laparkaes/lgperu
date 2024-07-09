-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 24-07-08 23:35
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
-- 테이블 구조 `scm_purchase_order_template`
--

CREATE TABLE `scm_purchase_order_template` (
  `template_id` int(11) NOT NULL,
  `template` varchar(50) NOT NULL,
  `customer_word` varchar(50) DEFAULT NULL,
  `code` varchar(20) NOT NULL,
  `filename` varchar(50) DEFAULT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 테이블의 덤프 데이터 `scm_purchase_order_template`
--

INSERT INTO `scm_purchase_order_template` (`template_id`, `template`, `customer_word`, `code`, `filename`, `valid`) VALUES
(1, 'Hiraoka Pre-distubution (PDF)', 'hiraoka', 'hiraoka_pre', 'po_hiraoka_dist.png', 1),
(2, 'Hiraoka SKU (PDF)', 'hiraoka', 'hiraoka_sku', 'po_hiraoka.png', 1),
(3, 'Conecta (Excel)', 'conecta', 'conecta_excel', 'po_conecta_excel.png', 1),
(4, 'Sodimac - Maestro (PDF)', 'sodimac', 'sodimac_maestro', 'po_sodimac_maestro.png', 1),
(5, 'Estilos SKU (PDF)', 'estilos', 'estilos_sku', 'po_estilos.png', 1),
(6, 'Chancafe (PDF)', 'chancafe', 'chancafe', 'po_chancafe.png', 1),
(7, 'Sodimac (PDF)', 'sodimac', 'sodimac', 'po_sodimac.png', 1),
(8, 'Metro (PDF)', 'metro', 'metro', 'po_metro.png', 1);


--
-- 덤프된 테이블의 인덱스
--

--
-- 테이블의 인덱스 `scm_purchase_order_template`
--
ALTER TABLE `scm_purchase_order_template`
  ADD PRIMARY KEY (`template_id`);

--
-- 덤프된 테이블의 AUTO_INCREMENT
--

--
-- 테이블의 AUTO_INCREMENT `scm_purchase_order_template`
--
ALTER TABLE `scm_purchase_order_template`
  MODIFY `template_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
