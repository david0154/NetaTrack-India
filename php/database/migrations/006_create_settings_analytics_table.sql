-- NetaTrack India: Phase 1 Migration 006
-- Settings, Analytics, API Keys Tables

CREATE TABLE IF NOT EXISTS `settings` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key`         VARCHAR(200)  NOT NULL UNIQUE,
    `value`       LONGTEXT      DEFAULT NULL,
    `group`       VARCHAR(100)  DEFAULT 'general',
    `type`        ENUM('text','textarea','boolean','number','json','color','image','select') DEFAULT 'text',
    `label`       VARCHAR(300)  DEFAULT NULL,
    `updated_at`  DATETIME      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_group` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `api_keys` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(200)  NOT NULL,
    `key`         VARCHAR(100)  NOT NULL UNIQUE,
    `user_id`     INT UNSIGNED  DEFAULT NULL,
    `permissions` JSON          DEFAULT NULL,
    `rate_limit`  INT           DEFAULT 1000,
    `last_used_at`DATETIME      DEFAULT NULL,
    `expires_at`  DATETIME      DEFAULT NULL,
    `is_active`   TINYINT(1)    DEFAULT 1,
    `created_at`  DATETIME      DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ai_collected_data` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `source_url`    VARCHAR(500)   NOT NULL,
    `source_type`   VARCHAR(100)   DEFAULT NULL,
    `raw_data`      LONGTEXT       DEFAULT NULL,
    `extracted_type`VARCHAR(100)   DEFAULT NULL,
    `extracted_data`JSON           DEFAULT NULL,
    `ai_confidence` DECIMAL(5,2)   DEFAULT 0,
    `status`        ENUM('pending','approved','rejected','duplicate') DEFAULT 'pending',
    `duplicate_of`  INT UNSIGNED   DEFAULT NULL,
    `scraped_at`    DATETIME       DEFAULT CURRENT_TIMESTAMP,
    `created_at`    DATETIME       DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_status`  (`status`),
    INDEX `idx_type`    (`extracted_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_logs` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`       INT UNSIGNED   DEFAULT NULL,
    `action`        VARCHAR(300)   NOT NULL,
    `model_type`    VARCHAR(100)   DEFAULT NULL,
    `model_id`      INT UNSIGNED   DEFAULT NULL,
    `old_values`    JSON           DEFAULT NULL,
    `new_values`    JSON           DEFAULT NULL,
    `ip_address`    VARCHAR(45)    DEFAULT NULL,
    `user_agent`    TEXT           DEFAULT NULL,
    `created_at`    DATETIME       DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user`   (`user_id`),
    INDEX `idx_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default Settings
INSERT INTO `settings` (`key`,`value`,`group`,`type`,`label`) VALUES
('site_name',       'NetaTrack India',                  'general','text',    'Site Name'),
('site_tagline',    'Political Transparency Platform',  'general','text',    'Tagline'),
('site_email',      'contact@netatrack.in',             'general','text',    'Contact Email'),
('site_phone',      '',                                 'general','text',    'Phone'),
('site_logo',       '',                                 'general','image',   'Logo'),
('site_favicon',    '',                                 'general','image',   'Favicon'),
('site_description','Track political promises, projects and leaders across India.','general','textarea','Meta Description'),
('site_keywords',   'politics,india,transparency,accountability','general','text','Meta Keywords'),
('maintenance_mode','0',                                'general','boolean', 'Maintenance Mode'),
('google_analytics','',                                 'analytics','text',  'Google Analytics ID'),
('meta_pixel',      '',                                 'analytics','text',  'Meta Pixel ID'),
('google_ads',      '',                                 'ads','textarea',    'Google Ads Code'),
('smtp_host',       'smtp.gmail.com',                   'smtp','text',       'SMTP Host'),
('smtp_port',       '587',                              'smtp','number',     'SMTP Port'),
('smtp_username',   '',                                 'smtp','text',       'SMTP Username'),
('smtp_password',   '',                                 'smtp','text',       'SMTP Password'),
('smtp_from_name',  'NetaTrack India',                  'smtp','text',       'From Name'),
('smtp_from_email', 'noreply@netatrack.in',             'smtp','text',       'From Email'),
('openai_api_key',  '',                                 'ai','text',         'OpenAI API Key'),
('gemini_api_key',  '',                                 'ai','text',         'Gemini API Key'),
('openrouter_key',  '',                                 'ai','text',         'OpenRouter API Key'),
('sarvam_api_key',  '',                                 'ai','text',         'Sarvam AI API Key'),
('theme_color_primary','#3b82f6',                       'theme','color',     'Primary Color'),
('theme_color_secondary','#8b5cf6',                     'theme','color',     'Secondary Color'),
('dark_mode_default','1',                               'theme','boolean',   'Dark Mode Default'),
('promises_tracked', '0',                               'stats','number',    'Promises Tracked'),
('projects_monitored','0',                              'stats','number',    'Projects Monitored'),
('corruption_cases', '0',                               'stats','number',    'Corruption Cases'),
('fake_claims',      '0',                               'stats','number',    'Fake Claims Detected')
ON DUPLICATE KEY UPDATE label=VALUES(label);
