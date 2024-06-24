-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 24-06-22 15:51
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
-- 테이블 구조 `obs_most_likely`
--

CREATE TABLE `obs_most_likely` (
  `most_likely_id` int(11) NOT NULL,
  `subsidiary` varchar(50) DEFAULT NULL,
  `division` varchar(50) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `year` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `bp` float DEFAULT NULL,
  `target` float DEFAULT NULL,
  `monthly_report` float DEFAULT NULL,
  `ml` float DEFAULT NULL,
  `ml_actual` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 테이블의 덤프 데이터 `obs_most_likely`
--

INSERT INTO `obs_most_likely` (`most_likely_id`, `subsidiary`, `division`, `category`, `year`, `month`, `bp`, `target`, `monthly_report`, `ml`, `ml_actual`) VALUES
(1, 'LGEPR', NULL, NULL, 2024, 5, 330491, 825350, 839767, 625000, 641447),
(2, 'LGEPR', 'HA', NULL, 2024, 5, 184668, 462454, 416662, 327000, 335605),
(3, 'LGEPR', 'HA', 'REF', 2024, 5, 91015, 152707, 152707, 105000, 107763),
(4, 'LGEPR', 'HA', 'COOK', 2024, 5, 13566.8, 21728, 21728, 17000, 17447.4),
(5, 'LGEPR', 'HA', 'W/M', 2024, 5, 66384.4, 266569, 216569, 167000, 171395),
(6, 'LGEPR', 'HA', 'RAC', 2024, 5, 13702.3, 21450, 25657.9, 38000, 39000),
(7, 'LGEPR', 'HA', 'SAC', 2024, 5, 0, 0, 0, 0, 0),
(8, 'LGEPR', 'HA', 'A/C', 2024, 5, 0, 0, 0, 0, 0),
(9, 'LGEPR', 'HE', NULL, 2024, 5, 135762, 343715, 394105, 239000, 245289),
(10, 'LGEPR', 'HE', 'TV', 2024, 5, 126205, 320221, 338684, 181000, 185763),
(11, 'LGEPR', 'HE', 'AV', 2024, 5, 9557.86, 23494.2, 55421.1, 58000, 59526.3),
(12, 'LGEPR', 'BS', NULL, 2024, 5, 10060.2, 19180.9, 29000, 59000, 60552.6),
(13, 'LGEPR', 'BS', 'MNT', 2024, 5, 7389.17, 14088.3, 14000, 30000, 30789.5),
(14, 'LGEPR', 'BS', 'PC', 2024, 5, 0, 0, 0, 0, 0),
(15, 'LGEPR', 'BS', 'DS', 2024, 5, 0, 0, 0, 0, 0),
(16, 'LGEPR', 'BS', 'SGN', 2024, 5, 2671.05, 5092.66, 15000, 29000, 29763.2),
(17, 'LGEPR', 'BS', 'CTV', 2024, 5, 0, 0, 0, 0, 0);

--
-- 덤프된 테이블의 인덱스
--

--
-- 테이블의 인덱스 `obs_most_likely`
--
ALTER TABLE `obs_most_likely`
  ADD PRIMARY KEY (`most_likely_id`);

--
-- 덤프된 테이블의 AUTO_INCREMENT
--

--
-- 테이블의 AUTO_INCREMENT `obs_most_likely`
--
ALTER TABLE `obs_most_likely`
  MODIFY `most_likely_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
