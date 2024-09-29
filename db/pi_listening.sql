-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 24-09-28 19:22
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
-- 테이블 구조 `pi_listening`
--

CREATE TABLE `pi_listening` (
  `listening_id` int(11) NOT NULL,
  `dptFrom` varchar(100) NOT NULL,
  `dptTo` varchar(100) NOT NULL,
  `issue` text NOT NULL,
  `solution` text NOT NULL,
  `response` text DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `priority` int(11) DEFAULT NULL,
  `registered` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 테이블의 덤프 데이터 `pi_listening`
--

INSERT INTO `pi_listening` (`listening_id`, `dptFrom`, `dptTo`, `issue`, `solution`, `response`, `status`, `priority`, `registered`) VALUES
(1, 'asdf', 'CFO_PI', 'sadfdas fs', 'a ssfad sad ', NULL, NULL, NULL, NULL),
(2, 'Process Innovation & IT', 'CFO_PI', 'sadf dsaf dsa ', 'sad fsa fsad ', NULL, NULL, NULL, NULL),
(3, 'Planning', 'CFO_PI', 'sadfsadf', 'sadfsaf\r\nsadf\r\nasdf\r\nsaf\r\nsf', NULL, NULL, NULL, NULL);

--
-- 덤프된 테이블의 인덱스
--

--
-- 테이블의 인덱스 `pi_listening`
--
ALTER TABLE `pi_listening`
  ADD PRIMARY KEY (`listening_id`);

--
-- 덤프된 테이블의 AUTO_INCREMENT
--

--
-- 테이블의 AUTO_INCREMENT `pi_listening`
--
ALTER TABLE `pi_listening`
  MODIFY `listening_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
