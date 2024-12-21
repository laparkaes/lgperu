-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 24-12-21 13:22
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
-- 테이블 구조 `hr_internal_sale`
--

CREATE TABLE `hr_internal_sale` (
  `sale_id` int(11) NOT NULL,
  `division` enum('HA','HE','BS') DEFAULT NULL,
  `category` varchar(30) DEFAULT NULL,
  `model` varchar(255) NOT NULL,
  `grade` enum('A','B','C') NOT NULL,
  `remark` text DEFAULT NULL,
  `price_list` float NOT NULL,
  `price_offer` float NOT NULL,
  `created_at` date NOT NULL,
  `end_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 테이블의 덤프 데이터 `hr_internal_sale`
--

INSERT INTO `hr_internal_sale` (`sale_id`, `division`, `category`, `model`, `grade`, `remark`, `price_list`, `price_offer`, `created_at`, `end_date`) VALUES
(1, '', NULL, '', '', NULL, 0, 0, '2024-12-13', '0000-00-00'),
(2, '', NULL, '', '', NULL, 0, 0, '2024-12-13', '0000-00-00'),
(3, '', NULL, '', '', NULL, 0, 0, '2024-12-13', '0000-00-00'),
(4, NULL, '', '', '', NULL, 0, 0, '2024-12-21', '0000-00-00');

--
-- 덤프된 테이블의 인덱스
--

--
-- 테이블의 인덱스 `hr_internal_sale`
--
ALTER TABLE `hr_internal_sale`
  ADD PRIMARY KEY (`sale_id`);

--
-- 덤프된 테이블의 AUTO_INCREMENT
--

--
-- 테이블의 AUTO_INCREMENT `hr_internal_sale`
--
ALTER TABLE `hr_internal_sale`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
