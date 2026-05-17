-- NetaTrack India: Phase 1 Migration 001
-- Users Table

CREATE TABLE IF NOT EXISTS `users` (
    `id`                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`                VARCHAR(150)    NOT NULL,
    `email`               VARCHAR(200)    NOT NULL UNIQUE,
    `password`            VARCHAR(255)    NOT NULL,
    `role`                ENUM('super_admin','admin','moderator','editor','public') NOT NULL DEFAULT 'public',
    `status`              ENUM('active','inactive','banned') NOT NULL DEFAULT 'active',
    `phone`               VARCHAR(20)     DEFAULT NULL,
    `avatar`              VARCHAR(500)    DEFAULT NULL,
    `bio`                 TEXT            DEFAULT NULL,
    `credibility_score`   INT UNSIGNED    NOT NULL DEFAULT 0,
    `email_verified_at`   DATETIME        DEFAULT NULL,
    `last_login_at`       DATETIME        DEFAULT NULL,
    `ip_address`          VARCHAR(45)     DEFAULT NULL,
    `remember_token`      VARCHAR(100)    DEFAULT NULL,
    `created_at`          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email`   (`email`),
    INDEX `idx_role`    (`role`),
    INDEX `idx_status`  (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default Super Admin (password: Admin@12345)
INSERT INTO `users` (`name`,`email`,`password`,`role`,`status`,`email_verified_at`) VALUES
('NetaTrack Admin', 'admin@netatrack.in',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
'super_admin','active',NOW());
