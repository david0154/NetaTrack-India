-- NetaTrack India: SMTP Logs Table
CREATE TABLE IF NOT EXISTS `smtp_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `to_email` VARCHAR(191) NOT NULL,
  `to_name` VARCHAR(191) DEFAULT NULL,
  `subject` VARCHAR(500) NOT NULL,
  `template` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('sent','failed','bounced') NOT NULL DEFAULT 'sent',
  `error_message` TEXT DEFAULT NULL,
  `sent_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_smtp_email` (`to_email`),
  INDEX `idx_smtp_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `email_templates` (
  `id` SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `subject` VARCHAR(300) NOT NULL,
  `body_html` LONGTEXT NOT NULL,
  `body_text` TEXT DEFAULT NULL,
  `variables` JSON DEFAULT NULL COMMENT 'Available template variables',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
