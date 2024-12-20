-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2024 at 09:21 PM
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
-- Table structure for table `tercer_ojo_market_price`
--

CREATE TABLE `tercer_ojo_market_price` (
  `price_id` int(11) NOT NULL,
  `category` varchar(150) DEFAULT NULL,
  `retail` varchar(150) DEFAULT NULL,
  `brand` varchar(50) DEFAULT NULL,
  `product` varchar(250) DEFAULT NULL,
  `seller` varchar(150) DEFAULT NULL,
  `minimum` float DEFAULT NULL,
  `extra` float DEFAULT NULL,
  `offer` float DEFAULT NULL,
  `list` float DEFAULT NULL,
  `url` text DEFAULT NULL,
  `card` int(11) DEFAULT NULL,
  `features` text DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tercer_ojo_market_price`
--
ALTER TABLE `tercer_ojo_market_price`
  ADD PRIMARY KEY (`price_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tercer_ojo_market_price`
--
ALTER TABLE `tercer_ojo_market_price`
  MODIFY `price_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
