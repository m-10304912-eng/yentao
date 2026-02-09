-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 12, 2026 at 11:15 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sistem_undian_pustakawan`
--

-- --------------------------------------------------------

--
-- Table structure for table `calon`
--

CREATE TABLE `calon` (
  `id_calon` int(11) NOT NULL,
  `no_murid` varchar(20) NOT NULL,
  `id_jawatan` int(11) NOT NULL,
  `manifesto` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jawatan`
--

CREATE TABLE `jawatan` (
  `id_jawatan` int(11) NOT NULL,
  `nama_jawatan` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jawatan`
--

INSERT INTO `jawatan` (`id_jawatan`, `nama_jawatan`) VALUES
(2, 'Naib Pengerusi');

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `no_murid` varchar(20) NOT NULL,
  `nama_murid` varchar(100) NOT NULL,
  `kelas_murid` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','murid') NOT NULL DEFAULT 'murid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`no_murid`, `nama_murid`, `kelas_murid`, `password`, `role`) VALUES
('admin', 'Pentadbir Yen Tao', 'Pejabat', '12345', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `undian`
--

CREATE TABLE `undian` (
  `id_undi` int(11) NOT NULL,
  `no_murid` varchar(20) NOT NULL,
  `id_calon` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `calon`
--
ALTER TABLE `calon`
  ADD PRIMARY KEY (`id_calon`),
  ADD KEY `no_murid` (`no_murid`),
  ADD KEY `id_jawatan` (`id_jawatan`);

--
-- Indexes for table `jawatan`
--
ALTER TABLE `jawatan`
  ADD PRIMARY KEY (`id_jawatan`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`no_murid`);

--
-- Indexes for table `undian`
--
ALTER TABLE `undian`
  ADD PRIMARY KEY (`id_undi`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `calon`
--
ALTER TABLE `calon`
  MODIFY `id_calon` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jawatan`
--
ALTER TABLE `jawatan`
  MODIFY `id_jawatan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `undian`
--
ALTER TABLE `undian`
  MODIFY `id_undi` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `calon`
--
ALTER TABLE `calon`
  ADD CONSTRAINT `calon_ibfk_1` FOREIGN KEY (`no_murid`) REFERENCES `pengguna` (`no_murid`) ON DELETE CASCADE,
  ADD CONSTRAINT `calon_ibfk_2` FOREIGN KEY (`id_jawatan`) REFERENCES `jawatan` (`id_jawatan`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
