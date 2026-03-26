-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 24, 2026 at 02:56 PM
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
-- Database: `enrollment_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `fullname` varchar(100) NOT NULL,
  `role` enum('admin','registrar') NOT NULL DEFAULT 'registrar',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `branch_id` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `is_active`, `fullname`, `role`, `created_at`, `updated_at`, `branch_id`) VALUES
(1, 'admin', '$2y$10$O62Pj265w9m0wfMyy8hm7ucnTvMywbBOI/HYhGZCm4F1ttUtNeRJO', 1, 'System Administrator', 'admin', '2026-03-09 18:49:42', '2026-03-09 18:49:42', 1);

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_type` varchar(20) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `is_main` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `code`, `name`, `address`, `contact_number`, `is_main`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'MAIN', 'Main Campus', 'Default Address', NULL, 1, 1, '2026-03-23 12:45:02', '2026-03-23 12:45:02');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `REGION` text DEFAULT NULL,
  `PROVINCE` text DEFAULT NULL,
  `CITIES_MUNICIPALITIES` text DEFAULT NULL,
  `ZIPCODE` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`REGION`, `PROVINCE`, `CITIES_MUNICIPALITIES`, `ZIPCODE`) VALUES
('REGION XIII', 'AGUSAN DEL NORTE', 'Butuan City', '8600'),
('REGION XIII', 'AGUSAN DEL NORTE', 'Cabadbaran City', '8605'),
('REGION XIII', 'AGUSAN DEL NORTE', 'Buenavista', '8601'),
('REGION XIII', 'AGUSAN DEL NORTE', 'Carmen', '8603'),
('REGION XIII', 'AGUSAN DEL NORTE', 'Jabonga', '8607'),
('REGION XIII', 'AGUSAN DEL NORTE', 'Kitcharao', '8609'),
('REGION XIII', 'AGUSAN DEL NORTE', 'Las Nieves', '8610'),
('REGION XIII', 'AGUSAN DEL NORTE', 'Magallanes', '8604'),
('REGION XIII', 'AGUSAN DEL NORTE', 'Nasipit', '8602'),
('REGION XIII', 'AGUSAN DEL NORTE', 'Remedios T. Romualdez', '8611'),
('REGION XIII', 'AGUSAN DEL NORTE', 'Santiago', '8608'),
('REGION XIII', 'AGUSAN DEL NORTE', 'Tubay', '8606'),
('REGION XIII', 'AGUSAN DEL SUR', 'Bayugan', '8502'),
('REGION XIII', 'AGUSAN DEL SUR', 'Bunawan', '8506'),
('REGION XIII', 'AGUSAN DEL SUR', 'Esperanza', '8513'),
('REGION XIII', 'AGUSAN DEL SUR', 'La Paz', '8508'),
('REGION XIII', 'AGUSAN DEL SUR', 'Loreto', '8507'),
('REGION XIII', 'AGUSAN DEL SUR', 'Prosperidad', '8500'),
('REGION XIII', 'AGUSAN DEL SUR', 'Rosario', '8504'),
('REGION XIII', 'AGUSAN DEL SUR', 'San Francisco', '8501'),
('REGION XIII', 'AGUSAN DEL SUR', 'San Luis', '8511'),
('REGION XIII', 'AGUSAN DEL SUR', 'Santa Josefa', '8512'),
('REGION XIII', 'AGUSAN DEL SUR', 'Sibagat', '8503'),
('REGION XIII', 'AGUSAN DEL SUR', 'Talacogon', '8510'),
('REGION XIII', 'AGUSAN DEL SUR', 'Trento', '8505'),
('REGION XIII', 'AGUSAN DEL SUR', 'Veruela', '8509'),
('REGION XIII', 'SURIGAO DEL NORTE', 'Surigao City', '8400'),
('REGION XIII', 'SURIGAO DEL NORTE', 'Alegria', '8425'),
('REGION XIII', 'SURIGAO DEL NORTE', 'Bacuag', '8408'),
('REGION XIII', 'SURIGAO DEL NORTE', 'Burgos', '8424'),
('REGION XIII', 'SURIGAO DEL NORTE', 'Claver', '8410'),
('REGION XIII', 'SURIGAO DEL NORTE', 'Dapa', '8417'),
('REGION XIII', 'SURIGAO DEL NORTE', 'Del Carmen', '8418'),
('REGION XIII', 'SURIGAO DEL NORTE', 'General Luna', '8419'),
('REGION XIII', 'SURIGAO DEL NORTE', 'Gigaquit', '8409'),
('REGION XIII', 'SURIGAO DEL NORTE', 'Mainit', '8407'),
('REGION XIII', 'SURIGAO DEL NORTE', 'Malimano', '8402'),
('REGION XIII', 'SURIGAO DEL NORTE', 'Pilar', '8420'),
('REGION XIII', 'SURIGAO DEL NORTE', 'Placer', '8405'),
('REGION XIII', 'SURIGAO DEL NORTE', 'San Benito', '8423'),
('REGION XIII', 'SURIGAO DEL NORTE', 'San Francisco', '8401'),
('REGION XIII', 'SURIGAO DEL NORTE', 'San Isidro', '8421'),
('REGION XIII', 'SURIGAO DEL NORTE', 'Santa Monica', '8422'),
('REGION XIII', 'SURIGAO DEL NORTE', 'Sison', '8404'),
('REGION XIII', 'SURIGAO DEL NORTE', 'Socorro', '8416'),
('REGION XIII', 'SURIGAO DEL NORTE', 'Tagana-an', '8403'),
('REGION XIII', 'SURIGAO DEL NORTE', 'Tubod', '8406'),
('REGION XIII', 'SURIGAO DEL SUR', 'Bislig City', '8311'),
('REGION XIII', 'SURIGAO DEL SUR', 'Tandag City', '8300'),
('REGION XIII', 'SURIGAO DEL SUR', 'Barobo', '8309'),
('REGION XIII', 'SURIGAO DEL SUR', 'Bayabas', '8303'),
('REGION XIII', 'SURIGAO DEL SUR', 'Cagwait', '8304'),
('REGION XIII', 'SURIGAO DEL SUR', 'Cantilan', '8317'),
('REGION XIII', 'SURIGAO DEL SUR', 'Carmen', '8315'),
('REGION XIII', 'SURIGAO DEL SUR', 'Carrascal', '8318'),
('REGION XIII', 'SURIGAO DEL SUR', 'Cortes', '8313'),
('REGION XIII', 'SURIGAO DEL SUR', 'Hinatuan', '8310'),
('REGION XIII', 'SURIGAO DEL SUR', 'Lanuza', '8314'),
('REGION XIII', 'SURIGAO DEL SUR', 'Lianga', '8307'),
('REGION XIII', 'SURIGAO DEL SUR', 'Lingig', '8312'),
('REGION XIII', 'SURIGAO DEL SUR', 'Madrid', '8316'),
('REGION XIII', 'SURIGAO DEL SUR', 'Marihatag', '8306'),
('REGION XIII', 'SURIGAO DEL SUR', 'San Agustin', '8305'),
('REGION XIII', 'SURIGAO DEL SUR', 'San Miguel', '8301'),
('REGION XIII', 'SURIGAO DEL SUR', 'Tagbina', '8308'),
('REGION XIII', 'SURIGAO DEL SUR', 'Tago', '8302'),
('REGION XIII', 'DINAGAT ISLANDS', 'Basilisa (Rizal)', '8412'),
('REGION XIII', 'DINAGAT ISLANDS', 'Cagdianao', '8412'),
('REGION XIII', 'DINAGAT ISLANDS', 'Dinagat', '8412'),
('REGION XIII', 'DINAGAT ISLANDS', 'Libjo (Albor)', '8412'),
('REGION XIII', 'DINAGAT ISLANDS', 'Loreto', '8412'),
('REGION XIII', 'DINAGAT ISLANDS', 'San Jose', '8412'),
('REGION XIII', 'DINAGAT ISLANDS', 'Tubajon', '8412'),
('BARMM', 'BASILAN', 'Isabela City', '7300'),
('BARMM', 'BASILAN', 'Lamitan City', '7302'),
('BARMM', 'BASILAN', 'Akbar', '7300'),
('BARMM', 'BASILAN', 'Al-Barka', '7300'),
('BARMM', 'BASILAN', 'Hadji Mohammad Aju', '7300'),
('BARMM', 'BASILAN', 'Lantawan', '7301'),
('BARMM', 'BASILAN', 'Maluso', '7303'),
('BARMM', 'BASILAN', 'Sumisip', '7305'),
('BARMM', 'BASILAN', 'Tipo-Tipo', '7304'),
('BARMM', 'BASILAN', 'Tuburan', '7306'),
('BARMM', 'BASILAN', 'Ungkaya Pukan', '7300'),
('BARMM', 'LANAO DEL SUR', 'Marawi City', '9700'),
('BARMM', 'LANAO DEL SUR', 'Bacolod-Kalawi', '9316'),
('BARMM', 'LANAO DEL SUR', 'Balabagan', '9302'),
('BARMM', 'LANAO DEL SUR', 'Balindong', '9318'),
('BARMM', 'LANAO DEL SUR', 'Bayang', '9309'),
('BARMM', 'LANAO DEL SUR', 'Binidayan', '9310'),
('BARMM', 'LANAO DEL SUR', 'Buadiposo-Buntong', '9714'),
('BARMM', 'LANAO DEL SUR', 'Bubong', '9708'),
('BARMM', 'LANAO DEL SUR', 'Bumbaran', '9320'),
('BARMM', 'LANAO DEL SUR', 'Butig', '9305'),
('BARMM', 'LANAO DEL SUR', 'Calanogas', '9319'),
('BARMM', 'LANAO DEL SUR', 'Ditsaan-Ramain', '9713'),
('BARMM', 'LANAO DEL SUR', 'Ganassi', '9311'),
('BARMM', 'LANAO DEL SUR', 'Kapai', '9709'),
('BARMM', 'LANAO DEL SUR', 'Kapatagan', '9700'),
('BARMM', 'LANAO DEL SUR', 'Lumba-Bayabao', '9703'),
('BARMM', 'LANAO DEL SUR', 'Lumbaca-Unayan', '9308'),
('BARMM', 'LANAO DEL SUR', 'Lumbatan', '9307'),
('BARMM', 'LANAO DEL SUR', 'Lumbayanague', '9306'),
('BARMM', 'LANAO DEL SUR', 'Madalum', '9315'),
('BARMM', 'LANAO DEL SUR', 'Madamba', '9314'),
('BARMM', 'LANAO DEL SUR', 'Maguing', '9715'),
('BARMM', 'LANAO DEL SUR', 'Malabang', '9300'),
('BARMM', 'LANAO DEL SUR', 'Marantao', '9711'),
('BARMM', 'LANAO DEL SUR', 'Marogong', '9303'),
('BARMM', 'LANAO DEL SUR', 'Masiu', '9706'),
('BARMM', 'LANAO DEL SUR', 'Mulondo', '9702'),
('BARMM', 'LANAO DEL SUR', 'Pagayawan', '9312'),
('BARMM', 'LANAO DEL SUR', 'Piagapo', '9710'),
('BARMM', 'LANAO DEL SUR', 'Poona Bayabao', '9705'),
('BARMM', 'LANAO DEL SUR', 'Pualas', '9313'),
('BARMM', 'LANAO DEL SUR', 'Saguiaran', '9701'),
('BARMM', 'LANAO DEL SUR', 'Sultan Dumalondong', '9301'),
('BARMM', 'LANAO DEL SUR', 'Picong', '9301'),
('BARMM', 'LANAO DEL SUR', 'Tagoloan II', '9321'),
('BARMM', 'LANAO DEL SUR', 'Tamparan', '9704'),
('BARMM', 'LANAO DEL SUR', 'Taraka', '9712'),
('BARMM', 'LANAO DEL SUR', 'Tubaran', '9304'),
('BARMM', 'LANAO DEL SUR', 'Tugaya', '9317'),
('BARMM', 'LANAO DEL SUR', 'Wao', '9716'),
('BARMM', 'MAGUINDANAO', 'Cotabato City', '9600'),
('BARMM', 'MAGUINDANAO', 'Ampatuan', '9606'),
('BARMM', 'MAGUINDANAO', 'Buluan', '9616'),
('BARMM', 'MAGUINDANAO', 'Datu Abdullah Sangki', '9621'),
('BARMM', 'MAGUINDANAO', 'Datu Anggal Midtimbang', '9622'),
('BARMM', 'MAGUINDANAO', 'Datu Paglas', '9617'),
('BARMM', 'MAGUINDANAO', 'Datu Piang', '9607'),
('BARMM', 'MAGUINDANAO', 'Datu Saudi-Ampatuan', '9626'),
('BARMM', 'MAGUINDANAO', 'Datu Unsay', '9627'),
('BARMM', 'MAGUINDANAO', 'Gen. S. K. Pendatun', '9618'),
('BARMM', 'MAGUINDANAO', 'Guindulungan', '9628'),
('BARMM', 'MAGUINDANAO', 'Mamasapano', '9629'),
('BARMM', 'MAGUINDANAO', 'Mangudadatu', '9620'),
('BARMM', 'MAGUINDANAO', 'Pagagawan', '9631'),
('BARMM', 'MAGUINDANAO', 'Pagalungan', '9610'),
('BARMM', 'MAGUINDANAO', 'Paglat', '9632'),
('BARMM', 'MAGUINDANAO', 'Pandag', '9633'),
('BARMM', 'MAGUINDANAO', 'Rajah Buayan', '9634'),
('BARMM', 'MAGUINDANAO', 'Shariff Aguak', '9635'),
('BARMM', 'MAGUINDANAO', 'South Upi', '9603'),
('BARMM', 'MAGUINDANAO', 'Sultan sa Barongis', '9611'),
('BARMM', 'MAGUINDANAO', 'Talayan', '9612'),
('BARMM', 'MAGUINDANAO', 'Talitay', '9637'),
('BARMM', 'SULU', 'Hadji Panglima Tahil', '7413'),
('BARMM', 'SULU', 'Indanan', '7407'),
('BARMM', 'SULU', 'Jolo', '7400'),
('BARMM', 'SULU', 'Kalingalan Caluang', '7416'),
('BARMM', 'SULU', 'Lugus', '7411'),
('BARMM', 'SULU', 'Luuk', '7404'),
('BARMM', 'SULU', 'Maimbung', '7409'),
('BARMM', 'SULU', 'Old Panamao', '7402'),
('BARMM', 'SULU', 'Omar', '7404'),
('BARMM', 'SULU', 'Pandami', '7400'),
('BARMM', 'SULU', 'Panglima Estino', '7415'),
('BARMM', 'SULU', 'Pangutaran', '7414'),
('BARMM', 'SULU', 'Parang', '7408'),
('BARMM', 'SULU', 'Pata', '7405'),
('BARMM', 'SULU', 'Patikul', '7401'),
('BARMM', 'SULU', 'Siasi', '7412'),
('BARMM', 'SULU', 'Talipao', '7403'),
('BARMM', 'SULU', 'Tapul', '7410'),
('BARMM', 'SULU', 'Tongkil', '7406'),
('BARMM', 'TAWI-TAWI', 'Bongao', '7500'),
('BARMM', 'TAWI-TAWI', 'Languyan', '7509'),
('BARMM', 'TAWI-TAWI', 'Mapun', '7500'),
('BARMM', 'TAWI-TAWI', 'Panglima Sugala', '7500'),
('BARMM', 'TAWI-TAWI', 'Sapa-Sapa', '7503'),
('BARMM', 'TAWI-TAWI', 'Sibutu', '7500'),
('BARMM', 'TAWI-TAWI', 'Simunul', '7505'),
('BARMM', 'TAWI-TAWI', 'Sitangkai', '7506'),
('BARMM', 'TAWI-TAWI', 'South Ubian', '7504'),
('BARMM', 'TAWI-TAWI', 'Tandubas', '7502'),
('BARMM', 'TAWI-TAWI', 'Turtle Islands', '7500'),
('BARMM', 'MAGUINDANAO', 'Barira', '9613'),
('BARMM', 'MAGUINDANAO', 'Buldon', '9615'),
('BARMM', 'MAGUINDANAO', 'Datu Blah T. Sinsuat', '9623'),
('BARMM', 'MAGUINDANAO', 'Datu Odin Sinsuat', '9601'),
('BARMM', 'MAGUINDANAO', 'Kabuntalan', '9606'),
('BARMM', 'MAGUINDANAO', 'Matanog', '9613'),
('BARMM', 'MAGUINDANAO', 'Northern Kabuntalan', '9630'),
('BARMM', 'MAGUINDANAO', 'Parang', '9604'),
('BARMM', 'MAGUINDANAO', 'Sultan Kudarat', '9605'),
('BARMM', 'MAGUINDANAO', 'Sultan Mastura', '9636'),
('BARMM', 'MAGUINDANAO', 'Upi', '9602'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'Dapitan City', '7101'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'Dipolog City', '7100'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'Bacungan', '7100'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'Baliguian', '7123'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'Godod', '7100'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'Gutalac', '7118'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'Jose Dalman', '7111'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'Kalawit', '7124'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'Katipunan', '7109'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'La Libertad', '7119'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'Labason', '7117'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'Liloy', '7115'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'Manukan', '7110'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'Mutia', '7107'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'Piñan', '7105'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'Polanco', '7106'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'Pres. Manuel A. Roxas', '7102'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'Rizal', '7104'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'Salug', '7114'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'Sergio Osmeña Sr.', '7108'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'Siayan', '7113'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'Sibuco', '7122'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'Sibutad', '7103'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'Sindangan', '7112'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'Siocon', '7120'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'Sirawai', '7121'),
('REGION IX', 'ZAMBOANGA DEL NORTE', 'Tampilisan', '7116'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'Pagadian City', '7016'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'Zamboanga City', '7000'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'Aurora', '7020'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'Bayog', '7011'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'Dimataling', '7032'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'Dinas', '7030'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'Dumalinao', '7015'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'Dumingag', '7028'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'Guipos', '7042'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'Josefina', '7027'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'Kumalarang', '7013'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'Labangan', '7017'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'Lakewood', '7014'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'Lapuyan', '7037'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'Mahayag', '7026'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'Margosatubig', '7035'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'Midsalip', '7021'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'Molave', '7023'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'Pitogo', '7033'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'Ramon Magsaysay', '7024'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'San Miguel', '7029'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'San Pablo', '7031'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'Sominot', '7022'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'Tabina', '7034'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'Tambulig', '7025'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'Tigbao', '7043'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'Tukuran', '7019'),
('REGION IX', 'ZAMBOANGA DEL SUR', 'Vincenzo A. Sagun', '7036'),
('REGION IX', 'ZAMBOANGA SIBUGAY', 'Alicia', '7040'),
('REGION IX', 'ZAMBOANGA SIBUGAY', 'Buug', '7009'),
('REGION IX', 'ZAMBOANGA SIBUGAY', 'Diplahan', '7039'),
('REGION IX', 'ZAMBOANGA SIBUGAY', 'Imelda', '7007'),
('REGION IX', 'ZAMBOANGA SIBUGAY', 'Ipil', '7001'),
('REGION IX', 'ZAMBOANGA SIBUGAY', 'Kabasalan', '7005'),
('REGION IX', 'ZAMBOANGA SIBUGAY', 'Mabuhay', '7010'),
('REGION IX', 'ZAMBOANGA SIBUGAY', 'Malangas', '7038'),
('REGION IX', 'ZAMBOANGA SIBUGAY', 'Naga', '7004'),
('REGION IX', 'ZAMBOANGA SIBUGAY', 'Olutanga', '7041'),
('REGION IX', 'ZAMBOANGA SIBUGAY', 'Payao', '7008'),
('REGION IX', 'ZAMBOANGA SIBUGAY', 'Roseller Lim', '7002'),
('REGION IX', 'ZAMBOANGA SIBUGAY', 'Siay', '7006'),
('REGION IX', 'ZAMBOANGA SIBUGAY', 'Talusan', '7012'),
('REGION IX', 'ZAMBOANGA SIBUGAY', 'Titay', '7003'),
('REGION IX', 'ZAMBOANGA SIBUGAY', 'Tungawan', '7018'),
('REGION X', 'BUKIDNON', 'Malaybalay City', '8700'),
('REGION X', 'BUKIDNON', 'Valencia City', '8709'),
('REGION X', 'BUKIDNON', 'Baungon', '8707'),
('REGION X', 'BUKIDNON', 'Cabanglasan', '8723'),
('REGION X', 'BUKIDNON', 'Damulog', '8721'),
('REGION X', 'BUKIDNON', 'Dangcagan', '8719'),
('REGION X', 'BUKIDNON', 'Don Carlos', '8712'),
('REGION X', 'BUKIDNON', 'Impasug-ong', '8702'),
('REGION X', 'BUKIDNON', 'Kadingilan', '8713'),
('REGION X', 'BUKIDNON', 'Kalilangan', '8718'),
('REGION X', 'BUKIDNON', 'Kibawe', '8720'),
('REGION X', 'BUKIDNON', 'Kitaotao', '8716'),
('REGION X', 'BUKIDNON', 'Lantapan', '8722'),
('REGION X', 'BUKIDNON', 'Libona', '8706'),
('REGION X', 'BUKIDNON', 'Malitbog', '8704'),
('REGION X', 'BUKIDNON', 'Manolo Fortich', '8703'),
('REGION X', 'BUKIDNON', 'Maramag', '8714'),
('REGION X', 'BUKIDNON', 'Pangantucan', '8717'),
('REGION X', 'BUKIDNON', 'Quezon', '8715'),
('REGION X', 'BUKIDNON', 'San Fernando', '8711'),
('REGION X', 'BUKIDNON', 'Sumilao', '8701'),
('REGION X', 'BUKIDNON', 'Talakag', '8708'),
('REGION X', 'CAMIGUIN', 'Catarman', '9104'),
('REGION X', 'CAMIGUIN', 'Guinsiliban', '9102'),
('REGION X', 'CAMIGUIN', 'Mahinog', '9101'),
('REGION X', 'CAMIGUIN', 'Mambajao', '9100'),
('REGION X', 'CAMIGUIN', 'Sagay', '9103'),
('REGION X', 'LANAO DEL NORTE', 'Iligan City', '9200'),
('REGION X', 'LANAO DEL NORTE', 'Bacolod', '9205'),
('REGION X', 'LANAO DEL NORTE', 'Baloi', '9217'),
('REGION X', 'LANAO DEL NORTE', 'Baroy', '9210'),
('REGION X', 'LANAO DEL NORTE', 'Kapatagan', '9214'),
('REGION X', 'LANAO DEL NORTE', 'Kauswagan', '9202'),
('REGION X', 'LANAO DEL NORTE', 'Kolambugan', '9207'),
('REGION X', 'LANAO DEL NORTE', 'Lala', '9211'),
('REGION X', 'LANAO DEL NORTE', 'Linamon', '9201'),
('REGION X', 'LANAO DEL NORTE', 'Magsaysay', '9221'),
('REGION X', 'LANAO DEL NORTE', 'Maigo', '9206'),
('REGION X', 'LANAO DEL NORTE', 'Matungao', '9203'),
('REGION X', 'LANAO DEL NORTE', 'Munai', '9219'),
('REGION X', 'LANAO DEL NORTE', 'Nunungan', '9216'),
('REGION X', 'LANAO DEL NORTE', 'Pantao Ragat', '9208'),
('REGION X', 'LANAO DEL NORTE', 'Pantar', '9218'),
('REGION X', 'LANAO DEL NORTE', 'Poona Piagapo', '9204'),
('REGION X', 'LANAO DEL NORTE', 'Salvador', '9212'),
('REGION X', 'LANAO DEL NORTE', 'Sapad', '9213'),
('REGION X', 'LANAO DEL NORTE', 'Sultan Naga Dimaporo', '9220'),
('REGION X', 'LANAO DEL NORTE', 'Tagoloan', '9222'),
('REGION X', 'LANAO DEL NORTE', 'Tangcal', '9220'),
('REGION X', 'LANAO DEL NORTE', 'Tubod', '9209'),
('REGION X', 'MISAMIS OCCIDENTAL', 'Oroquieta City', '7207'),
('REGION X', 'MISAMIS OCCIDENTAL', 'Ozamiz City', '7200'),
('REGION X', 'MISAMIS OCCIDENTAL', 'Tangub City', '7214'),
('REGION X', 'MISAMIS OCCIDENTAL', 'Aloran', '7206'),
('REGION X', 'MISAMIS OCCIDENTAL', 'Baliangao', '7211'),
('REGION X', 'MISAMIS OCCIDENTAL', 'Bonifacio', '7215'),
('REGION X', 'MISAMIS OCCIDENTAL', 'Calamba', '7210'),
('REGION X', 'MISAMIS OCCIDENTAL', 'Clarin', '7201'),
('REGION X', 'MISAMIS OCCIDENTAL', 'Concepcion', '7213'),
('REGION X', 'MISAMIS OCCIDENTAL', 'Don Victoriano Chiongbian', '7214'),
('REGION X', 'MISAMIS OCCIDENTAL', 'Jimenez', '7204'),
('REGION X', 'MISAMIS OCCIDENTAL', 'Lopez Jaena', '7208'),
('REGION X', 'MISAMIS OCCIDENTAL', 'Panaon', '7205'),
('REGION X', 'MISAMIS OCCIDENTAL', 'Plaridel', '7209'),
('REGION X', 'MISAMIS OCCIDENTAL', 'Sapang Dalaga', '7212'),
('REGION X', 'MISAMIS OCCIDENTAL', 'Sinacaban', '7203'),
('REGION X', 'MISAMIS OCCIDENTAL', 'Tudela', '7202'),
('REGION X', 'MISAMIS ORIENTAL', 'Cagayan de Oro', '9000'),
('REGION X', 'MISAMIS ORIENTAL', 'Gingoog City', '9014'),
('REGION X', 'MISAMIS ORIENTAL', 'El Salvador City', '9017'),
('REGION X', 'MISAMIS ORIENTAL', 'Alubijid', '9018'),
('REGION X', 'MISAMIS ORIENTAL', 'Balingasag', '9005'),
('REGION X', 'MISAMIS ORIENTAL', 'Balingoan', '9011'),
('REGION X', 'MISAMIS ORIENTAL', 'Binuangan', '9008'),
('REGION X', 'MISAMIS ORIENTAL', 'Claveria', '9004'),
('REGION X', 'MISAMIS ORIENTAL', 'Gitagum', '9020'),
('REGION X', 'MISAMIS ORIENTAL', 'Initao', '9022'),
('REGION X', 'MISAMIS ORIENTAL', 'Jasaan', '9003'),
('REGION X', 'MISAMIS ORIENTAL', 'Kinoguitan', '9010'),
('REGION X', 'MISAMIS ORIENTAL', 'Lagonglong', '9006'),
('REGION X', 'MISAMIS ORIENTAL', 'Laguindingan', '9019'),
('REGION X', 'MISAMIS ORIENTAL', 'Libertad', '9021'),
('REGION X', 'MISAMIS ORIENTAL', 'Lugait', '9025'),
('REGION X', 'MISAMIS ORIENTAL', 'Magsaysay', '9015'),
('REGION X', 'MISAMIS ORIENTAL', 'Manticao', '9024'),
('REGION X', 'MISAMIS ORIENTAL', 'Medina', '9013'),
('REGION X', 'MISAMIS ORIENTAL', 'Naawan', '9023'),
('REGION X', 'MISAMIS ORIENTAL', 'Opol', '9016'),
('REGION X', 'MISAMIS ORIENTAL', 'Salay', '9007'),
('REGION X', 'MISAMIS ORIENTAL', 'Sugbongcogon', '9009'),
('REGION X', 'MISAMIS ORIENTAL', 'Tagoloan', '9001'),
('REGION X', 'MISAMIS ORIENTAL', 'Talisayan', '9012'),
('REGION X', 'MISAMIS ORIENTAL', 'Villanueva', '9002'),
('REGION XI', 'DAVAO DEL NORTE', 'Island Garden City of Samal', '8119'),
('REGION XI', 'DAVAO DEL NORTE', 'Panabo City', '8105'),
('REGION XI', 'DAVAO DEL NORTE', 'Tagum City', '8100'),
('REGION XI', 'DAVAO DEL NORTE', 'Asuncion', '8102'),
('REGION XI', 'DAVAO DEL NORTE', 'Braulio E. Dujali', '8100'),
('REGION XI', 'DAVAO DEL NORTE', 'Carmen', '8101'),
('REGION XI', 'DAVAO DEL NORTE', 'Kapalong', '8113'),
('REGION XI', 'DAVAO DEL NORTE', 'New Corella', '8100'),
('REGION XI', 'DAVAO DEL NORTE', 'San Isidro', '8100'),
('REGION XI', 'DAVAO DEL NORTE', 'Santo Tomas', '8112'),
('REGION XI', 'DAVAO DEL NORTE', 'Talaingod', '8100'),
('REGION XI', 'DAVAO DEL SUR', 'Davao City', '8000'),
('REGION XI', 'DAVAO DEL SUR', 'Digos City', '8002'),
('REGION XI', 'DAVAO DEL SUR', 'Bansalan', '8005'),
('REGION XI', 'DAVAO DEL SUR', 'Don Marcelino', '8013'),
('REGION XI', 'DAVAO DEL SUR', 'Hagonoy', '8006'),
('REGION XI', 'DAVAO DEL SUR', 'Jose Abad Santos', '8014'),
('REGION XI', 'DAVAO DEL SUR', 'Kiblawan', '8008'),
('REGION XI', 'DAVAO DEL SUR', 'Magsaysay', '8004'),
('REGION XI', 'DAVAO DEL SUR', 'Malalag', '8010'),
('REGION XI', 'DAVAO DEL SUR', 'Malita', '8012'),
('REGION XI', 'DAVAO DEL SUR', 'Matanao', '8003'),
('REGION XI', 'DAVAO DEL SUR', 'Padada', '8007'),
('REGION XI', 'DAVAO DEL SUR', 'Santa Cruz', '8001'),
('REGION XI', 'DAVAO DEL SUR', 'Santa Maria', '8011'),
('REGION XI', 'DAVAO DEL SUR', 'Sarangani', '8015'),
('REGION XI', 'DAVAO DEL SUR', 'Sulop', '8009'),
('REGION XI', 'DAVAO ORIENTAL', 'Mati City', '8200'),
('REGION XI', 'DAVAO ORIENTAL', 'Baganga', '8204'),
('REGION XI', 'DAVAO ORIENTAL', 'Banaybanay', '8208'),
('REGION XI', 'DAVAO ORIENTAL', 'Boston', '8206'),
('REGION XI', 'DAVAO ORIENTAL', 'Caraga', '8203'),
('REGION XI', 'DAVAO ORIENTAL', 'Cateel', '8205'),
('REGION XI', 'DAVAO ORIENTAL', 'Governor Generoso', '8210'),
('REGION XI', 'DAVAO ORIENTAL', 'Lupon', '8207'),
('REGION XI', 'DAVAO ORIENTAL', 'Manay', '8202'),
('REGION XI', 'DAVAO ORIENTAL', 'San Isidro', '8209'),
('REGION XI', 'DAVAO ORIENTAL', 'Tarragona', '8201'),
('REGION XI', 'DAVAO DE ORO', 'Compostela', '8803'),
('REGION XI', 'DAVAO DE ORO', 'Laak', '8810'),
('REGION XI', 'DAVAO DE ORO', 'Mabini', '8807'),
('REGION XI', 'DAVAO DE ORO', 'Maco', '8806'),
('REGION XI', 'DAVAO DE ORO', 'Maragusan', '8808'),
('REGION XI', 'DAVAO DE ORO', 'Mawab', '8802'),
('REGION XI', 'DAVAO DE ORO', 'Monkayo', '8805'),
('REGION XI', 'DAVAO DE ORO', 'Montevista', '8801'),
('REGION XI', 'DAVAO DE ORO', 'Nabunturan', '8800'),
('REGION XI', 'DAVAO DE ORO', 'New Bataan', '8804'),
('REGION XI', 'DAVAO DE ORO', 'Pantukan', '8809'),
('REGION XII', 'COTABATO', 'Kidapawan City', '9400'),
('REGION XII', 'COTABATO', 'Alamada', '9413'),
('REGION XII', 'COTABATO', 'Aleosan', '9415'),
('REGION XII', 'COTABATO', 'Antipas', '9414'),
('REGION XII', 'COTABATO', 'Arakan', '9417'),
('REGION XII', 'COTABATO', 'Banisilan', '9416'),
('REGION XII', 'COTABATO', 'Carmen', '9408'),
('REGION XII', 'COTABATO', 'Kabacan', '9407'),
('REGION XII', 'COTABATO', 'Libungan', '9411'),
('REGION XII', 'COTABATO', 'M\'lang', '9402'),
('REGION XII', 'COTABATO', 'Magpet', '9404'),
('REGION XII', 'COTABATO', 'Makilala', '9401'),
('REGION XII', 'COTABATO', 'Matalam', '9406'),
('REGION XII', 'COTABATO', 'Midsayap', '9410'),
('REGION XII', 'COTABATO', 'Pigcawayan', '9412'),
('REGION XII', 'COTABATO', 'Pikit', '9409'),
('REGION XII', 'COTABATO', 'President Roxas', '9405'),
('REGION XII', 'COTABATO', 'Tulunan', '9403'),
('REGION XII', 'SOUTH COTABATO', 'General Santos City', '9500'),
('REGION XII', 'SOUTH COTABATO', 'Koronadal City', '9506'),
('REGION XII', 'SOUTH COTABATO', 'Banga', '9511'),
('REGION XII', 'SOUTH COTABATO', 'Lake Sebu', '9514'),
('REGION XII', 'SOUTH COTABATO', 'Norala', '9508'),
('REGION XII', 'SOUTH COTABATO', 'Polomolok', '9504'),
('REGION XII', 'SOUTH COTABATO', 'Santo Niño', '9509'),
('REGION XII', 'SOUTH COTABATO', 'Surallah', '9512'),
('REGION XII', 'SOUTH COTABATO', 'T\'boli', '9513'),
('REGION XII', 'SOUTH COTABATO', 'Tampakan', '9507'),
('REGION XII', 'SOUTH COTABATO', 'Tantangan', '9510'),
('REGION XII', 'SOUTH COTABATO', 'Tupi', '9505'),
('REGION XII', 'SULTAN KUDARAT', 'Tacurong City', '9800'),
('REGION XII', 'SULTAN KUDARAT', 'Bagumbayan', '9810'),
('REGION XII', 'SULTAN KUDARAT', 'Columbio', '9801'),
('REGION XII', 'SULTAN KUDARAT', 'Esperanza', '9806'),
('REGION XII', 'SULTAN KUDARAT', 'Isulan', '9805'),
('REGION XII', 'SULTAN KUDARAT', 'Kalamansig', '9808'),
('REGION XII', 'SULTAN KUDARAT', 'Lambayong', '9802'),
('REGION XII', 'SULTAN KUDARAT', 'Lebak', '9807'),
('REGION XII', 'SULTAN KUDARAT', 'Lutayan', '9803'),
('REGION XII', 'SULTAN KUDARAT', 'Palimbang', '9809'),
('REGION XII', 'SULTAN KUDARAT', 'President Quirino', '9804'),
('REGION XII', 'SULTAN KUDARAT', 'Sen. Ninoy Aquino', '9811'),
('REGION XII', 'SARANGANI', 'Alabel', '9501'),
('REGION XII', 'SARANGANI', 'Glan', '9517'),
('REGION XII', 'SARANGANI', 'Kiamba', '9514'),
('REGION XII', 'SARANGANI', 'Maasim', '9502'),
('REGION XII', 'SARANGANI', 'Maitum', '9515'),
('REGION XII', 'SARANGANI', 'Malapatan', '9516'),
('REGION XII', 'SARANGANI', 'Malungon', '9503');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `major` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `code`, `name`, `department_id`, `major`, `created_at`, `updated_at`) VALUES
(1, 'BSCE', '- Bachelor of Science in Civil Engineering', 4, '', NULL, NULL),
(2, 'BSCS', '- Bachelor of Science in Computer Science', 1, '', NULL, NULL),
(3, 'BSCA', '- Bachelor of Science in Customs Administration', 7, '', NULL, NULL),
(4, 'BSSW', '- Bachelor of Science in Social Work', 5, '', NULL, NULL),
(5, 'AB', '- Bachelor of Arts Social Science', 5, 'Social Science', NULL, NULL),
(6, 'AB', 'Bachelor of Arts', 5, 'Filipino', NULL, NULL),
(7, 'AB', 'Bachelor of Arts', 5, 'Economics', NULL, NULL),
(8, 'AB', 'Bachelor of Arts', 5, 'English', NULL, NULL),
(9, 'AB', 'Bachelor of Arts', 5, 'Mass Communication', NULL, NULL),
(10, 'BSOA', '- Bachelor of Science in Office Administration Office Management', 7, 'Office Management', NULL, NULL),
(11, 'BSOA', 'Bachelor of Science in Office Administration', 7, 'Computer Education', NULL, NULL),
(12, 'AOA', '- Associate in Office Administration', 7, '', NULL, NULL),
(13, 'BSCR', '- Bachelor of Science in Criminology', 8, '', NULL, NULL),
(14, 'BSA', '- Bachelor of Science in Accountancy', 2, '', NULL, NULL),
(15, 'BEED', '- Bachelor of Elementary Education Filipino', 6, 'Filipino', NULL, NULL),
(16, 'BSED', '- Bachelor of Secondary Education Science', 6, 'Math', NULL, NULL),
(17, 'BSED', 'Bachelor of Secondary Education', 6, 'Science', NULL, NULL),
(18, 'BSED', 'Bachelor of Secondary Education', 6, 'Filipino', NULL, NULL),
(19, 'BSED', 'Bachelor of Secondary Education', 6, 'English', NULL, NULL),
(20, 'MA', 'Master of Arts', NULL, 'English', NULL, NULL),
(21, 'MA', 'Master of Arts', NULL, 'Guidance and Counseling', NULL, NULL),
(22, 'MA', 'Master of Arts', NULL, 'Educational Management', NULL, NULL),
(23, 'BSIT', '- Bachelor of Science in Information Technology', 1, '', NULL, NULL),
(24, 'BSBA', '- Bachelor of Science in Business Administration Management Accounting', 7, 'Management Accounting', NULL, NULL),
(25, 'BSIM', '- Bachelor of Science in Information Management', 1, '', NULL, NULL),
(26, 'ACT', '- Associates in Computer Technology', 4, '', NULL, NULL),
(27, 'CT', '- Computer System and Network Specialist', 4, '', NULL, NULL),
(28, 'MT', 'Medical Transcriptionist', 7, '', NULL, NULL),
(29, 'CC', '- Contact Center', 7, '', NULL, NULL),
(30, 'CHS', '- Computer Hardware Servicing NCII', 4, '', NULL, NULL),
(31, 'BSGE', '- Bachelor of Science in Geodetic Engineering', 4, '', NULL, NULL),
(32, 'BSES', '- Bachelor of Science in Environmental Science', 5, '', NULL, NULL),
(33, 'BSMATH', '- Bachelor of Science in Mathematics', 5, '', NULL, NULL),
(34, 'BPE', '- Bachelor of Physical Education School Physical Education', 6, 'School Physical Education', NULL, NULL),
(35, 'SHS', 'Senior High School', NULL, 'ABM', NULL, NULL),
(36, 'SHS', 'Senior High School', NULL, 'HUMSS', NULL, NULL),
(37, 'SHS', 'Senior High School', NULL, 'STEM', NULL, NULL),
(38, 'SHS', 'Senior High School', NULL, 'GAS', NULL, NULL),
(39, 'BLIS', '- Bachelor of Library and Information Science', 5, '', NULL, NULL),
(40, 'BSMA', '- Bachelor of Science in Management Accounting', 7, '', NULL, NULL),
(41, 'BECED', '- Bachelor of Early Childhood Education', 6, '', NULL, NULL),
(42, 'BSIA', '- Bachelor of Science in Internal Auditing', 2, '', NULL, NULL),
(43, 'BSAIS', '- Bachelor of Science in Accounting Information System', 7, '', NULL, NULL),
(44, 'GRADE', 'Grade School', NULL, 'Grade 1', NULL, NULL),
(45, 'GRADE', 'Grade School', NULL, 'Grade 2', NULL, NULL),
(46, 'GRADE', 'Grade School', NULL, 'Grade 3', NULL, NULL),
(47, 'GRADE', 'Grade School', NULL, 'Grade 4', NULL, NULL),
(48, 'GRADE', 'Grade School', NULL, 'Grade 5', NULL, NULL),
(49, 'GRADE', 'Grade School', NULL, 'Grade 6', NULL, NULL),
(50, 'JHIGH', 'Junior High School', NULL, 'Grade 7', NULL, NULL),
(51, 'JHIGH', 'Junior High School', NULL, 'Grade 8', NULL, NULL),
(52, 'JHIGH', 'Junior High School', NULL, 'Grade 9', NULL, NULL),
(53, 'JHIGH', 'Junior High School', NULL, 'Grade 10', NULL, NULL),
(54, 'PREP', 'Preparatory', NULL, 'K1', NULL, NULL),
(55, 'PREP', 'Preparatory', NULL, 'K2', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `course_fees`
--

CREATE TABLE `course_fees` (
  `id` int(11) NOT NULL,
  `course_code` varchar(50) NOT NULL,
  `fee_name` varchar(255) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `semester` varchar(20) DEFAULT 'All',
  `is_required` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deans`
--

CREATE TABLE `deans` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `branch_id` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `deans`
--

INSERT INTO `deans` (`id`, `employee_id`, `first_name`, `middle_name`, `last_name`, `email`, `department_id`, `password`, `is_active`, `created_at`, `branch_id`) VALUES
(1, 'D-2020-001', 'Maria', NULL, 'Santos', 'maria.santos@school.edu', 2, '$2y$10$qGJoEPxtpDxPtbMnuY0v9e7gm6AGbw1VaIn8CX1ac9Myy3n2Tp9iq', 1, '2026-03-23 07:14:59', 1);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `passing_grade` decimal(5,2) DEFAULT 75.00,
  `dean_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `code`, `passing_grade`, `dean_name`, `created_at`) VALUES
(1, 'Computer Science', 'CS', 75.00, NULL, '2026-03-23 07:10:44'),
(2, 'Business Administration', 'BA', 75.00, NULL, '2026-03-23 07:10:44'),
(3, 'Engineering', 'ENG', 75.00, NULL, '2026-03-23 07:10:44'),
(4, 'College of Engineering and Technology', 'CET', 75.00, NULL, '2026-03-24 09:56:36'),
(5, 'College of Arts and Sciences', 'CAS', 75.00, NULL, '2026-03-24 09:56:36'),
(6, 'College of Education', 'EDUC', 75.00, NULL, '2026-03-24 09:56:36'),
(7, 'College of Business Administration', 'CBA', 75.00, NULL, '2026-03-24 09:56:36'),
(8, 'College of Criminal Justice', 'CRIM', 75.00, NULL, '2026-03-24 09:56:36'),
(9, 'College of Nursing', 'NURS', 75.00, NULL, '2026-03-24 09:56:36'),
(10, 'College of Dentistry', 'DENT', 75.00, NULL, '2026-03-24 09:56:36'),
(11, 'College of Pharmacy', 'PHAR', 75.00, NULL, '2026-03-24 09:56:36');

-- --------------------------------------------------------

--
-- Table structure for table `dialects`
--

CREATE TABLE `dialects` (
  `dialects` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `dialects`
--

INSERT INTO `dialects` (`dialects`) VALUES
('Aklanon'),
('Asi (Bantoanon)'),
('Boholano (Binol-anon)'),
('Bolinao'),
('Bontoc (Ifuntok)'),
('Botolan'),
('Buhinon Bikol (Buhi)'),
('Butuanon'),
('Capiznon'),
('Caviteño Chavacano'),
('Cebuano'),
('Central Bikol (Canaman)'),
('Cuyonon'),
('East Miraya Bikol (Daraga)'),
('English'),
('Español'),
('Gubatnon Bikol (Gubat)'),
('Hiligaynon (Ilonggo)'),
('Ibanag'),
('Ilokano'),
('Itawis'),
('Jama Mapun'),
('Kapampangan'),
('Kinaray-a'),
('Malaysian & Indonesian'),
('Manobo (Obo)'),
('Maranao'),
('Masbateño'),
('Northern Catanduanes Bikol'),
('Pangasinan'),
('Rinconada Bikol (Iriga)'),
('Romblomanon (Ini)'),
('Sambali'),
('Sangil'),
('Sinama'),
('Sorsoganon'),
('Surigaonon'),
('Tagalog (Manila)'),
('Tagalog (Tayabas)'),
('Tausug'),
('Ternateño Chavacano'),
('Waray (Leyte)'),
('Waray (Northern Samar)'),
('West Miraya Bikol (Ligao)'),
('West Miraya Bikol (Oas)'),
('Yakan'),
('Zamboangueño Chavacano');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `school_year` varchar(9) NOT NULL,
  `semester` int(11) NOT NULL,
  `status` enum('Pending','Confirmed','Cancelled') NOT NULL DEFAULT 'Pending',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `subject_id`, `school_year`, `semester`, `status`, `remarks`, `created_at`, `updated_at`) VALUES
(3, 10, NULL, '2026-2027', 1, 'Cancelled', NULL, NULL, '2026-03-24 12:20:24'),
(4, 31, NULL, '2026-2027', 1, 'Confirmed', NULL, NULL, '2026-03-10 14:10:26'),
(5, 32, NULL, '2026-2027', 1, 'Confirmed', NULL, NULL, NULL),
(6, 21, NULL, '2026-2027', 1, '', NULL, NULL, '2026-03-10 14:35:31'),
(7, 4, NULL, '2026-2027', 1, 'Confirmed', NULL, NULL, '2026-03-10 14:24:03'),
(8, 29, NULL, '2026-2027', 1, 'Confirmed', NULL, NULL, '2026-03-10 14:26:29'),
(9, 2, NULL, '2026-2027', 1, '', NULL, NULL, '2026-03-10 14:36:05'),
(10, 33, NULL, '2026-2027', 1, '', NULL, NULL, '2026-03-16 08:10:57'),
(11, 28, NULL, '2026-2027', 1, 'Pending', NULL, NULL, NULL),
(12, 24, NULL, '2026-2027', 1, 'Pending', NULL, NULL, NULL),
(13, 23, NULL, '2026-2027', 1, 'Pending', NULL, NULL, NULL),
(14, 3, NULL, '2026-2027', 1, 'Pending', NULL, NULL, NULL),
(15, 20, NULL, '2026-2027', 1, 'Pending', NULL, NULL, NULL),
(16, 9, NULL, '2026-2027', 1, 'Pending', NULL, NULL, NULL),
(17, 27, NULL, '2026-2027', 1, 'Pending', NULL, NULL, NULL),
(18, 1, NULL, '2026-2027', 1, 'Confirmed', NULL, NULL, '2026-03-16 09:51:41'),
(19, 16, NULL, '2026-2027', 1, 'Confirmed', NULL, NULL, '2026-03-16 09:53:07'),
(20, 34, NULL, '2025-2026', 1, 'Confirmed', NULL, NULL, NULL),
(21, 35, NULL, '2025-2026', 1, 'Confirmed', NULL, NULL, NULL),
(22, 36, NULL, '2025-2026', 1, 'Confirmed', NULL, NULL, NULL),
(23, 37, NULL, '2025-2026', 1, 'Confirmed', NULL, NULL, NULL),
(24, 38, NULL, '2025-2026', 1, 'Confirmed', NULL, NULL, NULL),
(25, 39, NULL, '2025-2026', 1, 'Confirmed', NULL, NULL, NULL),
(26, 40, NULL, '2025-2026', 1, 'Confirmed', NULL, NULL, NULL),
(27, 41, NULL, '2025-2026', 1, 'Confirmed', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ethnicities`
--

CREATE TABLE `ethnicities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ethnicities`
--

INSERT INTO `ethnicities` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Tagalog', NULL, NULL),
(2, 'Ilocano', NULL, NULL),
(3, 'Visayan', NULL, NULL),
(4, 'Bicolano', NULL, NULL),
(5, 'Kapampangan', NULL, NULL),
(6, 'Pangasinense', NULL, NULL),
(7, 'Waray', NULL, NULL),
(8, 'Maguindanaon', NULL, NULL),
(9, 'Maranao', NULL, NULL),
(10, 'Tausug', NULL, NULL),
(11, 'Manobo', NULL, NULL),
(12, 'Igorot', NULL, NULL),
(13, 'Ivatan', NULL, NULL),
(14, 'Ilonggo', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ethnicity`
--

CREATE TABLE `ethnicity` (
  `ethnicname` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `ethnicity`
--

INSERT INTO `ethnicity` (`ethnicname`) VALUES
('Adasen'),
('Agta'),
('Aklanon'),
('Alangan'),
('Alta'),
('Amerasian'),
('Ati'),
('Atta'),
('B\'laan'),
('Badjao'),
('Bagobo'),
('Balangao'),
('Balangingi'),
('Bangon'),
('Bantoanon'),
('Banwaon'),
('Batak'),
('Bicolano'),
('Binukid'),
('Boholano'),
('Bolinao'),
('Bontoc'),
('Buhid'),
('Butuanon'),
('Caluyanon'),
('Capiznon'),
('Caviteño'),
('Cebuano'),
('Cotabateño'),
('Davaoeño'),
('Ermiteño'),
('Ga\'dang'),
('Gaddang'),
('Hanunoo'),
('Higaonon'),
('Ibaloi'),
('Ibanag'),
('Ifugao'),
('Ikalahan'),
('Illanun'),
('Ilocano'),
('Ilonggo'),
('Ilongot'),
('Inonhan'),
('Iraya'),
('Isinai'),
('Isneg'),
('Itneg'),
('Ivatan'),
('Kagayanen'),
('Kalagan'),
('Kalinga'),
('Kamayo'),
('Kankanaey'),
('Kapampangan'),
('Karao'),
('Kasiguranin'),
('Kinaray-a'),
('Korean Filipinos'),
('Magahat'),
('Maguindanaon'),
('Malaweg'),
('Malaynon'),
('Mamanwa'),
('Mandaya'),
('Manguwangan'),
('Manobo'),
('Maranao'),
('Masbateño'),
('Palawano'),
('Palaweño'),
('Pangasinense'),
('Paranan'),
('Porohanon'),
('Ratagnon'),
('Romblomanon'),
('Sama'),
('Sambal'),
('Sangil'),
('Sangir'),
('Sinauna'),
('Spanish Filipinos'),
('Subanen, Central'),
('Sulod'),
('Surigaonon'),
('T\'boli'),
('Tadyawan'),
('Tagabawa'),
('Tagalog'),
('Tagbanwa'),
('Tasaday'),
('Tau\'t Bato'),
('Tausug'),
('Tawbuid'),
('Ternateño'),
('Tiruray'),
('Tsinoy'),
('Waray'),
('Yakan'),
('Yogad'),
('Zamboangueño');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fee_types`
--

CREATE TABLE `fee_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_types`
--

INSERT INTO `fee_types` (`id`, `name`, `description`, `amount`, `created_at`) VALUES
(1, 'Tuition Fee', 'Regular tuition fee per unit', 500.00, '2026-03-23 06:41:17'),
(2, 'Miscellaneous', 'Miscellaneous fee', 2000.00, '2026-03-23 06:41:17'),
(3, 'Laboratory', 'Laboratory fee', 1500.00, '2026-03-23 06:41:17'),
(4, 'Registration Fee', 'One-time registration', 500.00, '2026-03-23 06:41:17'),
(5, 'Assessment Fee', 'Assessment fee', 300.00, '2026-03-23 06:41:17');

-- --------------------------------------------------------

--
-- Table structure for table `finance_users`
--

CREATE TABLE `finance_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `fullname` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `branch_id` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `finance_users`
--

INSERT INTO `finance_users` (`id`, `username`, `password`, `is_active`, `fullname`, `created_at`, `branch_id`) VALUES
(1, 'finance', '$2y$10$KRmSSB00AWRqI9HDB5vu1uqyttQjBHJ1ZEsLhS5MIUuKbig.d2K2W', 1, 'Finance Admin', '2026-03-23 06:41:17', 1);

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `prelim` decimal(5,2) DEFAULT NULL,
  `midterm` decimal(5,2) DEFAULT NULL,
  `final_exam` decimal(5,2) DEFAULT NULL,
  `final_grade` decimal(5,2) DEFAULT NULL,
  `remarks` varchar(20) DEFAULT NULL,
  `semester` varchar(20) DEFAULT '1st Semester',
  `school_year` varchar(20) DEFAULT NULL,
  `status` enum('Draft','Submitted','Approved') DEFAULT 'Draft',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `student_id`, `subject_id`, `teacher_id`, `prelim`, `midterm`, `final_exam`, `final_grade`, `remarks`, `semester`, `school_year`, `status`, `submitted_at`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, 34, 1, NULL, 90.00, 90.00, 89.78, 89.93, 'Passed', '1st Semester', '2025-2026', 'Draft', NULL, NULL, '2026-03-23 07:58:49', '2026-03-23 08:12:57'),
(2, 35, 1, NULL, 90.00, 81.00, 70.00, 80.33, 'Passed', '1st Semester', '2025-2026', 'Draft', NULL, NULL, '2026-03-24 11:06:55', '2026-03-24 11:08:56');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2024_01_01_000001_create_enrollment_tables', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` varchar(20) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('Cash','Bank Transfer','GCash','Check') DEFAULT 'Cash',
  `reference_number` varchar(50) DEFAULT NULL,
  `or_number` varchar(50) DEFAULT NULL,
  `payment_date` date NOT NULL,
  `received_by` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `student_id`, `amount`, `payment_method`, `reference_number`, `or_number`, `payment_date`, `received_by`, `remarks`, `created_at`) VALUES
(1, 1, 5000.00, 'Cash', NULL, 'OR-2026-001', '2026-03-01', 'Finance Admin', NULL, '2026-03-23 06:41:17');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `display_name`, `description`, `category`, `created_at`) VALUES
(1, 'manage_branches', 'Manage Branches', 'Create, edit, and delete branches', 'Branches', '2026-03-23 12:45:01'),
(2, 'manage_all_users', 'Manage All Users', 'Create, edit, and delete any user', 'Users', '2026-03-23 12:45:01'),
(3, 'manage_students', 'Manage Students', 'Create, edit, and delete students', 'Users', '2026-03-23 12:45:01'),
(4, 'manage_teachers', 'Manage Teachers', 'Create, edit, and delete teachers', 'Users', '2026-03-23 12:45:01'),
(5, 'manage_registrars', 'Manage Registrars', 'Create, edit, and delete registrar accounts', 'Users', '2026-03-23 12:45:01'),
(6, 'manage_deans', 'Manage Deans', 'Create, edit, and delete dean accounts', 'Users', '2026-03-23 12:45:01'),
(7, 'manage_finance_staff', 'Manage Finance Staff', 'Create, edit, and delete finance accounts', 'Users', '2026-03-23 12:45:01'),
(8, 'manage_courses', 'Manage Courses', 'Create, edit, and delete courses', 'Academic', '2026-03-23 12:45:01'),
(9, 'manage_subjects', 'Manage Subjects', 'Create, edit, and delete subjects', 'Academic', '2026-03-23 12:45:01'),
(10, 'manage_departments', 'Manage Departments', 'Create, edit, and delete departments', 'Academic', '2026-03-23 12:45:01'),
(11, 'manage_enrollments', 'Manage Enrollments', 'Process and manage student enrollments', 'Academic', '2026-03-23 12:45:01'),
(12, 'assign_teachers', 'Assign Teachers', 'Assign teachers to subjects', 'Academic', '2026-03-23 12:45:01'),
(13, 'approve_schedules', 'Approve Schedules', 'Approve or reject schedules', 'Academic', '2026-03-23 12:45:01'),
(14, 'set_grading_policy', 'Set Grading Policy', 'Set passing grades and grading thresholds', 'Academic', '2026-03-23 12:45:01'),
(15, 'encode_grades', 'Encode Grades', 'Encode and update student grades', 'Grades', '2026-03-23 12:45:01'),
(16, 'approve_grades', 'Approve Grades', 'Approve or reject submitted grades', 'Grades', '2026-03-23 12:45:01'),
(17, 'view_grades', 'View Grades', 'View student grades', 'Grades', '2026-03-23 12:45:01'),
(18, 'manage_payments', 'Manage Payments', 'Record and manage payments', 'Finance', '2026-03-23 12:45:01'),
(19, 'manage_financial_reports', 'Financial Reports', 'Generate and view financial reports', 'Finance', '2026-03-23 12:45:01'),
(20, 'manage_student_accounts', 'Manage Student Accounts', 'Manage student financial accounts', 'Finance', '2026-03-23 12:45:01'),
(21, 'request_enrollment', 'Request Enrollment', 'Request to enroll in subjects', 'Student', '2026-03-23 12:45:01'),
(22, 'view_own_grades', 'View Own Grades', 'View personal grades', 'Student', '2026-03-23 12:45:01'),
(23, 'view_own_schedule', 'View Own Schedule', 'View personal schedule', 'Student', '2026-03-23 12:45:01'),
(24, 'view_own_finance', 'View Own Finance', 'View personal financial records', 'Student', '2026-03-23 12:45:01');

