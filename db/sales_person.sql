-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 24-04-26 00:18
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
-- 테이블 구조 `sales_person`
--

CREATE TABLE `sales_person` (
  `person_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 테이블의 덤프 데이터 `sales_person`
--

INSERT INTO `sales_person` (`person_id`, `name`, `valid`) VALUES
(1, 'MIRANDA OVALLE, EDUARDO', 1),
(2, 'Padilla, Ricardo', 1),
(3, 'Castillo, Jose', 1),
(4, 'Ginocchio, Angelica', 1),
(5, 'MENDOZA NOEL, JULIO', 1),
(6, 'Altamirano, Jose', 1),
(7, 'Chavez, Leslie', 1),
(8, 'Reategui, Javier', 1),
(9, 'Alcazar, Ricardo', 1),
(10, 'PAUCAR SUAREZ, PEDRO', 1),
(11, 'Electronica, Facturacion', 1),
(12, 'Cruz, Rosa Evelyn', 1),
(13, 'Ruiz, Morella', 1);

--
-- 덤프된 테이블의 인덱스
--

--
-- 테이블의 인덱스 `sales_person`
--
ALTER TABLE `sales_person`
  ADD PRIMARY KEY (`person_id`);

--
-- 덤프된 테이블의 AUTO_INCREMENT
--

--
-- 테이블의 AUTO_INCREMENT `sales_person`
--
ALTER TABLE `sales_person`
  MODIFY `person_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
