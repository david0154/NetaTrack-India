-- NetaTrack India: Scraper Sources
CREATE TABLE IF NOT EXISTS `scraper_sources` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(191) NOT NULL,
  `url` VARCHAR(1000) NOT NULL,
  `type` ENUM('government','party','news','social','election','tender','parliament','other') NOT NULL,
  `scrape_method` ENUM('static','dynamic','scrapy','selenium','ocr','ffmpeg') NOT NULL DEFAULT 'static',
  `selectors` JSON DEFAULT NULL COMMENT 'CSS/XPath selectors',
  `schedule_cron` VARCHAR(100) DEFAULT '0 * * * *' COMMENT 'cron expression',
  `last_scraped_at` TIMESTAMP NULL DEFAULT NULL,
  `last_status` ENUM('success','failed','timeout','blocked') DEFAULT NULL,
  `total_scraped` INT UNSIGNED DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `needs_js` TINYINT(1) NOT NULL DEFAULT 0,
  `requires_login` TINYINT(1) NOT NULL DEFAULT 0,
  `priority` TINYINT UNSIGNED DEFAULT 5 COMMENT '1-10',
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
