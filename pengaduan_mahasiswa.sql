-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 11 Jun 2025 pada 15.30
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pengaduan_mahasiswa`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `is_active`, `created_at`) VALUES
(1, 'Fasilitas', 'Pengaduan terkait fasilitas kampus', 1, '2025-06-11 18:43:48'),
(2, 'Akademik', 'Pengaduan terkait kegiatan akademik', 1, '2025-06-11 18:43:48'),
(3, 'Layanan', 'Pengaduan terkait layanan kampus', 1, '2025-06-11 18:43:48'),
(4, 'Keuangan', 'Pengaduan terkait keuangan dan pembayaran', 1, '2025-06-11 18:43:48'),
(5, 'Lainnya', 'Pengaduan kategori lainnya', 1, '2025-06-11 18:43:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `complaints`
--

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `nama_pelapor` varchar(100) DEFAULT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') DEFAULT NULL,
  `nim` varchar(20) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `email_pelapor` varchar(100) DEFAULT NULL,
  `tanggal_kejadian` date NOT NULL,
  `lokasi_kejadian` varchar(255) NOT NULL,
  `status` enum('Baru','Diproses','Selesai','Ditolak') NOT NULL DEFAULT 'Baru',
  `tipe_aduan` enum('private','public') NOT NULL DEFAULT 'private',
  `lampiran` varchar(255) DEFAULT NULL,
  `lampiran_path` varchar(500) DEFAULT NULL,
  `date_posted` datetime NOT NULL,
  `last_updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `complaints`
--

INSERT INTO `complaints` (`id`, `user_id`, `category_id`, `title`, `description`, `nama_pelapor`, `jenis_kelamin`, `nim`, `whatsapp`, `email_pelapor`, `tanggal_kejadian`, `lokasi_kejadian`, `status`, `tipe_aduan`, `lampiran`, `lampiran_path`, `date_posted`, `last_updated`) VALUES
(1, NULL, 1, 'test', 'nyoba aja', NULL, NULL, NULL, NULL, NULL, '2025-06-11', 'test', 'Baru', 'public', '1749646823885-493013093.jpg', 'uploads\\complaints\\1749646823885-493013093.jpg', '2025-06-11 13:00:23', '2025-06-11 13:00:23'),
(2, 2, 1, 'nyoba', 'bisa dong', NULL, NULL, NULL, NULL, NULL, '2025-06-11', 'kantin', 'Baru', 'public', '1749647162784-663144518.jpg', 'uploads\\complaints\\1749647162784-663144518.jpg', '2025-06-11 13:06:02', '2025-06-11 13:06:02'),
(3, 2, 1, 'nyoba lagii deh', 'entah lah semoga selesai', NULL, NULL, NULL, NULL, NULL, '2025-06-11', 'kantin', 'Baru', 'public', '1749647215847-989976650.jpg', 'uploads\\complaints\\1749647215847-989976650.jpg', '2025-06-11 13:06:55', '2025-06-11 13:06:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','mahasiswa') NOT NULL DEFAULT 'mahasiswa',
  `full_name` varchar(100) DEFAULT NULL,
  `nim` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `full_name`, `nim`, `phone`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@example.com', '$2a$12$XCJ1aLsLRzQt5KAJ7dkS2u7vLRbVRBIktCZf66VgsKuHPJ.fLKB7O', 'admin', 'Administrator', NULL, NULL, 1, '2025-06-11 18:43:48', '2025-06-11 18:43:48'),
