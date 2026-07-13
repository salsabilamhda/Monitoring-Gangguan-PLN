-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 24, 2025 at 12:10 AM
-- Server version: 10.11.14-MariaDB-cll-lve
-- PHP Version: 8.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sarc5556_sarup3pon`
--

--
-- VIEW `v_penyulang`
-- Data: None
--


-- --------------------------------------------------------

--
-- Structure for view `v_penyulang`
--

CREATE ALGORITHM=UNDEFINED DEFINER=`sarc5556`@`localhost` SQL SECURITY DEFINER VIEW `v_penyulang`  AS SELECT DISTINCT `a`.`kodepenyul` AS `kodepenyul`, `a`.`unit` AS `unit`, `b`.`uraian` AS `uraian`, `c`.`uraianpenyul` AS `uraianpenyul` FROM ((`kodekeypoint` `a` join `kodeunit` `b`) join `kodepenyulang` `c`) WHERE `a`.`unit` = `b`.`kodeunit` AND `a`.`kodepenyul` = `c`.`kodepenyul` ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
