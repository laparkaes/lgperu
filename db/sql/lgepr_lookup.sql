-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 25-03-02 15:12
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
-- 테이블 구조 `lgepr_lookup`
--

CREATE TABLE `lgepr_lookup` (
  `lookup_id` int(11) NOT NULL,
  `code` varchar(30) NOT NULL,
  `lookup` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 테이블의 덤프 데이터 `lgepr_lookup`
--

INSERT INTO `lgepr_lookup` (`lookup_id`, `code`, `lookup`) VALUES
(1, 'vacation_type', 'Vacation'),
(2, 'vacation_type', 'Vacation (Morning)'),
(3, 'vacation_type', 'Vacation (Afternoon)');

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
  MODIFY `lookup_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
