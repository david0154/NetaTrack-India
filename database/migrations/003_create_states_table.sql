-- NetaTrack India: States Table
CREATE TABLE IF NOT EXISTS `states` (
  `id` SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `code` VARCHAR(10) NOT NULL UNIQUE,
  `region` ENUM('North','South','East','West','Northeast','Central') NOT NULL,
  `capital` VARCHAR(100) DEFAULT NULL,
  `total_districts` SMALLINT UNSIGNED DEFAULT 0,
  `current_ruling_party` VARCHAR(100) DEFAULT NULL,
  `cm_name` VARCHAR(191) DEFAULT NULL,
  `cm_leader_id` BIGINT UNSIGNED DEFAULT NULL,
  `total_budget` DECIMAL(20,2) DEFAULT NULL,
  `corruption_index` DECIMAL(5,2) DEFAULT 0.00,
  `infrastructure_score` DECIMAL(5,2) DEFAULT 0.00,
  `latitude` DECIMAL(10,7) DEFAULT NULL,
  `longitude` DECIMAL(10,7) DEFAULT NULL,
  `geojson_path` VARCHAR(500) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_states_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
