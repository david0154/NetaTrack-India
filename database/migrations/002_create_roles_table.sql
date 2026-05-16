-- NetaTrack India: Roles Table
CREATE TABLE IF NOT EXISTS `roles` (
  `id` TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `permissions` JSON DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `roles` (`id`,`name`,`slug`,`permissions`) VALUES
(1,'Super Admin','super_admin','{"all":true}'),
(2,'Admin','admin','{"content":true,"reports":true,"scraper":true}'),
(3,'Public User','user','{"submit":true,"view":true}'),
(4,'Moderator','moderator','{"reports":true,"verify":true}'),
(5,'Journalist','journalist','{"submit":true,"view":true,"priority":true}');
