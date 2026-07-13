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
-- Table structure for table `kodepenyulang`
--

CREATE TABLE `kodepenyulang` (
  `kodepenyul` varchar(10) NOT NULL,
  `uraianpenyul` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `kodepenyulang`
--

INSERT INTO `kodepenyulang` (`kodepenyul`, `uraianpenyul`) VALUES
('BDGAN', 'BADEGAN'),
('BNGKL', 'BUNGKAL'),
('COKRO', 'COKROKEMBANG'),
('DNGKO', 'DONGKO'),
('DROJO', 'DONOROJO'),
('DUREN', 'DURENAN'),
('GDSRI', 'GANDUSARI'),
('GOTOR', 'GONTOR'),
('JENNG', 'JENANGAN'),
('KAUMN', 'KAUMAN'),
('KBAGU', 'KEBUN AGUNG'),
('KDPTN', 'KADIPATEN'),
('KETAN', 'KETANGGUNG'),
('KMPAK', 'KAMPAK'),
('KRGAN', 'KARANGAN'),
('KTURI', 'KARANG TURI'),
('LOROK', 'LOROK'),
('MELIS', 'MELIS'),
('MJGAN', 'MUNJUNGAN'),
('MLARK', 'MLARAK'),
('NAWGN', 'NAWANGAN'),
('NGMPL', 'NGUMPUL'),
('NGRES', 'NGARES'),
('NKDNO', 'NONGKODONO'),
('PGLAN', 'POGALAN'),
('PNDPO', 'PENDOPO'),
('POPER', 'POPER'),
('PRIGI', 'PRIGI'),
('PSRPO', 'PASAR PON'),
('PULE', 'PULE'),
('PULUN', 'PULUNG'),
('RSUDP', 'RSUD PONOROGO'),
('SAMPG', 'SAMPUNG'),
('SAWGL', 'SAWUNGGALING'),
('SLAHN', 'SLAHUNG'),
('SLOJI', 'SELOAJI'),
('SMBIT', 'SAMBIT'),
('SOOGE', 'SOGE'),
('SUMOR', 'SUMOROTO'),
('T-ASR', 'TAMAN ASRI'),
('T-LEN', 'TELENG'),
('T-OMB', 'TEGAL OMBO'),
('T-UMA', 'TEUKU UMAR'),
('TUGU', 'TUGU'),
('WBNDO', 'WADUK BENDO'),
('WKARU', 'WATU KARUNG'),
('WTLMO', 'WATULIMO');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `kodepenyulang`
--
ALTER TABLE `kodepenyulang`
  ADD PRIMARY KEY (`kodepenyul`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
