-- NetaTrack India — Complete Database Schema
-- Matches seed_states_leaders.sql columns exactly

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL UNIQUE,
    permissions JSON NOT NULL DEFAULT ('[]'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO roles (name, permissions) VALUES
  ('super_admin', '["all"]'),
  ('admin',       '["content","reports","leaders"]'),
  ('moderator',   '["reports"]'),
  ('viewer',      '["read"]');

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role_id INT NOT NULL DEFAULT 4,
    credibility_score INT NOT NULL DEFAULT 50,
    is_banned TINYINT(1) NOT NULL DEFAULT 0,
    ban_reason VARCHAR(255) DEFAULT NULL,
    email_verified TINYINT(1) NOT NULL DEFAULT 0,
    verification_token VARCHAR(100) DEFAULT NULL,
    reset_token VARCHAR(100) DEFAULT NULL,
    reset_token_expires TIMESTAMP NULL DEFAULT NULL,
    last_login TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE IF NOT EXISTS parties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE,
    abbreviation VARCHAR(20) NOT NULL,
    color VARCHAR(20) DEFAULT '#808080',
    symbol_url VARCHAR(500) DEFAULT NULL,
    founded_year INT DEFAULT NULL,
    ideology VARCHAR(255) DEFAULT NULL,
    headquarters VARCHAR(255) DEFAULT NULL,
    website_url VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS states (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    code VARCHAR(10) NOT NULL UNIQUE,
    capital VARCHAR(100) DEFAULT NULL,
    region VARCHAR(80) DEFAULT NULL,
    type ENUM('state','ut') DEFAULT 'state',
    total_seats INT DEFAULT 0,
    population BIGINT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS leaders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    party_id INT DEFAULT NULL,
    state_id INT DEFAULT NULL,
    constituency VARCHAR(200) DEFAULT NULL,
    role VARCHAR(200) DEFAULT NULL,
    position VARCHAR(200) DEFAULT NULL,
    photo_url VARCHAR(500) DEFAULT NULL,
    dob DATE DEFAULT NULL,
    birth_date DATE DEFAULT NULL,
    education TEXT DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    total_score DECIMAL(5,2) DEFAULT 0,
    attendance_score DECIMAL(5,2) DEFAULT 0,
    promise_score DECIMAL(5,2) DEFAULT 0,
    project_score DECIMAL(5,2) DEFAULT 0,
    criminal_score DECIMAL(5,2) DEFAULT 0,
    fund_score DECIMAL(5,2) DEFAULT 0,
    transparency_score DECIMAL(5,2) DEFAULT 0,
    public_score DECIMAL(5,2) DEFAULT 0,
    final_score DECIMAL(5,2) DEFAULT 0,
    corruption_level INT DEFAULT 0,
    rank_label VARCHAR(20) DEFAULT 'Unranked',
    social_twitter VARCHAR(255) DEFAULT NULL,
    social_facebook VARCHAR(255) DEFAULT NULL,
    official_website VARCHAR(500) DEFAULT NULL,
    verified TINYINT(1) DEFAULT 0,
    status ENUM('active','inactive','banned') DEFAULT 'active',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (party_id) REFERENCES parties(id),
    FOREIGN KEY (state_id) REFERENCES states(id)
);

CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(200) NOT NULL UNIQUE,
    title VARCHAR(300) NOT NULL,
    description LONGTEXT DEFAULT NULL,
    category VARCHAR(120) DEFAULT NULL,
    state_id INT DEFAULT NULL,
    leader_id INT DEFAULT NULL,
    budget DECIMAL(20,2) DEFAULT NULL,
    spent DECIMAL(20,2) DEFAULT 0,
    start_date DATE DEFAULT NULL,
    expected_end_date DATE DEFAULT NULL,
    actual_end_date DATE DEFAULT NULL,
    status ENUM('not_started','in_progress','completed','delayed','cancelled') DEFAULT 'not_started',
    progress_percent INT DEFAULT 0,
    source_url VARCHAR(500) DEFAULT NULL,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (state_id) REFERENCES states(id),
    FOREIGN KEY (leader_id) REFERENCES leaders(id)
);

CREATE TABLE IF NOT EXISTS promises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(200) NOT NULL UNIQUE,
    title VARCHAR(300) NOT NULL,
    description LONGTEXT NOT NULL,
    leader_id INT DEFAULT NULL,
    state_id INT DEFAULT NULL,
    category VARCHAR(120) NOT NULL,
    budget DECIMAL(20,2) DEFAULT NULL,
    deadline DATE DEFAULT NULL,
    promise_date DATE DEFAULT NULL,
    status ENUM('pending','in_progress','completed','failed','delayed','fake') DEFAULT 'pending',
    source_name VARCHAR(200) NOT NULL,
    source_url VARCHAR(500) NOT NULL,
    fact_check_notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (leader_id) REFERENCES leaders(id),
    FOREIGN KEY (state_id) REFERENCES states(id)
);

