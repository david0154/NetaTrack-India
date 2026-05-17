-- Extra tables needed for Auto-Fetch Engine
-- Run this once after your main migration

CREATE TABLE IF NOT EXISTS `announcements` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `source_uid`    VARCHAR(64) UNIQUE,
  `source_name`   VARCHAR(120),
  `title`         VARCHAR(255) NOT NULL,
  `description`   TEXT,
  `link`          VARCHAR(500),
  `category`      ENUM('promise','project','fund','scam','criminal','election','general') DEFAULT 'general',
  `leader_id`     INT UNSIGNED NULL,
  `status`        ENUM('pending','approved','rejected') DEFAULT 'pending',
  `published_at`  DATETIME,
  `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `criminal_cases` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `leader_id`     INT UNSIGNED NOT NULL,
  `source_uid`    VARCHAR(64) UNIQUE,
  `title`         VARCHAR(255) NOT NULL,
  `type`          ENUM('criminal','corruption','fake','civil','other') DEFAULT 'other',
  `status`        ENUM('pending','convicted','acquitted','ongoing') DEFAULT 'ongoing',
  `is_fake`       TINYINT(1) DEFAULT 0,
  `description`   TEXT,
  `detected_at`   DATETIME,
  `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `fund_records` (
  `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `leader_id`         INT UNSIGNED NOT NULL UNIQUE,
  `allocated_cr`      DECIMAL(10,2) NULL,
  `utilized_cr`       DECIMAL(10,2) NULL,
  `utilization_pct`   DECIMAL(5,2) NULL,
  `projects_count`    INT NULL,
  `leakage_suspected` TINYINT(1) DEFAULT 0,
  `summary`           TEXT,
  `recorded_at`       DATETIME,
  `updated_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `leaders`
  ADD COLUMN IF NOT EXISTS `house`               VARCHAR(30) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `score_fund_utilization` INT DEFAULT 50,
  ADD COLUMN IF NOT EXISTS `score_rank`          INT DEFAULT 0;

ALTER TABLE `promises`
  ADD COLUMN IF NOT EXISTS `last_checked` DATETIME DEFAULT NULL;