(2, 'rifqi', 'rifqi@gmail.com', '$2a$12$oa8fok2bNYvwPdzqPGJkveRyhRHq2vb8Gf/Sp7DMvNQ2nfwSp6PmO', 'mahasiswa', NULL, NULL, NULL, 1, '2025-06-11 18:51:06', '2025-06-11 18:53:23'),
(3, 'admin1', 'admin1@gmail.com', '$2a$12$NnlsflsWiaGGbMGN/66l6eK/7iqS.blriHwPfeXIGZdATwDKcUwFu', 'admin', NULL, NULL, NULL, 1, '2025-06-11 18:52:54', '2025-06-11 18:52:54');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `name_2` (`name`),
  ADD UNIQUE KEY `name_3` (`name`),
  ADD UNIQUE KEY `name_4` (`name`),
  ADD UNIQUE KEY `name_5` (`name`),
  ADD UNIQUE KEY `name_6` (`name`),
  ADD UNIQUE KEY `name_7` (`name`),
  ADD UNIQUE KEY `name_8` (`name`),
  ADD UNIQUE KEY `name_9` (`name`),
  ADD UNIQUE KEY `name_10` (`name`),
  ADD UNIQUE KEY `name_11` (`name`),
  ADD UNIQUE KEY `name_12` (`name`),
  ADD UNIQUE KEY `name_13` (`name`),
  ADD UNIQUE KEY `name_14` (`name`),
  ADD UNIQUE KEY `name_15` (`name`),
  ADD UNIQUE KEY `name_16` (`name`),
  ADD UNIQUE KEY `name_17` (`name`),
  ADD UNIQUE KEY `name_18` (`name`),
  ADD UNIQUE KEY `name_19` (`name`),
  ADD UNIQUE KEY `name_20` (`name`);

--
-- Indeks untuk tabel `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `complaints_user_id` (`user_id`),
  ADD KEY `complaints_category_id` (`category_id`),
  ADD KEY `complaints_status` (`status`),
  ADD KEY `complaints_tipe_aduan` (`tipe_aduan`),
  ADD KEY `complaints_date_posted` (`date_posted`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username_2` (`username`),
  ADD UNIQUE KEY `email_2` (`email`),
  ADD UNIQUE KEY `username_3` (`username`),
  ADD UNIQUE KEY `email_3` (`email`),
  ADD UNIQUE KEY `username_4` (`username`),
  ADD UNIQUE KEY `email_4` (`email`),
  ADD UNIQUE KEY `username_5` (`username`),
  ADD UNIQUE KEY `email_5` (`email`),
  ADD UNIQUE KEY `username_6` (`username`),
  ADD UNIQUE KEY `email_6` (`email`),
  ADD UNIQUE KEY `username_7` (`username`),
  ADD UNIQUE KEY `email_7` (`email`),
  ADD UNIQUE KEY `username_8` (`username`),
  ADD UNIQUE KEY `email_8` (`email`),
  ADD UNIQUE KEY `username_9` (`username`),
  ADD UNIQUE KEY `email_9` (`email`),
  ADD UNIQUE KEY `username_10` (`username`),
  ADD UNIQUE KEY `email_10` (`email`),
  ADD UNIQUE KEY `username_11` (`username`),
  ADD UNIQUE KEY `email_11` (`email`),
  ADD UNIQUE KEY `username_12` (`username`),
  ADD UNIQUE KEY `email_12` (`email`),
  ADD UNIQUE KEY `username_13` (`username`),
  ADD UNIQUE KEY `email_13` (`email`),
  ADD UNIQUE KEY `username_14` (`username`),
  ADD UNIQUE KEY `email_14` (`email`),
  ADD UNIQUE KEY `username_15` (`username`),
  ADD UNIQUE KEY `email_15` (`email`),
  ADD UNIQUE KEY `username_16` (`username`),
  ADD UNIQUE KEY `email_16` (`email`),
  ADD UNIQUE KEY `username_17` (`username`),
  ADD UNIQUE KEY `email_17` (`email`),
  ADD UNIQUE KEY `username_18` (`username`),
  ADD UNIQUE KEY `email_18` (`email`),
  ADD UNIQUE KEY `username_19` (`username`),
  ADD UNIQUE KEY `email_19` (`email`),
  ADD UNIQUE KEY `username_20` (`username`),
  ADD UNIQUE KEY `email_20` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_39` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `complaints_ibfk_40` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
