-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 11, 2025 at 04:58 PM
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
-- Table structure for table `scm_tracking_dispatch`
--

CREATE TABLE `scm_tracking_dispatch` (
  `id` int(11) NOT NULL,
  `_3pl` varchar(10) DEFAULT NULL,
  `tracking_key` varchar(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `actual_load_date` datetime DEFAULT NULL,
  `transport` varchar(50) DEFAULT NULL,
  `placa` varchar(20) DEFAULT NULL,
  `movil` varchar(20) DEFAULT NULL,
  `ut_load_arrival_time` datetime DEFAULT NULL,
  `actual_load_time` datetime DEFAULT NULL,
  `load_end_time` datetime DEFAULT NULL,
  `load_status` varchar(255) DEFAULT NULL,
  `container_district` varchar(255) DEFAULT NULL,
  `customer` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `pick_order` varchar(255) DEFAULT NULL,
  `service_type` varchar(20) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `b2c_zone` varchar(255) DEFAULT NULL,
  `ot_per_point` varchar(255) DEFAULT NULL,
  `purchase_order` varchar(255) DEFAULT NULL,
  `guide` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `cbm` decimal(10,2) DEFAULT NULL,
  `cbm_per_unit` decimal(10,2) DEFAULT NULL,
  `rejected_qty` decimal(10,2) DEFAULT NULL,
  `rejected_cbm` decimal(10,2) DEFAULT NULL,
  `delivered_cbm` decimal(10,2) DEFAULT NULL,
  `observation` varchar(100) DEFAULT NULL,
  `client_appointment` time DEFAULT NULL,
  `to_appointment` time DEFAULT NULL,
  `arrival_time` time DEFAULT NULL,
  `download_time` time DEFAULT NULL,
  `completion_time` time DEFAULT NULL,
  `service_completion_time` time DEFAULT NULL,
  `waiting_time` time DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `status_2` varchar(255) DEFAULT NULL,
  `observations` varchar(255) DEFAULT NULL,
  `otd` varchar(255) DEFAULT NULL,
  `updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `scm_tracking_dispatch`
--
ALTER TABLE `scm_tracking_dispatch`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `scm_tracking_dispatch`
--
ALTER TABLE `scm_tracking_dispatch`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
