-- NetaTrack India - Complete Database Schema
-- Phase 1: All core tables

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+05:30";

CREATE DATABASE IF NOT EXISTS `netatrack` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `netatrack`;

-- ROLES
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `permissions` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `roles` (`name`, `permissions`) VALUES
('superadmin', '{"all":true}'),
('admin', '{"content":true,"reports":true,"users":true}'),
('moderator', '{"reports":true}'),
('user', '{"submit":true}');

-- USERS
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL DEFAULT 4,
  `avatar` varchar(255) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `credibility_score` int(11) NOT NULL DEFAULT 0,
  `approved_reports` int(11) NOT NULL DEFAULT 0,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `is_banned` tinyint(1) NOT NULL DEFAULT 0,
  `ban_reason` text DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_role_fk` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PARTIES
CREATE TABLE `parties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `abbreviation` varchar(20) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `color` varchar(10) DEFAULT '#333333',
  `founded_year` year DEFAULT NULL,
  `ideology` varchar(255) DEFAULT NULL,
  `national_or_state` enum('national','state','regional') NOT NULL DEFAULT 'regional',
  `website` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- STATES
CREATE TABLE `states` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(5) NOT NULL,
  `region` varchar(50) DEFAULT NULL,
  `capital` varchar(100) DEFAULT NULL,
  `ruling_party_id` int(11) DEFAULT NULL,
  `cm_leader_id` int(11) DEFAULT NULL,
  `total_promises` int(11) NOT NULL DEFAULT 0,
  `completed_promises` int(11) NOT NULL DEFAULT 0,
  `total_projects` int(11) NOT NULL DEFAULT 0,
  `delayed_projects` int(11) NOT NULL DEFAULT 0,
  `corruption_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `infrastructure_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `budget_allocated` decimal(20,2) DEFAULT NULL,
  `budget_utilized` decimal(20,2) DEFAULT NULL,
  `population` bigint(20) DEFAULT NULL,
  `map_coordinates` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `ruling_party_id` (`ruling_party_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- LEADERS
CREATE TABLE `leaders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) NOT NULL,
  `name` varchar(150) NOT NULL,
  `name_hindi` varchar(150) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `party_id` int(11) DEFAULT NULL,
  `state_id` int(11) DEFAULT NULL,
  `constituency` varchar(150) DEFAULT NULL,
  `designation` varchar(150) DEFAULT NULL,
  `level` enum('national','state','district','local') NOT NULL DEFAULT 'state',
  `date_of_birth` date DEFAULT NULL,
  `education` varchar(255) DEFAULT NULL,
  `assets_declared` decimal(20,2) DEFAULT NULL,
  `criminal_cases` int(11) NOT NULL DEFAULT 0,
  -- Score fields
  `promise_completion_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `project_delivery_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `budget_efficiency_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `public_satisfaction_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `transparency_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `corruption_deduction` decimal(5,2) NOT NULL DEFAULT 0.00,
  `fake_claims_deduction` decimal(5,2) NOT NULL DEFAULT 0.00,
  `verification_trust_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `final_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `rank_label` enum('Excellent','Good','Average','Poor') NOT NULL DEFAULT 'Average',
  -- Corruption
  `corruption_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `corruption_level` enum('Very Clean','Minor Allegations','Moderate','High Corruption Risk') NOT NULL DEFAULT 'Very Clean',
  -- Metadata
  `total_promises` int(11) NOT NULL DEFAULT 0,
  `completed_promises` int(11) NOT NULL DEFAULT 0,
  `total_projects` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `social_twitter` varchar(255) DEFAULT NULL,
  `social_facebook` varchar(255) DEFAULT NULL,
  `social_instagram` varchar(255) DEFAULT NULL,
  `wiki_url` varchar(255) DEFAULT NULL,
  `bio` longtext DEFAULT NULL,
  `score_updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  KEY `party_id` (`party_id`),
  KEY `state_id` (`state_id`),
  KEY `final_score` (`final_score`),
  CONSTRAINT `leaders_party_fk` FOREIGN KEY (`party_id`) REFERENCES `parties` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leaders_state_fk` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PROMISES
