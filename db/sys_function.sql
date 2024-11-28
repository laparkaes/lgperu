-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 29, 2024 at 12:34 AM
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
-- Table structure for table `sys_function`
--

CREATE TABLE `sys_function` (
  `function_id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  `path` varchar(100) NOT NULL,
  `title` varchar(150) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `sys_function`
--

INSERT INTO `sys_function` (`function_id`, `type`, `path`, `title`, `valid`) VALUES
(1, 'data_upload', 'dfda', 'sfsaf', 1),
(2, 'data_upload', 'sdfas f', ' sfsa ', 1),
(3, 'page', 'sa fsad ', 's adfsad f', 1),
(4, 'data_upload', 'sadfsaf', 's fs fsa ', 1),
(5, 'data_upload', 'sdfas fsfsaf', 'sfsafsfs', 1),
(6, 'module', 'hola', 'hola', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sys_function`
--
ALTER TABLE `sys_function`
  ADD PRIMARY KEY (`function_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sys_function`
--
ALTER TABLE `sys_function`
  MODIFY `function_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
