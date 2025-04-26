-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 25-04-26 02:15
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
-- 테이블 구조 `lgepr_lookup`
--

CREATE TABLE `lgepr_lookup` (
  `lookup_id` int(11) NOT NULL,
  `code` varchar(30) NOT NULL,
  `lookup` varchar(150) NOT NULL,
  `seq` int(11) DEFAULT NULL,
  `attr_1` varchar(100) DEFAULT NULL,
  `attr_2` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- 테이블의 덤프 데이터 `lgepr_lookup`
--

INSERT INTO `lgepr_lookup` (`lookup_id`, `code`, `lookup`, `seq`, `attr_1`, `attr_2`) VALUES
(1, 'vacation_type', 'Vacation', NULL, NULL, NULL),
(2, 'vacation_type', 'Vacation (Morning)', NULL, NULL, NULL),
(3, 'vacation_type', 'Vacation (Afternoon)', NULL, NULL, NULL),
(4, 'company', 'HS', 1, NULL, NULL),
(5, 'company', 'MS', 2, NULL, NULL),
(6, 'company', 'ES', 3, NULL, NULL),
(7, 'division', 'REF', 1, 'HS', NULL),
(8, 'division', 'Cooking', 2, 'HS', NULL),
(9, 'division', 'W/M', 3, 'HS', NULL),
(10, 'division', 'LTV', 1, 'MS', NULL),
(11, 'division', 'Audio', 2, 'MS', NULL),
(12, 'division', 'MNT', 3, 'MS', NULL),
(13, 'division', 'DS', 4, 'MS', NULL),
(14, 'division', 'MNT Signage', 5, 'MS', NULL),
(15, 'division', 'LED Signage', 6, 'MS', NULL),
(16, 'division', 'Commercial TV', 7, 'MS', NULL),
(17, 'division', 'RAC', 1, 'ES', NULL),
(18, 'division', 'SAC', 2, 'ES', NULL),
(19, 'division', 'Chiller', 3, 'ES', NULL),
(20, 'department', 'LGEPR', 1, NULL, NULL),
(21, 'department', 'Branch', 2, NULL, NULL);

--
-- 덤프된 테이블의 인덱스
--

--
-- 테이블의 인덱스 `lgepr_lookup`
--
ALTER TABLE `lgepr_lookup`
  ADD PRIMARY KEY (`lookup_id`);

--
-- 덤프된 테이블의 AUTO_INCREMENT
--

--
-- 테이블의 AUTO_INCREMENT `lgepr_lookup`
--
ALTER TABLE `lgepr_lookup`
  MODIFY `lookup_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
