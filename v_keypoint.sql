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
-- VIEW `v_keypoint`
-- Data: None
--


-- --------------------------------------------------------

--
-- Structure for view `v_keypoint`
--

DROP VIEW IF EXISTS `v_keypoint`;

CREATE VIEW `v_keypoint` AS 
SELECT 
  `a`.`idkeypoint` AS `idkeypoint`, 
  `a`.`kodepenyul` AS `kodepenyul`, 
  `a`.`jenis` AS `jenis`, 
  `a`.`keterangan` AS `keterangan`, 
  `a`.`unit` AS `unit`, 
  `a`.`zona` AS `zona`, 
  `a`.`latitud` AS `latitud`, 
  `a`.`longitud` AS `longitud`, 
  `a`.`id_keypint` AS `id_keypint`, 
  COALESCE(`b`.`uraianpenyul`, `a`.`kodepenyul`) AS `uraianpenyul`, 
  COALESCE(`c`.`uraian`, `a`.`unit`) AS `uraian` 
FROM `kodekeypoint` `a` 
LEFT JOIN `kodepenyulang` `b` ON `a`.`kodepenyul` = `b`.`kodepenyul` 
LEFT JOIN `kodeunit` `c` ON `a`.`unit` = `c`.`kodeunit`;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
