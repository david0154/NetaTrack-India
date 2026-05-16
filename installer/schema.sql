-- NetaTrack India — Complete Database Schema
-- Phase 1: All 18+ tables

SET NAMES utf8mb4;
SET time_zone = '+05:30';

CREATE DATABASE IF NOT EXISTS netatrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE netatrack;

-- 1. roles
CREATE TABLE IF NOT EXISTS roles (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(50) NOT NULL UNIQUE,
  permissions JSON,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO roles (name, permissions) VALUES
  ('superadmin', '{"all": true}'),
  ('admin',      '{"manage_content": true, "manage_users": true}'),
  ('editor',     '{"manage_content": true}'),
  ('user',       '{"submit": true}');

-- 2. users
CREATE TABLE IF NOT EXISTS users (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name            VARCHAR(160) NOT NULL,
  email           VARCHAR(255) NOT NULL UNIQUE,
  password_hash   VARCHAR(255) NOT NULL,
  role            ENUM('superadmin','admin','editor','user') DEFAULT 'user',
  avatar_url      VARCHAR(500),
  credibility_score SMALLINT UNSIGNED DEFAULT 50,
  total_submissions INT UNSIGNED DEFAULT 0,
  approved_submissions INT UNSIGNED DEFAULT 0,
  is_banned       TINYINT(1) DEFAULT 0,
  ban_reason      TEXT,
  email_verified  TINYINT(1) DEFAULT 0,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_role  (role)
) ENGINE=InnoDB;

-- 3. states
CREATE TABLE IF NOT EXISTS states (
  id         TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL UNIQUE,
  code       CHAR(3) NOT NULL UNIQUE,
  capital    VARCHAR(100),
  region     VARCHAR(60),
  population BIGINT UNSIGNED,
  area_sqkm  INT UNSIGNED,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO states (name, code, capital, region) VALUES
('Andhra Pradesh','AP','Amaravati','South'),('Arunachal Pradesh','AR','Itanagar','Northeast'),
('Assam','AS','Dispur','Northeast'),('Bihar','BR','Patna','East'),
('Chhattisgarh','CG','Raipur','Central'),('Goa','GA','Panaji','West'),
('Gujarat','GJ','Gandhinagar','West'),('Haryana','HR','Chandigarh','North'),
('Himachal Pradesh','HP','Shimla','North'),('Jharkhand','JH','Ranchi','East'),
('Karnataka','KA','Bengaluru','South'),('Kerala','KL','Thiruvananthapuram','South'),
('Madhya Pradesh','MP','Bhopal','Central'),('Maharashtra','MH','Mumbai','West'),
('Manipur','MN','Imphal','Northeast'),('Meghalaya','ML','Shillong','Northeast'),
('Mizoram','MZ','Aizawl','Northeast'),('Nagaland','NL','Kohima','Northeast'),
('Odisha','OD','Bhubaneswar','East'),('Punjab','PB','Chandigarh','North'),
('Rajasthan','RJ','Jaipur','North'),('Sikkim','SK','Gangtok','Northeast'),
('Tamil Nadu','TN','Chennai','South'),('Telangana','TG','Hyderabad','South'),
('Tripura','TR','Agartala','Northeast'),('Uttar Pradesh','UP','Lucknow','North'),
('Uttarakhand','UK','Dehradun','North'),('West Bengal','WB','Kolkata','East'),
('Delhi','DL','New Delhi','North'),('Jammu and Kashmir','JK','Srinagar','North');

-- 4. parties
CREATE TABLE IF NOT EXISTS parties (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(200) NOT NULL,
  abbreviation  VARCHAR(20) NOT NULL UNIQUE,
  logo_url      VARCHAR(500),
  founded_year  YEAR,
  ideology      VARCHAR(200),
  headquarters  VARCHAR(200),
  website       VARCHAR(300),
  color_code    CHAR(7),
  is_national   TINYINT(1) DEFAULT 0,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. leaders
CREATE TABLE IF NOT EXISTS leaders (
  id                        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name                      VARCHAR(200) NOT NULL,
  slug                      VARCHAR(220) NOT NULL UNIQUE,
  party_id                  INT UNSIGNED,
  state_id                  TINYINT UNSIGNED,
  position                  VARCHAR(200),
  constituency              VARCHAR(200),
  photo_url                 VARCHAR(500),
  date_of_birth             DATE,
  education                 VARCHAR(300),
  bio                       TEXT,
  twitter_handle            VARCHAR(100),
  -- Score fields
  promise_completion_score  DECIMAL(5,2) DEFAULT 0,
  project_delivery_score    DECIMAL(5,2) DEFAULT 0,
  budget_efficiency_score   DECIMAL(5,2) DEFAULT 0,
  public_satisfaction_score DECIMAL(5,2) DEFAULT 0,
  transparency_score        DECIMAL(5,2) DEFAULT 0,
  corruption_penalty        DECIMAL(5,2) DEFAULT 0,
  fake_claims_penalty       DECIMAL(5,2) DEFAULT 0,
  verification_trust_score  DECIMAL(5,2) DEFAULT 0,
  final_score               DECIMAL(5,2) DEFAULT 0,
  rank_label                ENUM('Excellent','Good','Average','Poor') DEFAULT 'Average',
  corruption_level          ENUM('Very Clean','Minor Allegations','Moderate','High Risk') DEFAULT 'Very Clean',
  total_promises            SMALLINT UNSIGNED DEFAULT 0,
  completed_promises        SMALLINT UNSIGNED DEFAULT 0,
  total_projects            SMALLINT UNSIGNED DEFAULT 0,
  is_active                 TINYINT(1) DEFAULT 1,
  last_scored_at            TIMESTAMP NULL,
  created_at                TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at                TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (party_id) REFERENCES parties(id) ON DELETE SET NULL,
  FOREIGN KEY (state_id) REFERENCES states(id)  ON DELETE SET NULL,
  INDEX idx_slug        (slug),
  INDEX idx_final_score (final_score)
) ENGINE=InnoDB;

-- 6. promises
CREATE TABLE IF NOT EXISTS promises (
  id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  leader_id          INT UNSIGNED NOT NULL,
  state_id           TINYINT UNSIGNED,
  title              VARCHAR(500) NOT NULL,
  slug               VARCHAR(520) NOT NULL UNIQUE,
  description        TEXT,
  category           VARCHAR(100),
  budget             DECIMAL(20,2),
  deadline           DATE,
  made_on            DATE,
  source_url         VARCHAR(500),
  status             ENUM('pending','in_progress','completed','delayed','broken','fake') DEFAULT 'pending',
  verification_score TINYINT UNSIGNED DEFAULT 0,
  ai_confidence      TINYINT UNSIGNED DEFAULT 0,
  is_ai_collected    TINYINT(1) DEFAULT 0,
  admin_verified     TINYINT(1) DEFAULT 0,
  verified_by        INT UNSIGNED,
  notes              TEXT,
  created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (leader_id) REFERENCES leaders(id) ON DELETE CASCADE,
  FOREIGN KEY (state_id)  REFERENCES states(id)  ON DELETE SET NULL,
  INDEX idx_leader_id (leader_id),
  INDEX idx_status    (status)
) ENGINE=InnoDB;

-- 7. projects
CREATE TABLE IF NOT EXISTS projects (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  leader_id         INT UNSIGNED,
  state_id          TINYINT UNSIGNED,
  title             VARCHAR(500) NOT NULL,
  slug              VARCHAR(520) NOT NULL UNIQUE,
  description       TEXT,
  category          VARCHAR(100),
  total_budget      DECIMAL(20,2),
  spent_budget      DECIMAL(20,2) DEFAULT 0,
  start_date        DATE,
  expected_end_date DATE,
  actual_end_date   DATE,
  progress_percent  TINYINT UNSIGNED DEFAULT 0,
  location_lat      DECIMAL(10,7),
  location_lng      DECIMAL(10,7),
  location_name     VARCHAR(300),
  status            ENUM('planned','in_progress','completed','delayed','stalled','cancelled') DEFAULT 'planned',
  tender_url        VARCHAR(500),
  source_url        VARCHAR(500),
  is_ai_collected   TINYINT(1) DEFAULT 0,
  admin_verified    TINYINT(1) DEFAULT 0,
  created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (leader_id) REFERENCES leaders(id) ON DELETE SET NULL,
  FOREIGN KEY (state_id)  REFERENCES states(id)  ON DELETE SET NULL,
  INDEX idx_status    (status),
  INDEX idx_leader_id (leader_id)
) ENGINE=InnoDB;

-- 8. corruption_cases
CREATE TABLE IF NOT EXISTS corruption_cases (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  leader_id      INT UNSIGNED NOT NULL,
  title          VARCHAR(400) NOT NULL,
  description    TEXT,
  agency         VARCHAR(100),
  case_number    VARCHAR(100),
  filed_on       DATE,
  current_status ENUM('alleged','under_investigation','chargesheeted','acquitted','convicted','pending') DEFAULT 'alleged',
  severity       ENUM('low','medium','high','critical') DEFAULT 'medium',
  source_url     VARCHAR(500),
  verified       TINYINT(1) DEFAULT 0,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (leader_id) REFERENCES leaders(id) ON DELETE CASCADE,
  INDEX idx_leader_id (leader_id)
) ENGINE=InnoDB;

-- 9. public_submissions
CREATE TABLE IF NOT EXISTS public_submissions (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id       INT UNSIGNED,
  type          ENUM('corruption','fake_claim','complaint','damage','project_update','other') DEFAULT 'other',
  title         VARCHAR(500) NOT NULL,
  leader_name   VARCHAR(200),
  description   TEXT NOT NULL,
  source_link   VARCHAR(500),
  location      VARCHAR(300),
  ip_address    VARCHAR(45),
  status        ENUM('pending','approved','rejected','investigating') DEFAULT 'pending',
  ai_spam_score TINYINT UNSIGNED DEFAULT 0,
  ai_verified   TINYINT(1) DEFAULT 0,
  admin_notes   TEXT,
  reviewed_by   INT UNSIGNED,
  reviewed_at   TIMESTAMP NULL,
  credibility_score TINYINT UNSIGNED DEFAULT 50,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)     REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_status    (status),
  INDEX idx_ip        (ip_address),
  INDEX idx_created   (created_at)
) ENGINE=InnoDB;

-- 10. media
CREATE TABLE IF NOT EXISTS media (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  entity_type  ENUM('leader','project','promise','submission','party') NOT NULL,
  entity_id    INT UNSIGNED NOT NULL,
  type         ENUM('image','video','document','audio') DEFAULT 'image',
  url          VARCHAR(500) NOT NULL,
  thumbnail    VARCHAR(500),
  caption      VARCHAR(500),
  size_bytes   BIGINT UNSIGNED,
  mime_type    VARCHAR(100),
  is_verified  TINYINT(1) DEFAULT 0,
  deepfake_score TINYINT UNSIGNED DEFAULT 0,
  uploaded_by  INT UNSIGNED,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_entity (entity_type, entity_id)
) ENGINE=InnoDB;

-- 11. verification_logs
CREATE TABLE IF NOT EXISTS verification_logs (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  entity_type VARCHAR(60) NOT NULL,
  entity_id   INT UNSIGNED NOT NULL,
  action      VARCHAR(100) NOT NULL,
  result      ENUM('verified','rejected','pending','escalated') DEFAULT 'pending',
  ai_model    VARCHAR(100),
  ai_response JSON,
  confidence  TINYINT UNSIGNED DEFAULT 0,
  reviewed_by INT UNSIGNED,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_entity (entity_type, entity_id)
) ENGINE=InnoDB;

-- 12. analytics
CREATE TABLE IF NOT EXISTS analytics (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  page       VARCHAR(300) NOT NULL,
  ip_address VARCHAR(45),
  country    CHAR(2),
  device     VARCHAR(50),
  referrer   VARCHAR(500),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_page (page),
  INDEX idx_date (created_at)
) ENGINE=InnoDB;

-- 13. settings
CREATE TABLE IF NOT EXISTS settings (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  key_name   VARCHAR(100) NOT NULL UNIQUE,
  value      TEXT,
  type       ENUM('text','textarea','boolean','json','color','file') DEFAULT 'text',
  group_name VARCHAR(60) DEFAULT 'general',
  label      VARCHAR(200),
  updated_by INT UNSIGNED,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT IGNORE INTO settings (key_name, value, type, group_name, label) VALUES
('site_name',     'NetaTrack India', 'text',    'general', 'Website Name'),
('site_tagline',  'Political Transparency & Public Accountability', 'text', 'general', 'Tagline'),
('site_logo',     '', 'file',    'general', 'Logo URL'),
('site_favicon',  '', 'file',    'general', 'Favicon URL'),
('contact_email', '', 'text',    'general', 'Contact Email'),
('dark_mode',     '1', 'boolean','general', 'Enable Dark Mode Toggle'),
('allow_register','1', 'boolean','general', 'Allow Public Registration'),
('smtp_host',     '', 'text',    'smtp',    'SMTP Host'),
('smtp_port',     '587', 'text', 'smtp',    'SMTP Port'),
('smtp_user',     '', 'text',    'smtp',    'SMTP Username'),
('smtp_pass',     '', 'text',    'smtp',    'SMTP Password'),
('smtp_from',     '', 'text',    'smtp',    'From Email'),
('analytics_ga',  '', 'text',    'analytics','Google Analytics ID'),
('meta_pixel',    '', 'text',    'analytics','Meta Pixel ID'),
('home_banner',   '', 'textarea','home',    'Homepage Banner HTML'),
('footer_text',   '', 'textarea','footer',  'Footer Content'),
('social_twitter','', 'text',   'social',  'Twitter/X URL'),
('social_facebook','','text',   'social',  'Facebook URL'),
('social_instagram','','text',  'social',  'Instagram URL'),
('ad_header',     '', 'textarea','ads',    'Header Ad Code'),
('ad_sidebar',    '', 'textarea','ads',    'Sidebar Ad Code'),
('ad_footer',     '', 'textarea','ads',    'Footer Ad Code'),
('primary_color', '#6366f1','color','theme','Primary Color'),
('accent_color',  '#f59e0b','color','theme','Accent Color');

-- 14. sponsors
CREATE TABLE IF NOT EXISTS sponsors (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(200) NOT NULL,
  logo_url   VARCHAR(500),
  website    VARCHAR(300),
  placement  VARCHAR(60) DEFAULT 'homepage',
  is_active  TINYINT(1) DEFAULT 1,
  sort_order TINYINT UNSIGNED DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 15. advertisements
CREATE TABLE IF NOT EXISTS advertisements (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(200) NOT NULL,
  type        ENUM('banner','video','native','sponsored') DEFAULT 'banner',
  placement   VARCHAR(100) NOT NULL,
  code        TEXT,
  image_url   VARCHAR(500),
  link_url    VARCHAR(500),
  start_date  DATE,
  end_date    DATE,
  impressions BIGINT UNSIGNED DEFAULT 0,
  clicks      BIGINT UNSIGNED DEFAULT 0,
  is_active   TINYINT(1) DEFAULT 1,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 16. scraper_sources
CREATE TABLE IF NOT EXISTS scraper_sources (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name           VARCHAR(200) NOT NULL,
  url            VARCHAR(500) NOT NULL,
  type           ENUM('static','dynamic','pdf','social','rss','parliament') DEFAULT 'static',
  purpose        VARCHAR(200),
  ai_confidence  TINYINT UNSIGNED DEFAULT 75,
  schedule       VARCHAR(50) DEFAULT '0 */6 * * *',
  last_scraped   TIMESTAMP NULL,
  items_scraped  INT UNSIGNED DEFAULT 0,
  is_active      TINYINT(1) DEFAULT 1,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 17. ai_collected_data
CREATE TABLE IF NOT EXISTS ai_collected_data (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  source_id       INT UNSIGNED,
  source_url      VARCHAR(500),
  raw_title       VARCHAR(500),
  raw_content     TEXT,
  entity_type     ENUM('promise','project','corruption','scheme','election','budget','other') DEFAULT 'other',
  ai_extracted    JSON,
  ai_model        VARCHAR(100),
  ai_confidence   TINYINT UNSIGNED DEFAULT 0,
  duplicate_of    INT UNSIGNED,
  status          ENUM('pending','approved','rejected','duplicate') DEFAULT 'pending',
  admin_notes     TEXT,
  reviewed_by     INT UNSIGNED,
  reviewed_at     TIMESTAMP NULL,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (source_id)   REFERENCES scraper_sources(id) ON DELETE SET NULL,
  FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_status    (status),
  INDEX idx_entity    (entity_type)
) ENGINE=InnoDB;

-- 18. api_keys
CREATE TABLE IF NOT EXISTS api_keys (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(200) NOT NULL,
  key_hash   VARCHAR(255) NOT NULL UNIQUE,
  user_id    INT UNSIGNED,
  rate_limit INT UNSIGNED DEFAULT 1000,
  calls_today INT UNSIGNED DEFAULT 0,
  last_used  TIMESTAMP NULL,
  is_active  TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
