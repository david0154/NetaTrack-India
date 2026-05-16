-- NetaTrack India: Sponsors & Advertisements Table
CREATE TABLE IF NOT EXISTS `sponsors` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(191) NOT NULL,
  `logo` VARCHAR(500) DEFAULT NULL,
  `website` VARCHAR(500) DEFAULT NULL,
  `type` ENUM('Gold','Silver','Bronze','Media Partner','NGO Partner') NOT NULL DEFAULT 'Bronze',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `display_order` SMALLINT UNSIGNED DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `advertisements` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(300) NOT NULL,
  `type` ENUM('banner','sidebar','popup','video','sponsored_content') NOT NULL DEFAULT 'banner',
  `position` VARCHAR(100) DEFAULT NULL COMMENT 'header, footer, sidebar_left, etc.',
  `image` VARCHAR(500) DEFAULT NULL,
  `link` VARCHAR(1000) DEFAULT NULL,
  `ad_code` TEXT DEFAULT NULL COMMENT 'raw HTML/JS ad code',
  `impressions` INT UNSIGNED DEFAULT 0,
  `clicks` INT UNSIGNED DEFAULT 0,
  `starts_at` DATE DEFAULT NULL,
  `ends_at` DATE DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
