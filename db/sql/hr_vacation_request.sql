-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 02, 2025 at 02:17 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `llamasys`
--

-- --------------------------------------------------------

--
-- Table structure for table `hr_vacation_request`
--

CREATE TABLE `hr_vacation_request` (
  `request_id` int(11) NOT NULL,
  `emp_ep` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL,
  `type` varchar(100) NOT NULL,
  `d_from` date NOT NULL,
  `d_to` date NOT NULL,
  `qty_day` float NOT NULL,
  `approver_key` varchar(5) DEFAULT NULL,
  `approver_now` varchar(50) DEFAULT NULL,
  `approver_1` varchar(50) NOT NULL,
  `approver_2` varchar(50) NOT NULL,
  `approver_3` varchar(50) NOT NULL,
  `remark` text DEFAULT NULL,
  `registed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `hr_vacation_request`
--

INSERT INTO `hr_vacation_request` (`request_id`, `emp_ep`, `status`, `type`, `d_from`, `d_to`, `qty_day`, `approver_key`, `approver_now`, `approver_1`, `approver_2`, `approver_3`, `remark`, `registed_at`) VALUES
(1, 'georgio.park', 'Cancelled', 'Vacation (Afternoon)', '2025-03-10', '2025-03-10', 0.5, 'lKYVC', 'bion.hwang', 'bion.hwang', 'wonshik.woo', 'andre.cho', 'me canse de trabajar', '2025-03-01 22:11:42'),
(2, 'georgio.park', 'Cancelled', 'Vacation (Afternoon)', '2025-03-06', '2025-03-19', 0.5, 'nueDH', 'bion.hwang', 'bion.hwang', 'melisa.carbajal', 'wonshik.woo', '', '2025-03-01 22:44:37'),
(3, 'georgio.park', 'Cancelled', 'Vacation (Afternoon)', '2025-03-20', '2025-03-20', 0.5, 'gVlyh', 'enrique.salazar', 'enrique.salazar', 'andre.cho', 'wonshik.woo', '', '2025-03-01 22:47:05'),
(4, 'georgio.park', 'Requested', 'Vacation', '2025-03-03', '2025-03-21', 19, 'gDFSA', 'enrique.salazar', 'enrique.salazar', 'andre.cho', 'wonshik.woo', '', '2025-03-02 01:13:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `hr_vacation_request`
--
ALTER TABLE `hr_vacation_request`
  ADD PRIMARY KEY (`request_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hr_vacation_request`
--
ALTER TABLE `hr_vacation_request`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
