-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 24-11-30 20:53
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
-- 테이블 구조 `sys_function`
--

CREATE TABLE `sys_function` (
  `function_id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  `path` varchar(100) NOT NULL,
  `title` varchar(150) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 테이블의 덤프 데이터 `sys_function`
--

INSERT INTO `sys_function` (`function_id`, `type`, `path`, `title`, `created_at`, `updated_at`, `valid`) VALUES
(1, 'data_upload', 'dfda', 'sfsaf', '2024-12-01 01:49:09', '2024-12-01 01:51:01', 0),
(2, 'data_upload', 'sdfas f', ' sfsa ', '2024-12-01 01:49:09', NULL, 1),
(3, 'page', 'sa fsad ', 's adfsad f', '2024-12-01 01:49:09', '2024-12-01 01:53:07', 1),
(4, 'data_upload', 'sadfsaf', 's fs fsa ', '2024-12-01 01:49:09', NULL, 1),
(5, 'data_upload', 'sdfas fsfsaf', 'sfsafsfs', '2024-12-01 01:49:09', NULL, 1),
(6, 'module', 'hola', 'hola', '2024-12-01 01:49:09', '2024-12-01 01:53:02', 0),
(7, 'module', 'hola1', 'como/estas', '2024-12-01 01:49:09', '2024-12-01 01:52:59', 1),
(8, 'data_upload', 'gerp_stock_update', 'testing created and updated', '2024-12-01 01:51:25', '2024-12-01 01:52:15', 1);

--
-- 덤프된 테이블의 인덱스
--

--
-- 테이블의 인덱스 `sys_function`
--
ALTER TABLE `sys_function`
  ADD PRIMARY KEY (`function_id`);

--
-- 덤프된 테이블의 AUTO_INCREMENT
--

--
-- 테이블의 AUTO_INCREMENT `sys_function`
--
ALTER TABLE `sys_function`
  MODIFY `function_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
