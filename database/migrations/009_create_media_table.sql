-- NetaTrack India: Media Table
CREATE TABLE IF NOT EXISTS `media` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `original_name` VARCHAR(500) NOT NULL,
  `stored_name` VARCHAR(500) NOT NULL,
  `path` VARCHAR(1000) NOT NULL,
  `url` VARCHAR(1000) NOT NULL,
  `type` ENUM('image','video','document','audio') NOT NULL,
  `mime_type` VARCHAR(100) DEFAULT NULL,
  `size` BIGINT UNSIGNED DEFAULT NULL COMMENT 'in bytes',
  `width` SMALLINT UNSIGNED DEFAULT NULL,
  `height` SMALLINT UNSIGNED DEFAULT NULL,
  `duration` SMALLINT UNSIGNED DEFAULT NULL COMMENT 'seconds for video/audio',
  `entity_type` VARCHAR(50) DEFAULT NULL COMMENT 'leader, project, promise, report',
  `entity_id` BIGINT UNSIGNED DEFAULT NULL,
  `is_deepfake_checked` TINYINT(1) DEFAULT 0,
  `deepfake_score` DECIMAL(5,2) DEFAULT NULL,
  `is_manipulated` TINYINT(1) DEFAULT 0,
  `uploaded_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_media_entity` (`entity_type`,`entity_id`),
  INDEX `idx_media_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
