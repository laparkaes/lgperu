-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 24-07-10 23:49
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
-- 테이블 구조 `ism_activity`
--

CREATE TABLE `ism_activity` (
  `activity_id` int(11) NOT NULL,
  `title` varchar(250) DEFAULT NULL,
  `pr_pic` varchar(150) DEFAULT NULL,
  `pr_number` varchar(50) DEFAULT NULL,
  `pr_buyer` varchar(250) DEFAULT NULL,
  `approval` varchar(150) DEFAULT NULL,
  `retail` varchar(250) DEFAULT NULL,
  `category` varchar(20) DEFAULT NULL,
  `project_type` varchar(100) DEFAULT NULL,
  `period_from` timestamp NULL DEFAULT NULL,
  `period_to` timestamp NULL DEFAULT NULL,
  `vendor` varchar(250) DEFAULT NULL,
  `currency` varchar(5) DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `approval_status` varchar(30) DEFAULT NULL,
  `activity_status` varchar(30) DEFAULT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `invoice_date` timestamp NULL DEFAULT NULL,
  `invoice_status` varchar(50) DEFAULT NULL,
  `invoice_description` varchar(250) DEFAULT NULL,
  `detail` text DEFAULT NULL,
  `registered` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 덤프된 테이블의 인덱스
--

--
-- 테이블의 인덱스 `ism_activity`
--
ALTER TABLE `ism_activity`
  ADD PRIMARY KEY (`activity_id`);

--
-- 덤프된 테이블의 AUTO_INCREMENT
--

--
-- 테이블의 AUTO_INCREMENT `ism_activity`
--
ALTER TABLE `ism_activity`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
