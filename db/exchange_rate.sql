-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 24-05-03 21:03
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
-- 테이블 구조 `exchange_rate`
--

CREATE TABLE `exchange_rate` (
  `exchange_rate_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `currency_from` varchar(5) NOT NULL,
  `currency_to` varchar(5) NOT NULL,
  `rate` decimal(10,2) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- 테이블의 덤프 데이터 `exchange_rate`
--

INSERT INTO `exchange_rate` (`exchange_rate_id`, `date`, `currency_from`, `currency_to`, `rate`, `valid`) VALUES
(1, '2024-05-01', 'USD', 'PEN', '3.72', 1);

--
-- 덤프된 테이블의 인덱스
--

--
-- 테이블의 인덱스 `exchange_rate`
--
ALTER TABLE `exchange_rate`
  ADD PRIMARY KEY (`exchange_rate_id`);

--
-- 덤프된 테이블의 AUTO_INCREMENT
--

--
-- 테이블의 AUTO_INCREMENT `exchange_rate`
--
ALTER TABLE `exchange_rate`
  MODIFY `exchange_rate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
