-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 28, 2025 at 03:13 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `qr_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `link_clicks`
--

CREATE TABLE `link_clicks` (
  `id` int(11) NOT NULL,
  `short_link_id` int(11) NOT NULL COMMENT 'ID короткой ссылки',
  `ip_address` varchar(45) NOT NULL COMMENT 'IP адрес',
  `user_agent` text DEFAULT NULL COMMENT 'User Agent',
  `referer` text DEFAULT NULL COMMENT 'Реферер',
  `clicked_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Время перехода'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица логов переходов';

--
-- Dumping data for table `link_clicks`
--

INSERT INTO `link_clicks` (`id`, `short_link_id`, `ip_address`, `user_agent`, `referer`, `clicked_at`) VALUES
(1, 1, '192.168.1.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'https://example.com', '2025-07-28 11:48:56'),
(2, 1, '192.168.1.2', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36', NULL, '2025-07-28 12:18:56'),
(3, 2, '192.168.1.3', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15', 'https://mobile.example.com', '2025-07-28 12:33:56');

-- --------------------------------------------------------

--
-- Table structure for table `short_links`
--

CREATE TABLE `short_links` (
  `id` int(11) NOT NULL,
  `original_url` text NOT NULL COMMENT 'Оригинальная ссылка',
  `short_code` varchar(10) NOT NULL COMMENT 'Короткий код',
  `qr_code_path` varchar(255) DEFAULT NULL COMMENT 'Путь к QR коду',
  `clicks_count` int(11) DEFAULT 0 COMMENT 'Количество переходов',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Дата создания',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Дата обновления'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица коротких ссылок';

--
-- Dumping data for table `short_links`
--

INSERT INTO `short_links` (`id`, `original_url`, `short_code`, `qr_code_path`, `clicks_count`, `created_at`, `updated_at`) VALUES
(1, 'https://www.google.com', 'AbC123Xy', NULL, 5, '2025-07-28 12:48:56', '2025-07-28 12:48:56'),
(2, 'https://www.yiiframework.com', 'DeF456Zz', NULL, 3, '2025-07-28 12:48:56', '2025-07-28 12:48:56'),
(3, 'https://github.com', 'GhI789Ww', NULL, 8, '2025-07-28 12:48:56', '2025-07-28 12:48:56'),
(4, 'https://www.youtube.com/', 'DenuTbBJ', NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(5, 'https://fast.com/', 'E12PrmiB', NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(6, 'https://play.google.com/store/apps/details?id=com.google.android.apps.translate&hl=en_IN', 'sW94r8KL', NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(7, 'https://play.google.com/store/games?hl=en_IN', 'K0In6jF5', '/qr-codes/K0In6jF5.svg', 0, '0000-00-00 00:00:00', '2025-07-28 13:04:55'),
(8, 'https://play.google.com/store/apps/category/FAMILY?hl=en_IN', 'FBWpYL3r', '/qr-codes/FBWpYL3r.svg', 0, '0000-00-00 00:00:00', '2025-07-28 13:09:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `link_clicks`
--
ALTER TABLE `link_clicks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_link_clicks_short_link_id` (`short_link_id`),
  ADD KEY `idx_link_clicks_clicked_at` (`clicked_at`);

--
-- Indexes for table `short_links`
--
ALTER TABLE `short_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_short_links_short_code` (`short_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `link_clicks`
--
ALTER TABLE `link_clicks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `short_links`
--
ALTER TABLE `short_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `link_clicks`
--
ALTER TABLE `link_clicks`
  ADD CONSTRAINT `fk_link_clicks_short_link_id` FOREIGN KEY (`short_link_id`) REFERENCES `short_links` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
