-- NetaTrack India: API Keys Table
CREATE TABLE IF NOT EXISTS `api_keys` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(191) NOT NULL,
  `key` VARCHAR(100) NOT NULL UNIQUE,
  `secret` VARCHAR(255) DEFAULT NULL,
  `permissions` JSON DEFAULT NULL,
  `user_id` BIGINT UNSIGNED DEFAULT NULL,
  `rate_limit` INT UNSIGNED DEFAULT 1000 COMMENT 'requests per hour',
  `requests_today` INT UNSIGNED DEFAULT 0,
  `total_requests` BIGINT UNSIGNED DEFAULT 0,
  `last_used_at` TIMESTAMP NULL DEFAULT NULL,
  `last_used_ip` VARCHAR(45) DEFAULT NULL,
  `expires_at` TIMESTAMP NULL DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_apikeys_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
