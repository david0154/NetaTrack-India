-- NetaTrack India: Corruption Cases Table
CREATE TABLE IF NOT EXISTS `corruption_cases` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `leader_id` BIGINT UNSIGNED NOT NULL,
  `case_title` VARCHAR(500) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `type` ENUM('Scam','ED Case','CBI Case','Court Case','RTI Revelation','Media Investigation','Public Complaint','Other') NOT NULL DEFAULT 'Other',
  `agency` VARCHAR(191) DEFAULT NULL COMMENT 'ED, CBI, Court Name, etc.',
  `case_number` VARCHAR(200) DEFAULT NULL,
  `amount_involved` DECIMAL(20,2) DEFAULT NULL,
  `status` ENUM('Alleged','Under Investigation','Chargesheeted','Convicted','Acquitted','Closed') NOT NULL DEFAULT 'Alleged',
  `started_at` DATE DEFAULT NULL,
  `source_url` VARCHAR(1000) DEFAULT NULL,
  `evidence_urls` JSON DEFAULT NULL,
  `ai_severity_score` DECIMAL(5,2) DEFAULT NULL COMMENT '0-100',
  `is_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `verified_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_corruption_leader` (`leader_id`),
  INDEX `idx_corruption_type` (`type`),
  INDEX `idx_corruption_status` (`status`),
  CONSTRAINT `fk_corruption_leader` FOREIGN KEY (`leader_id`) REFERENCES `leaders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
