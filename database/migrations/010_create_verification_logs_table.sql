-- NetaTrack India: Verification Logs Table
CREATE TABLE IF NOT EXISTS `verification_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `entity_type` VARCHAR(50) NOT NULL COMMENT 'promise, project, report, leader',
  `entity_id` BIGINT UNSIGNED NOT NULL,
  `verification_type` ENUM('AI','Manual','Fact Check','Media','Document') NOT NULL,
  `verifier_id` BIGINT UNSIGNED DEFAULT NULL,
  `ai_model` VARCHAR(100) DEFAULT NULL,
  `input_data` JSON DEFAULT NULL,
  `output_data` JSON DEFAULT NULL,
  `confidence_score` DECIMAL(5,2) DEFAULT NULL,
  `verdict` ENUM('True','False','Partially True','Unverified','Misleading','Satire') DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `sources_checked` JSON DEFAULT NULL,
  `time_taken_ms` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_veriflog_entity` (`entity_type`,`entity_id`),
  INDEX `idx_veriflog_verdict` (`verdict`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
