-- NetaTrack India - Complete Database Schema
-- Phase 1: Core Tables
-- Engine: MySQL 8.0+

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =============================================
-- USERS & AUTHENTICATION
-- =============================================
CREATE TABLE IF NOT EXISTS `users` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`              VARCHAR(150) NOT NULL,
    `email`             VARCHAR(255) NOT NULL UNIQUE,
    `password`          VARCHAR(255) NOT NULL,
    `phone`             VARCHAR(20),
    `role`              ENUM('user','moderator','admin','superadmin') DEFAULT 'user',
    `avatar`            VARCHAR(500),
    `credibility_score` INT DEFAULT 0,
    `status`            ENUM('active','banned','pending') DEFAULT 'active',
    `email_verified_at` TIMESTAMP NULL,
    `remember_token`    VARCHAR(100),
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (`email`),
    INDEX idx_role (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- STATES
-- =============================================
CREATE TABLE IF NOT EXISTS `states` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`          VARCHAR(100) NOT NULL,
    `slug`          VARCHAR(100) NOT NULL UNIQUE,
    `code`          VARCHAR(5),
    `capital`       VARCHAR(100),
    `region`        VARCHAR(50),
    `population`    BIGINT,
    `area_km2`      DECIMAL(12,2),
    `map_coords`    JSON COMMENT 'GeoJSON coordinates for 3D map',
    `ruling_party`  VARCHAR(100),
    `chief_minister`VARCHAR(150),
    `governor`      VARCHAR(150),
    `budget_year`   DECIMAL(20,2) COMMENT 'Annual budget in crores INR',
    `image`         VARCHAR(500),
    `description`   TEXT,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- PARTIES
-- =============================================
CREATE TABLE IF NOT EXISTS `parties` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`          VARCHAR(150) NOT NULL,
    `abbreviation`  VARCHAR(20),
    `slug`          VARCHAR(150) UNIQUE,
    `logo`          VARCHAR(500),
    `color`         VARCHAR(20) DEFAULT '#3b82f6',
    `founded_year`  YEAR,
    `ideology`      VARCHAR(255),
    `headquarters`  VARCHAR(255),
    `website`       VARCHAR(500),
    `description`   TEXT,
    `is_national`   TINYINT(1) DEFAULT 0,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- LEADERS
-- =============================================
CREATE TABLE IF NOT EXISTS `leaders` (
    `id`                     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`                   VARCHAR(150) NOT NULL,
    `slug`                   VARCHAR(200) UNIQUE,
    `photo`                  VARCHAR(500),
    `date_of_birth`          DATE,
    `gender`                 ENUM('male','female','other'),
    `party_id`               INT UNSIGNED,
    `state_id`               INT UNSIGNED,
    `constituency`           VARCHAR(200),
    `position`               VARCHAR(200) COMMENT 'Current position/designation',
    `education`              TEXT,
    `bio`                    TEXT,
    `social_twitter`         VARCHAR(300),
    `social_facebook`        VARCHAR(300),
    `social_instagram`       VARCHAR(300),
    `social_youtube`         VARCHAR(300),
    -- Score components
    `promise_completion_rate`DECIMAL(5,2) DEFAULT 0 COMMENT '0-100',
    `project_delivery_rate`  DECIMAL(5,2) DEFAULT 0 COMMENT '0-100',
    `budget_efficiency`      DECIMAL(5,2) DEFAULT 0 COMMENT '0-100',
    `public_satisfaction`    DECIMAL(5,2) DEFAULT 0 COMMENT '0-100',
    `transparency_score`     DECIMAL(5,2) DEFAULT 0 COMMENT '0-100',
    `verification_trust`     DECIMAL(5,2) DEFAULT 0 COMMENT '0-100',
    `corruption_score`       DECIMAL(5,2) DEFAULT 0 COMMENT '0-100',
    `fake_claims_count`      INT DEFAULT 0,
    `overall_score`          DECIMAL(5,2) DEFAULT 0,
    `rank`                   ENUM('Excellent','Good','Average','Poor') DEFAULT 'Average',
    `is_verified`            TINYINT(1) DEFAULT 0,
    `status`                 ENUM('active','inactive','deceased') DEFAULT 'active',
    `created_at`             TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`             TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`party_id`) REFERENCES `parties`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`state_id`) REFERENCES `states`(`id`) ON DELETE SET NULL,
    INDEX idx_state (`state_id`),
    INDEX idx_party (`party_id`),
    INDEX idx_score (`overall_score`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- PROMISES
-- =============================================
CREATE TABLE IF NOT EXISTS `promises` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `leader_id`     INT UNSIGNED NOT NULL,
    `title`         VARCHAR(500) NOT NULL,
    `description`   TEXT,
    `category`      VARCHAR(100) COMMENT 'health, education, infrastructure, economy, etc.',
    `made_on`       DATE,
    `deadline`      DATE,
    `status`        ENUM('pending','in_progress','completed','broken','fake') DEFAULT 'pending',
    `proof_url`     VARCHAR(500),
    `source_url`    VARCHAR(500),
    `ai_verified`   TINYINT(1) DEFAULT 0,
    `ai_confidence` DECIMAL(5,2) DEFAULT 0,
    `admin_notes`   TEXT,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`leader_id`) REFERENCES `leaders`(`id`) ON DELETE CASCADE,
    INDEX idx_leader (`leader_id`),
    INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- GOVERNMENT PROJECTS
-- =============================================
CREATE TABLE IF NOT EXISTS `projects` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title`             VARCHAR(500) NOT NULL,
    `description`       TEXT,
    `leader_id`         INT UNSIGNED,
    `state_id`          INT UNSIGNED,
    `category`          VARCHAR(100) COMMENT 'roads, hospitals, schools, etc.',
    `allocated_budget`  DECIMAL(20,2) DEFAULT 0 COMMENT 'in INR',
    `spent_budget`      DECIMAL(20,2) DEFAULT 0,
    `start_date`        DATE,
    `expected_end_date` DATE,
    `actual_end_date`   DATE,
    `progress_pct`      TINYINT DEFAULT 0 COMMENT '0-100',
    `status`            ENUM('planned','in_progress','delayed','completed','cancelled') DEFAULT 'planned',
    `contractor`        VARCHAR(255),
    `tender_url`        VARCHAR(500),
    `source_url`        VARCHAR(500),
    `is_verified`       TINYINT(1) DEFAULT 0,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`leader_id`) REFERENCES `leaders`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`state_id`)  REFERENCES `states`(`id`)  ON DELETE SET NULL,
    INDEX idx_state  (`state_id`),
    INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- CORRUPTION ALLEGATIONS
