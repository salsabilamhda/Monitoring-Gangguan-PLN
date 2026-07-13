-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 24, 2025 at 12:09 AM
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

-- --------------------------------------------------------

--
-- Table structure for table `kodeunit`
--

CREATE TABLE `kodeunit` (
  `kodeunit` int(11) NOT NULL,
  `uraian` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `kodeunit`
--

INSERT INTO `kodeunit` (`kodeunit`, `uraian`) VALUES
(5152, 'UP3 PONOROGO'),
(51540, 'ULP PONOROGO'),
(51541, 'ULP BALONG'),
(51542, 'ULP PACITAN'),
(51543, 'ULP TRENGGALEK');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `kodeunit`
--
ALTER TABLE `kodeunit`
  ADD PRIMARY KEY (`kodeunit`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