-- --------------------------------------------------------

--
-- Table structure for table `religions`
--

CREATE TABLE `religions` (
  `religion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `religions`
--

INSERT INTO `religions` (`religion`) VALUES
('Aglipayan'),
('Association of Fundamental Baptist Churches in the Philippines'),
('Bible Baptist Church'),
('Buddhist'),
('Church of Christ'),
('Convention of the Philippine Baptist Church'),
('Crusaders of the Divine Church of Christ Inc.'),
('Evangelical Christian Outreach Foundation'),
('Evangelicals (PCEC)'),
('Faith Tabernacle Church (Living Rock Ministries)'),
('Iglesia Ni Cristo'),
('Iglesia sa Dios Espiritu Santo Inc.'),
('Igreja Catolica Apostolica Brasileira nas Filipinas'),
('Islam'),
('Jehovah\'s Witnesses'),
('Jesus Is Lord Church Worldwide'),
('Lutheran Church in the Philippines'),
('Non-Roman Catholic and Protestant (NCCP)'),
('Baptists'),
('Protestants'),
('Philippine Benevolent Missionaries Association'),
('Philippine Independent Catholic Church'),
('Roman Catholic including Catholic Charismatic'),
('Seventh-day Adventist'),
('The Church of Jesus Christ of Latter-day Saints'),
('Tribal Religions'),
('Union Espiritista Cristiana de Filipinas, Inc.'),
('United Church of Christ in the Philippines'),
('United Pentecostal Church (Philippines) Inc.'),
('Pentecost'),
('Alliance'),
('Assembly of God'),
('Four Square Church'),
('Christian Church Fellowship International'),
('Iglesia Filipiniana Independencia'),
('Wesleyan');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `hierarchy_level` int(11) DEFAULT 0,
  `is_system` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `hierarchy_level`, `is_system`, `created_at`) VALUES
(1, 'super_admin', 'Super Admin', 'Full system access with branch management', 100, 1, '2026-03-23 12:45:01'),
(2, 'registrar', 'Registrar', 'Manages students, teachers, subjects, and enrollments', 80, 1, '2026-03-23 12:45:01'),
(3, 'dean', 'Dean', 'Manages departments, subjects, and approves grades', 70, 1, '2026-03-23 12:45:01'),
(4, 'finance', 'Finance', 'Manages payments and financial records', 60, 1, '2026-03-23 12:45:01'),
(5, 'teacher', 'Teacher', 'Encodes and manages grades for assigned subjects', 40, 1, '2026-03-23 12:45:01'),
(6, 'student', 'Student', 'Requests enrollment and views records', 20, 1, '2026-03-23 12:45:01');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3),
(4, 1, 4),
(5, 1, 5),
(6, 1, 6),
(7, 1, 7),
(8, 1, 8),
(9, 1, 9),
(10, 1, 10),
(11, 1, 11),
(12, 1, 12),
(13, 1, 13),
(14, 1, 14),
(15, 1, 15),
(16, 1, 16),
(17, 1, 17),
(18, 1, 18),
(19, 1, 19),
(20, 1, 20),
(21, 1, 21),
(22, 1, 22),
(23, 1, 23),
(24, 1, 24),
(25, 2, 3),
(26, 2, 4),
(27, 2, 8),
(28, 2, 9),
(29, 2, 11),
(30, 2, 17),
(31, 2, 23),
(38, 3, 9),
(32, 3, 10),
(33, 3, 12),
(34, 3, 13),
(35, 3, 14),
(36, 3, 16),
(37, 3, 17),
(39, 3, 23),
(40, 4, 18),
(41, 4, 19),
(42, 4, 20),
(43, 4, 23),
(44, 5, 15),
(45, 5, 17),
(46, 5, 23),
(47, 6, 21),
(48, 6, 22),
(49, 6, 23),
(50, 6, 24);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('EjqYlCtgAvn9c3kTaZdBc4lEh1tDlmPvtk5AyY6f', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiQzVOWVltU0xYUWsxdWtPRVJ2Rjl1aGZtbE1LUHpYUlZkcXV1NXN0cSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FkbWluL2Rhc2hib2FyZCI7fX0=', 1773111832);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_number` varchar(20) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `middlename` varchar(100) DEFAULT NULL,
  `lastname` varchar(100) NOT NULL,
  `birthdate` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `zipcode` varchar(10) DEFAULT NULL,
  `region` varchar(50) DEFAULT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `civil_status` enum('Single','Married','Divorced','Widowed') DEFAULT NULL,
  `nationality` varchar(50) NOT NULL DEFAULT 'Filipino',
  `religion` varchar(100) DEFAULT NULL,
  `ethnicity` varchar(100) DEFAULT NULL,
  `dialect` varchar(100) DEFAULT NULL,
  `course_code` varchar(20) DEFAULT NULL,
  `major` varchar(100) DEFAULT NULL,
  `year_level` int(11) NOT NULL DEFAULT 1,
  `school_year` varchar(20) DEFAULT NULL,
  `enrollment_status` enum('Pending','Enrolled','Dropped') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `branch_id` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_number`, `firstname`, `middlename`, `lastname`, `birthdate`, `gender`, `address`, `city`, `province`, `zipcode`, `region`, `contact_no`, `email`, `civil_status`, `nationality`, `religion`, `ethnicity`, `dialect`, `course_code`, `major`, `year_level`, `school_year`, `enrollment_status`, `created_at`, `updated_at`, `profile_picture`, `password`, `branch_id`) VALUES
(1, '2020-1001', 'Juan', 'Santos', 'Dela Cruz', '2002-05-15', 'Male', 'Blk 12 Lot 8 Phase 3, Barangay San Jose', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09171234567', 'juan.delacruz@email.com', 'Single', 'Filipino', 'Roman Catholic', 'Bisaya', 'Cebuano', 'BSIT', 'Web Development', 3, NULL, 'Enrolled', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'delacruz20201001', 1),
(2, '2020-1002', 'Maria', 'Reyes', 'Santos', '2001-11-23', 'Female', 'Unit 4, Sunville Subdivision', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09182345678', 'maria.santos@email.com', 'Single', 'Filipino', 'Roman Catholic', 'Tagalog', 'Tagalog', 'BSED', 'English', 4, NULL, 'Enrolled', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'santos20201002', 1),
(3, '2021-1003', 'John', 'Mercado', 'Cruz', '2003-02-08', 'Male', 'Purok 3, Barangay Dadiangas', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09203456789', 'john.cruz@email.com', 'Single', 'Filipino', 'Iglesia Ni Cristo', 'Bisaya', 'Hiligaynon', 'BSBA', 'Marketing', 2, NULL, 'Enrolled', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'cruz20211003', 1),
(4, '2021-1004', 'Anna', 'Luna', 'Garcia', '2002-09-17', 'Female', 'Phase 2, City Heights Subdivision', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09314567890', 'anna.garcia@email.com', 'Single', 'Filipino', 'Roman Catholic', 'Bisaya', 'Cebuano', 'BSN', NULL, 2, NULL, 'Enrolled', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'garcia20211004', 1),
(5, '2022-1005', 'Mark', 'Villanueva', 'Ramos', '2004-03-21', 'Male', 'Blk 5, Fatima Subdivision', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09425678901', 'mark.ramos@email.com', 'Single', 'Filipino', 'Roman Catholic', 'Ilonggo', 'Hiligaynon', 'BSCRIM', NULL, 1, NULL, 'Pending', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'ramos20221005', 1),
(6, '2022-1006', 'Sarah', 'Gonzales', 'Mendoza', '2003-07-12', 'Female', 'Purok Malipayon, Barangay Calumpang', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09536789012', 'sarah.mendoza@email.com', 'Single', 'Filipino', 'Seventh-day Adventist', 'Bisaya', 'Cebuano', 'BSIT', 'Network Security', 1, NULL, 'Enrolled', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'mendoza20221006', 1),
(7, '2020-1007', 'Michael', NULL, 'Fernandez', '2001-12-04', 'Male', 'Door 7, Mabuhay Pension House', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09647890123', 'michael.fernandez@email.com', 'Single', 'Filipino', 'Roman Catholic', 'Bisaya', 'Cebuano', 'BSN', NULL, 4, NULL, 'Pending', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'fernandez20201007', 1),
(8, '2021-1008', 'Angela', 'Rivera', 'Villanueva', '2002-06-19', 'Female', 'Blk 10, Green Meadows Subdivision', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09758901234', 'angela.villanueva@email.com', 'Single', 'Filipino', 'Born Again', 'Bisaya', 'Cebuano', 'BSED', 'Mathematics', 3, NULL, 'Enrolled', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'villanueva20211008', 1),
(9, '2022-1009', 'Christian', 'Torres', 'Lim', '2003-10-28', 'Male', 'Purok 8, Barangay Bula', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09869012345', 'christian.lim@email.com', 'Single', 'Filipino', 'Roman Catholic', 'Chinese', 'Mandarin', 'BSBA', 'Finance', 2, NULL, 'Enrolled', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'lim20221009', 1),
(10, '2020-1010', 'Patricia', 'Castro', 'Aquino', '2002-01-30', 'Female', 'Phase 3, Camella Homes', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09970123456', 'patricia.aquino@email.com', 'Single', 'Filipino', 'Roman Catholic', 'Bisaya', 'Cebuano', 'AB', 'Mass Communication', 3, NULL, 'Enrolled', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'aquino20201010', 1),
(11, '2023-1011', 'Rey', 'Sanchez', 'Pascual', '2004-08-15', 'Male', 'Purok Saging, Barangay Labangal', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09181234567', 'rey.pascual@email.com', 'Single', 'Filipino', 'Roman Catholic', 'Bisaya', 'Cebuano', 'BSIT', NULL, 1, NULL, 'Pending', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'pascual20231011', 1),
(12, '2021-1012', 'Kristine', NULL, 'Dizon', '2002-12-11', 'Female', 'Blk 6, Villa San Miguel Subdivision', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09292345678', 'kristine.dizon@email.com', 'Single', 'Filipino', 'Roman Catholic', 'Kapampangan', 'Kapampangan', 'BSN', NULL, 3, NULL, 'Enrolled', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'dizon20211012', 1),
(13, '2022-1013', 'Joseph', 'Alcantara', 'Salazar', '2003-04-25', 'Male', 'Purok 5, Barangay City Heights', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09303456789', 'joseph.salazar@email.com', 'Single', 'Filipino', 'Roman Catholic', 'Bisaya', 'Cebuano', 'BSCRIM', NULL, 2, NULL, 'Enrolled', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'salazar20221013', 1),
(14, '2023-1014', 'Diana', 'Magsaysay', 'Velasco', '2004-02-14', 'Female', 'Door 12, Lapu-lapu Street', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09414567890', 'diana.velasco@email.com', 'Single', 'Filipino', 'Roman Catholic', 'Bisaya', 'Hiligaynon', 'BSED', 'Science', 1, NULL, 'Enrolled', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'velasco20231014', 1),
(15, '2021-1015', 'Kevin', 'Flores', 'Navarro', '2002-07-07', 'Male', 'Blk 15, Phase 2, Mabuhay Subdivision', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09525678901', 'kevin.navarro@email.com', 'Single', 'Filipino', 'Islam', 'Maguindanaoan', 'Maguindanaoan', 'BSBA', 'Human Resources', 3, NULL, 'Enrolled', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'navarro20211015', 1),
(16, '2020-1016', 'Michelle', 'David', 'Romualdez', '2001-09-03', 'Female', 'Purok Narra, Barangay Conel', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09636789012', 'michelle.romualdez@email.com', 'Single', 'Filipino', 'Roman Catholic', 'Bisaya', 'Cebuano', 'BSIT', 'Web Development', 4, NULL, 'Enrolled', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'romualdez20201016', 1),
(17, '2022-1017', 'Jerome', NULL, 'Roxas', '2003-11-19', 'Male', 'Unit 8, Rizal Street', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09747890123', 'jerome.roxas@email.com', 'Single', 'Filipino', 'Roman Catholic', 'Bisaya', 'Cebuano', 'BSN', NULL, 2, NULL, 'Enrolled', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'roxas20221017', 1),
(18, '2023-1018', 'Camille', 'Santiago', 'Reyes', '2004-05-05', 'Female', 'Blk 2, Phase 5, Greenfield Subdivision', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09858901234', 'camille.reyes@email.com', 'Single', 'Filipino', 'Roman Catholic', 'Bisaya', 'Cebuano', 'AB', 'Political Science', 1, NULL, 'Pending', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'reyes20231018', 1),
(19, '2021-1019', 'Francis', 'Vergara', 'Sison', '2002-10-10', 'Male', 'Purok 2, Barangay Apopong', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09969012345', 'francis.sison@email.com', 'Single', 'Filipino', 'Jehovah\'s Witnesses', 'Bisaya', 'Cebuano', 'BSIT', 'Network Security', 3, NULL, 'Enrolled', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'sison20211019', 1),
(20, '2022-1020', 'Rochelle', 'Mata', 'De Guzman', '2003-12-22', 'Female', 'Door 3, Quezon Avenue', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09081234567', 'rochelle.deguzman@email.com', 'Single', 'Filipino', 'Roman Catholic', 'Bisaya', 'Cebuano', 'BSBA', 'Marketing', 2, NULL, 'Enrolled', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'deguzman20221020', 1),
(21, '2023-1021', 'Bryan', NULL, 'Cabrera', '2004-06-30', 'Male', 'Blk 8, Purok Mahogany, Barangay Fatima', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09192345678', 'bryan.cabrera@email.com', 'Single', 'Filipino', 'Roman Catholic', 'Bisaya', 'Cebuano', 'BSCRIM', NULL, 1, NULL, 'Enrolled', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'cabrera20231021', 1),
(22, '2020-1022', 'Joanna', 'Lopez', 'Fernando', '2001-08-16', 'Female', 'Phase 1, Pacman Village', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09203456789', 'joanna.fernando@email.com', 'Married', 'Filipino', 'Roman Catholic', 'Bisaya', 'Cebuano', 'BSED', 'Filipino', 4, NULL, 'Enrolled', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'fernando20201022', 1),
(23, '2021-1023', 'Eduardo', 'Gutierrez', 'Miranda', '2002-03-09', 'Male', 'Purok 7, Barangay San Isidro', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09314567890', 'eduardo.miranda@email.com', 'Single', 'Filipino', 'Roman Catholic', 'Bisaya', 'Cebuano', 'BSIT', NULL, 3, NULL, 'Enrolled', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'miranda20211023', 1),
(24, '2022-1024', 'Jennifer', 'Panganiban', 'Tolentino', '2003-01-27', 'Female', 'Blk 4, Lourdes Subdivision', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09425678901', 'jennifer.tolentino@email.com', 'Single', 'Filipino', 'Roman Catholic', 'Bisaya', 'Hiligaynon', 'BSN', NULL, 2, NULL, 'Enrolled', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'tolentino20221024', 1),
(25, '2023-1025', 'Paolo', 'Ramirez', 'Villanueva', '2004-09-14', 'Male', 'Unit 5, Bonifacio Street', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09536789012', 'paolo.villanueva@email.com', 'Single', 'Filipino', 'Roman Catholic', 'Bisaya', 'Cebuano', 'AB', 'Economics', 1, NULL, 'Pending', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'villanueva20231025', 1),
(27, '2022-1027', 'Victor', 'Bautista', 'Maceda', '2003-07-24', 'Male', 'Blk 3, Phase 4, Villa Sofia Subdivision', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09758901234', 'victor.maceda@email.com', 'Single', 'Filipino', 'Iglesia Ni Cristo', 'Bisaya', 'Cebuano', 'BSBA', 'Finance', 2, NULL, 'Enrolled', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'maceda20221027', 1),
(28, '2023-1028', 'Angelica', 'Salvador', 'Chavez', '2004-11-03', 'Female', 'Purok Acacia, Barangay Dadiangas North', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09869012345', 'angelica.chavez@email.com', 'Single', 'Filipino', 'Roman Catholic', 'Bisaya', 'Cebuano', 'BSCRIM', NULL, 1, NULL, 'Enrolled', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'chavez20231028', 1),
(29, '2021-1029', 'Renato', 'Morales', 'Santiago', '2002-02-28', 'Male', 'Door 9, Mabini Extension', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09970123456', 'renato.santiago@email.com', 'Single', 'Filipino', 'Roman Catholic', 'Bisaya', 'Cebuano', 'BSN', NULL, 3, NULL, 'Enrolled', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'santiago20211029', 1),
(30, '2022-1030', 'Catherine', 'Navarro', 'Magsino', '2003-05-20', 'Female', 'Blk 11, Purok Sampaguita', 'GENERAL SANTOS CITY (DADIANGAS)', 'SOUTH COTABATO', '9500', NULL, '09182345670', 'catherine.magsino@email.com', 'Single', 'Filipino', 'Roman Catholic', 'Bisaya', 'Cebuano', 'BSIT', NULL, 2, NULL, 'Enrolled', '2026-03-10 14:00:59', '2026-03-10 14:00:59', NULL, 'magsino20221030', 1),
(31, '202600001', 'Gwyneth', 'fuxk', 'Callora', '2005-12-01', 'Female', 'Prk. Quilantang Brgy. Calumpang General Santos City', 'General Santos City', 'SOUTH COTABATO', '9500', NULL, '678678678678', 'gwynethcallora6@gmail.com', 'Single', 'Filipino', 'Roman Catholic', 'Cebuano', 'Cebuano', 'BSIT', '', 2, NULL, 'Pending', NULL, NULL, NULL, '$2y$10$0PKVtiegG/2zYLTEtoTYbeoZnnDAsKG7JCLpf6CHlWRxBJ7TkzSZa', 1),
(32, '202600002', 'Gwyneth', 'fcuk', 'tensionado', '2005-12-01', 'Female', 'ghghfgh', 'General Santos City', 'SOUTH COTABATO', '9500', NULL, '678678678678', 'gwynethcallora6@gmail.com', 'Single', 'Filipino', 'Roman Catholic', 'Maguindanaon', 'Romblomanon', 'BSES', '', 4, NULL, 'Pending', NULL, NULL, NULL, '$2y$10$0PKVtiegG/2zYLTEtoTYbeoZnnDAsKG7JCLpf6CHlWRxBJ7TkzSZa', 1),
(33, '202600003', 'testingone', 'middleone', 'lastnameone', '2005-12-01', 'Male', 'prk quilantang one', 'General Santos City', 'SOUTH COTABATO', '9500', NULL, '676767676767676767', 'testingone@gmail.com', 'Single', 'Filipino', 'Roman Catholic including Catholic Charismatic', 'Cebuano', 'Cebuano', 'BSIT', '', 1, NULL, 'Pending', NULL, NULL, '', '$2y$10$0PKVtiegG/2zYLTEtoTYbeoZnnDAsKG7JCLpf6CHlWRxBJ7TkzSZa', 1),
(34, '202600010', 'Ana', NULL, 'Bautista', NULL, NULL, NULL, 'Manila', 'Metro Manila', NULL, 'NCR', NULL, 'ana.bautista@email.com', NULL, 'Filipino', NULL, NULL, NULL, 'BSIT', NULL, 1, '2025-2026', 'Pending', NULL, NULL, NULL, '$2y$10$0PKVtiegG/2zYLTEtoTYbeoZnnDAsKG7JCLpf6CHlWRxBJ7TkzSZa', 1),
(35, '202600011', 'Roberto', NULL, 'Cruz', NULL, NULL, NULL, 'Manila', 'Metro Manila', NULL, 'NCR', NULL, 'roberto.cruz@email.com', NULL, 'Filipino', NULL, NULL, NULL, 'BSIT', NULL, 1, '2025-2026', 'Enrolled', NULL, NULL, NULL, '$2y$10$dVTXrDQs6xFg70wj9qUi/.nbOCNPQLfEw..NbelD57OP5MHSl4CsC', 1),
(36, '202600012', 'Carmen', NULL, 'Diaz', NULL, NULL, NULL, 'Manila', 'Metro Manila', NULL, 'NCR', NULL, 'carmen.diaz@email.com', NULL, 'Filipino', NULL, NULL, NULL, 'BSIT', NULL, 1, '2025-2026', 'Pending', NULL, NULL, NULL, '$2y$10$0PKVtiegG/2zYLTEtoTYbeoZnnDAsKG7JCLpf6CHlWRxBJ7TkzSZa', 1),
(37, '202600013', 'Eduardo', NULL, 'Flores', NULL, NULL, NULL, 'Manila', 'Metro Manila', NULL, 'NCR', NULL, 'eduardo.flores@email.com', NULL, 'Filipino', NULL, NULL, NULL, 'BSIT', NULL, 1, '2025-2026', 'Pending', NULL, NULL, NULL, '$2y$10$0PKVtiegG/2zYLTEtoTYbeoZnnDAsKG7JCLpf6CHlWRxBJ7TkzSZa', 1),
(38, '202600014', 'Fe', NULL, 'Garcia', NULL, NULL, NULL, 'Manila', 'Metro Manila', NULL, 'NCR', NULL, 'fe.garcia@email.com', NULL, 'Filipino', NULL, NULL, NULL, 'BSIT', NULL, 1, '2025-2026', 'Pending', NULL, NULL, NULL, '$2y$10$0PKVtiegG/2zYLTEtoTYbeoZnnDAsKG7JCLpf6CHlWRxBJ7TkzSZa', 1),
(39, '202600015', 'George', NULL, 'Hernandez', NULL, NULL, NULL, 'Manila', 'Metro Manila', NULL, 'NCR', NULL, 'george.hernandez@email.com', NULL, 'Filipino', NULL, NULL, NULL, 'BSIT', NULL, 1, '2025-2026', 'Pending', NULL, NULL, NULL, '$2y$10$0PKVtiegG/2zYLTEtoTYbeoZnnDAsKG7JCLpf6CHlWRxBJ7TkzSZa', 1),
(40, '202600016', 'Hilda', NULL, 'Isla', NULL, NULL, NULL, 'Manila', 'Metro Manila', NULL, 'NCR', NULL, 'hilda.isla@email.com', NULL, 'Filipino', NULL, NULL, NULL, 'BSIT', NULL, 1, '2025-2026', 'Pending', NULL, NULL, NULL, '$2y$10$0PKVtiegG/2zYLTEtoTYbeoZnnDAsKG7JCLpf6CHlWRxBJ7TkzSZa', 1),
(41, '202600017', 'Ivan', NULL, 'Jose', NULL, NULL, NULL, 'Manila', 'Metro Manila', NULL, 'NCR', NULL, 'ivan.jose@email.com', NULL, 'Filipino', NULL, NULL, NULL, 'BSIT', NULL, 1, '2025-2026', 'Pending', NULL, NULL, NULL, '$2y$10$0PKVtiegG/2zYLTEtoTYbeoZnnDAsKG7JCLpf6CHlWRxBJ7TkzSZa', 1);

-- --------------------------------------------------------

--
-- Table structure for table `student_fees`
--

CREATE TABLE `student_fees` (
  `id` int(11) NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `fee_type_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_fees`
--

INSERT INTO `student_fees` (`id`, `student_id`, `fee_type_id`, `amount`, `due_date`, `created_at`) VALUES
(1, 1, 1, 500.00, '2026-04-22', '2026-03-23 06:41:17'),
(2, 1, 2, 2000.00, '2026-04-22', '2026-03-23 06:41:17'),
(3, 1, 3, 1500.00, '2026-04-22', '2026-03-23 06:41:17'),
(4, 1, 4, 500.00, '2026-04-22', '2026-03-23 06:41:17'),
(5, 1, 5, 300.00, '2026-04-22', '2026-03-23 06:41:17');

-- --------------------------------------------------------

--
-- Table structure for table `student_subjects`
--

CREATE TABLE `student_subjects` (
  `id` int(11) NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `enrollment_date` date DEFAULT curdate(),
  `status` enum('Enrolled','Dropped','Completed') DEFAULT 'Enrolled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_subjects`
--

INSERT INTO `student_subjects` (`id`, `student_id`, `subject_id`, `enrollment_date`, `status`) VALUES
(1, 34, 1, '2026-03-23', 'Enrolled'),
(2, 34, 2, '2026-03-23', 'Enrolled'),
(3, 34, 5, '2026-03-23', 'Enrolled'),
(4, 35, 1, '2026-03-23', 'Enrolled'),
(5, 35, 2, '2026-03-23', 'Enrolled'),
(6, 35, 5, '2026-03-23', 'Enrolled'),
(7, 36, 1, '2026-03-23', 'Enrolled'),
(8, 36, 2, '2026-03-23', 'Enrolled'),
(9, 36, 5, '2026-03-23', 'Enrolled'),
(10, 37, 1, '2026-03-23', 'Enrolled'),
(11, 37, 2, '2026-03-23', 'Enrolled'),
(12, 37, 5, '2026-03-23', 'Enrolled'),
(13, 38, 1, '2026-03-23', 'Enrolled'),
(14, 38, 2, '2026-03-23', 'Enrolled'),
(15, 38, 5, '2026-03-23', 'Enrolled'),
(16, 39, 1, '2026-03-23', 'Enrolled'),
(17, 39, 2, '2026-03-23', 'Enrolled'),
(18, 39, 5, '2026-03-23', 'Enrolled'),
(19, 40, 1, '2026-03-23', 'Enrolled'),
(20, 40, 2, '2026-03-23', 'Enrolled'),
(21, 40, 5, '2026-03-23', 'Enrolled'),
(22, 41, 1, '2026-03-23', 'Enrolled'),
(23, 41, 2, '2026-03-23', 'Enrolled'),
(24, 41, 5, '2026-03-23', 'Enrolled');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `units` int(11) NOT NULL,
  `course_code` varchar(20) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `schedule` varchar(50) DEFAULT NULL,
  `room` varchar(20) DEFAULT NULL,
  `capacity` int(11) NOT NULL DEFAULT 0,
  `enrolled` int(11) NOT NULL DEFAULT 0,
  `instructor` varchar(100) DEFAULT NULL,
  `is_open` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `subject_code`, `description`, `units`, `course_code`, `semester`, `school_year`, `schedule`, `room`, `capacity`, `enrolled`, `instructor`, `is_open`, `created_at`, `updated_at`) VALUES
