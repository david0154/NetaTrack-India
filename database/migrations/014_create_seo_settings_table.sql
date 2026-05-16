-- NetaTrack India: Per-page SEO Settings
CREATE TABLE IF NOT EXISTS `seo_settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `page_type` VARCHAR(100) NOT NULL COMMENT 'homepage, leader, state, promise, project',
  `entity_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'null for global page types',
  `meta_title` VARCHAR(300) DEFAULT NULL,
  `meta_description` VARCHAR(500) DEFAULT NULL,
  `meta_keywords` VARCHAR(500) DEFAULT NULL,
  `og_title` VARCHAR(300) DEFAULT NULL,
  `og_description` VARCHAR(500) DEFAULT NULL,
  `og_image` VARCHAR(500) DEFAULT NULL,
  `schema_json` JSON DEFAULT NULL COMMENT 'JSON-LD structured data',
  `canonical_url` VARCHAR(1000) DEFAULT NULL,
  `robots` VARCHAR(100) DEFAULT 'index,follow',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_seo_page` (`page_type`,`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
