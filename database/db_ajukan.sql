-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 15, 2025 at 09:32 AM
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
-- Database: `db_ajukan`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nomor_telepon` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `nama`, `email`, `password`, `nomor_telepon`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$10$Es2KzvznDbNrM1u4LDrVAucVimsvWKDJGFyNW.FqY2pJU/8eoMRU2', '089512345667');

-- --------------------------------------------------------

--
-- Table structure for table `jurusan`
--

CREATE TABLE `jurusan` (
  `jurusan_id` int(11) NOT NULL,
  `nama_jurusan` varchar(100) NOT NULL,
  `admin_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jurusan`
--

INSERT INTO `jurusan` (`jurusan_id`, `nama_jurusan`, `admin_id`) VALUES
(1, 'Teknik Informatika', 1),
(2, 'Teknik Mesin', 1),
(3, 'Teknik Elektro', 1),
(4, 'Manajemen Bisnis', 1);

-- --------------------------------------------------------

--
-- Table structure for table `program_studi`
--

CREATE TABLE `program_studi` (
  `program_studi_id` int(11) NOT NULL,
  `nama_program_studi` varchar(100) NOT NULL,
  `jurusan_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `program_studi`
--

INSERT INTO `program_studi` (`program_studi_id`, `nama_program_studi`, `jurusan_id`, `admin_id`) VALUES
(1, 'Teknologi Rekayasa Perangkat Lunak', 1, 1),
(2, 'Teknologi Rekayasa Multimedia', 1, 1),
(3, 'Rekayasa Keamanan Siber', 1, 1),
(4, 'Animasi', 1, 1),
(5, 'Teknik Informatika', 1, 1),
(6, 'Teknologi Geomatika', 1, 1),
(7, 'Teknologi Permainan', 1, 1),
(8, 'Teknik Perawatan Pesawat Udara', 2, 1),
(9, 'Teknik Mesin', 2, 1),
(10, 'Teknologi Rekayasa Konstruksi Perkapalan', 2, 1),
(11, 'Teknologi Rekayasa Pengelasan dan Fabrikasi', 2, 1),
(12, 'Program Profesi Insinyur', 2, 1),
(13, 'Teknologi Rekayasa Metalurgi', 2, 1),
(14, 'Teknik Elektronika Manufaktur', 3, 1),
(15, 'Teknik Instrumentasi', 3, 1),
(16, 'Teknologi Rekayasa Pembangkit Energi', 3, 1),
(17, 'Teknologi Rekayasa Elektronika', 3, 1),
(18, 'Teknik Mekatronika', 3, 1),
(19, 'Teknologi Rekayasa Robotika', 3, 1),
(20, 'Akutansi', 4, 1),
(21, 'Administrasi Bisnis Terapan', 4, 1),
(22, 'Administrasi BIsnis Terapan International Class', 4, 1),
(23, 'Akutansi Manajerial', 4, 1),
(24, 'Logistik Perdagangan Internasional', 4, 1),
(25, 'Distribusi Barang', 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `nik` varchar(20) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `tempat_lahir` varchar(255) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `alamat` text NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('karyawan','manajer') NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `nik`, `nama`, `email`, `tempat_lahir`, `tanggal_lahir`, `alamat`, `password`, `role`, `admin_id`, `foto_profil`) VALUES
(2, '4342401075', 'Arshafin Alfisyahrin', 'arsha100@gmail.com', 'Batam', '2024-12-18', 'Odesa', '$2y$10$8qR/Rs7Yk.JeE9wJCeB4qujIZWXCNQACaUJr6czYb4OHijjBC6un.', 'karyawan', 1, 'b0a3f39db6b95d9e1169ebc9e5d85e45.png'),
(3, '4342401072', 'Diva Satria', 'divasatria700@gmail.com', 'Batam', '2006-06-24', 'Tanjung Riau', '$2y$10$nY3y/GHQDjH9VKyMNEuCxuWF4du3f.x2EWchunny1CuCY0GXp2DK6', 'manajer', 1, ''),
(4, '4342401064', 'Navita Damayanti Syarif', 'navita100@gmail.com', 'Tanjung Batu', '2024-12-22', 'Tanjung Riau', '$2y$10$ZNZIlzsRNKT9NNHFL9ATZ.sV67FA6YurcbSrrOsut97/RI1QWCpnG', 'karyawan', 1, '4be37c52c271fdc77495aa08594cb7fe.png'),
(5, '4342401061', 'Fajar Mirza Hanif', 'fajarmirza100@gmail.com', 'Batam', '2024-12-20', 'KDA', '$2y$10$KZzjNMLUpAZ4iZzzGiJw4uNeRzYNQcGUSQaGdLO4GeSbg4vloTfKu', 'manajer', 1, '6e22f3a24940c20ec1aee20e164d873d.png'),
(6, '4342401070', 'Muhamad Ariffadhlullah', 'muhamadarif100@gmail.com', 'Batam', '2024-12-20', 'Tiban Kampung', '$2y$10$kzsg9gUvqb3kGxW.O9BCWeNpEblCDex7cb.ISNrDRkJsOP8fA0Oru', 'karyawan', 1, '24e9547445e12ccd00c737d2f5b7736f.png');