CREATE TABLE IF NOT EXISTS public_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(300) NOT NULL,
    leader_name VARCHAR(200) NOT NULL,
    leader_id INT DEFAULT NULL,
    state_id INT DEFAULT NULL,
    state VARCHAR(120) NOT NULL,
    description LONGTEXT NOT NULL,
    submission_type ENUM('corruption','fake_claim','complaint','infrastructure','project_update','other') DEFAULT 'other',
    source_link VARCHAR(500) DEFAULT NULL,
    media_paths JSON DEFAULT NULL,
    ai_spam_score INT DEFAULT 0,
    ai_fake_score INT DEFAULT 0,
    ai_verified TINYINT(1) DEFAULT 0,
    status ENUM('pending','approved','rejected','duplicate','under_review') DEFAULT 'pending',
    submitted_by INT DEFAULT NULL,
    reviewed_by INT DEFAULT NULL,
    reviewed_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (leader_id) REFERENCES leaders(id)
);

CREATE TABLE IF NOT EXISTS media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(300) NOT NULL,
    original_name VARCHAR(300) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    size_bytes BIGINT NOT NULL,
    storage_path VARCHAR(600) NOT NULL,
    public_url VARCHAR(600) DEFAULT NULL,
    related_type ENUM('submission','leader','project','promise','other') DEFAULT 'other',
    related_id INT DEFAULT NULL,
    uploaded_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS corruption_cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    leader_id INT DEFAULT NULL,
    title VARCHAR(300) NOT NULL,
    description LONGTEXT DEFAULT NULL,
    agency ENUM('CBI','ED','IT','ACB','Court','Media','RTI','Other') DEFAULT 'Other',
    case_number VARCHAR(200) DEFAULT NULL,
    amount_crore DECIMAL(20,2) DEFAULT NULL,
    status ENUM('alleged','under_investigation','chargesheeted','convicted','acquitted','closed') DEFAULT 'alleged',
    severity ENUM('low','medium','high','critical') DEFAULT 'low',
    source_url VARCHAR(600) DEFAULT NULL,
    reported_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (leader_id) REFERENCES leaders(id)
);

CREATE TABLE IF NOT EXISTS scraper_sources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    url VARCHAR(600) NOT NULL,
    scraper_type ENUM('static','dynamic','pdf','social','rss','api') DEFAULT 'static',
    category VARCHAR(120) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_scraped TIMESTAMP NULL DEFAULT NULL,
    scrape_interval_hours INT DEFAULT 24,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(100) NOT NULL,
    entity_type VARCHAR(60) DEFAULT NULL,
    entity_id INT DEFAULT NULL,
    user_id INT DEFAULT NULL,
    ip_hash VARCHAR(64) DEFAULT NULL,
    meta JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event (event_type),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at)
);

CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    value LONGTEXT DEFAULT NULL,
    type ENUM('string','json','boolean','integer') DEFAULT 'string',
    group_name VARCHAR(60) DEFAULT 'general',
    label VARCHAR(200) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT IGNORE INTO settings (`key`, value, type, group_name, label) VALUES
  ('site_name',              'NetaTrack India',                 'string',  'general', 'Site Name'),
  ('site_tagline',           'Political Transparency Platform', 'string',  'general', 'Tagline'),
  ('site_email',             'admin@netatrack.in',              'string',  'general', 'Admin Email'),
  ('maintenance_mode',       'false',                           'boolean', 'general', 'Maintenance Mode'),
  ('gemini_api_key',         '',                                'string',  'ai',      'Gemini API Key'),
  ('openai_api_key',         '',                                'string',  'ai',      'OpenAI API Key'),
  ('sarvam_api_key',         '',                                'string',  'ai',      'Sarvam AI Key'),
  ('ai_confidence_threshold','75',                              'integer', 'ai',      'Min AI Confidence'),
  ('theme_primary_color',    '#f97316',                         'string',  'theme',   'Primary Color'),
  ('dark_mode_default',      'true',                            'boolean', 'theme',   'Dark Mode Default');

CREATE TABLE IF NOT EXISTS sponsors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    logo_url VARCHAR(500) DEFAULT NULL,
    website_url VARCHAR(500) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS advertisements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sponsor_id INT DEFAULT NULL,
    title VARCHAR(200) NOT NULL,
    ad_type ENUM('banner','sidebar','inline','video','google_adsense') DEFAULT 'banner',
    placement VARCHAR(100) DEFAULT NULL,
    image_url VARCHAR(500) DEFAULT NULL,
    target_url VARCHAR(500) DEFAULT NULL,
    ad_code LONGTEXT DEFAULT NULL,
    impressions INT DEFAULT 0,
    clicks INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sponsor_id) REFERENCES sponsors(id)
);

CREATE TABLE IF NOT EXISTS seo_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_path VARCHAR(300) NOT NULL UNIQUE,
    title VARCHAR(300) DEFAULT NULL,
    description VARCHAR(500) DEFAULT NULL,
    og_image VARCHAR(500) DEFAULT NULL,
    no_index TINYINT(1) DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT IGNORE INTO seo_settings (page_path, title, description) VALUES
  ('/','NetaTrack India — Political Transparency Platform','Track promises, projects, and corruption allegations across all Indian states'),
  ('/leaders','Leaders | NetaTrack India','Browse and track political leaders across India'),
  ('/promises','Promises | NetaTrack India','Track political promises and their fulfilment'),
  ('/submit','Submit Report | NetaTrack India','Submit corruption reports for verification');

CREATE TABLE IF NOT EXISTS api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    key_hash VARCHAR(255) NOT NULL UNIQUE,
    permissions JSON DEFAULT NULL,
    user_id INT DEFAULT NULL,
    last_used TIMESTAMP NULL DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

SET FOREIGN_KEY_CHECKS = 1;
