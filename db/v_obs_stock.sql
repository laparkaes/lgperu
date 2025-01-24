-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 24, 2025 at 06:25 PM
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
-- Structure for view `v_lgepr_stock`
--

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_obs_stock`  AS SELECT `lgepr_stock`.`model_description` AS `model_description`, `lgepr_stock`.`dash_company` AS `dash_company`, `lgepr_stock`.`dash_division` AS `dash_division`, `lgepr_stock`.`model` AS `model`, sum(case when `lgepr_stock`.`org` = 'N4M' then `lgepr_stock`.`available_qty` else 0 end) AS `N4M_qty`, sum(case when `lgepr_stock`.`org` = 'N4E' then `lgepr_stock`.`available_qty` else 0 end) AS `N4E_qty`, sum(`lgepr_stock`.`seaStockTotal`) AS `seaStockTotal`, sum(`lgepr_stock`.`seaStockW1`) AS `seaStockW1`, sum(`lgepr_stock`.`seaStockW2`) AS `seaStockW2`, sum(`lgepr_stock`.`seaStockW3`) AS `seaStockW3`, sum(`lgepr_stock`.`seaStockW4`) AS `seaStockW4`, sum(`lgepr_stock`.`seaStockW5`) AS `seaStockW5` FROM `lgepr_stock` WHERE `lgepr_stock`.`org` in ('N4M','N4E') AND `lgepr_stock`.`model_status` = 'Active' GROUP BY `lgepr_stock`.`model_description` HAVING (`N4M_qty` <> 0 OR `N4E_qty` <> 0 OR `seaStockTotal` <> 0) AND `lgepr_stock`.`dash_company` is not null AND `lgepr_stock`.`dash_division` is not null;

--
-- VIEW `v_lgepr_stock`
-- Data: None
--

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