-- --------------------------------------------------------

--
-- Table structure for table `usulan_pelatihan`
--

CREATE TABLE `usulan_pelatihan` (
  `id` int(11) NOT NULL,
  `judul_pelatihan` varchar(255) NOT NULL,
  `jenis_pelatihan` varchar(100) NOT NULL,
  `nama_peserta` varchar(255) NOT NULL DEFAULT 'Tidak Diketahui',
  `lembaga` varchar(255) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `tempat` varchar(255) DEFAULT NULL,
  `sumber_dana` varchar(100) DEFAULT NULL,
  `manajer_pembimbing` varchar(255) DEFAULT NULL,
  `target` varchar(255) DEFAULT NULL,
  `lpj_submitted` tinyint(1) DEFAULT 0,
  `status` enum('Sedang Ditinjau','Pending','Disetujui','Ditolak') DEFAULT 'Sedang Ditinjau',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `komentar` text DEFAULT NULL,
  `lpj_file` varchar(255) DEFAULT NULL,
  `tanggal_upload` datetime DEFAULT NULL,
  `lpj_status` enum('Belum Diajukan','Sedang Ditinjau','Disetujui','Revisi') DEFAULT NULL,
  `sertifikat_file` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `jurusan_id` int(11) DEFAULT NULL,
  `program_studi_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usulan_pelatihan`
--

INSERT INTO `usulan_pelatihan` (`id`, `judul_pelatihan`, `jenis_pelatihan`, `nama_peserta`, `lembaga`, `tanggal_mulai`, `tanggal_selesai`, `tempat`, `sumber_dana`, `manajer_pembimbing`, `target`, `lpj_submitted`, `status`, `created_at`, `updated_at`, `komentar`, `lpj_file`, `tanggal_upload`, `lpj_status`, `sertifikat_file`, `user_id`, `jurusan_id`, `program_studi_id`) VALUES
(20, 'Pelatihan Disetujui', 'Pelatihan', 'Alexis', 'Polibatam', '2025-01-15', '2025-01-22', 'aa', 'aa', '3', 'aa', 1, 'Disetujui', '2025-01-15 08:15:25', '2025-01-15 08:18:13', '', 'LPJ_67876f0085af6.pdf', '2025-01-15 15:17:04', 'Disetujui', 'Sertifikat_67876f0088ecc.pdf', 2, 1, 1),
(21, 'Pelatihan Ditolak', 'Pelatihan', 'Henry', 'Polibatam', '2025-01-15', '2025-01-23', 'aa', 'aa', '3', 'aa', 0, 'Ditolak', '2025-01-15 08:15:57', '2025-01-15 08:16:18', NULL, NULL, NULL, 'Belum Diajukan', NULL, 2, 1, 2),
(22, 'Pelatihan 1', 'Pelatihan', 'Nolan', 'Polibatam', '2025-01-15', '2025-01-22', 'aa', 'aa', '3', 'aa', 0, 'Disetujui', '2025-01-15 08:26:28', '2025-01-15 08:29:21', NULL, NULL, NULL, 'Belum Diajukan', NULL, 2, 3, 18),
(23, 'Pelatihan 2', 'Pelatihan', 'Joseph', 'Polibatam', '2025-01-15', '2025-01-23', 'aa', 'aa', '3', 'aa', 0, 'Sedang Ditinjau', '2025-01-15 08:26:54', '2025-01-15 08:26:54', NULL, NULL, NULL, 'Belum Diajukan', NULL, 2, 2, 8),
(24, 'Pelatihan 3', 'Pelatihan', 'Baron', 'Polibatam', '2025-01-15', '2025-01-23', 'aa', 'aa', '3', 'aa', 0, 'Ditolak', '2025-01-15 08:27:36', '2025-01-15 08:29:26', NULL, NULL, NULL, 'Belum Diajukan', NULL, 2, 4, 22),
(25, 'Pelatihan 4', 'Pelatihan', 'Cole', 'Polibatam', '2025-01-15', '2025-01-23', 'aa', 'aa', '3', 'aa', 1, 'Disetujui', '2025-01-15 08:27:59', '2025-01-15 08:30:30', 'dfs', 'LPJ_67877209a7f18.pdf', '2025-01-15 15:30:01', 'Disetujui', 'Sertifikat_67877209a91df.pdf', 2, 1, 3),
(26, 'Pelatihan 5', 'Pelatihan', 'Ash', 'Polibatam', '2025-01-16', '2025-01-28', 'aa', 'aa', '3', 'aa', 1, 'Disetujui', '2025-01-15 08:28:31', '2025-01-15 08:30:36', 'fsefse', 'LPJ_67877212b5db4.pdf', '2025-01-15 15:30:10', 'Revisi', 'Sertifikat_67877212b6b80.pdf', 2, 1, 4),
(27, 'Pelatihan 6', 'Pelatihan', 'Bruce', 'Polibatam', '2025-01-15', '2025-01-23', 'aa', 'aa', '3', 'a', 0, 'Sedang Ditinjau', '2025-01-15 08:29:00', '2025-01-15 08:29:00', NULL, NULL, NULL, 'Belum Diajukan', NULL, 2, 3, 18);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `jurusan`
--
ALTER TABLE `jurusan`
  ADD PRIMARY KEY (`jurusan_id`),
  ADD KEY `FK_jurusan_admin` (`admin_id`);

--
-- Indexes for table `program_studi`
--
ALTER TABLE `program_studi`
  ADD PRIMARY KEY (`program_studi_id`),
  ADD KEY `jurusan_id` (`jurusan_id`),
  ADD KEY `FK_program_studi_admin` (`admin_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `fk_admin_id` (`admin_id`);

--
-- Indexes for table `usulan_pelatihan`
--
ALTER TABLE `usulan_pelatihan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_id` (`user_id`),
  ADD KEY `jurusan_id` (`jurusan_id`),
  ADD KEY `program_studi_id` (`program_studi_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jurusan`
--
ALTER TABLE `jurusan`
  MODIFY `jurusan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `program_studi`
--
ALTER TABLE `program_studi`
  MODIFY `program_studi_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `usulan_pelatihan`
--
ALTER TABLE `usulan_pelatihan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `jurusan`
--
ALTER TABLE `jurusan`
  ADD CONSTRAINT `FK_jurusan_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`);

--
-- Constraints for table `program_studi`
--
ALTER TABLE `program_studi`
  ADD CONSTRAINT `FK_program_studi_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`),
  ADD CONSTRAINT `program_studi_ibfk_1` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`jurusan_id`);

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `fk_admin_id` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL;

--
-- Constraints for table `usulan_pelatihan`
--
ALTER TABLE `usulan_pelatihan`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usulan_pelatihan_ibfk_1` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`jurusan_id`),
  ADD CONSTRAINT `usulan_pelatihan_ibfk_2` FOREIGN KEY (`program_studi_id`) REFERENCES `program_studi` (`program_studi_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
