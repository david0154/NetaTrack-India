-- NetaTrack India: Phase 1 Migration 005
-- Public Reports and Media Tables

CREATE TABLE IF NOT EXISTS `public_reports` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`       INT UNSIGNED   DEFAULT NULL,
    `leader_id`     INT UNSIGNED   DEFAULT NULL,
    `state_id`      INT UNSIGNED   DEFAULT NULL,
    `project_id`    INT UNSIGNED   DEFAULT NULL,
    `type`          ENUM('corruption','fake_claim','complaint','infrastructure','project_update','other') DEFAULT 'other',
    `title`         VARCHAR(500)   NOT NULL,
    `description`   TEXT           NOT NULL,
    `evidence_urls` TEXT           DEFAULT NULL,
    `rti_doc_url`   VARCHAR(500)   DEFAULT NULL,
    `location`      VARCHAR(300)   DEFAULT NULL,
    `latitude`      DECIMAL(10,7)  DEFAULT NULL,
    `longitude`     DECIMAL(10,7)  DEFAULT NULL,
    `status`        ENUM('pending','approved','rejected','under_review','duplicate') DEFAULT 'pending',
    `ai_spam_score` DECIMAL(5,2)   DEFAULT 0,
    `ai_verified`   TINYINT(1)     DEFAULT 0,
    `ai_fake_score` DECIMAL(5,2)   DEFAULT 0,
    `is_duplicate`  TINYINT(1)     DEFAULT 0,
    `duplicate_of`  INT UNSIGNED   DEFAULT NULL,
    `admin_notes`   TEXT           DEFAULT NULL,
    `reported_at`   DATETIME       DEFAULT CURRENT_TIMESTAMP,
    `created_at`    DATETIME       DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)    ON DELETE SET NULL,
    FOREIGN KEY (`leader_id`)  REFERENCES `leaders`(`id`)  ON DELETE SET NULL,
    FOREIGN KEY (`state_id`)   REFERENCES `states`(`id`)   ON DELETE SET NULL,
    INDEX `idx_status`  (`status`),
    INDEX `idx_type`    (`type`),
    INDEX `idx_user`    (`user_id`),
    INDEX `idx_leader`  (`leader_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `media` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`       INT UNSIGNED   DEFAULT NULL,
    `related_type`  VARCHAR(50)    DEFAULT NULL,
    `related_id`    INT UNSIGNED   DEFAULT NULL,
    `file_name`     VARCHAR(300)   NOT NULL,
    `file_path`     VARCHAR(500)   NOT NULL,
    `file_type`     VARCHAR(100)   DEFAULT NULL,
    `file_size`     INT UNSIGNED   DEFAULT 0,
    `mime_type`     VARCHAR(100)   DEFAULT NULL,
    `ai_scanned`    TINYINT(1)     DEFAULT 0,
    `ai_safe`       TINYINT(1)     DEFAULT 1,
    `is_deepfake`   TINYINT(1)     DEFAULT 0,
    `created_at`    DATETIME       DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_related` (`related_type`,`related_id`),
    INDEX `idx_user`    (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `verification_logs` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `related_type`  VARCHAR(50)    NOT NULL,
    `related_id`    INT UNSIGNED   NOT NULL,
    `action`        VARCHAR(100)   NOT NULL,
    `performed_by`  INT UNSIGNED   DEFAULT NULL,
    `ai_engine`     VARCHAR(100)   DEFAULT NULL,
    `result`        VARCHAR(50)    DEFAULT NULL,
    `confidence`    DECIMAL(5,2)   DEFAULT NULL,
    `notes`         TEXT           DEFAULT NULL,
    `created_at`    DATETIME       DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_related` (`related_type`,`related_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