-- =============================================
CREATE TABLE IF NOT EXISTS `corruption_allegations` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `leader_id`     INT UNSIGNED NOT NULL,
    `title`         VARCHAR(500) NOT NULL,
    `description`   TEXT,
    `type`          ENUM('scam','ed_case','cbi_case','court_case','rti_report','media_investigation','public_complaint') DEFAULT 'public_complaint',
    `amount_crores` DECIMAL(20,2) COMMENT 'Alleged scam amount in crores INR',
    `status`        ENUM('alleged','under_investigation','verified','dismissed','convicted') DEFAULT 'alleged',
    `source_url`    VARCHAR(500),
    `proof_url`     VARCHAR(500),
    `reported_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`leader_id`) REFERENCES `leaders`(`id`) ON DELETE CASCADE,
    INDEX idx_leader (`leader_id`),
    INDEX idx_type   (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- PUBLIC REPORTS
-- =============================================
CREATE TABLE IF NOT EXISTS `public_reports` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`       INT UNSIGNED,
    `type`          ENUM('corruption','fake_claim','complaint','infrastructure','project_update','evidence') DEFAULT 'complaint',
    `title`         VARCHAR(500) NOT NULL,
    `description`   TEXT NOT NULL,
    `leader_id`     INT UNSIGNED,
    `state_id`      INT UNSIGNED,
    `location`      VARCHAR(500),
    `media_paths`   JSON COMMENT 'Array of uploaded file paths',
    `status`        ENUM('pending','approved','rejected','fake') DEFAULT 'pending',
    `ai_verified`   TINYINT(1) DEFAULT 0,
    `ai_score`      DECIMAL(5,2) DEFAULT 0,
    `ai_notes`      TEXT,
    `reviewed_by`   INT UNSIGNED,
    `reviewed_at`   TIMESTAMP NULL,
    `admin_notes`   TEXT,
    `ip_address`    VARCHAR(45),
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)   ON DELETE SET NULL,
    FOREIGN KEY (`leader_id`)  REFERENCES `leaders`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`state_id`)   REFERENCES `states`(`id`)  ON DELETE SET NULL,
    INDEX idx_status (`status`),
    INDEX idx_type   (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- AI COLLECTED DATA (Scraper Output)
-- =============================================
CREATE TABLE IF NOT EXISTS `ai_collected_data` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title`         VARCHAR(500) NOT NULL,
    `content`       TEXT,
    `source_url`    VARCHAR(1000),
    `source_type`   ENUM('government','party','news','social_media','election','tender','parliament') DEFAULT 'news',
    `category`      VARCHAR(100),
    `leader_id`     INT UNSIGNED,
    `state_id`      INT UNSIGNED,
    `ai_category`   VARCHAR(100),
    `ai_confidence` DECIMAL(5,2) DEFAULT 0,
    `ai_summary`    TEXT,
    `is_fake`       TINYINT(1) DEFAULT 0,
    `status`        ENUM('pending_review','approved','rejected') DEFAULT 'pending_review',
    `scraped_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (`status`),
    INDEX idx_source (`source_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SETTINGS (CMS)
-- =============================================
CREATE TABLE IF NOT EXISTS `settings` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key`        VARCHAR(100) NOT NULL UNIQUE,
    `value`      TEXT,
    `type`       ENUM('text','textarea','image','boolean','json') DEFAULT 'text',
    `group`      VARCHAR(50) DEFAULT 'general',
    `label`      VARCHAR(200),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key   (`key`),
    INDEX idx_group (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- ANALYTICS
-- =============================================
CREATE TABLE IF NOT EXISTS `analytics` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `page`       VARCHAR(500) NOT NULL,
    `ip_address` VARCHAR(45),
    `user_agent` VARCHAR(500),
    `user_id`    INT UNSIGNED,
    `referrer`   VARCHAR(500),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_page (`page`(191)),
    INDEX idx_date (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- API KEYS
-- =============================================
CREATE TABLE IF NOT EXISTS `api_keys` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT UNSIGNED,
    `name`       VARCHAR(100),
    `key`        VARCHAR(255) NOT NULL UNIQUE,
    `permissions`JSON,
    `last_used`  TIMESTAMP NULL,
    `is_active`  TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- VERIFICATION LOGS
-- =============================================
CREATE TABLE IF NOT EXISTS `verification_logs` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `entity_type` ENUM('promise','project','report','leader','corruption') NOT NULL,
    `entity_id`   INT UNSIGNED NOT NULL,
    `action`      VARCHAR(100),
    `performed_by`INT UNSIGNED,
    `ai_result`   JSON,
    `notes`       TEXT,
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_entity (`entity_type`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- ADVERTISEMENTS
-- =============================================
CREATE TABLE IF NOT EXISTS `advertisements` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(200) NOT NULL,
    `type`       ENUM('google_ads','banner','sponsored','affiliate','video') DEFAULT 'banner',
    `position`   VARCHAR(100) COMMENT 'header, sidebar, footer, inline',
    `code`       TEXT COMMENT 'Ad code/HTML',
    `image`      VARCHAR(500),
    `link`       VARCHAR(500),
    `is_active`  TINYINT(1) DEFAULT 1,
    `impressions`INT DEFAULT 0,
    `clicks`     INT DEFAULT 0,
    `start_date` DATE,
    `end_date`   DATE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SCRAPER SOURCES
-- =============================================
CREATE TABLE IF NOT EXISTS `scraper_sources` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`         VARCHAR(200) NOT NULL,
    `url`          VARCHAR(1000) NOT NULL,
    `type`         ENUM('rss','html','api','social') DEFAULT 'rss',
    `source_type`  ENUM('government','party','news','social_media','election','tender','parliament') DEFAULT 'news',
    `interval_min` INT DEFAULT 60 COMMENT 'Scrape interval in minutes',
    `is_active`    TINYINT(1) DEFAULT 1,
    `last_scraped` TIMESTAMP NULL,
    `fail_count`   INT DEFAULT 0,
    `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- DEFAULT DATA SEEDING
-- =============================================
INSERT IGNORE INTO `settings` (`key`, `value`, `type`, `group`, `label`) VALUES
('site_name',        'NetaTrack India',                      'text',    'general', 'Website Name'),
('site_tagline',     'Political Transparency & Accountability','text',   'general', 'Tagline'),
('site_logo',        '/assets/img/logo.png',                 'image',   'general', 'Logo'),
('site_favicon',     '/assets/img/favicon.ico',              'image',   'general', 'Favicon'),
('site_email',       'contact@netatrack.in',                 'text',    'general', 'Contact Email'),
('site_phone',       '',                                     'text',    'general', 'Phone'),
('primary_color',    '#6366f1',                              'text',    'theme',   'Primary Color'),
('dark_mode_default','true',                                 'boolean', 'theme',   'Dark Mode Default'),
('google_analytics', '',                                     'textarea','analytics','GA Script'),
('meta_pixel',       '',                                     'textarea','analytics','Meta Pixel'),
('smtp_host',        '',                                     'text',    'smtp',    'SMTP Host'),
('smtp_port',        '587',                                  'text',    'smtp',    'SMTP Port'),
('smtp_username',    '',                                     'text',    'smtp',    'SMTP Username'),
('smtp_from',        'noreply@netatrack.in',                 'text',    'smtp',    'From Email'),
('footer_text',      '© 2025 NetaTrack India. Political transparency for every citizen.', 'textarea', 'general', 'Footer Text'),
('social_twitter',   'https://twitter.com/netatrack',        'text',    'social',  'Twitter'),
('social_facebook',  'https://facebook.com/netatrack',       'text',    'social',  'Facebook');

-- Seed Indian States
INSERT IGNORE INTO `states` (`name`, `slug`, `code`, `capital`, `region`) VALUES
('Andhra Pradesh',      'andhra-pradesh',    'AP', 'Amaravati',     'South'),
('Arunachal Pradesh',   'arunachal-pradesh', 'AR', 'Itanagar',      'Northeast'),
('Assam',               'assam',             'AS', 'Dispur',        'Northeast'),
('Bihar',               'bihar',             'BR', 'Patna',         'East'),
('Chhattisgarh',        'chhattisgarh',      'CG', 'Raipur',        'Central'),
('Goa',                 'goa',               'GA', 'Panaji',        'West'),
('Gujarat',             'gujarat',           'GJ', 'Gandhinagar',   'West'),
('Haryana',             'haryana',           'HR', 'Chandigarh',    'North'),
('Himachal Pradesh',    'himachal-pradesh',  'HP', 'Shimla',        'North'),
('Jharkhand',           'jharkhand',         'JH', 'Ranchi',        'East'),
('Karnataka',           'karnataka',         'KA', 'Bengaluru',     'South'),
('Kerala',              'kerala',            'KL', 'Thiruvananthapuram','South'),
('Madhya Pradesh',      'madhya-pradesh',    'MP', 'Bhopal',        'Central'),
('Maharashtra',         'maharashtra',       'MH', 'Mumbai',        'West'),
('Manipur',             'manipur',           'MN', 'Imphal',        'Northeast'),
('Meghalaya',           'meghalaya',         'ML', 'Shillong',      'Northeast'),
('Mizoram',             'mizoram',           'MZ', 'Aizawl',        'Northeast'),
('Nagaland',            'nagaland',          'NL', 'Kohima',        'Northeast'),
('Odisha',              'odisha',            'OD', 'Bhubaneswar',   'East'),
('Punjab',              'punjab',            'PB', 'Chandigarh',    'North'),
('Rajasthan',           'rajasthan',         'RJ', 'Jaipur',        'North'),
('Sikkim',              'sikkim',            'SK', 'Gangtok',       'Northeast'),
('Tamil Nadu',          'tamil-nadu',        'TN', 'Chennai',       'South'),
('Telangana',           'telangana',         'TS', 'Hyderabad',     'South'),
('Tripura',             'tripura',           'TR', 'Agartala',      'Northeast'),
('Uttar Pradesh',       'uttar-pradesh',     'UP', 'Lucknow',       'North'),
('Uttarakhand',         'uttarakhand',       'UK', 'Dehradun',      'North'),
('West Bengal',         'west-bengal',       'WB', 'Kolkata',       'East'),
('Delhi',               'delhi',             'DL', 'New Delhi',     'North'),
('Jammu and Kashmir',   'jammu-kashmir',     'JK', 'Srinagar',      'North');

-- Seed National Parties
INSERT IGNORE INTO `parties` (`name`, `abbreviation`, `slug`, `color`, `is_national`) VALUES
('Bharatiya Janata Party',    'BJP',  'bjp',  '#FF6600', 1),
('Indian National Congress',  'INC',  'inc',  '#138808', 1),
('Aam Aadmi Party',           'AAP',  'aap',  '#0066CC', 1),
('Bahujan Samaj Party',       'BSP',  'bsp',  '#1B5E20', 1),
('Samajwadi Party',           'SP',   'sp',   '#FF0000', 1),
('Trinamool Congress',        'TMC',  'tmc',  '#298EB5', 0),
('Dravida Munnetra Kazhagam', 'DMK',  'dmk',  '#CC0000', 0),
('Telugu Desam Party',        'TDP',  'tdp',  '#FFFF00', 0),
('Shiv Sena (UBT)',           'SHS',  'shs',  '#F36F21', 0),
('Communist Party of India',  'CPI',  'cpi',  '#CC0000', 1);
