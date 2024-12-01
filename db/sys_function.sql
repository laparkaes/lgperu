-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 24-12-01 01:35
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
(1, 'module', 'hr_attendance', '[HR] Attendance', '2024-12-01 05:29:07', '2024-12-01 05:29:07', 1),
(2, 'module', 'hr_employee', '[HR] Employee', '2024-12-01 05:29:25', '2024-12-01 05:29:25', 1),
(3, 'module', 'ism_activity_management', '[ISM] Activity Management', '2024-12-01 05:30:23', '2024-12-01 05:30:23', 1),
(4, 'module', 'obs_report', '[OBS] Report', '2024-12-01 05:31:41', '2024-12-01 05:31:41', 1),
(5, 'module', 'pi_listening', '[PI] Listening', '2024-12-01 05:32:47', '2024-12-01 05:32:47', 1),
(6, 'module', 'sa_promotion', '[SA] Promotion', '2024-12-01 05:33:14', '2024-12-01 05:33:14', 1),
(7, 'module', 'sa_sell_inout', '[SA] Sell-In/Out', '2024-12-01 05:34:37', '2024-12-01 05:34:37', 1),
(8, 'module', 'scm_purchase_order', '[SCM] Purchase Order', '2024-12-01 05:35:58', '2024-12-01 05:35:58', 1),
(9, 'module', 'tax_invoice_comparison', '[TAX] Invoice Comparison', '2024-12-01 05:36:38', '2024-12-01 05:36:38', 1),
(10, 'data_upload', 'lgepr_sales_order', '[LGEPR] Sale Order', '2024-12-01 05:43:38', '2024-12-01 05:43:38', 1),
(11, 'data_upload', 'lgepr_stock_update', '[LGEPR] Stock Update', '2024-12-01 05:44:03', '2024-12-01 05:44:03', 1),
(12, 'data_upload', 'ar_exchange_rate', '[AR] Exchange Rate', '2024-12-01 05:49:02', '2024-12-01 05:49:02', 1),
(13, 'data_upload', 'hr_access_record', '[HR] Access Record', '2024-12-01 05:50:14', '2024-12-01 05:50:14', 1),
(14, 'data_upload', 'obs_gerp', '[OBS] GERP Sale Order', '2024-12-01 05:51:56', '2024-12-01 05:51:56', 1),
(15, 'data_upload', 'obs_magento', '[OBS] Magento', '2024-12-01 05:52:21', '2024-12-01 05:52:21', 1),
(16, 'data_upload', 'obs_most_likely', '[OBS] Most Likely', '2024-12-01 05:58:39', '2024-12-01 05:58:39', 1),
(17, 'data_upload', 'sa_sell_out', '[SA] Sell Out', '2024-12-01 05:59:20', '2024-12-01 05:59:20', 1),
(18, 'data_upload', 'tax_paperless_document', '[TAX] Paperless Document', '2024-12-01 06:00:07', '2024-12-01 06:00:07', 1),
(19, 'page', 'lgepr_punctuality', '[LGEPR] Punctuality', '2024-12-01 06:13:44', '2024-12-01 06:13:44', 1),
(20, 'page', 'pi_listening_request', '[PI] Listening Request', '2024-12-01 06:15:17', '2024-12-01 06:15:17', 1),
(21, 'page', 'obs_nsp', '[OBS] NSP', '2024-12-01 06:31:48', '2024-12-01 06:31:48', 1);

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
  MODIFY `function_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
