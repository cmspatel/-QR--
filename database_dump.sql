-- Дамп базы данных для сервиса коротких ссылок
-- Версия MySQL: 5.7+

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Создание базы данных
CREATE DATABASE IF NOT EXISTS `qr_database` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `qr_database`;

-- Таблица коротких ссылок
CREATE TABLE `short_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original_url` text NOT NULL COMMENT 'Оригинальная ссылка',
  `short_code` varchar(10) NOT NULL COMMENT 'Короткий код',
  `qr_code_path` varchar(255) DEFAULT NULL COMMENT 'Путь к QR коду',
  `clicks_count` int(11) DEFAULT 0 COMMENT 'Количество переходов',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата обновления',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_short_links_short_code` (`short_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица коротких ссылок';

-- Таблица логов переходов
CREATE TABLE `link_clicks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `short_link_id` int(11) NOT NULL COMMENT 'ID короткой ссылки',
  `ip_address` varchar(45) NOT NULL COMMENT 'IP адрес',
  `user_agent` text DEFAULT NULL COMMENT 'User Agent',
  `referer` text DEFAULT NULL COMMENT 'Реферер',
  `clicked_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Время перехода',
  PRIMARY KEY (`id`),
  KEY `idx_link_clicks_short_link_id` (`short_link_id`),
  KEY `idx_link_clicks_clicked_at` (`clicked_at`),
  CONSTRAINT `fk_link_clicks_short_link_id` FOREIGN KEY (`short_link_id`) REFERENCES `short_links` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица логов переходов';

-- Примеры данных для тестирования
INSERT INTO `short_links` (`original_url`, `short_code`, `clicks_count`, `created_at`) VALUES
('https://www.google.com', 'AbC123Xy', 5, NOW()),
('https://www.yiiframework.com', 'DeF456Zz', 3, NOW()),
('https://github.com', 'GhI789Ww', 8, NOW());

-- Примеры логов переходов
INSERT INTO `link_clicks` (`short_link_id`, `ip_address`, `user_agent`, `referer`, `clicked_at`) VALUES
(1, '192.168.1.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'https://example.com', NOW() - INTERVAL 1 HOUR),
(1, '192.168.1.2', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36', NULL, NOW() - INTERVAL 30 MINUTE),
(2, '192.168.1.3', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15', 'https://mobile.example.com', NOW() - INTERVAL 15 MINUTE);

COMMIT; 