(1, 'IT101', 'Introduction to Computing', 3, 'BSIT', 1, '2026-2027', 'MWF 8:00-9:00', 'Lab 1', 30, 0, 'Juan Dela Cruz', 1, NULL, NULL),
(2, 'IT102', 'Computer Programming 1', 3, 'BSIT', 1, '2026-2027', 'MWF 9:00-10:00', 'Lab 1', 30, 0, 'Juan Dela Cruz', 1, NULL, NULL),
(3, 'IT103', 'Database Management', 3, 'BSIT', 1, '2026-2027', 'TTh 10:00-12:00', 'Lab 2', 30, 0, 'Prof. Williams', 1, NULL, NULL),
(4, 'ENG101', 'English Communication', 3, 'BSIT', 1, '2026-2027', 'MWF 11:00-12:00', 'Room 101', 40, 0, 'Juan Dela Cruz', 1, NULL, NULL),
(5, 'MATH101', 'College Mathematics', 3, 'BSIT', 1, '2026-2027', 'TTh 8:00-10:00', 'Room 102', 40, 0, 'Juan Dela Cruz', 1, NULL, NULL),
(6, 'PE101', 'Physical Education 1', 2, 'BSIT', 1, '2026-2027', 'F 2:00-5:00', 'Gym', 50, 0, 'Coach Miller', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `system_users`
--

CREATE TABLE `system_users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_users`
--

INSERT INTO `system_users` (`id`, `username`, `email`, `password`, `role_id`, `branch_id`, `first_name`, `last_name`, `department_id`, `employee_id`, `is_active`, `last_login`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@school.edu', '$2y$10$.R3CMgSevMjU4Ijf7VK3ouXlLXkYZhcFKv22Vz2rF56yiOi2qN5Du', 1, 1, 'System', 'Administrator', NULL, 'SA-0001', 1, '2026-03-24 13:43:40', NULL, '2026-03-23 12:45:02', '2026-03-24 13:43:40'),
(2, 'finance_user', 'chrislyjay@gmail.com', '$2y$10$6FUx8Gfv6k3VdVWUtQdQi.y.w5Eu6iiqmMRtEX6xbJLgjX8YZSjLm', 4, 1, 'cj', 'Callora', 1, '01', 1, '2026-03-24 13:41:13', 1, '2026-03-23 12:51:51', '2026-03-24 13:41:13'),
(4, 'T-2020-001', 'juan.delacruz@school.edu', '$2y$10$CU9SB0IKCWhvqmONe2cZOO/GLb0T3oki1JdJSGbvPU19VwoRvt.qq', 5, 1, 'Juan', 'Dela Cruz', 1, 'T-2020-001', 1, '2026-03-24 13:23:01', NULL, '2026-03-23 12:53:49', '2026-03-24 13:23:01'),
(5, 'dean_account', 'financeadmin@school.edu', '$2y$10$VVrWmRWGGFyx9Yht2GcUG.crFuBgwBFCuER8CR/ANlE9vjOZG/R7K', 4, 1, 'Finance', 'Admin', 1, 'U-2026-001', 1, '2026-03-24 10:44:21', NULL, '2026-03-23 12:53:49', '2026-03-24 11:23:21'),
(8, 'registrar', 'registrar@school.edu', '$2y$10$Z3v5Xu1Y3nAyQR9fdr8gZegOo7O29ADS0iq1alwQUbWbxYt3PG7GO', 2, NULL, 'System', 'Registrar', NULL, 'R-2026-001', 1, '2026-03-24 13:12:26', NULL, '2026-03-24 10:40:33', '2026-03-24 13:12:26');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `password` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `branch_id` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `employee_id`, `first_name`, `middle_name`, `last_name`, `email`, `phone`, `department_id`, `status`, `password`, `is_active`, `created_at`, `branch_id`) VALUES
(1, 'T-2020-001', 'Juan', 'B.', 'Dela Cruz', 'juan.delacruz@school.edu', NULL, 2, 'Inactive', '$2y$10$bg8wQ.IZdCk0iUB6iZTx4.KS7AHxk./34LqlhZt.T8oJM9nDz.wwe', 1, '2026-03-23 07:10:44', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tuition_fees`
--

CREATE TABLE `tuition_fees` (
  `id` int(11) NOT NULL,
  `course_code` varchar(50) NOT NULL,
  `year_level` int(11) DEFAULT 1,
  `semester` varchar(20) DEFAULT 'All',
  `tuition_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `miscellaneous_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `laboratory_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_fees` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_per_unit` decimal(10,2) DEFAULT 0.00,
  `units_required` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tuition_fees`
--

INSERT INTO `tuition_fees` (`id`, `course_code`, `year_level`, `semester`, `tuition_amount`, `miscellaneous_amount`, `laboratory_amount`, `other_fees`, `total_per_unit`, `units_required`, `created_at`, `updated_at`) VALUES
(1, 'BSCS', 1, 'All', 15000.00, 5000.00, 2000.00, 1000.00, 23000.00, 24, '2026-03-24 13:46:47', '2026-03-24 13:46:47'),
(2, 'BSCS', 2, 'All', 15000.00, 5000.00, 2500.00, 1000.00, 23500.00, 25, '2026-03-24 13:46:47', '2026-03-24 13:46:47'),
(3, 'BSCS', 3, 'All', 15000.00, 5000.00, 3000.00, 1000.00, 24000.00, 26, '2026-03-24 13:46:47', '2026-03-24 13:46:47'),
(4, 'BSCS', 4, 'All', 15000.00, 5000.00, 2500.00, 1000.00, 23500.00, 25, '2026-03-24 13:46:47', '2026-03-24 13:46:47'),
(5, 'BSBA', 1, 'All', 14000.00, 5000.00, 1000.00, 1000.00, 21000.00, 22, '2026-03-24 13:46:47', '2026-03-24 13:46:47'),
(6, 'BSBA', 2, 'All', 14000.00, 5000.00, 1000.00, 1000.00, 21000.00, 22, '2026-03-24 13:46:47', '2026-03-24 13:46:47'),
(7, 'BSBA', 3, 'All', 14000.00, 5000.00, 1000.00, 1000.00, 21000.00, 22, '2026-03-24 13:46:47', '2026-03-24 13:46:47'),
(8, 'BSBA', 4, 'All', 14000.00, 5000.00, 1000.00, 1000.00, 21000.00, 22, '2026-03-24 13:46:47', '2026-03-24 13:46:47');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_activity_log`
--

CREATE TABLE `user_activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_activity_log`
--

INSERT INTO `user_activity_log` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-23 12:46:10'),
(2, 1, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-23 12:50:34'),
(3, 2, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-23 12:52:25'),
(4, 2, 'login', 'Dean logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-23 12:52:25'),
(5, 2, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-23 12:52:34'),
(6, 2, 'login', 'Dean logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-23 12:52:34'),
(7, 2, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-23 12:52:54'),
(8, 2, 'login', 'Dean logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-23 12:52:54'),
(9, 2, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-23 12:54:15'),
(10, 2, 'login', 'Dean logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-23 12:54:15'),
(11, 2, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-23 12:54:27'),
(12, 2, 'login', 'Dean logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-23 12:54:27'),
(13, 2, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-23 12:55:16'),
(14, 2, 'login', 'Dean logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-23 12:55:16'),
(16, 1, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-23 13:16:23'),
(17, 2, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-23 13:26:02'),
(18, 2, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-23 13:29:45'),
(19, 2, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-23 13:33:08'),
(20, 1, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-24 09:49:46'),
(21, 1, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-24 10:24:57'),
(22, 1, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-24 10:32:40'),
(23, 8, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-24 10:40:58'),
(24, 5, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-24 10:44:21'),
(25, 1, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-24 10:52:40'),
(26, 2, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-24 10:55:40'),
(27, 4, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-24 11:06:23'),
(28, 1, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-24 12:17:30'),
(29, 8, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-24 12:20:10'),
(30, 8, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-24 13:12:26'),
(31, 1, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-24 13:21:17'),
(32, 4, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-24 13:23:01'),
(33, 1, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-24 13:37:05'),
(34, 2, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-24 13:41:13'),
(35, 1, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-24 13:43:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admins_username_unique` (`username`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_fees`
--
ALTER TABLE `course_fees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_fee` (`course_code`,`fee_name`,`semester`);

--
-- Indexes for table `deans`
--
ALTER TABLE `deans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enrollments_student_id_foreign` (`student_id`);

--
-- Indexes for table `ethnicities`
--
ALTER TABLE `ethnicities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `fee_types`
--
ALTER TABLE `fee_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `finance_users`
--
ALTER TABLE `finance_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_subject` (`student_id`,`subject_id`,`semester`,`school_year`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_permission` (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `students_student_number_unique` (`student_number`);

--
-- Indexes for table `student_fees`
--
ALTER TABLE `student_fees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `fee_type_id` (`fee_type_id`);

--
-- Indexes for table `student_subjects`
--
ALTER TABLE `student_subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enrollment` (`student_id`,`subject_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_users`
--
ALTER TABLE `system_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `tuition_fees`
--
ALTER TABLE `tuition_fees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_tuition` (`course_code`,`year_level`,`semester`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `course_fees`
--
ALTER TABLE `course_fees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deans`
--
ALTER TABLE `deans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `ethnicities`
--
ALTER TABLE `ethnicities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fee_types`
--
ALTER TABLE `fee_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `finance_users`
--
ALTER TABLE `finance_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `student_fees`
--
ALTER TABLE `student_fees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `student_subjects`
--
ALTER TABLE `student_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `system_users`
--
ALTER TABLE `system_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tuition_fees`
--
ALTER TABLE `tuition_fees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `deans`
--
ALTER TABLE `deans`
  ADD CONSTRAINT `deans_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_fees`
--
ALTER TABLE `student_fees`
  ADD CONSTRAINT `student_fees_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_fees_ibfk_2` FOREIGN KEY (`fee_type_id`) REFERENCES `fee_types` (`id`);

--
-- Constraints for table `system_users`
--
ALTER TABLE `system_users`
  ADD CONSTRAINT `system_users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `system_users_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  ADD CONSTRAINT `user_activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `system_users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