CREATE TABLE `promises` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) NOT NULL,
  `leader_id` int(11) NOT NULL,
  `state_id` int(11) DEFAULT NULL,
  `title` varchar(500) NOT NULL,
  `description` longtext DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `source_type` enum('manifesto','speech','interview','social_media','official','scraped') NOT NULL DEFAULT 'scraped',
  `source_url` text DEFAULT NULL,
  `promised_date` date DEFAULT NULL,
  `deadline_date` date DEFAULT NULL,
  `status` enum('pending','in_progress','completed','partially_completed','broken','expired') NOT NULL DEFAULT 'pending',
  `completion_percent` int(11) NOT NULL DEFAULT 0,
  `verification_status` enum('unverified','verified','disputed','fake') NOT NULL DEFAULT 'unverified',
  `verified_by` int(11) DEFAULT NULL,
  `ai_confidence` decimal(5,2) DEFAULT NULL,
  `fake_claim_flag` tinyint(1) NOT NULL DEFAULT 0,
  `fake_claim_reason` text DEFAULT NULL,
  `evidence_url` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `views` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  KEY `leader_id` (`leader_id`),
  KEY `state_id` (`state_id`),
  KEY `status` (`status`),
  CONSTRAINT `promises_leader_fk` FOREIGN KEY (`leader_id`) REFERENCES `leaders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PROJECTS
CREATE TABLE `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) NOT NULL,
  `leader_id` int(11) DEFAULT NULL,
  `state_id` int(11) DEFAULT NULL,
  `promise_id` int(11) DEFAULT NULL,
  `title` varchar(500) NOT NULL,
  `description` longtext DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `department` varchar(150) DEFAULT NULL,
  `budget_allocated` decimal(20,2) DEFAULT NULL,
  `budget_spent` decimal(20,2) DEFAULT NULL,
  `tender_id` varchar(100) DEFAULT NULL,
  `contractor` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `expected_completion` date DEFAULT NULL,
  `actual_completion` date DEFAULT NULL,
  `status` enum('announced','tendered','in_progress','completed','delayed','cancelled','stalled') NOT NULL DEFAULT 'announced',
  `progress_percent` int(11) NOT NULL DEFAULT 0,
  `delay_reason` text DEFAULT NULL,
  `ground_reality` longtext DEFAULT NULL,
  `official_claim` longtext DEFAULT NULL,
  `verification_status` enum('unverified','verified','disputed') NOT NULL DEFAULT 'unverified',
  `source_url` text DEFAULT NULL,
  `location_lat` decimal(10,7) DEFAULT NULL,
  `location_lng` decimal(10,7) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `views` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  KEY `leader_id` (`leader_id`),
  KEY `state_id` (`state_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PUBLIC REPORTS
CREATE TABLE `public_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `leader_id` int(11) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `state_id` int(11) DEFAULT NULL,
  `type` enum('corruption','fake_claim','complaint','infrastructure_damage','project_update','rti_document','other') NOT NULL,
  `title` varchar(500) NOT NULL,
  `description` longtext NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `evidence_type` set('photo','video','document','link') DEFAULT NULL,
  `evidence_paths` longtext DEFAULT NULL,
  `source_links` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `device_info` text DEFAULT NULL,
  -- AI Verification
  `spam_score` decimal(5,2) DEFAULT NULL,
  `ai_verification_status` enum('pending','passed','failed','manual_review') NOT NULL DEFAULT 'pending',
  `ai_verification_notes` text DEFAULT NULL,
  `duplicate_of` int(11) DEFAULT NULL,
  `is_fake_media` tinyint(1) NOT NULL DEFAULT 0,
  -- Admin
  `status` enum('pending','approved','rejected','investigating','merged') NOT NULL DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `views` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  KEY `user_id` (`user_id`),
  KEY `leader_id` (`leader_id`),
  KEY `status` (`status`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- MEDIA
CREATE TABLE `media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) NOT NULL,
  `related_type` enum('leader','project','promise','report','party') NOT NULL,
  `related_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` enum('image','video','document','audio') NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `is_fake_detected` tinyint(1) NOT NULL DEFAULT 0,
  `deepfake_score` decimal(5,2) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  KEY `related_type_id` (`related_type`,`related_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- VERIFICATION LOGS
CREATE TABLE `verification_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `related_type` enum('promise','project','report','leader','claim') NOT NULL,
  `related_id` int(11) NOT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `verification_type` enum('manual','ai','auto_scrape','crowd') NOT NULL,
  `result` enum('verified','fake','disputed','inconclusive') NOT NULL,
  `confidence` decimal(5,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `sources_used` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `related_type_id` (`related_type`,`related_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- AI COLLECTED DATA (scraper queue)
CREATE TABLE `ai_collected_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scraper_job_id` int(11) DEFAULT NULL,
  `source_url` text NOT NULL,
  `source_type` enum('government','party','news','social','election','tender','parliament') NOT NULL,
  `content_type` enum('promise','project','corruption','scheme','budget','election','manifesto','speech') DEFAULT NULL,
  `raw_content` longtext DEFAULT NULL,
  `extracted_data` longtext DEFAULT NULL,
  `ai_confidence` decimal(5,2) DEFAULT NULL,
  `duplicate_hash` varchar(64) DEFAULT NULL,
  `is_duplicate` tinyint(1) NOT NULL DEFAULT 0,
  `fact_check_status` enum('pending','passed','failed','uncertain') NOT NULL DEFAULT 'pending',
  `admin_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `published_id` int(11) DEFAULT NULL,
  `published_type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `source_type` (`source_type`),
  KEY `admin_status` (`admin_status`),
  KEY `duplicate_hash` (`duplicate_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SCRAPER JOBS
CREATE TABLE `scraper_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `source_url` text NOT NULL,
  `scraper_type` enum('static','dynamic','heavy','social','pdf','video') NOT NULL DEFAULT 'static',
  `content_type` varchar(100) DEFAULT NULL,
  `schedule` varchar(100) DEFAULT 'daily',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_run_at` timestamp NULL DEFAULT NULL,
  `next_run_at` timestamp NULL DEFAULT NULL,
  `last_status` enum('idle','running','success','failed','paused') NOT NULL DEFAULT 'idle',
  `last_items_collected` int(11) NOT NULL DEFAULT 0,
  `total_items_collected` int(11) NOT NULL DEFAULT 0,
  `error_log` text DEFAULT NULL,
  `config_json` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ANALYTICS
CREATE TABLE `analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(100) NOT NULL,
  `related_type` varchar(50) DEFAULT NULL,
  `related_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `referrer` text DEFAULT NULL,
  `page_url` text DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `event_type` (`event_type`),
  KEY `related_type_id` (`related_type`,`related_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SETTINGS
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(191) NOT NULL,
  `value` longtext DEFAULT NULL,
  `type` enum('text','textarea','image','boolean','json','number','color','select') NOT NULL DEFAULT 'text',
  `group` varchar(100) NOT NULL DEFAULT 'general',
  `label` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`key`, `value`, `type`, `group`, `label`) VALUES
('site_name', 'NetaTrack India', 'text', 'general', 'Website Name'),
('site_tagline', 'Political Transparency & Public Accountability', 'text', 'general', 'Tagline'),
('site_logo', '', 'image', 'general', 'Logo'),
('site_favicon', '', 'image', 'general', 'Favicon'),
('site_description', 'Track political promises, government projects, budgets, and ground reality across all Indian states.', 'textarea', 'seo', 'Meta Description'),
('site_keywords', 'political transparency india, neta tracker, corruption india, promise tracker', 'text', 'seo', 'Meta Keywords'),
('google_analytics_id', '', 'text', 'analytics', 'Google Analytics ID'),
('meta_pixel_id', '', 'text', 'analytics', 'Meta Pixel ID'),
('custom_tracking_script', '', 'textarea', 'analytics', 'Custom Tracking Script'),
('smtp_host', '', 'text', 'smtp', 'SMTP Host'),
('smtp_port', '587', 'number', 'smtp', 'SMTP Port'),
('smtp_user', '', 'text', 'smtp', 'SMTP Username'),
('smtp_pass', '', 'text', 'smtp', 'SMTP Password'),
('smtp_from_email', '', 'text', 'smtp', 'From Email'),
('smtp_from_name', 'NetaTrack India', 'text', 'smtp', 'From Name'),
('smtp_driver', 'smtp', 'select', 'smtp', 'SMTP Driver'),
('theme_color_primary', '#6366f1', 'color', 'theme', 'Primary Color'),
('theme_color_accent', '#8b5cf6', 'color', 'theme', 'Accent Color'),
('dark_mode_default', '1', 'boolean', 'theme', 'Dark Mode Default'),
('homepage_banner', '', 'image', 'homepage', 'Homepage Banner'),
('footer_text', '© 2025 NetaTrack India. All rights reserved.', 'textarea', 'general', 'Footer Text'),
('social_twitter', '', 'text', 'social', 'Twitter URL'),
('social_facebook', '', 'text', 'social', 'Facebook URL'),
('social_instagram', '', 'text', 'social', 'Instagram URL'),
('social_youtube', '', 'text', 'social', 'YouTube URL'),
('google_ads_code', '', 'textarea', 'ads', 'Google Ads Code'),
('custom_banner_ad', '', 'textarea', 'ads', 'Custom Banner Ad HTML'),
('maintenance_mode', '0', 'boolean', 'general', 'Maintenance Mode'),
('registration_enabled', '1', 'boolean', 'general', 'User Registration'),
('ai_openai_key', '', 'text', 'ai', 'OpenAI API Key'),
('ai_gemini_key', '', 'text', 'ai', 'Gemini API Key'),
('ai_openrouter_key', '', 'text', 'ai', 'OpenRouter API Key'),
('ai_sarvam_key', '', 'text', 'ai', 'Sarvam AI API Key');

-- API KEYS
CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `key_hash` varchar(255) NOT NULL,
  `permissions` text DEFAULT NULL,
  `rate_limit` int(11) NOT NULL DEFAULT 1000,
  `calls_count` int(11) NOT NULL DEFAULT 0,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_hash` (`key_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SPONSORS / ADVERTISEMENTS
CREATE TABLE `advertisements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` enum('google_ads','custom_banner','sponsor','video','affiliate') NOT NULL DEFAULT 'custom_banner',
  `position` varchar(100) DEFAULT NULL,
  `content_html` longtext DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `target_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `impressions` int(11) NOT NULL DEFAULT 0,
  `clicks` int(11) NOT NULL DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SEO SETTINGS (per-page)
CREATE TABLE `seo_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_key` varchar(191) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `keywords` text DEFAULT NULL,
  `og_image` varchar(255) DEFAULT NULL,
  `canonical_url` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_key` (`page_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- USER SESSIONS
CREATE TABLE `user_sessions` (
  `id` varchar(191) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ADMIN AUDIT LOGS
CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `target_type` varchar(100) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `old_data` longtext DEFAULT NULL,
  `new_data` longtext DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PASSWORD RESETS
CREATE TABLE `password_resets` (
  `email` varchar(191) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
