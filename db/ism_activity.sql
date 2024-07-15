-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 24-07-15 18:18
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
  `approval_no` varchar(150) DEFAULT NULL,
  `retail` varchar(250) DEFAULT NULL,
  `category` varchar(20) DEFAULT NULL,
  `project_type` varchar(100) DEFAULT NULL,
  `period_from` date DEFAULT NULL,
  `period_to` date DEFAULT NULL,
  `vendor` varchar(250) DEFAULT NULL,
  `currency` varchar(5) DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `activity_status` varchar(30) DEFAULT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `invoice_date` timestamp NULL DEFAULT NULL,
  `invoice_status` varchar(50) DEFAULT NULL,
  `invoice_description` varchar(250) DEFAULT NULL,
  `detail` text DEFAULT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1,
  `registered` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 테이블의 덤프 데이터 `ism_activity`
--

INSERT INTO `ism_activity` (`activity_id`, `title`, `pr_pic`, `pr_number`, `pr_buyer`, `approval_no`, `retail`, `category`, `project_type`, `period_from`, `period_to`, `vendor`, `currency`, `amount`, `activity_status`, `invoice_number`, `invoice_date`, `invoice_status`, `invoice_description`, `detail`, `valid`, `registered`) VALUES
(3, 'sadfsaf sdfas fsa fsa s f', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PEN', 0, 'En proceso', NULL, NULL, 'En proceso', NULL, NULL, 1, '2024-07-15 22:28:12'),
(4, 'sdf asdfsa fsdfsa fas', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PEN', NULL, 'En proceso', NULL, NULL, 'En proceso', NULL, NULL, 1, '2024-07-15 22:31:52'),
(5, 'asdf asf ksajd fksadl fjsalk fjsa ', 'HSAD', 'dkaj hask fsa', 'Andy', 'NW28274', 'Sodimac', 'WM', '1 Mantenimiento', '2024-07-19', '2024-08-01', 'HS AD LATIN AMERICA S.A. SUCURSAL DEL PERU', 'PEN', 300.5, 'Aprobado', 'INV12-3234', '2024-07-25 05:00:00', 'Aprobado', 'saodfi uya fkljsadfl kj', 'sadk jfdslakf sda \r\nfsa f\r\nas f\r\nas fsad fsad flksha fk', 1, '2024-07-15 23:03:01');

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
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
