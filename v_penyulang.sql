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

DROP VIEW IF EXISTS `v_penyulang`;

CREATE VIEW `v_penyulang` AS 
SELECT DISTINCT 
  `a`.`kodepenyul` AS `kodepenyul`, 
  `a`.`unit` AS `unit`, 
  COALESCE(`b`.`uraian`, `a`.`unit`) AS `uraian`, 
  COALESCE(`c`.`uraianpenyul`, `a`.`kodepenyul`) AS `uraianpenyul` 
FROM `kodekeypoint` `a` 
LEFT JOIN `kodeunit` `b` ON `a`.`unit` = `b`.`kodeunit` 
LEFT JOIN `kodepenyulang` `c` ON `a`.`kodepenyul` = `c`.`kodepenyul` 
WHERE `a`.`kodepenyul` != '' AND `a`.`kodepenyul` IS NOT NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
