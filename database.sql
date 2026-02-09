-- Database Schema (Updated per User Request)
CREATE DATABASE IF NOT EXISTS sistem_undian_pustakawan;
USE sistem_undian_pustakawan;

-- Tables
CREATE TABLE IF NOT EXISTS PENGGUNA (
  `no_murid` varchar(20) NOT NULL PRIMARY KEY,
  `nama_murid` varchar(100) NOT NULL,
  `kelas_murid` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','murid') NOT NULL DEFAULT 'murid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS JAWATAN (
  `id_jawatan` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nama_jawatan` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS CALON (
  `id_calon` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `no_murid` varchar(20) NOT NULL,
  `id_jawatan` int(11) NOT NULL,
  `manifesto` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  FOREIGN KEY (`no_murid`) REFERENCES `PENGGUNA`(`no_murid`) ON DELETE CASCADE,
  FOREIGN KEY (`id_jawatan`) REFERENCES `JAWATAN`(`id_jawatan`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS UNDIAN (
  `id_undi` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `no_murid` varchar(20) NOT NULL, -- The voter
  `id_calon` int(11) NOT NULL,    -- Who they voted for
  FOREIGN KEY (`no_murid`) REFERENCES `PENGGUNA`(`no_murid`) ON DELETE CASCADE,
  FOREIGN KEY (`id_calon`) REFERENCES `CALON`(`id_calon`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Admin (if not exists)
INSERT IGNORE INTO PENGGUNA (no_murid, nama_murid, kelas_murid, password, role) 
VALUES ('admin', 'Pentadbir', 'Staff', '12345', 'admin');
