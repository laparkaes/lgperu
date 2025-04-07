-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 25-04-06 20:03
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
-- 뷰 구조 `v_lgepr_model_master`
--

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_lgepr_model_master`  AS SELECT DISTINCT `lgepr_sales_order`.`dash_company` AS `dash_company`, `lgepr_sales_order`.`dash_division` AS `dash_division`, `lgepr_sales_order`.`model` AS `model` FROM `lgepr_sales_order`union select distinct `lgepr_closed_order`.`dash_company` AS `dash_company`,`lgepr_closed_order`.`dash_division` AS `dash_division`,`lgepr_closed_order`.`model` AS `model` from `lgepr_closed_order`  ;

--
-- VIEW `v_lgepr_model_master`
-- 데이터: 없음
--

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
